<?php

declare(strict_types=1);

use App\Enums\AssetStatus;
use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\User;

test('authenticated user can assign an available asset to an employee', function () {
    $admin = User::factory()->create();
    $employee = User::factory()->create();
    $asset = Asset::factory()->create(['status' => AssetStatus::Available]);

    $this->actingAs($admin)
        ->post(route('assets.assign', $asset), [
            'user_id' => $employee->id,
            'expected_return_at' => now()->addMonths(6)->format('Y-m-d'),
            'condition_on_assignment' => 'Clean screen, charger included.',
            'notes' => 'Assigned for engineering sprint.',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('assets', [
        'id' => $asset->id,
        'status' => AssetStatus::Assigned->value,
    ]);

    $this->assertDatabaseHas('asset_assignments', [
        'asset_id' => $asset->id,
        'user_id' => $employee->id,
        'assigned_by' => $admin->id,
        'condition_on_assignment' => 'Clean screen, charger included.',
        'returned_at' => null,
    ]);
});

test('cannot assign an asset that is not available', function () {
    $admin = User::factory()->create();
    $employee = User::factory()->create();
    $asset = Asset::factory()->assigned()->create();

    $this->actingAs($admin)
        ->post(route('assets.assign', $asset), [
            'user_id' => $employee->id,
        ])
        ->assertSessionHasErrors(['asset']);
});

test('authenticated user can check in an assigned asset', function () {
    $admin = User::factory()->create();
    $employee = User::factory()->create();
    $asset = Asset::factory()->assigned()->create();

    $assignment = AssetAssignment::factory()->create([
        'asset_id' => $asset->id,
        'user_id' => $employee->id,
        'returned_at' => null,
    ]);

    $this->actingAs($admin)
        ->post(route('assets.check-in', $asset), [
            'target_status' => AssetStatus::Available->value,
            'condition_on_return' => 'Normal wear, wiped down.',
            'notes' => 'Returned on schedule.',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('assets', [
        'id' => $asset->id,
        'status' => AssetStatus::Available->value,
    ]);

    $this->assertNotNull($assignment->fresh()->returned_at);
    $this->assertEquals('Normal wear, wiped down.', $assignment->fresh()->condition_on_return);
});

test('asset can be returned to maintenance status if damaged', function () {
    $admin = User::factory()->create();
    $employee = User::factory()->create();
    $asset = Asset::factory()->assigned()->create();

    AssetAssignment::factory()->create([
        'asset_id' => $asset->id,
        'user_id' => $employee->id,
        'returned_at' => null,
    ]);

    $this->actingAs($admin)
        ->post(route('assets.check-in', $asset), [
            'target_status' => AssetStatus::Maintenance->value,
            'condition_on_return' => 'Battery swollen, screen flickering.',
            'notes' => 'Sent to repair depot.',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('assets', [
        'id' => $asset->id,
        'status' => AssetStatus::Maintenance->value,
    ]);
});
