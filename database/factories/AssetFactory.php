<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\AssetStatus;
use App\Enums\AssetType;
use App\Models\Asset;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Asset>
 */
class AssetFactory extends Factory
{
    protected $model = Asset::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(AssetType::cases());
        $names = match ($type) {
            AssetType::Laptop => ['MacBook Pro 16" M3 Max', 'MacBook Air 15" M2', 'Dell XPS 15 9530', 'Lenovo ThinkPad X1 Carbon Gen 11'],
            AssetType::Desktop => ['Mac Studio M2 Ultra', 'Dell Precision 3660 Workstation', 'HP Z2 Mini G9'],
            AssetType::Monitor => ['Dell UltraSharp U2723QE 27"', 'LG UltraFine 32UN880 4K', 'Apple Studio Display 27"'],
            AssetType::Mobile => ['iPhone 15 Pro 256GB', 'Google Pixel 8 Pro', 'iPad Pro 12.9" M2'],
            AssetType::Server => ['Dell PowerEdge R660', 'HPE ProLiant DL380 Gen11'],
            AssetType::Peripheral => ['Apple Magic Keyboard & Trackpad', 'Logitech MX Master 3S', 'CalDigit TS4 Thunderbolt Dock'],
        };

        return [
            'asset_tag' => 'AST-'.fake()->unique()->numerify('#####'),
            'name' => fake()->randomElement($names),
            'type' => $type,
            'status' => AssetStatus::Available,
            'serial_number' => fake()->unique()->bothify('SN-???-#####'),
            'model_number' => fake()->bothify('MDL-####'),
            'cost' => fake()->randomFloat(2, 250, 4500),
            'purchased_at' => fake()->dateTimeBetween('-2 years', 'now')->format('Y-m-d'),
            'warranty_expires_at' => fake()->dateTimeBetween('now', '+3 years')->format('Y-m-d'),
            'notes' => fake()->boolean(60) ? fake()->sentence() : null,
        ];
    }

    public function assigned(): static
    {
        return $this->state(fn () => [
            'status' => AssetStatus::Assigned,
        ]);
    }

    public function maintenance(): static
    {
        return $this->state(fn () => [
            'status' => AssetStatus::Maintenance,
        ]);
    }

    public function retired(): static
    {
        return $this->state(fn () => [
            'status' => AssetStatus::Retired,
        ]);
    }
}
