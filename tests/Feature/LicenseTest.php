<?php

declare(strict_types=1);

use App\Enums\BillingCycle;
use App\Models\LicenseAssignment;
use App\Models\SoftwareLicense;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected from license pages to login', function () {
    $this->get('/licenses')->assertRedirect('/login');
    $this->get('/licenses/create')->assertRedirect('/login');
    $this->post('/licenses', [])->assertRedirect('/login');
});

test('authenticated users can view license index with metrics and items', function () {
    $user = User::factory()->create();
    SoftwareLicense::factory()->count(3)->create();

    $this->actingAs($user)
        ->get('/licenses')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Licenses/Index')
            ->has('licenses.data')
            ->has('metrics.total_licenses')
            ->has('metrics.total_seats')
            ->has('metrics.allocated_seats')
            ->has('filters')
            ->has('billingCycles')
        );
});

test('licenses can be filtered by search query and vendor', function () {
    $user = User::factory()->create();
    $target = SoftwareLicense::factory()->create([
        'name' => 'JetBrains Space Enterprise',
        'vendor' => 'JetBrains',
    ]);
    SoftwareLicense::factory()->create([
        'name' => 'Figma Professional',
        'vendor' => 'Figma',
    ]);

    $this->actingAs($user)
        ->get('/licenses?search=Space&vendor=JetBrains')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Licenses/Index')
            ->has('licenses.data', 1)
            ->where('licenses.data.0.id', $target->id)
        );
});

test('authenticated user can view create license page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/licenses/create')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Licenses/Create')
            ->has('billingCycles')
        );
});

test('authenticated user can store a new software license', function () {
    $user = User::factory()->create();

    $payload = [
        'name' => 'GitHub Copilot Enterprise',
        'vendor' => 'GitHub',
        'seats_total' => 25,
        'cost_per_seat' => 39.00,
        'billing_cycle' => BillingCycle::Monthly->value,
        'license_key' => 'GH-TEST-KEY-1234',
        'expires_at' => '2025-12-31',
        'notes' => 'Central developer tooling license.',
    ];

    $response = $this->actingAs($user)
        ->post('/licenses', $payload);

    $this->assertDatabaseHas('software_licenses', [
        'name' => 'GitHub Copilot Enterprise',
        'vendor' => 'GitHub',
        'seats_total' => 25,
        'cost_per_seat' => 39.00,
    ]);

    $license = SoftwareLicense::where('name', 'GitHub Copilot Enterprise')->first();
    $response->assertRedirect(route('licenses.show', $license));
});

test('storing a license enforces validation rules', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/licenses', [
            'name' => '',
            'vendor' => '',
            'seats_total' => 0,
            'billing_cycle' => 'invalid-cycle',
        ])
        ->assertSessionHasErrors(['name', 'vendor', 'seats_total', 'billing_cycle']);
});

test('authenticated user can view license show page with assignments', function () {
    $user = User::factory()->create();
    $license = SoftwareLicense::factory()->create(['seats_total' => 10]);
    LicenseAssignment::factory()->create([
        'software_license_id' => $license->id,
        'user_id' => $user->id,
    ]);

    $this->actingAs($user)
        ->get(route('licenses.show', $license))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Licenses/Show')
            ->where('license.id', $license->id)
            ->has('license.assignments', 1)
            ->has('availableUsers')
        );
});

test('authenticated user can update license details', function () {
    $user = User::factory()->create();
    $license = SoftwareLicense::factory()->create([
        'name' => 'Old License Name',
        'seats_total' => 5,
    ]);

    $this->actingAs($user)
        ->put(route('licenses.update', $license), [
            'name' => 'New License Name',
            'vendor' => $license->vendor,
            'seats_total' => 15,
            'billing_cycle' => BillingCycle::Yearly->value,
            'cost_per_seat' => 120.00,
        ])
        ->assertRedirect(route('licenses.show', $license));

    $this->assertDatabaseHas('software_licenses', [
        'id' => $license->id,
        'name' => 'New License Name',
        'seats_total' => 15,
    ]);
});

test('cannot reduce total seats below current assigned count', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $license = SoftwareLicense::factory()->create(['seats_total' => 5]);

    LicenseAssignment::factory()->create([
        'software_license_id' => $license->id,
        'user_id' => $user1->id,
    ]);
    LicenseAssignment::factory()->create([
        'software_license_id' => $license->id,
        'user_id' => $user2->id,
    ]);

    $this->actingAs($user1)
        ->put(route('licenses.update', $license), [
            'name' => $license->name,
            'vendor' => $license->vendor,
            'seats_total' => 1, // Less than 2 assigned seats
            'billing_cycle' => BillingCycle::Monthly->value,
        ])
        ->assertSessionHasErrors(['seats_total']);
});

test('authenticated user can delete an unallocated license', function () {
    $user = User::factory()->create();
    $license = SoftwareLicense::factory()->create();

    $this->actingAs($user)
        ->delete(route('licenses.destroy', $license))
        ->assertRedirect(route('licenses.index'));

    $this->assertDatabaseMissing('software_licenses', ['id' => $license->id]);
});

test('cannot delete a license with active seat assignments', function () {
    $user = User::factory()->create();
    $license = SoftwareLicense::factory()->create();
    LicenseAssignment::factory()->create([
        'software_license_id' => $license->id,
        'user_id' => $user->id,
    ]);

    $this->actingAs($user)
        ->delete(route('licenses.destroy', $license))
        ->assertSessionHasErrors(['license']);

    $this->assertDatabaseHas('software_licenses', ['id' => $license->id]);
});
