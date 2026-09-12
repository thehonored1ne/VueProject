<?php

declare(strict_types=1);

namespace App\Actions\Assets;

use App\DTOs\Assets\AssetData;
use App\Enums\AssetStatus;
use App\Models\Asset;
use Illuminate\Support\Str;

class CreateAssetAction
{
    public function execute(AssetData $data): Asset
    {
        $assetTag = $data->assetTag ?: $this->generateUniqueAssetTag();

        return Asset::create([
            'asset_tag' => strtoupper($assetTag),
            'name' => $data->name,
            'type' => $data->type,
            'status' => $data->status ?? AssetStatus::Available,
            'serial_number' => $data->serialNumber,
            'model_number' => $data->modelNumber,
            'cost' => $data->cost,
            'purchased_at' => $data->purchasedAt,
            'warranty_expires_at' => $data->warrantyExpiresAt,
            'notes' => $data->notes,
        ]);
    }

    private function generateUniqueAssetTag(): string
    {
        do {
            $tag = 'AST-'.strtoupper(Str::random(6));
        } while (Asset::where('asset_tag', $tag)->exists());

        return $tag;
    }
}
