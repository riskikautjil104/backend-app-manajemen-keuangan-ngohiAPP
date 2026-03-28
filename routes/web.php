<?php

use App\Http\Controllers\Admin\AdminAppSettingsController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminBroadcastController;
use App\Http\Controllers\Admin\AdminUserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('admin.login');
});

Route::prefix('admin')->name('admin.')->group(function (): void {
    Route::middleware('guest')->group(function (): void {
        Route::get('login', [AdminAuthController::class, 'create'])->name('login');
        Route::post('login', [AdminAuthController::class, 'store']);
    });

    Route::middleware(['auth', 'admin'])->group(function (): void {
        Route::post('logout', [AdminAuthController::class, 'destroy'])->name('logout');
        Route::get('users', [AdminUserController::class, 'index'])->name('users.index');
        Route::get('app-settings', [AdminAppSettingsController::class, 'edit'])->name('app-settings.edit');
        Route::put('app-settings', [AdminAppSettingsController::class, 'update'])->name('app-settings.update');

        Route::get('broadcasts', [AdminBroadcastController::class, 'index'])->name('broadcasts.index');
        Route::get('broadcasts/create', [AdminBroadcastController::class, 'create'])->name('broadcasts.create');
        Route::post('broadcasts', [AdminBroadcastController::class, 'store'])->name('broadcasts.store');
        Route::get('broadcasts/{broadcast}/edit', [AdminBroadcastController::class, 'edit'])->name('broadcasts.edit');
        Route::put('broadcasts/{broadcast}', [AdminBroadcastController::class, 'update'])->name('broadcasts.update');
        Route::delete('broadcasts/{broadcast}', [AdminBroadcastController::class, 'destroy'])->name('broadcasts.destroy');
    });
});
