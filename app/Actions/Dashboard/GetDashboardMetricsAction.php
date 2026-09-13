<?php

declare(strict_types=1);

namespace App\Actions\Dashboard;

use App\Enums\AssetStatus;
use App\Enums\AssetType;
use App\Enums\BillingCycle;
use App\Enums\MaintenanceStatus;
use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\AssetMaintenance;
use App\Models\LicenseAssignment;
use App\Models\SoftwareLicense;
use Carbon\Carbon;

final class GetDashboardMetricsAction
{
    /**
     * Gather consolidated executive metrics, urgent queues, and activity feeds.
     *
     * @return array<string, mixed>
     */
    public function execute(): array
    {
        $now = now();
        $today = $now->toDateString();
        $urgentThreshold = $now->copy()->addDays(30)->toDateString();

        // 1. Hardware Inventory Aggregations
        $totalAssets = Asset::query()->count();
        $totalHardwareValue = (float) Asset::query()->sum('cost');

        /** @var array<string, int> $statusCounts */
        $rawStatusCounts = Asset::query()
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->all();

        $statusCounts = [
            'available' => (int) ($rawStatusCounts[AssetStatus::Available->value] ?? 0),
            'assigned' => (int) ($rawStatusCounts[AssetStatus::Assigned->value] ?? 0),
            'maintenance' => (int) ($rawStatusCounts[AssetStatus::Maintenance->value] ?? 0),
            'retired' => (int) ($rawStatusCounts[AssetStatus::Retired->value] ?? 0),
        ];

        $hardwareUtilizationRate = $totalAssets > 0
            ? round(($statusCounts['assigned'] / $totalAssets) * 100, 1)
            : 0.0;

        // Hardware Categories Breakdown
        $rawCategories = Asset::query()
            ->selectRaw('type, count(*) as total, sum(case when status = ? then 1 else 0 end) as deployed', [
                AssetStatus::Assigned->value,
            ])
            ->groupBy('type')
            ->get();

        $categoryBreakdown = $rawCategories->map(function ($row) use ($totalAssets) {
            $typeValue = $row->type instanceof AssetType ? $row->type->value : (string) $row->type;
            $typeEnum = $row->type instanceof AssetType ? $row->type : AssetType::tryFrom($typeValue);
            $total = (int) $row->total;
            $deployed = (int) $row->deployed;

            return [
                'type' => $typeValue,
                'label' => $typeEnum?->label() ?? ucfirst($typeValue),
                'total' => $total,
                'deployed' => $deployed,
                'percentage' => $totalAssets > 0 ? round(($total / $totalAssets) * 100, 1) : 0.0,
                'deployment_rate' => $total > 0 ? round(($deployed / $total) * 100, 1) : 0.0,
            ];
        })->sortByDesc('total')->values()->all();

        // 2. Software License Aggregations
        $totalLicenses = SoftwareLicense::query()->count();
        $totalSeats = (int) SoftwareLicense::query()->sum('seats_total');
        $allocatedSeats = LicenseAssignment::query()->count();
        $availableSeats = max(0, $totalSeats - $allocatedSeats);
        $seatUtilizationRate = $totalSeats > 0 ? round(($allocatedSeats / $totalSeats) * 100, 1) : 0.0;

        $licenses = SoftwareLicense::query()->get(['seats_total', 'cost_per_seat', 'billing_cycle']);
        $annualSoftwareSpend = 0.0;
        foreach ($licenses as $lic) {
            $seatCost = (float) $lic->cost_per_seat * (int) $lic->seats_total;
            if ($lic->billing_cycle === BillingCycle::Monthly) {
                $annualSoftwareSpend += $seatCost * 12;
            } else {
                $annualSoftwareSpend += $seatCost;
            }
        }

        // 3. Maintenance Aggregations
        $activeRepairsCount = AssetMaintenance::query()->active()->count();
        $totalRepairSpend = (float) AssetMaintenance::query()
            ->where('status', MaintenanceStatus::Completed->value)
            ->sum('cost');

        // 4. Counts for Urgent Attention
        $overdueReturnsCount = AssetAssignment::query()
            ->whereNull('returned_at')
            ->where('expected_return_at', '<', $today)
            ->count();

        $expiringWarrantiesCount = Asset::query()
            ->whereNotNull('warranty_expires_at')
            ->where('warranty_expires_at', '<=', $urgentThreshold)
            ->where('status', '!=', AssetStatus::Retired->value)
            ->count();

        $expiringLicensesCount = SoftwareLicense::query()
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', $urgentThreshold)
            ->count();

        $urgentTotalCount = $overdueReturnsCount + $expiringWarrantiesCount + $expiringLicensesCount + $activeRepairsCount;

        // 5. Build Urgent Attention Items List
        $urgentItems = $this->buildUrgentItems($today, $urgentThreshold);

        // 6. Build Recent Activity Feed
        $recentActivity = $this->buildRecentActivity();

        return [
            'metrics' => [
                'total_asset_count' => $totalAssets,
                'total_hardware_value' => $totalHardwareValue,
                'annual_software_spend' => $annualSoftwareSpend,
                'total_fleet_valuation' => $totalHardwareValue + $annualSoftwareSpend,
                'asset_status_counts' => $statusCounts,
                'hardware_utilization_rate' => $hardwareUtilizationRate,
                'total_licenses' => $totalLicenses,
                'total_software_seats' => $totalSeats,
                'allocated_software_seats' => $allocatedSeats,
                'available_software_seats' => $availableSeats,
                'seat_utilization_rate' => $seatUtilizationRate,
                'active_repairs_count' => $activeRepairsCount,
                'total_repair_spend' => $totalRepairSpend,
                'overdue_returns_count' => $overdueReturnsCount,
                'expiring_warranties_count' => $expiringWarrantiesCount,
                'expiring_licenses_count' => $expiringLicensesCount,
                'urgent_total_count' => $urgentTotalCount,
            ],
            'categoryBreakdown' => $categoryBreakdown,
            'urgentItems' => $urgentItems,
            'recentActivity' => $recentActivity,
        ];
    }

