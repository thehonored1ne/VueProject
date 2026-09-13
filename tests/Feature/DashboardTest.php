<?php

declare(strict_types=1);

use App\Enums\AssetStatus;
use App\Enums\AssetType;
use App\Enums\BillingCycle;
use App\Enums\MaintenanceStatus;
use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\AssetMaintenance;
use App\Models\LicenseAssignment;
use App\Models\SoftwareLicense;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected to the login page', function () {
    $response = $this->get('/dashboard');
    $response->assertRedirect('/login');
});

test('authenticated users can visit the dashboard and receive typed inertia props', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->has('metrics')
            ->has('metrics.total_asset_count')
            ->has('metrics.total_hardware_value')
            ->has('metrics.total_fleet_valuation')
            ->has('metrics.hardware_utilization_rate')
            ->has('metrics.urgent_total_count')
            ->has('categoryBreakdown')
            ->has('urgentItems')
            ->has('recentActivity')
        );
});

test('calculates fleet valuation and hardware metrics accurately', function () {
    $user = User::factory()->create();

    // Create assets
    Asset::factory()->create([
        'cost' => 1200.00,
        'status' => AssetStatus::Assigned,
        'type' => AssetType::Laptop,
    ]);
    Asset::factory()->create([
        'cost' => 800.00,
        'status' => AssetStatus::Available,
        'type' => AssetType::Monitor,
    ]);

    // Create software license (monthly: 5 seats * $20 = $100/mo * 12 = $1200/yr)
    SoftwareLicense::factory()->create([
        'seats_total' => 5,
        'cost_per_seat' => 20.00,
        'billing_cycle' => BillingCycle::Monthly,
    ]);

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('metrics.total_asset_count', 2)
            ->where('metrics.total_hardware_value', fn ($val) => (float) $val === 2000.0)
            ->where('metrics.annual_software_spend', fn ($val) => (float) $val === 1200.0)
            ->where('metrics.total_fleet_valuation', fn ($val) => (float) $val === 3200.0)
            ->where('metrics.asset_status_counts.assigned', 1)
            ->where('metrics.asset_status_counts.available', 1)
            ->where('metrics.hardware_utilization_rate', fn ($val) => (float) $val === 50.0)
        );
});

test('surfaces overdue asset assignments in the urgent queue', function () {
    $user = User::factory()->create();
    $employee = User::factory()->create();

    $asset = Asset::factory()->create([
        'status' => AssetStatus::Assigned,
        'warranty_expires_at' => now()->addYears(2)->toDateString(),
    ]);

    // Overdue assignment
    AssetAssignment::factory()->create([
        'asset_id' => $asset->id,
        'user_id' => $employee->id,
        'assigned_at' => now()->subDays(30),
        'expected_return_at' => now()->subDays(5)->toDateString(),
        'returned_at' => null,
    ]);

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('metrics.overdue_returns_count', 1)
            ->has('urgentItems', 1)
            ->where('urgentItems.0.type', 'overdue_return')
            ->where('urgentItems.0.urgency', 'critical')
        );
});

test('surfaces expiring hardware warranties and licenses in the urgent queue', function () {
    $user = User::factory()->create();

    // Asset with warranty expiring in 10 days
    $expiringAsset = Asset::factory()->create([
        'warranty_expires_at' => now()->addDays(10)->toDateString(),
        'status' => AssetStatus::Available,
    ]);

    // License expiring in 15 days
    $expiringLicense = SoftwareLicense::factory()->create([
        'expires_at' => now()->addDays(15)->toDateString(),
    ]);

    $response = $this->actingAs($user)->get('/dashboard');

    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('metrics.expiring_warranties_count', 1)
            ->where('metrics.expiring_licenses_count', 1)
            ->has('urgentItems', 2)
        );
});

test('compiles operational activity feed across models', function () {
    $admin = User::factory()->create(['name' => 'Admin Alex']);
    $user = User::factory()->create(['name' => 'Employee Dave']);

    $asset = Asset::factory()->create();
    $license = SoftwareLicense::factory()->create();

    // Assignment event
    AssetAssignment::factory()->create([
        'asset_id' => $asset->id,
        'user_id' => $user->id,
        'assigned_by' => $admin->id,
        'assigned_at' => now()->subHours(2),
    ]);

    // Repair ticket
    AssetMaintenance::factory()->create([
        'asset_id' => $asset->id,
        'user_id' => $admin->id,
        'title' => 'Screen Replacement',
        'status' => MaintenanceStatus::Scheduled,
    ]);

    // License assignment
    LicenseAssignment::create([
        'software_license_id' => $license->id,
        'user_id' => $user->id,
        'assigned_at' => now()->subMinutes(30),
    ]);

    $response = $this->actingAs($admin)->get('/dashboard');

    $response->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('recentActivity', 3)
        );
});
