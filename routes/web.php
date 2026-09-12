<?php

use App\Http\Controllers\AssetAssignmentController;
use App\Http\Controllers\AssetController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::resource('assets', AssetController::class);
    Route::post('assets/{asset}/assign', [AssetAssignmentController::class, 'store'])->name('assets.assign');
    Route::post('assets/{asset}/check-in', [AssetAssignmentController::class, 'update'])->name('assets.check-in');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
