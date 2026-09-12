<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Licenses\CreateLicenseAction;
use App\Actions\Licenses\DeleteLicenseAction;
use App\Actions\Licenses\UpdateLicenseAction;
use App\Enums\BillingCycle;
use App\Http\Requests\Licenses\StoreLicenseRequest;
use App\Http\Requests\Licenses\UpdateLicenseRequest;
use App\Models\SoftwareLicense;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LicenseController extends Controller
{
    /**
     * Display a listing of software licenses with seat utilization metrics.
     */
    public function index(Request $request): Response
    {
        $search = $request->string('search')->toString() ?: null;
        $vendor = $request->string('vendor')->toString() ?: null;

        $licenses = SoftwareLicense::query()
            ->search($search)
            ->filterVendor($vendor)
            ->withCount('assignments')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        $allLicenses = SoftwareLicense::withCount('assignments')->get();
        $totalLicenses = $allLicenses->count();
        $totalSeats = (int) $allLicenses->sum('seats_total');
        $allocatedSeats = (int) $allLicenses->sum('assignments_count');
        $availableSeats = max(0, $totalSeats - $allocatedSeats);
        $utilizationRate = $totalSeats > 0 ? round(($allocatedSeats / $totalSeats) * 100, 1) : 0;

        $expiringCount = $allLicenses->filter(function (SoftwareLicense $lic) {
            if (! $lic->expires_at) {
                return false;
            }
            $diff = now()->diffInDays($lic->expires_at, false);

            return $diff >= 0 && $diff <= 30;
        })->count();

        $metrics = [
            'total_licenses' => $totalLicenses,
            'total_seats' => $totalSeats,
            'allocated_seats' => $allocatedSeats,
            'available_seats' => $availableSeats,
            'utilization_rate' => $utilizationRate,
            'expiring_count' => $expiringCount,
        ];

        $vendors = SoftwareLicense::distinct()->orderBy('vendor')->pluck('vendor');
        $users = User::select(['id', 'name', 'email'])->orderBy('name')->get();

        return Inertia::render('Licenses/Index', [
            'licenses' => $licenses,
            'metrics' => $metrics,
            'vendors' => $vendors,
            'users' => $users,
            'filters' => [
                'search' => $search,
                'vendor' => $vendor,
            ],
            'billingCycles' => collect(BillingCycle::cases())->map(fn (BillingCycle $b) => [
                'value' => $b->value,
                'label' => $b->label(),
            ]),
        ]);
    }

    /**
     * Show the form for creating a new software license.
     */
    public function create(): Response
    {
        return Inertia::render('Licenses/Create', [
            'billingCycles' => collect(BillingCycle::cases())->map(fn (BillingCycle $b) => [
                'value' => $b->value,
                'label' => $b->label(),
            ]),
        ]);
    }

    /**
     * Store a newly created software license in storage.
     */
    public function store(StoreLicenseRequest $request, CreateLicenseAction $action): RedirectResponse
    {
        $license = $action->execute($request->toDto());

        return redirect()->route('licenses.show', $license)
            ->with('success', "License {$license->name} was registered successfully.");
    }

    /**
     * Display the specified software license with assigned team members.
     */
    public function show(SoftwareLicense $license): Response
    {
        $license->loadCount('assignments');
        $license->load(['assignments.user']);

        $assignedUserIds = $license->assignments->pluck('user_id');
        $availableUsers = User::select(['id', 'name', 'email'])
            ->whereNotIn('id', $assignedUserIds)
            ->orderBy('name')
            ->get();

        return Inertia::render('Licenses/Show', [
            'license' => $license,
            'availableUsers' => $availableUsers,
        ]);
    }

    /**
     * Show the form for editing the specified software license.
     */
    public function edit(SoftwareLicense $license): Response
    {
        return Inertia::render('Licenses/Edit', [
            'license' => $license,
            'billingCycles' => collect(BillingCycle::cases())->map(fn (BillingCycle $b) => [
                'value' => $b->value,
                'label' => $b->label(),
            ]),
        ]);
    }

    /**
     * Update the specified software license in storage.
     */
    public function update(UpdateLicenseRequest $request, SoftwareLicense $license, UpdateLicenseAction $action): RedirectResponse
    {
        $updated = $action->execute($license, $request->toDto());

        return redirect()->route('licenses.show', $updated)
            ->with('success', "License {$updated->name} was updated successfully.");
    }

    /**
     * Remove the specified software license from storage.
     */
    public function destroy(SoftwareLicense $license, DeleteLicenseAction $action): RedirectResponse
    {
        $name = $license->name;
        $action->execute($license);

        return redirect()->route('licenses.index')
            ->with('success', "License {$name} was deleted successfully.");
    }
}
