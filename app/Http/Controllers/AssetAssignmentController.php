<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Assets\AssignAssetAction;
use App\Actions\Assets\CheckInAssetAction;
use App\Http\Requests\Assets\AssignAssetRequest;
use App\Http\Requests\Assets\CheckInAssetRequest;
use App\Models\Asset;
use Illuminate\Http\RedirectResponse;

class AssetAssignmentController extends Controller
{
    /**
     * Assign / Check out the asset to a user.
     */
    public function store(AssignAssetRequest $request, Asset $asset, AssignAssetAction $action): RedirectResponse
    {
        $action->execute(
            asset: $asset,
            data: $request->toDto(),
            assignedBy: $request->user(),
        );

        return back()->with('success', "Asset {$asset->asset_tag} was assigned successfully.");
    }

    /**
     * Check in / Return the asset.
     */
    public function update(CheckInAssetRequest $request, Asset $asset, CheckInAssetAction $action): RedirectResponse
    {
        $action->execute(
            asset: $asset,
            data: $request->toDto(),
        );

        return back()->with('success', "Asset {$asset->asset_tag} was checked in successfully.");
    }
}
