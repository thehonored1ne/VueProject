<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Assets\CreateAssetAction;
use App\Actions\Assets\DeleteAssetAction;
use App\Actions\Assets\UpdateAssetAction;
use App\Enums\AssetStatus;
use App\Enums\AssetType;
use App\Http\Requests\Assets\StoreAssetRequest;
use App\Http\Requests\Assets\UpdateAssetRequest;
use App\Models\Asset;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AssetController extends Controller
{
    /**
     * Display a listing of assets with summary metrics and filters.
     */
    public function index(Request $request): Response
    {
        $search = $request->string('search')->toString() ?: null;
        $status = $request->string('status')->toString() ?: null;
        $type = $request->string('type')->toString() ?: null;

        $assets = Asset::query()
            ->search($search)
            ->filterStatus($status)
            ->filterType($type)
            ->with(['currentAssignment.user'])
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        $totalAssets = Asset::count();
        $assignedCount = Asset::where('status', AssetStatus::Assigned)->count();

        $metrics = [
            'total' => $totalAssets,
            'assigned' => $assignedCount,
            'available' => Asset::where('status', AssetStatus::Available)->count(),
            'maintenance' => Asset::where('status', AssetStatus::Maintenance)->count(),
            'utilization_rate' => $totalAssets > 0 ? round(($assignedCount / $totalAssets) * 100, 1) : 0,
        ];

        $users = User::select(['id', 'name', 'email'])->orderBy('name')->get();

        return Inertia::render('Assets/Index', [
            'assets' => $assets,
            'metrics' => $metrics,
            'users' => $users,
            'filters' => [
                'search' => $search,
                'status' => $status,
                'type' => $type,
            ],
            'statuses' => collect(AssetStatus::cases())->map(fn (AssetStatus $s) => [
                'value' => $s->value,
                'label' => $s->label(),
                'color' => $s->badgeColor(),
            ]),
            'types' => collect(AssetType::cases())->map(fn (AssetType $t) => [
                'value' => $t->value,
                'label' => $t->label(),
            ]),
        ]);
    }

    /**
     * Show the form for creating a new asset.
     */
    public function create(): Response
    {
        return Inertia::render('Assets/Create', [
            'types' => collect(AssetType::cases())->map(fn (AssetType $t) => [
                'value' => $t->value,
                'label' => $t->label(),
            ]),
            'statuses' => collect(AssetStatus::cases())->map(fn (AssetStatus $s) => [
                'value' => $s->value,
                'label' => $s->label(),
            ]),
        ]);
    }

    /**
     * Store a newly created asset in storage.
     */
    public function store(StoreAssetRequest $request, CreateAssetAction $action): RedirectResponse
    {
        $asset = $action->execute($request->toDto());

        return redirect()->route('assets.show', $asset)
            ->with('success', "Asset {$asset->asset_tag} was registered successfully.");
    }

    /**
     * Display the specified asset with its custody assignment history.
     */
    public function show(Asset $asset): Response
    {
        $asset->load([
            'currentAssignment.user',
            'currentAssignment.assignedByUser',
            'assignments.user',
            'assignments.assignedByUser',
        ]);

        $users = User::select(['id', 'name', 'email'])->orderBy('name')->get();

        return Inertia::render('Assets/Show', [
            'asset' => $asset,
            'users' => $users,
        ]);
    }

    /**
     * Show the form for editing the specified asset.
     */
    public function edit(Asset $asset): Response
    {
        return Inertia::render('Assets/Edit', [
            'asset' => $asset,
            'types' => collect(AssetType::cases())->map(fn (AssetType $t) => [
                'value' => $t->value,
                'label' => $t->label(),
            ]),
        ]);
    }

    /**
     * Update the specified asset in storage.
     */
    public function update(UpdateAssetRequest $request, Asset $asset, UpdateAssetAction $action): RedirectResponse
    {
        $updated = $action->execute($asset, $request->toDto());

        return redirect()->route('assets.show', $updated)
            ->with('success', "Asset {$updated->asset_tag} was updated successfully.");
    }

    /**
     * Remove the specified asset from storage.
     */
    public function destroy(Asset $asset, DeleteAssetAction $action): RedirectResponse
    {
        $tag = $asset->asset_tag;
        $action->execute($asset);

        return redirect()->route('assets.index')
            ->with('success', "Asset {$tag} was deleted successfully.");
    }
}
