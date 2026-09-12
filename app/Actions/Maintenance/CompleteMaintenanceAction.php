<?php

declare(strict_types=1);

namespace App\Actions\Maintenance;

use App\Enums\AssetStatus;
use App\Enums\MaintenanceStatus;
use App\Models\Asset;
use App\Models\AssetMaintenance;
use Illuminate\Support\Facades\DB;

final class CompleteMaintenanceAction
{
    /**
     * Resolve a maintenance ticket and transition asset back to Available.
     */
    public function execute(AssetMaintenance $maintenance, ?float $finalCost = null, ?string $resolutionNotes = null): AssetMaintenance
    {
        return DB::transaction(function () use ($maintenance, $finalCost, $resolutionNotes): AssetMaintenance {
            $notes = $maintenance->notes;
            if ($resolutionNotes) {
                $notes = $notes ? "{$notes}\n\nResolution: {$resolutionNotes}" : "Resolution: {$resolutionNotes}";
            }

            $maintenance->update([
                'status' => MaintenanceStatus::Completed,
                'cost' => $finalCost ?? $maintenance->cost,
                'completed_at' => now()->toDateString(),
                'notes' => $notes,
            ]);

            // Restore asset to available status if no other active maintenance tickets exist
            $asset = Asset::findOrFail($maintenance->asset_id);
            $hasOtherActiveMaintenance = AssetMaintenance::query()
                ->where('asset_id', $asset->id)
                ->where('id', '!=', $maintenance->id)
                ->active()
                ->exists();

            if (! $hasOtherActiveMaintenance) {
                $asset->update([
                    'status' => AssetStatus::Available,
                ]);
            }

            return $maintenance;
        });
    }
}
