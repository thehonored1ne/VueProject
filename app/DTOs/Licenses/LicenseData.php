<?php

declare(strict_types=1);

namespace App\DTOs\Licenses;

use App\Enums\BillingCycle;

readonly class LicenseData
{
    public function __construct(
        public string $name,
        public string $vendor,
        public int $seatsTotal,
        public BillingCycle $billingCycle,
        public ?string $licenseKey = null,
        public ?float $costPerSeat = null,
        public ?string $expiresAt = null,
        public ?string $notes = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: (string) $data['name'],
            vendor: (string) $data['vendor'],
            seatsTotal: (int) $data['seats_total'],
            billingCycle: $data['billing_cycle'] instanceof BillingCycle
                ? $data['billing_cycle']
                : BillingCycle::from((string) $data['billing_cycle']),
            licenseKey: isset($data['license_key']) && is_string($data['license_key']) ? $data['license_key'] : null,
            costPerSeat: isset($data['cost_per_seat']) && is_numeric($data['cost_per_seat']) ? (float) $data['cost_per_seat'] : null,
            expiresAt: isset($data['expires_at']) && is_string($data['expires_at']) ? $data['expires_at'] : null,
            notes: isset($data['notes']) && is_string($data['notes']) ? $data['notes'] : null,
        );
    }
}