    /**
     * Compile immediate attention items across assets, returns, warranties, and repairs.
     *
     * @return array<int, array<string, mixed>>
     */
    private function buildUrgentItems(string $today, string $urgentThreshold): array
    {
        $items = collect();

        // Overdue returns
        $overdueAssignments = AssetAssignment::query()
            ->whereNull('returned_at')
            ->where('expected_return_at', '<', $today)
            ->with(['asset:id,asset_tag,name', 'user:id,name'])
            ->orderBy('expected_return_at')
            ->limit(5)
            ->get();

        foreach ($overdueAssignments as $assignment) {
            $expectedDate = $assignment->expected_return_at?->toDateString() ?? $today;
            $daysOverdue = (int) now()->startOfDay()->diffInDays(Carbon::parse($expectedDate)->startOfDay(), false);

            $items->push([
                'id' => 'overdue_'.$assignment->id,
                'type' => 'overdue_return',
                'title' => ($assignment->asset?->asset_tag ?? 'Asset').' - '.($assignment->asset?->name ?? 'Hardware'),
                'subtitle' => 'Checked out to '.($assignment->user?->name ?? 'Employee'),
                'date_label' => abs($daysOverdue).'d overdue',
                'date' => $expectedDate,
                'urgency' => 'critical',
                'action_url' => route('assets.show', $assignment->asset_id),
                'action_label' => 'Inspect & Check In',
            ]);
        }

        // Expiring warranties
        $expiringAssets = Asset::query()
            ->whereNotNull('warranty_expires_at')
            ->where('warranty_expires_at', '<=', $urgentThreshold)
            ->where('status', '!=', AssetStatus::Retired->value)
            ->orderBy('warranty_expires_at')
            ->limit(5)
            ->get(['id', 'asset_tag', 'name', 'warranty_expires_at']);

        foreach ($expiringAssets as $asset) {
            $expDate = $asset->warranty_expires_at?->toDateString() ?? $today;
            $daysLeft = (int) now()->startOfDay()->diffInDays(Carbon::parse($expDate)->startOfDay(), false);
            $isExpired = $daysLeft < 0;

            $items->push([
                'id' => 'warranty_'.$asset->id,
                'type' => 'warranty_expiring',
                'title' => $asset->asset_tag.' - '.$asset->name,
                'subtitle' => 'Hardware Manufacturer Warranty',
                'date_label' => $isExpired ? 'Expired '.abs($daysLeft).'d ago' : 'Expires in '.$daysLeft.'d',
                'date' => $expDate,
                'urgency' => $isExpired ? 'critical' : 'warning',
                'action_url' => route('assets.show', $asset->id),
                'action_label' => 'View Asset',
            ]);
        }

        // Expiring licenses
        $expiringLicenses = SoftwareLicense::query()
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', $urgentThreshold)
            ->orderBy('expires_at')
            ->limit(5)
            ->get(['id', 'name', 'vendor', 'expires_at', 'seats_total']);

        foreach ($expiringLicenses as $license) {
            $expDate = $license->expires_at?->toDateString() ?? $today;
            $daysLeft = (int) now()->startOfDay()->diffInDays(Carbon::parse($expDate)->startOfDay(), false);
            $isExpired = $daysLeft < 0;

            $items->push([
                'id' => 'license_'.$license->id,
                'type' => 'license_expiring',
                'title' => $license->name.' ('.$license->vendor.')',
                'subtitle' => $license->seats_total.' seats subscription renewal',
                'date_label' => $isExpired ? 'Expired '.abs($daysLeft).'d ago' : 'Renews in '.$daysLeft.'d',
                'date' => $expDate,
                'urgency' => $isExpired ? 'critical' : 'warning',
                'action_url' => route('licenses.show', $license->id),
                'action_label' => 'Review Renewal',
            ]);
        }

        // Active repairs
        $activeRepairs = AssetMaintenance::query()
            ->active()
            ->with(['asset:id,asset_tag,name'])
            ->orderByRaw("CASE WHEN status = 'in_progress' THEN 1 ELSE 2 END")
            ->latest('started_at')
            ->limit(4)
            ->get();

        foreach ($activeRepairs as $repair) {
            $items->push([
                'id' => 'repair_'.$repair->id,
                'type' => 'active_repair',
                'title' => ($repair->asset?->asset_tag ?? 'Asset').': '.$repair->title,
                'subtitle' => 'Service Vendor: '.$repair->provider,
                'date_label' => ucfirst(str_replace('_', ' ', $repair->status->value)),
                'date' => $repair->started_at?->toDateString() ?? $today,
                'urgency' => 'info',
                'action_url' => route('maintenance.index'),
                'action_label' => 'Maintenance Hub',
            ]);
        }

        // Sort items by critical first, then warning, then info
        $urgencyWeights = ['critical' => 1, 'warning' => 2, 'info' => 3];

        return $items
            ->sortBy(fn (array $item) => $urgencyWeights[$item['urgency']] ?? 9)
            ->take(8)
            ->values()
            ->all();
    }

