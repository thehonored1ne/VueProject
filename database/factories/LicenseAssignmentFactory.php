<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\LicenseAssignment;
use App\Models\SoftwareLicense;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LicenseAssignment>
 */
class LicenseAssignmentFactory extends Factory
{
    protected $model = LicenseAssignment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'software_license_id' => SoftwareLicense::factory(),
            'user_id' => User::factory(),
            'assigned_at' => fake()->dateTimeBetween('-3 months', 'now'),
            'notes' => fake()->boolean(40) ? fake()->sentence() : null,
        ];
    }
}
