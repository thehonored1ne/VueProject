<?php

use App\Http\Controllers\AssetAssignmentController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\AssetExportController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\LicenseAssignmentController;
use App\Http\Controllers\LicenseController;
use App\Http\Controllers\MaintenanceController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::get('assets/export', AssetExportController::class)->name('assets.export');
    Route::resource('assets', AssetController::class);
    Route::post('assets/{asset}/assign', [AssetAssignmentController::class, 'store'])->name('assets.assign');
    Route::post('assets/{asset}/check-in', [AssetAssignmentController::class, 'update'])->name('assets.check-in');

    Route::resource('licenses', LicenseController::class);
    Route::post('licenses/{license}/assign', [LicenseAssignmentController::class, 'store'])->name('licenses.assign');
    Route::delete('licenses/{license}/revoke/{user}', [LicenseAssignmentController::class, 'destroy'])->name('licenses.revoke');

    Route::get('maintenance', [MaintenanceController::class, 'index'])->name('maintenance.index');
    Route::post('maintenance', [MaintenanceController::class, 'store'])->name('maintenance.store');
    Route::post('maintenance/{maintenance}/complete', [MaintenanceController::class, 'complete'])->name('maintenance.complete');
    Route::post('maintenance/{maintenance}/cancel', [MaintenanceController::class, 'cancel'])->name('maintenance.cancel');

    Route::resource('employees', EmployeeController::class)->parameters(['employees' => 'user']);
    Route::post('employees/{user}/offboard', [EmployeeController::class, 'offboard'])->name('employees.offboard');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
