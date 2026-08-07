<?php

namespace App\Http\Controllers\Cabinet;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CabinetStatusController extends Controller
{
    /**
     * "Pending activation" screen shown to owners of a cabinet that has not
     * yet been activated (or has been suspended) by platform staff.
     */
    public function pending(Request $request): Response|RedirectResponse
    {
        $user = $request->user();

        if (! $user instanceof User || $user->cabinet?->isActive() === true) {
            return redirect()->route('dashboard');
        }

        $cabinet = $user->cabinet;

        return Inertia::render('auth/PendingActivation', [
            'cabinet' => $cabinet === null ? null : [
                'name' => $cabinet->name,
                'status' => $cabinet->status->value,
            ],
        ]);
    }

    /**
     * "Awaiting approval" screen shown to members who joined an existing
     * cabinet and are waiting for the owner to approve their account.
     */
    public function awaitingApproval(Request $request): Response|RedirectResponse
    {
        $user = $request->user();

        if (! $user instanceof User || ! $user->isPendingApproval()) {
            return redirect()->route('dashboard');
        }

        $cabinet = $user->cabinet;

        return Inertia::render('auth/AwaitingApproval', [
            'cabinet' => $cabinet === null ? null : [
                'name' => $cabinet->name,
            ],
        ]);
    }
}
