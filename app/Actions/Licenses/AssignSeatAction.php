<?php

declare(strict_types=1);

namespace App\Actions\Licenses;

use App\DTOs\Licenses\AssignSeatData;
use App\Models\LicenseAssignment;
use App\Models\SoftwareLicense;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AssignSeatAction
{
    public function execute(SoftwareLicense $license, AssignSeatData $data): LicenseAssignment
    {
        if ($license->assignments()->where('user_id', $data->userId)->exists()) {
            throw ValidationException::withMessages([
                'user_id' => 'This employee already has an active seat assigned for this software license.',
            ]);
        }

        return DB::transaction(function () use ($license, $data) {
            $currentAssigned = $license->assignments()->lockForUpdate()->count();

            if ($currentAssigned >= $license->seats_total) {
                throw ValidationException::withMessages([
                    'license' => "All {$license->seats_total} seats for {$license->name} are currently occupied. Increase total seats to allocate more.",
                ]);
            }

            return $license->assignments()->create([
                'user_id' => $data->userId,
                'assigned_at' => now(),
                'notes' => $data->notes,
            ]);
        });
    }
}
