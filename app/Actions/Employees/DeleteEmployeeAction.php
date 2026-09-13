<?php

declare(strict_types=1);

namespace App\Actions\Employees;

use App\Models\User;
use Illuminate\Validation\ValidationException;

class DeleteEmployeeAction
{
    public function execute(User $user, ?User $actor = null): bool
    {
        if ($actor && $user->id === $actor->id) {
            throw ValidationException::withMessages([
                'user' => 'You cannot delete your own account.',
            ]);
        }

        if ($user->activeAssetAssignments()->exists()) {
            throw ValidationException::withMessages([
                'user' => "Cannot delete employee {$user->name} because they currently hold active hardware in custody. Please offboard the employee first.",
            ]);
        }

        if ($user->licenseAssignments()->exists()) {
            throw ValidationException::withMessages([
                'user' => "Cannot delete employee {$user->name} because they hold active software license seats. Please offboard the employee first.",
            ]);
        }

        return (bool) $user->delete();
    }
}
