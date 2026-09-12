<?php

declare(strict_types=1);

use App\Enums\AssetStatus;
use App\Enums\AssetType;
use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected from assets pages to login', function () {
    $this->get('/assets')->assertRedirect('/login');
    $this->get('/assets/create')->assertRedirect('/login');
    $this->post('/assets', [])->assertRedirect('/login');
});

test('authenticated users can view assets index with metrics and items', function () {
    $user = User::factory()->create();
    Asset::factory()->count(3)->create();

    $this->actingAs($user)
        ->get('/assets')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Assets/Index')
            ->has('assets.data')
            ->has('metrics.total')
            ->has('metrics.utilization_rate')
            ->has('filters')
            ->has('statuses')
            ->has('types')
        );
});

test('assets can be filtered by search query and status', function () {
    $user = User::factory()->create();
    $target = Asset::factory()->create([
        'name' => 'Ultra Rare Prototype Workstation',
        'status' => AssetStatus::Available,
    ]);
    Asset::factory()->create([
        'name' => 'Standard Dell Monitor',
        'status' => AssetStatus::Assigned,
    ]);

    $response = $this->actingAs($user)
        ->get('/assets?search=Prototype&status=available');

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Assets/Index')
        ->has('assets.data', 1)
        ->where('assets.data.0.id', $target->id)
    );
});

test('authenticated user can view create asset page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/assets/create')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Assets/Create')
            ->has('types')
            ->has('statuses')
        );
});

test('authenticated user can store a new asset', function () {
    $user = User::factory()->create();

    $payload = [
        'name' => 'MacBook Pro 16" M3 Max',
        'type' => AssetType::Laptop->value,
        'asset_tag' => 'AST-99901',
        'serial_number' => 'C02TESTSERIAL1',
        'model_number' => 'A2991',
        'cost' => 3499.00,
        'purchased_at' => '2024-01-15',
        'warranty_expires_at' => '2027-01-15',
        'notes' => 'Executive laptop test unit.',
    ];

    $response = $this->actingAs($user)
        ->post('/assets', $payload);

    $this->assertDatabaseHas('assets', [
        'asset_tag' => 'AST-99901',
        'name' => 'MacBook Pro 16" M3 Max',
        'serial_number' => 'C02TESTSERIAL1',
        'status' => AssetStatus::Available->value,
    ]);

    $asset = Asset::where('asset_tag', 'AST-99901')->first();
    $response->assertRedirect(route('assets.show', $asset));
});

test('storing an asset requires valid data and enforces uniqueness', function () {
    $user = User::factory()->create();
    Asset::factory()->create([
        'asset_tag' => 'AST-DUPE',
        'serial_number' => 'SN-DUPE',
    ]);

    $this->actingAs($user)
        ->post('/assets', [
            'name' => '',
            'type' => 'invalid-type',
            'asset_tag' => 'AST-DUPE',
            'serial_number' => 'SN-DUPE',
        ])
        ->assertSessionHasErrors(['name', 'type', 'asset_tag', 'serial_number']);
});

test('authenticated user can view asset detail page with custody timeline', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->create();
    AssetAssignment::factory()->create([
        'asset_id' => $asset->id,
        'user_id' => $user->id,
    ]);

    $this->actingAs($user)
        ->get(route('assets.show', $asset))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Assets/Show')
            ->where('asset.id', $asset->id)
            ->has('asset.assignments', 1)
        );
});

test('authenticated user can update asset attributes', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->create(['name' => 'Old Name']);

    $this->actingAs($user)
        ->put(route('assets.update', $asset), [
            'name' => 'Updated Hardware Name',
            'type' => AssetType::Desktop->value,
            'cost' => 1200.50,
        ])
        ->assertRedirect(route('assets.show', $asset));

    $this->assertDatabaseHas('assets', [
        'id' => $asset->id,
        'name' => 'Updated Hardware Name',
        'type' => AssetType::Desktop->value,
    ]);
});

test('authenticated user can delete an unassigned asset', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->create(['status' => AssetStatus::Available]);

    $this->actingAs($user)
        ->delete(route('assets.destroy', $asset))
        ->assertRedirect(route('assets.index'));

    $this->assertDatabaseMissing('assets', ['id' => $asset->id]);
});

test('cannot delete an asset that is currently assigned to a user', function () {
    $user = User::factory()->create();
    $asset = Asset::factory()->assigned()->create();
    AssetAssignment::factory()->create([
        'asset_id' => $asset->id,
        'user_id' => $user->id,
        'returned_at' => null,
    ]);

    $this->actingAs($user)
        ->delete(route('assets.destroy', $asset))
        ->assertSessionHasErrors(['asset']);

    $this->assertDatabaseHas('assets', ['id' => $asset->id]);
});
