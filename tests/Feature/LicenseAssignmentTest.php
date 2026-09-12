<?php

declare(strict_types=1);

use App\Models\LicenseAssignment;
use App\Models\SoftwareLicense;
use App\Models\User;

test('authenticated user can assign a license seat to an employee', function () {
    $admin = User::factory()->create();
    $employee = User::factory()->create();
    $license = SoftwareLicense::factory()->create(['seats_total' => 5]);

    $this->actingAs($admin)
        ->post(route('licenses.assign', $license), [
            'user_id' => $employee->id,
            'notes' => 'Assigned for engineering project.',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('license_assignments', [
        'software_license_id' => $license->id,
        'user_id' => $employee->id,
        'notes' => 'Assigned for engineering project.',
    ]);
});

test('cannot assign duplicate seat to the same employee', function () {
    $admin = User::factory()->create();
    $employee = User::factory()->create();
    $license = SoftwareLicense::factory()->create(['seats_total' => 5]);

    LicenseAssignment::factory()->create([
        'software_license_id' => $license->id,
        'user_id' => $employee->id,
    ]);

    $this->actingAs($admin)
        ->post(route('licenses.assign', $license), [
            'user_id' => $employee->id,
        ])
        ->assertSessionHasErrors(['user_id']);
});

test('cannot allocate a seat when all seats are occupied', function () {
    $admin = User::factory()->create();
    $employee = User::factory()->create();
    $license = SoftwareLicense::factory()->create(['seats_total' => 1]);

    LicenseAssignment::factory()->create([
        'software_license_id' => $license->id,
        'user_id' => User::factory()->create()->id,
    ]);

    $this->actingAs($admin)
        ->post(route('licenses.assign', $license), [
            'user_id' => $employee->id,
        ])
        ->assertSessionHasErrors(['license']);
});

test('authenticated user can revoke an employee seat', function () {
    $admin = User::factory()->create();
    $employee = User::factory()->create();
    $license = SoftwareLicense::factory()->create(['seats_total' => 5]);

    $assignment = LicenseAssignment::factory()->create([
        'software_license_id' => $license->id,
        'user_id' => $employee->id,
    ]);

    $this->actingAs($admin)
        ->delete(route('licenses.revoke', [$license, $employee]))
        ->assertRedirect();

    $this->assertDatabaseMissing('license_assignments', [
        'id' => $assignment->id,
    ]);
});
