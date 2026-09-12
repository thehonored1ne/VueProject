<?php

declare(strict_types=1);

namespace App\Actions\Licenses;

use App\Models\SoftwareLicense;
use Illuminate\Validation\ValidationException;

class DeleteLicenseAction
{
    public function execute(SoftwareLicense $license): bool
    {
        $assignedCount = $license->assignments()->count();

        if ($assignedCount > 0) {
            throw ValidationException::withMessages([
                'license' => "Cannot delete license {$license->name} because {$assignedCount} seats are currently assigned. Revoke all seats first.",
            ]);
        }

        return (bool) $license->delete();
    }
}
