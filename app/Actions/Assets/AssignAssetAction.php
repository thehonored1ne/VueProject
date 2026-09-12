<?php

declare(strict_types=1);

namespace App\Actions\Assets;

use App\DTOs\Assets\AssignAssetData;
use App\Enums\AssetStatus;
use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AssignAssetAction
{
    public function execute(Asset $asset, AssignAssetData $data, ?User $assignedBy = null): AssetAssignment
    {
        if ($asset->status !== AssetStatus::Available) {
            throw ValidationException::withMessages([
                'asset' => "Asset {$asset->asset_tag} is currently {$asset->status->value} and cannot be assigned.",
            ]);
        }

        return DB::transaction(function () use ($asset, $data, $assignedBy) {
            $assignment = $asset->assignments()->create([
                'user_id' => $data->userId,
                'assigned_by' => $assignedBy?->id,
                'assigned_at' => now(),
                'expected_return_at' => $data->expectedReturnAt,
                'condition_on_assignment' => $data->conditionOnAssignment ?? 'Standard deployment condition.',
                'notes' => $data->notes,
            ]);

            $asset->update([
                'status' => AssetStatus::Assigned,
            ]);

            return $assignment;
        });
    }
}
