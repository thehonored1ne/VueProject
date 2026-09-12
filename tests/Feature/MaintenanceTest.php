<?php

declare(strict_types=1);

use App\Enums\AssetStatus;
use App\Enums\MaintenanceStatus;
use App\Models\Asset;
use App\Models\AssetMaintenance;
use App\Models\SoftwareLicense;
use App\Models\User;

test('guests are redirected from maintenance center to login', function () {
    $response = $this->get(route('maintenance.index'));

    $response->assertRedirect(route('login'));
});

test('authenticated user can view maintenance center with metrics and alerts', function () {
    $user = User::factory()->create();

    $asset = Asset::factory()->create([
        'status' => AssetStatus::Maintenance,
        'warranty_expires_at' => now()->addDays(20),
    ]);

    AssetMaintenance::factory()->create([
        'asset_id' => $asset->id,
        'user_id' => $user->id,
        'status' => MaintenanceStatus::InProgress,
    ]);

    SoftwareLicense::factory()->create([
        'expires_at' => now()->addDays(15),
    ]);

    $response = $this->actingAs($user)->get(route('maintenance.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Maintenance/Index')
        ->has('activeRepairs', 1)
        ->has('warrantyAlerts', 1)
        ->has('licenseAlerts', 1)
        ->has('metrics')
    );
});

test('scheduling a repair ticket updates asset status to maintenance', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->create(['status' => AssetStatus::Available]);

    $response = $this->actingAs($user)->post(route('maintenance.store'), [
        'asset_id' => $asset->id,
        'title' => 'Broken Display Replacement',
        'provider' => 'Dell ProSupport',
        'cost' => 199.50,
        'status' => 'in_progress',
        'scheduled_at' => now()->toDateString(),
        'started_at' => now()->toDateString(),
        'notes' => 'Screen flicker issue on HDMI 2.',
    ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect();

    $this->assertDatabaseHas('asset_maintenances', [
        'asset_id' => $asset->id,
        'title' => 'Broken Display Replacement',
        'status' => 'in_progress',
    ]);

    expect($asset->fresh()->status)->toBe(AssetStatus::Maintenance);
});

test('completing a repair ticket resolves ticket and restores asset to available', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->create(['status' => AssetStatus::Maintenance]);

    $maintenance = AssetMaintenance::factory()->create([
        'asset_id' => $asset->id,
        'user_id' => $user->id,
        'status' => MaintenanceStatus::InProgress,
        'cost' => 100.00,
    ]);

    $response = $this->actingAs($user)->post(route('maintenance.complete', $maintenance->id), [
        'cost' => 125.50,
        'notes' => 'Technician replaced motherboard fan and updated BIOS.',
    ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect();

    $maintenance->refresh();
    expect($maintenance->status)->toBe(MaintenanceStatus::Completed)
        ->and((float) $maintenance->cost)->toBe(125.50)
        ->and($maintenance->completed_at)->not->toBeNull()
        ->and($asset->fresh()->status)->toBe(AssetStatus::Available);
});

test('cancelling a repair ticket sets status to cancelled and restores asset', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->create(['status' => AssetStatus::Maintenance]);

    $maintenance = AssetMaintenance::factory()->create([
        'asset_id' => $asset->id,
        'user_id' => $user->id,
        'status' => MaintenanceStatus::InProgress,
    ]);

    $response = $this->actingAs($user)->post(route('maintenance.cancel', $maintenance->id), [
        'notes' => 'False alarm, cable was loose.',
    ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect();

    $maintenance->refresh();
    expect($maintenance->status)->toBe(MaintenanceStatus::Cancelled)
        ->and($asset->fresh()->status)->toBe(AssetStatus::Available);
});