    /**
     * Assemble consolidated activity timeline from assignments, repairs, and licenses.
     *
     * @return array<int, array<string, mixed>>
     */
    private function buildRecentActivity(): array
    {
        $events = collect();

        // 1. Asset Assignments (Checkout & Check-in)
        $assignments = AssetAssignment::query()
            ->with(['asset:id,asset_tag,name', 'user:id,name', 'assignedByUser:id,name'])
            ->latest('assigned_at')
            ->limit(8)
            ->get();

        foreach ($assignments as $assignment) {
            $isReturned = $assignment->returned_at !== null;
            $events->push([
                'id' => 'assign_'.$assignment->id.'_'.($isReturned ? 'in' : 'out'),
                'type' => $isReturned ? 'asset_checkin' : 'asset_checkout',
                'title' => $isReturned
                    ? 'Hardware checked in'
                    : 'Hardware checked out',
                'description' => ($assignment->asset?->asset_tag ?? 'Asset').' ('.($assignment->asset?->name ?? 'Device').') '.
                    ($isReturned ? 'returned from' : 'assigned to').' '.($assignment->user?->name ?? 'Employee'),
                'actor' => $assignment->assignedByUser?->name ?? 'IT Admin',
                'target' => $assignment->user?->name ?? 'Employee',
                'timestamp' => $isReturned
                    ? $assignment->returned_at?->toISOString()
                    : $assignment->assigned_at?->toISOString(),
                'time_ago' => $isReturned
                    ? $assignment->returned_at?->diffForHumans()
                    : $assignment->assigned_at?->diffForHumans(),
                'url' => route('assets.show', $assignment->asset_id),
            ]);
        }

        // 2. Maintenance Events
        $repairs = AssetMaintenance::query()
            ->with(['asset:id,asset_tag,name', 'technician:id,name'])
            ->latest('updated_at')
            ->limit(6)
            ->get();

        foreach ($repairs as $repair) {
            $isCompleted = $repair->status === MaintenanceStatus::Completed;
            $events->push([
                'id' => 'maint_'.$repair->id.'_'.$repair->status->value,
                'type' => $isCompleted ? 'repair_completed' : 'repair_logged',
                'title' => $isCompleted ? 'Repair ticket completed' : 'Repair ticket logged',
                'description' => ($repair->asset?->asset_tag ?? 'Asset').': '.$repair->title.' ('.$repair->provider.')',
                'actor' => $repair->technician?->name ?? 'IT Staff',
                'target' => $repair->asset?->asset_tag ?? 'Asset',
                'timestamp' => $repair->updated_at?->toISOString(),
                'time_ago' => $repair->updated_at?->diffForHumans(),
                'url' => route('maintenance.index'),
            ]);
        }

        // 3. License Assignments
        $licenseAssignments = LicenseAssignment::query()
            ->with(['license:id,name,vendor', 'user:id,name'])
            ->latest('assigned_at')
            ->limit(6)
            ->get();

        foreach ($licenseAssignments as $la) {
            $events->push([
                'id' => 'lic_assign_'.$la->id,
                'type' => 'license_allocated',
                'title' => 'Software seat allocated',
                'description' => ($la->license?->name ?? 'License').' seat assigned to '.($la->user?->name ?? 'Employee'),
                'actor' => 'IT Admin',
                'target' => $la->user?->name ?? 'Employee',
                'timestamp' => $la->assigned_at?->toISOString(),
                'time_ago' => $la->assigned_at?->diffForHumans(),
                'url' => route('licenses.show', $la->software_license_id),
            ]);
        }

        return $events
            ->filter(fn (array $item) => ! empty($item['timestamp']))
            ->sortByDesc('timestamp')
            ->take(8)
            ->values()
            ->all();
    }
}
