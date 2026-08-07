<?php

namespace App\Actions\Cabinet;

use App\Models\AuditLog;
use App\Models\Cabinet;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Registers a prospective staff member against an existing active cabinet,
 * identified by its owner's e-mail address. The new member is created in the
 * pending-approval state (no role, approved_at null) and reserves a seat.
 *
 * Shared by the web JoinCabinetController and the Sanctum API so the seat-limit
 * and eligibility rules never diverge.
 */
class JoinCabinetAction
{
    /**
     * @param  array{name: string, email: string, password: string, owner_email: string}  $data
     */
    public function execute(array $data): User
    {
        $owner = User::query()
            ->whereHas('cabinet', fn ($query) => $query->where('status', 'active'))
            ->where('email', $data['owner_email'])
            ->first();

        $cabinet = $owner?->cabinet;

        if ($owner === null || $cabinet === null || ! $cabinet->isActive()) {
            throw ValidationException::withMessages([
                'owner_email' => "Aucun cabinet actif n'a été trouvé pour cette adresse e-mail.",
            ]);
        }

        // Seats include both approved and pending members.
        if (! $cabinet->hasAvailableSeat()) {
            throw ValidationException::withMessages([
                'owner_email' => 'Ce cabinet a atteint sa limite de '.Cabinet::MAX_SEATS.' utilisateurs.',
            ]);
        }

        return DB::transaction(function () use ($data, $cabinet): User {
            $member = User::query()->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'cabinet_id' => $cabinet->getKey(),
            ]);
            $member->forceFill([
                'email_verified_at' => now(),
                'approved_at' => null,
            ])->save();

            AuditLog::record('cabinet.join_requested', $member, [
                'cabinet_id' => $cabinet->getKey(),
            ], $member->getKey());

            return $member;
        });
    }
}
