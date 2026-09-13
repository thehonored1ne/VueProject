<?php

declare(strict_types=1);

use App\Enums\AssetStatus;
use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\LicenseAssignment;
use App\Models\SoftwareLicense;
use App\Models\User;

test('guest is redirected from employee directory and custody views', function () {
    $user = User::factory()->create();

    $this->get(route('employees.index'))->assertRedirect(route('login'));
    $this->get(route('employees.show', $user))->assertRedirect(route('login'));
    $this->post(route('employees.offboard', $user))->assertRedirect(route('login'));
});

test('authenticated user can view the employee directory', function () {
    $admin = User::factory()->create();
    User::factory()->count(3)->create();

    $response = $this->actingAs($admin)->get(route('employees.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Employees/Index')
        ->has('employees.data', 4)
        ->has('metrics')
        ->where('metrics.total_employees', 4)
    );
});

test('user can search employees by name or email', function () {
    $admin = User::factory()->create(['name' => 'Alice Admin', 'email' => 'alice@example.com']);
    User::factory()->create(['name' => 'Bob Builder', 'email' => 'bob@example.com']);
    User::factory()->create(['name' => 'Charlie Chaplin', 'email' => 'charlie@example.com']);

    $response = $this->actingAs($admin)->get(route('employees.index', ['search' => 'Builder']));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Employees/Index')
        ->has('employees.data', 1)
        ->where('employees.data.0.name', 'Bob Builder')
    );
});

test('authenticated user can view employee 360 custody portal', function () {
    $admin = User::factory()->create();
    $employee = User::factory()->create(['name' => 'Jane Developer']);

    $asset = Asset::factory()->assigned()->create(['name' => 'MacBook Pro 16']);
    AssetAssignment::factory()->create([
        'asset_id' => $asset->id,
        'user_id' => $employee->id,
        'returned_at' => null,
    ]);

    $license = SoftwareLicense::factory()->create(['name' => 'JetBrains All Products']);
    LicenseAssignment::factory()->create([
        'software_license_id' => $license->id,
        'user_id' => $employee->id,
    ]);

    $response = $this->actingAs($admin)->get(route('employees.show', $employee));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Employees/Show')
        ->where('employee.name', 'Jane Developer')
        ->has('activeHardware', 1)
        ->has('softwareLicenses', 1)
        ->where('summary.active_hardware_count', 1)
        ->where('summary.software_seats_count', 1)
    );
});

test('atomic offboarding returns all active hardware and revokes software license seats', function () {
    $admin = User::factory()->create(['name' => 'IT Admin']);
    $employee = User::factory()->create(['name' => 'Departing Employee']);

    // 2 active assets
    $asset1 = Asset::factory()->assigned()->create(['name' => 'Laptop A', 'status' => AssetStatus::Assigned]);
    $asset2 = Asset::factory()->assigned()->create(['name' => 'Monitor B', 'status' => AssetStatus::Assigned]);

    $assignment1 = AssetAssignment::factory()->create([
        'asset_id' => $asset1->id,
        'user_id' => $employee->id,
        'returned_at' => null,
        'notes' => 'Original checkout note',
    ]);
    $assignment2 = AssetAssignment::factory()->create([
        'asset_id' => $asset2->id,
        'user_id' => $employee->id,
        'returned_at' => null,
    ]);

    // 1 previously returned asset (should remain untouched)
    $asset3 = Asset::factory()->create(['status' => AssetStatus::Available]);
    $pastReturnTime = now()->subMonth();
    $assignment3 = AssetAssignment::factory()->create([
        'asset_id' => $asset3->id,
        'user_id' => $employee->id,
        'returned_at' => $pastReturnTime,
        'condition_on_return' => 'Returned in normal order',
    ]);

    // 2 software license seats
    $lic1 = SoftwareLicense::factory()->create();
    $lic2 = SoftwareLicense::factory()->create();
    LicenseAssignment::factory()->create([
        'software_license_id' => $lic1->id,
        'user_id' => $employee->id,
    ]);
    LicenseAssignment::factory()->create([
        'software_license_id' => $lic2->id,
        'user_id' => $employee->id,
    ]);

    // Dispatch 1-click offboard
    $response = $this->actingAs($admin)->post(route('employees.offboard', $employee), [
        'notes' => 'Employee completed transition on last working day.',
    ]);

    $response->assertRedirect(route('employees.show', $employee));
    $response->assertSessionHas('success');

    // Verify assets returned and status restored to available
    $this->assertNotNull($assignment1->fresh()->returned_at);
    $this->assertNotNull($assignment2->fresh()->returned_at);
    $this->assertEquals(AssetStatus::Available, $asset1->fresh()->status);
    $this->assertEquals(AssetStatus::Available, $asset2->fresh()->status);
    $this->assertStringContainsString('Employee completed transition on last working day.', (string) $assignment1->fresh()->notes);

    // Verify past returned assignment remains unchanged
    $this->assertEquals('Returned in normal order', $assignment3->fresh()->condition_on_return);

    // Verify all license seats revoked
    $this->assertDatabaseMissing('license_assignments', [
        'user_id' => $employee->id,
    ]);
});

