<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Middleware\AdminMiddleware;

// Admin routes - only for system admins
Route::middleware(['auth', 'verified', AdminMiddleware::class])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Users Management
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [AdminDashboardController::class, 'users'])->name('index');
        Route::get('/{user}/edit', [AdminDashboardController::class, 'editUser'])->name('edit');
        Route::put('/{user}', [AdminDashboardController::class, 'updateUser'])->name('update');
        Route::delete('/{user}', [AdminDashboardController::class, 'deleteUser'])->name('destroy');
    });

    // Groups Management
    Route::prefix('groups')->name('groups.')->group(function () {
        Route::get('/', [AdminDashboardController::class, 'groups'])->name('index');
        Route::get('/{group}', [AdminDashboardController::class, 'showGroup'])->name('show');
        Route::get('/{group}/edit', [AdminDashboardController::class, 'editGroup'])->name('edit');
        Route::put('/{group}', [AdminDashboardController::class, 'updateGroup'])->name('update');
    });

    // Loans Management
    Route::prefix('loans')->name('loans.')->group(function () {
        Route::get('/', [AdminDashboardController::class, 'loans'])->name('index');
        Route::get('/{loan}', [AdminDashboardController::class, 'showLoan'])->name('show');
    });

    // Savings Management
    Route::prefix('savings')->name('savings.')->group(function () {
        Route::get('/', [AdminDashboardController::class, 'savings'])->name('index');
        Route::get('/{saving}', [AdminDashboardController::class, 'showSaving'])->name('show');
    });

    // Transactions Log
    Route::get('/transactions', [AdminDashboardController::class, 'transactions'])->name('transactions');

    // Reports
    Route::get('/reports', [AdminDashboardController::class, 'reports'])->name('reports');

    // Settings
    Route::get('/settings', [AdminDashboardController::class, 'settings'])->name('settings');
});
