<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\BillingCycle;
use App\Models\SoftwareLicense;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SoftwareLicense>
 */
class SoftwareLicenseFactory extends Factory
{
    protected $model = SoftwareLicense::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $software = [
            ['name' => 'GitHub Copilot Business', 'vendor' => 'GitHub / Microsoft', 'cost' => 19.00, 'cycle' => BillingCycle::Monthly],
            ['name' => 'JetBrains All Products Pack', 'vendor' => 'JetBrains', 'cost' => 779.00, 'cycle' => BillingCycle::Yearly],
            ['name' => 'Figma Organization', 'vendor' => 'Figma', 'cost' => 45.00, 'cycle' => BillingCycle::Monthly],
            ['name' => '1Password Business', 'vendor' => '1Password', 'cost' => 7.99, 'cycle' => BillingCycle::Monthly],
            ['name' => 'Slack Business+', 'vendor' => 'Slack Technologies', 'cost' => 12.50, 'cycle' => BillingCycle::Monthly],
            ['name' => 'Sublime Text 4 Dev License', 'vendor' => 'Sublime HQ', 'cost' => 99.00, 'cycle' => BillingCycle::Perpetual],
        ];

        $choice = fake()->randomElement($software);

        return [
            'name' => $choice['name'],
            'vendor' => $choice['vendor'],
            'license_key' => fake()->boolean(80) ? fake()->bothify('LIC-????-####-????-####') : null,
            'seats_total' => fake()->numberBetween(5, 30),
            'cost_per_seat' => $choice['cost'],
            'billing_cycle' => $choice['cycle'],
            'expires_at' => $choice['cycle'] === BillingCycle::Perpetual ? null : fake()->dateTimeBetween('+1 month', '+1 year')->format('Y-m-d'),
            'notes' => fake()->boolean(50) ? fake()->sentence() : null,
        ];
    }
}
