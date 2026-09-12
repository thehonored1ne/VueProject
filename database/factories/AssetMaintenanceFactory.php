<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\MaintenanceStatus;
use App\Models\Asset;
use App\Models\AssetMaintenance;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AssetMaintenance>
 */
final class AssetMaintenanceFactory extends Factory
{
    protected $model = AssetMaintenance::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'asset_id' => Asset::factory(),
            'user_id' => User::factory(),
            'title' => fake()->randomElement([
                'Keyboard & Trackpad Replacement',
                'Battery Swelling Diagnostic',
                'Display Panel Glitch Repair',
                'Internal Fan Cleaning & Thermal Paste',
                'Power Supply Unit Overhaul',
                'RAM Upgrade & SSD Firmware Flash',
            ]),
            'provider' => fake()->randomElement([
                'Apple Authorized Service Provider',
                'Dell ProSupport On-Site',
                'Lenovo Premier Support',
                'In-House IT Workshop',
            ]),
            'cost' => fake()->randomFloat(2, 45, 650),
            'status' => fake()->randomElement(MaintenanceStatus::cases()),
            'scheduled_at' => now()->subDays(fake()->numberBetween(1, 15)),
            'started_at' => now()->subDays(fake()->numberBetween(1, 5)),
            'completed_at' => null,
            'notes' => fake()->sentence(),
        ];
    }

    public function inProgress(): static
    {
        return $this->state(fn () => [
            'status' => MaintenanceStatus::InProgress,
            'completed_at' => null,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'status' => MaintenanceStatus::Completed,
            'completed_at' => now(),
        ]);
    }
}
