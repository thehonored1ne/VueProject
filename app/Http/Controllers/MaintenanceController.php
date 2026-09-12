<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Maintenance\CancelMaintenanceAction;
use App\Actions\Maintenance\CompleteMaintenanceAction;
use App\Actions\Maintenance\CreateMaintenanceAction;
use App\Enums\AssetStatus;
use App\Enums\MaintenanceStatus;
use App\Http\Requests\Maintenance\CompleteMaintenanceRequest;
use App\Http\Requests\Maintenance\StoreMaintenanceRequest;
use App\Models\Asset;
use App\Models\AssetMaintenance;
use App\Models\SoftwareLicense;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class MaintenanceController extends Controller
{
    /**
     * Display maintenance dashboard and expiration alerts.
     */
    public function index(): Response
    {
        $thresholdDate = now()->addDays(60)->toDateString();

        // 1. Active Maintenance Tickets
        $activeRepairs = AssetMaintenance::query()
            ->active()
            ->with(['asset', 'technician:id,name,email'])
            ->orderByRaw("CASE WHEN status = 'in_progress' THEN 1 ELSE 2 END")
            ->latest('started_at')
            ->latest('id')
            ->get();

        // 2. Recent Completed / History
        $recentCompleted = AssetMaintenance::query()
            ->whereIn('status', [MaintenanceStatus::Completed->value, MaintenanceStatus::Cancelled->value])
            ->with(['asset', 'technician:id,name,email'])
            ->latest('completed_at')
            ->latest('id')
            ->limit(10)
            ->get();

        // 3. Hardware Warranty Alerts (expired or expiring in <= 60 days)
        $warrantyAlerts = Asset::query()
            ->whereNotNull('warranty_expires_at')
            ->where('warranty_expires_at', '<=', $thresholdDate)
            ->where('status', '!=', AssetStatus::Retired->value)
            ->with(['currentAssignment.user:id,name,email'])
            ->orderBy('warranty_expires_at')
            ->limit(20)
            ->get()
            ->map(function (Asset $asset) {
                $expiresAt = $asset->warranty_expires_at;
                $days = $expiresAt ? (int) now()->startOfDay()->diffInDays($expiresAt->startOfDay(), false) : null;

                return [
                    'id' => $asset->id,
                    'asset_tag' => $asset->asset_tag,
                    'name' => $asset->name,
                    'type' => $asset->type->value,
                    'status' => $asset->status->value,
                    'model_number' => $asset->model_number,
                    'serial_number' => $asset->serial_number,
                    'warranty_expires_at' => $expiresAt?->format('Y-m-d'),
                    'days_until_expiration' => $days,
                    'is_expired' => $days !== null && $days < 0,
                    'assignee' => $asset->currentAssignment?->user?->name,
                ];
            });

        // 4. Software License Renewal Alerts (expired or expiring in <= 60 days)
        $licenseAlerts = SoftwareLicense::query()
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', $thresholdDate)
            ->withCount('assignments')
            ->orderBy('expires_at')
            ->limit(20)
            ->get()
            ->map(function (SoftwareLicense $license) {
                $expiresAt = $license->expires_at;
                $days = $expiresAt ? (int) now()->startOfDay()->diffInDays($expiresAt->startOfDay(), false) : null;

                return [
                    'id' => $license->id,
                    'name' => $license->name,
                    'vendor' => $license->vendor,
                    'seats_total' => $license->seats_total,
                    'seats_assigned' => $license->assignments_count,
                    'billing_cycle' => $license->billing_cycle->value,
                    'cost_per_seat' => $license->cost_per_seat,
                    'expires_at' => $expiresAt?->format('Y-m-d'),
                    'days_until_expiration' => $days,
                    'is_expired' => $days !== null && $days < 0,
                ];
            });

        // 5. High-level KPI Summary
        $metrics = [
            'active_repairs' => $activeRepairs->count(),
            'expiring_warranties' => Asset::query()
                ->whereNotNull('warranty_expires_at')
                ->where('warranty_expires_at', '<=', $thresholdDate)
                ->where('status', '!=', AssetStatus::Retired->value)
                ->count(),
            'expiring_licenses' => SoftwareLicense::query()
                ->whereNotNull('expires_at')
                ->where('expires_at', '<=', $thresholdDate)
                ->count(),
            'total_repair_costs' => (float) AssetMaintenance::query()
                ->where('status', MaintenanceStatus::Completed->value)
                ->sum('cost'),
        ];

        // 6. Available assets for the "Log Repair" dialog
        $availableAssets = Asset::query()
            ->where('status', '!=', AssetStatus::Retired->value)
            ->orderBy('asset_tag')
            ->get(['id', 'asset_tag', 'name', 'status']);

        return Inertia::render('Maintenance/Index', [
            'activeRepairs' => $activeRepairs,
            'recentCompleted' => $recentCompleted,
            'warrantyAlerts' => $warrantyAlerts,
            'licenseAlerts' => $licenseAlerts,
            'metrics' => $metrics,
            'availableAssets' => $availableAssets,
        ]);
    }

    /**
     * Log a new maintenance ticket.
     */
    public function store(StoreMaintenanceRequest $request, CreateMaintenanceAction $action): RedirectResponse
    {
        $action->execute($request->toDto(), $request->user());

        return back()->with('success', 'Maintenance job scheduled successfully.');
    }

    /**
     * Mark a maintenance job completed.
     */
    public function complete(CompleteMaintenanceRequest $request, AssetMaintenance $maintenance, CompleteMaintenanceAction $action): RedirectResponse
    {
        $action->execute(
            $maintenance,
            $request->filled('cost') ? (float) $request->validated('cost') : null,
            $request->validated('notes') ? (string) $request->validated('notes') : null
        );

        return back()->with('success', 'Maintenance job marked as completed and asset restored to Available.');
    }

    /**
     * Cancel a maintenance job.
     */
    public function cancel(Request $request, AssetMaintenance $maintenance, CancelMaintenanceAction $action): RedirectResponse
    {
        $action->execute($maintenance, $request->input('notes') ? (string) $request->input('notes') : null);

        return back()->with('success', 'Maintenance job cancelled.');
    }
}
