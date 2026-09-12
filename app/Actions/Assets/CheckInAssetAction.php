<?php

declare(strict_types=1);

namespace App\Actions\Assets;

use App\DTOs\Assets\CheckInAssetData;
use App\Enums\AssetStatus;
use App\Models\Asset;
use App\Models\AssetAssignment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CheckInAssetAction
{
    public function execute(Asset $asset, CheckInAssetData $data): AssetAssignment
    {
        $currentAssignment = $asset->currentAssignment;

        if (! $currentAssignment) {
            throw ValidationException::withMessages([
                'asset' => "Asset {$asset->asset_tag} does not have an active assignment to check in.",
            ]);
        }

        return DB::transaction(function () use ($asset, $currentAssignment, $data) {
            $currentAssignment->update([
                'returned_at' => now(),
                'condition_on_return' => $data->conditionOnReturn ?? 'Returned in normal working order.',
                'notes' => $data->notes
                    ? ($currentAssignment->notes ? $currentAssignment->notes."\n[Check-in]: ".$data->notes : $data->notes)
                    : $currentAssignment->notes,
            ]);

            $newStatus = in_array($data->targetStatus, [AssetStatus::Available, AssetStatus::Maintenance], true)
                ? $data->targetStatus
                : AssetStatus::Available;

            $asset->update([
                'status' => $newStatus,
            ]);

            return $currentAssignment->fresh();
        });
    }
}
