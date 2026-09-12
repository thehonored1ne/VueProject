<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\AssetStatus;
use App\Enums\MaintenanceStatus;
use App\Models\Asset;
use App\Models\AssetMaintenance;
use App\Models\User;
use Illuminate\Database\Seeder;

final class AssetMaintenanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::first();
        if (! $admin) {
            return;
        }

        $assets = Asset::all();
        if ($assets->isEmpty()) {
            return;
        }

        // 1. One active in_progress repair
        $inProgressAsset = $assets->where('status', AssetStatus::Available)->first();
        if ($inProgressAsset) {
            AssetMaintenance::create([
                'asset_id' => $inProgressAsset->id,
                'user_id' => $admin->id,
                'title' => 'MacBook Retina Display Backlight Glitch',
                'provider' => 'Apple Authorized Service Provider',
                'cost' => 380.00,
                'status' => MaintenanceStatus::InProgress,
                'started_at' => now()->subDays(3)->toDateString(),
                'notes' => 'Display flickering intermittently when tilted past 90 degrees.',
            ]);

            $inProgressAsset->update(['status' => AssetStatus::Maintenance]);
        }

        // 2. One scheduled repair
        $scheduledAsset = $assets->where('status', AssetStatus::Available)->skip(1)->first();
        if ($scheduledAsset) {
            AssetMaintenance::create([
                'asset_id' => $scheduledAsset->id,
                'user_id' => $admin->id,
                'title' => 'Preventative Thermal Paste Repaste & Internal Fan Dusting',
                'provider' => 'In-House IT Workshop',
                'cost' => 45.00,
                'status' => MaintenanceStatus::Scheduled,
                'scheduled_at' => now()->addDays(2)->toDateString(),
                'started_at' => null,
                'notes' => 'High thermal readings under peak build compile workloads.',
            ]);
        }

        // 3. Two completed historical repairs
        $completedAsset1 = $assets->first();
        if ($completedAsset1) {
            AssetMaintenance::create([
                'asset_id' => $completedAsset1->id,
                'user_id' => $admin->id,
                'title' => 'Keycap Mechanism & Membrane Swap (Spacebar Sticky)',
                'provider' => 'Dell ProSupport On-Site',
                'cost' => 120.00,
                'status' => MaintenanceStatus::Completed,
                'started_at' => now()->subDays(45)->toDateString(),
                'completed_at' => now()->subDays(43)->toDateString(),
                'notes' => 'Technician replaced upper top case assembly under warranty.',
            ]);
        }
    }
}
