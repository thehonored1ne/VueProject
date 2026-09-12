<?php

declare(strict_types=1);

namespace App\Actions\Assets;

use App\Models\Asset;
use Illuminate\Validation\ValidationException;

class DeleteAssetAction
{
    public function execute(Asset $asset): bool
    {
        if ($asset->currentAssignment()->exists()) {
            throw ValidationException::withMessages([
                'asset' => "Asset {$asset->asset_tag} is currently checked out to an employee and cannot be deleted.",
            ]);
        }

        return (bool) $asset->delete();
    }
}
