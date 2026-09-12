<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Licenses\AssignSeatAction;
use App\Actions\Licenses\RevokeSeatAction;
use App\Http\Requests\Licenses\AssignSeatRequest;
use App\Models\SoftwareLicense;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class LicenseAssignmentController extends Controller
{
    /**
     * Allocate a seat on this license to a user.
     */
    public function store(AssignSeatRequest $request, SoftwareLicense $license, AssignSeatAction $action): RedirectResponse
    {
        $action->execute(
            license: $license,
            data: $request->toDto(),
        );

        return back()->with('success', 'Seat was allocated successfully.');
    }

    /**
     * Revoke an assigned seat from an employee.
     */
    public function destroy(SoftwareLicense $license, User $user, RevokeSeatAction $action): RedirectResponse
    {
        $action->execute(
            license: $license,
            user: $user,
        );

        return back()->with('success', "Seat for {$user->name} was revoked successfully.");
    }
}
