<?php

declare(strict_types=1);

namespace App\Actions\Maintenance;

use App\DTOs\Maintenance\MaintenanceData;
use App\Enums\AssetStatus;
use App\Models\Asset;
use App\Models\AssetMaintenance;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class CreateMaintenanceAction
{
    /**
     * Create a maintenance ticket and transition asset status to Maintenance.
     */
    public function execute(MaintenanceData $data, ?User $technician = null): AssetMaintenance
    {
        return DB::transaction(function () use ($data, $technician): AssetMaintenance {
            $asset = Asset::findOrFail($data->assetId);

            $maintenance = AssetMaintenance::create([
                'asset_id' => $asset->id,
                'user_id' => $technician?->id,
                'title' => $data->title,
                'provider' => $data->provider,
                'cost' => $data->cost,
                'status' => $data->status,
                'scheduled_at' => $data->scheduledAt,
                'started_at' => $data->startedAt ?? now()->toDateString(),
                'notes' => $data->notes,
            ]);

            // Transition asset to maintenance state
            $asset->update([
                'status' => AssetStatus::Maintenance,
            ]);

            return $maintenance;
        });
    }
}
