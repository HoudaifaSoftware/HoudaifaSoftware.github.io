<?php

namespace App\Services\Cabinet;

use App\Models\User;

/**
 * Shared eligibility rules deciding whether an authenticated cabinet member is
 * allowed to use the application, used by both the web middleware
 * (EnsureCabinetIsActive) and the Sanctum API. Keeping the rules in one place
 * guarantees the web and API surfaces gate access identically.
 */
class CabinetAccessService
{
    public const REASON_CABINET_PENDING = 'cabinet_pending';

    public const REASON_CABINET_SUSPENDED = 'cabinet_suspended';

    public const REASON_AWAITING_APPROVAL = 'awaiting_approval';

    /**
     * Whether the user may access cabinet-scoped resources. Platform staff and
     * legacy accounts without a cabinet are always eligible.
     */
    public function isEligible(User $user): bool
    {
        return $this->denialReason($user) === null;
    }

    /**
     * The machine-readable reason a user is blocked, or null when eligible.
     */
    public function denialReason(User $user): ?string
    {
        if ($user->is_platform_admin) {
            return null;
        }

        $cabinet = $user->cabinet;

        // Legacy / unscoped accounts have no tenant gate.
        if ($cabinet === null) {
            return null;
        }

        if ($cabinet->isPending()) {
            return self::REASON_CABINET_PENDING;
        }

        if (! $cabinet->isActive()) {
            return self::REASON_CABINET_SUSPENDED;
        }

        if ($user->isPendingApproval()) {
            return self::REASON_AWAITING_APPROVAL;
        }

        return null;
    }

    /**
     * A human-readable French message matching the denial reason.
     */
    public function denialMessage(User $user): ?string
    {
        return match ($this->denialReason($user)) {
            self::REASON_CABINET_PENDING => "Votre cabinet est en attente d'activation par l'équipe MediSmart.",
            self::REASON_CABINET_SUSPENDED => 'Votre cabinet est actuellement suspendu. Contactez le support MediSmart.',
            self::REASON_AWAITING_APPROVAL => "Votre compte est en attente d'approbation par le propriétaire du cabinet.",
            default => null,
        };
    }
}
