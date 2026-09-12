<?php

declare(strict_types=1);

namespace App\Actions\Assets;

use App\DTOs\Assets\AssetData;
use App\Models\Asset;

class UpdateAssetAction
{
    public function execute(Asset $asset, AssetData $data): Asset
    {
        $updatePayload = [
            'name' => $data->name,
            'type' => $data->type,
            'serial_number' => $data->serialNumber,
            'model_number' => $data->modelNumber,
            'cost' => $data->cost,
            'purchased_at' => $data->purchasedAt,
            'warranty_expires_at' => $data->warrantyExpiresAt,
            'notes' => $data->notes,
        ];

        if ($data->assetTag !== null) {
            $updatePayload['asset_tag'] = strtoupper($data->assetTag);
        }

        if ($data->status !== null) {
            $updatePayload['status'] = $data->status;
        }

        $asset->update($updatePayload);

        return $asset->fresh();
    }
}
