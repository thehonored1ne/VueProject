<?php

declare(strict_types=1);

namespace App\Actions\Licenses;

use App\DTOs\Licenses\LicenseData;
use App\Models\SoftwareLicense;
use Illuminate\Validation\ValidationException;

class UpdateLicenseAction
{
    public function execute(SoftwareLicense $license, LicenseData $data): SoftwareLicense
    {
        $currentAssigned = $license->assignments()->count();
        if ($data->seatsTotal < $currentAssigned) {
            throw ValidationException::withMessages([
                'seats_total' => "Cannot reduce total seats to {$data->seatsTotal} because {$currentAssigned} seats are currently assigned to employees.",
            ]);
        }

        $license->update([
            'name' => $data->name,
            'vendor' => $data->vendor,
            'seats_total' => $data->seatsTotal,
            'cost_per_seat' => $data->costPerSeat,
            'billing_cycle' => $data->billingCycle,
            'license_key' => $data->licenseKey,
            'expires_at' => $data->expiresAt,
            'notes' => $data->notes,
        ]);

        return $license->fresh();
    }
}