test('authenticated user can view the create employee form', function () {
    $admin = User::factory()->create();

    $this->actingAs($admin)
        ->get(route('employees.create'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Employees/Create'));
});

test('authenticated user can store a new employee', function () {
    $admin = User::factory()->create();

    $this->actingAs($admin)
        ->post(route('employees.store'), [
            'name' => 'Alexander Pierce',
            'email' => 'alexander.pierce@example.com',
            'password' => 'secretPassword123!',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('users', [
        'name' => 'Alexander Pierce',
        'email' => 'alexander.pierce@example.com',
    ]);
});

test('storing an employee validates required fields and unique email', function () {
    $admin = User::factory()->create(['email' => 'existing@example.com']);

    $this->actingAs($admin)
        ->post(route('employees.store'), [
            'name' => '',
            'email' => 'existing@example.com',
        ])
        ->assertSessionHasErrors(['name', 'email']);
});

test('authenticated user can view the edit employee form', function () {
    $admin = User::factory()->create();
    $employee = User::factory()->create(['name' => 'Target Employee']);

    $this->actingAs($admin)
        ->get(route('employees.edit', $employee))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Employees/Edit')
            ->where('employee.name', 'Target Employee')
        );
});

test('authenticated user can update an employee', function () {
    $admin = User::factory()->create();
    $employee = User::factory()->create(['name' => 'Old Name', 'email' => 'old@example.com']);

    $this->actingAs($admin)
        ->put(route('employees.update', $employee), [
            'name' => 'New Name',
            'email' => 'new@example.com',
        ])
        ->assertRedirect(route('employees.show', $employee));

    $this->assertDatabaseHas('users', [
        'id' => $employee->id,
        'name' => 'New Name',
        'email' => 'new@example.com',
    ]);
});

test('authenticated user can delete an employee without active custody', function () {
    $admin = User::factory()->create();
    $employee = User::factory()->create(['name' => 'No Assets User']);

    $this->actingAs($admin)
        ->delete(route('employees.destroy', $employee))
        ->assertRedirect(route('employees.index'));

    $this->assertDatabaseMissing('users', [
        'id' => $employee->id,
    ]);
});

test('cannot delete an employee while they hold active hardware in custody', function () {
    $admin = User::factory()->create();
    $employee = User::factory()->create();
    $asset = Asset::factory()->assigned()->create();
    AssetAssignment::factory()->create([
        'asset_id' => $asset->id,
        'user_id' => $employee->id,
        'returned_at' => null,
    ]);

    $this->actingAs($admin)
        ->delete(route('employees.destroy', $employee))
        ->assertSessionHasErrors(['user']);

    $this->assertDatabaseHas('users', [
        'id' => $employee->id,
    ]);
});

test('cannot delete an employee while they hold active software license seats', function () {
    $admin = User::factory()->create();
    $employee = User::factory()->create();
    $license = SoftwareLicense::factory()->create();
    LicenseAssignment::factory()->create([
        'software_license_id' => $license->id,
        'user_id' => $employee->id,
    ]);

    $this->actingAs($admin)
        ->delete(route('employees.destroy', $employee))
        ->assertSessionHasErrors(['user']);

    $this->assertDatabaseHas('users', [
        'id' => $employee->id,
    ]);
});

test('user cannot delete their own account via employee destroy', function () {
    $admin = User::factory()->create();

    $this->actingAs($admin)
        ->delete(route('employees.destroy', $admin))
        ->assertSessionHasErrors(['user']);

    $this->assertDatabaseHas('users', [
        'id' => $admin->id,
    ]);
});
