<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Employees\CreateEmployeeAction;
use App\Actions\Employees\DeleteEmployeeAction;
use App\Actions\Employees\OffboardEmployeeAction;
use App\Actions\Employees\UpdateEmployeeAction;
use App\Http\Requests\Employees\OffboardEmployeeRequest;
use App\Http\Requests\Employees\StoreEmployeeRequest;
use App\Http\Requests\Employees\UpdateEmployeeRequest;
use App\Models\AssetAssignment;
use App\Models\LicenseAssignment;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EmployeeController extends Controller
{
    /**
     * Display a paginated listing of employees and their equipment custody status.
     */
    public function index(Request $request): Response
    {
        $search = $request->string('search')->toString() ?: null;

        $employees = User::query()
            ->search($search)
            ->withCount([
                'assetAssignments as active_assets_count' => fn ($q) => $q->whereNull('returned_at'),
                'licenseAssignments as active_licenses_count',
            ])
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        $totalEmployees = User::count();
        $totalAssignedAssets = AssetAssignment::whereNull('returned_at')->count();
        $totalAssignedLicenses = LicenseAssignment::count();

        $activeCustodyEmployees = User::whereHas('assetAssignments', fn ($q) => $q->whereNull('returned_at'))
            ->orWhereHas('licenseAssignments')
            ->distinct()
            ->count();

        $metrics = [
            'total_employees' => $totalEmployees,
            'active_custody_employees' => $activeCustodyEmployees,
            'total_assigned_assets' => $totalAssignedAssets,
            'total_assigned_licenses' => $totalAssignedLicenses,
        ];

        return Inertia::render('Employees/Index', [
            'employees' => $employees,
            'metrics' => $metrics,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    /**
     * Show the form for creating a new employee.
     */
    public function create(): Response
    {
        return Inertia::render('Employees/Create');
    }

    /**
     * Store a newly created employee in storage.
     */
    public function store(StoreEmployeeRequest $request, CreateEmployeeAction $action): RedirectResponse
    {
        $employee = $action->execute($request->toDto());

        return redirect()->route('employees.show', $employee)
            ->with('success', "Employee {$employee->name} was added successfully.");
    }

    /**
     * Display the 360° custody view for an individual employee.
     */
    public function show(User $user): Response
    {
        $activeHardware = $user->activeAssetAssignments()
            ->with(['asset'])
            ->orderByDesc('assigned_at')
            ->get();

        $softwareLicenses = $user->licenseAssignments()
            ->with(['license'])
            ->orderByDesc('assigned_at')
            ->get();

        $custodyHistory = $user->assetAssignments()
            ->with(['asset', 'assignedByUser'])
            ->orderByDesc('assigned_at')
            ->get();

        return Inertia::render('Employees/Show', [
            'employee' => $user,
            'activeHardware' => $activeHardware,
            'softwareLicenses' => $softwareLicenses,
            'custodyHistory' => $custodyHistory,
            'summary' => [
                'active_hardware_count' => $activeHardware->count(),
                'software_seats_count' => $softwareLicenses->count(),
                'lifetime_hardware_count' => $custodyHistory->count(),
            ],
        ]);
    }

    /**
     * Show the form for editing an existing employee.
     */
    public function edit(User $user): Response
    {
        return Inertia::render('Employees/Edit', [
            'employee' => $user,
        ]);
    }

    /**
     * Update the specified employee in storage.
     */
    public function update(
        UpdateEmployeeRequest $request,
        User $user,
        UpdateEmployeeAction $action
    ): RedirectResponse {
        $updated = $action->execute($user, $request->toDto());

        return redirect()->route('employees.show', $updated)
            ->with('success', "Employee {$updated->name} was updated successfully.");
    }

    /**
     * Remove the specified employee from storage.
     */
    public function destroy(
        Request $request,
        User $user,
        DeleteEmployeeAction $action
    ): RedirectResponse {
        $name = $user->name;
        $action->execute($user, $request->user());

        return redirect()->route('employees.index')
            ->with('success', "Employee {$name} was deleted successfully.");
    }

    /**
     * Atomically offboard an employee, returning all hardware and releasing all software seats.
     */
    public function offboard(
        OffboardEmployeeRequest $request,
        User $user,
        OffboardEmployeeAction $action
    ): RedirectResponse {
        $notes = $request->validated('notes');
        $result = $action->execute(
            user: $user,
            notes: is_string($notes) ? $notes : null,
            offboardedBy: $request->user(),
        );

        return redirect()->route('employees.show', $user)->with(
            'success',
            "Employee {$user->name} offboarded successfully ({$result['assets_returned']} hardware returned, {$result['licenses_revoked']} software seats revoked)."
        );
    }
}
