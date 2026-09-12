<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AssetAssignment>
 */
class AssetAssignmentFactory extends Factory
{
    protected $model = AssetAssignment::class;

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
            'assigned_by' => User::factory(),
            'assigned_at' => fake()->dateTimeBetween('-6 months', '-1 month'),
            'expected_return_at' => fake()->dateTimeBetween('+1 month', '+6 months')->format('Y-m-d'),
            'returned_at' => null,
            'condition_on_assignment' => 'Pristine condition, brand new deployment.',
            'condition_on_return' => null,
            'notes' => fake()->boolean(40) ? fake()->sentence() : null,
        ];
    }

    public function returned(): static
    {
        return $this->state(fn () => [
            'returned_at' => now(),
            'condition_on_return' => 'Good condition, light normal wear.',
        ]);
    }
}
