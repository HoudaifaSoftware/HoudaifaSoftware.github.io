<?php

namespace App\Services;

use App\Enums\CabinetStatus;
use App\Mail\CabinetActivatedMail;
use App\Models\AuditLog;
use App\Models\Cabinet;
use App\Models\License;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

/**
 * Server-side cabinet lifecycle operations used by the Filament back office
 * (and reusable from a future API). One-time activation mints a perpetual,
 * server-issued licence with no expiry and links it to the cabinet.
 */
class CabinetFulfillmentService
{
    /**
     * Activate a pending cabinet: issue a perpetual licence, link it, flip the
     * status to active and notify the owner. Idempotent for already-active
     * cabinets (returns without re-issuing).
     */
    public function activate(Cabinet $cabinet): Cabinet
    {
        if ($cabinet->isActive()) {
            return $cabinet;
        }

        $cabinet = DB::transaction(function () use ($cabinet): Cabinet {
            $license = $this->issueLicense($cabinet);

            $cabinet->forceFill([
                'status' => CabinetStatus::ACTIVE,
                'activated_at' => now(),
                'license_id' => $license->getKey(),
            ])->save();

            AuditLog::record('cabinet.activated', $cabinet, [
                'license_id' => $license->license_id,
                'owner_user_id' => $cabinet->owner_user_id,
            ], $cabinet->owner_user_id);

            return $cabinet;
        });

        $this->notifyOwner($cabinet);

        return $cabinet;
    }

    public function suspend(Cabinet $cabinet): Cabinet
    {
        $cabinet->forceFill(['status' => CabinetStatus::SUSPENDED])->save();
        AuditLog::record('cabinet.suspended', $cabinet, [
            'owner_user_id' => $cabinet->owner_user_id,
        ], $cabinet->owner_user_id);

        return $cabinet;
    }

    public function reactivate(Cabinet $cabinet): Cabinet
    {
        $cabinet->forceFill([
            'status' => CabinetStatus::ACTIVE,
            'activated_at' => $cabinet->activated_at ?? now(),
        ])->save();
        AuditLog::record('cabinet.reactivated', $cabinet, [
            'owner_user_id' => $cabinet->owner_user_id,
        ], $cabinet->owner_user_id);

        return $cabinet;
    }

    /**
     * Mint a perpetual, server-issued licence record for the cabinet. The
     * signed_certificate stays empty (populated to '' by the License model);
     * the central server treats the DB row itself as the authority, unlike the
     * desktop client which verifies a signed certificate.
     */
    private function issueLicense(Cabinet $cabinet): License
    {
        return License::query()->create([
            'license_id' => 'CAB-'.$cabinet->getKey().'-'.Str::upper(Str::random(10)),
            'product' => (string) config('medismart.licensing.product', config('app.name', 'ClickDZ')),
            'edition' => 'hosted',
            'customer_id' => (string) $cabinet->getKey(),
            'status' => 'active',
            'issued_at' => now(),
            'expires_at' => null,
            'offline_grace_until' => null,
            'last_verified_at' => now(),
            'last_server_response' => [
                'source' => 'central_fulfillment',
                'cabinet_id' => $cabinet->getKey(),
                'one_time_activation' => true,
            ],
        ]);
    }

    private function notifyOwner(Cabinet $cabinet): void
    {
        $owner = $cabinet->owner;

        if ($owner === null || blank($owner->email)) {
            return;
        }

        try {
            Mail::to($owner->email)->send(new CabinetActivatedMail($cabinet, $owner->name));
        } catch (Throwable $exception) {
            // Never fail activation because mail is misconfigured.
            Log::warning('Cabinet activation e-mail could not be sent.', [
                'cabinet_id' => $cabinet->getKey(),
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
