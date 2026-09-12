<?php

declare(strict_types=1);

use App\Enums\AssetStatus;
use App\Enums\AssetType;
use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\User;

test('guests are redirected from export endpoint to login', function () {
    $response = $this->get(route('assets.export'));

    $response->assertRedirect(route('login'));
});

test('authenticated users can download inventory csv with correct headers', function () {
    $user = User::factory()->create();

    Asset::factory()->create([
        'asset_tag' => 'AST-EXP001',
        'name' => 'MacBook Pro 16 M3',
        'status' => AssetStatus::Available,
    ]);

    $response = $this->actingAs($user)->get(route('assets.export'));

    $response->assertOk();
    $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    expect($response->headers->get('Content-Disposition'))->toContain('assetflow-inventory-');
});

test('exported csv contains asset records and active assignee info', function () {
    $user = User::factory()->create();
    $employee = User::factory()->create(['name' => 'Alex Mercer', 'email' => 'alex@example.com']);

    $asset = Asset::factory()->create([
        'asset_tag' => 'AST-EXP002',
        'name' => 'Dell UltraSharp 32',
        'type' => AssetType::Monitor,
        'status' => AssetStatus::Assigned,
        'cost' => 849.99,
    ]);

    AssetAssignment::factory()->create([
        'asset_id' => $asset->id,
        'user_id' => $employee->id,
        'assigned_at' => now()->subDays(10),
        'returned_at' => null,
    ]);

    $response = $this->actingAs($user)->get(route('assets.export'));

    $response->assertOk();
    $content = $response->streamedContent();

    expect($content)->toContain('Asset Tag')
        ->toContain('Current Assignee')
        ->toContain('AST-EXP002')
        ->toContain('Dell UltraSharp 32')
        ->toContain('Alex Mercer')
        ->toContain('alex@example.com');
});

test('export query filters by search keyword', function () {
    $user = User::factory()->create();

    Asset::factory()->create([
        'asset_tag' => 'AST-MATCH',
        'name' => 'ThinkPad X1 Carbon',
    ]);

    Asset::factory()->create([
        'asset_tag' => 'AST-OTHER',
        'name' => 'Mac mini M2',
    ]);

    $response = $this->actingAs($user)->get(route('assets.export', ['q' => 'ThinkPad']));

    $response->assertOk();
    $content = $response->streamedContent();

    expect($content)->toContain('AST-MATCH')
        ->toContain('ThinkPad X1 Carbon')
        ->not->toContain('AST-OTHER')
        ->not->toContain('Mac mini M2');
});

test('export query filters by status and type', function () {
    $user = User::factory()->create();

    Asset::factory()->create([
        'asset_tag' => 'AST-MAINT',
        'name' => 'Broken Monitor',
        'type' => AssetType::Monitor,
        'status' => AssetStatus::Maintenance,
    ]);

    Asset::factory()->create([
        'asset_tag' => 'AST-LAPTOP-AVAIL',
        'name' => 'Available Laptop',
        'type' => AssetType::Laptop,
        'status' => AssetStatus::Available,
    ]);

    $response = $this->actingAs($user)->get(route('assets.export', ['status' => 'maintenance', 'type' => 'monitor']));

    $response->assertOk();
    $content = $response->streamedContent();

    expect($content)->toContain('AST-MAINT')
        ->not->toContain('AST-LAPTOP-AVAIL');
});
