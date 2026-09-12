<?php

declare(strict_types=1);

namespace App\Actions\Licenses;

use App\DTOs\Licenses\LicenseData;
use App\Models\SoftwareLicense;

class CreateLicenseAction
{
    public function execute(LicenseData $data): SoftwareLicense
    {
        return SoftwareLicense::create([
            'name' => $data->name,
            'vendor' => $data->vendor,
            'seats_total' => $data->seatsTotal,
            'cost_per_seat' => $data->costPerSeat,
            'billing_cycle' => $data->billingCycle,
            'license_key' => $data->licenseKey,
            'expires_at' => $data->expiresAt,
            'notes' => $data->notes,
        ]);
    }
}
