<?php

use App\Http\Controllers\AssetAssignmentController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\AssetExportController;
use App\Http\Controllers\LicenseAssignmentController;
use App\Http\Controllers\LicenseController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::get('assets/export', AssetExportController::class)->name('assets.export');
    Route::resource('assets', AssetController::class);
    Route::post('assets/{asset}/assign', [AssetAssignmentController::class, 'store'])->name('assets.assign');
    Route::post('assets/{asset}/check-in', [AssetAssignmentController::class, 'update'])->name('assets.check-in');

    Route::resource('licenses', LicenseController::class);
    Route::post('licenses/{license}/assign', [LicenseAssignmentController::class, 'store'])->name('licenses.assign');
    Route::delete('licenses/{license}/revoke/{user}', [LicenseAssignmentController::class, 'destroy'])->name('licenses.revoke');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
