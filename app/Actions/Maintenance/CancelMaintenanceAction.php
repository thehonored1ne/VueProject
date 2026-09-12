<?php

declare(strict_types=1);

namespace App\Actions\Maintenance;

use App\Enums\AssetStatus;
use App\Enums\MaintenanceStatus;
use App\Models\Asset;
use App\Models\AssetMaintenance;
use Illuminate\Support\Facades\DB;

final class CancelMaintenanceAction
{
    /**
     * Cancel a maintenance ticket and restore asset status if needed.
     */
    public function execute(AssetMaintenance $maintenance, ?string $reason = null): AssetMaintenance
    {
        return DB::transaction(function () use ($maintenance, $reason): AssetMaintenance {
            $notes = $maintenance->notes;
            if ($reason) {
                $notes = $notes ? "{$notes}\n\nCancellation Reason: {$reason}" : "Cancellation Reason: {$reason}";
            }

            $maintenance->update([
                'status' => MaintenanceStatus::Cancelled,
                'notes' => $notes,
            ]);

            // Restore asset to available status if no other active maintenance tickets exist
            $asset = Asset::findOrFail($maintenance->asset_id);
            $hasOtherActiveMaintenance = AssetMaintenance::query()
                ->where('asset_id', $asset->id)
                ->where('id', '!=', $maintenance->id)
                ->active()
                ->exists();

            if (! $hasOtherActiveMaintenance && $asset->status === AssetStatus::Maintenance) {
                $asset->update([
                    'status' => AssetStatus::Available,
                ]);
            }

            return $maintenance;
        });
    }
}
