<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GroupAdminDashboardController;
use App\Http\Controllers\MemberDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// RBAC-based dashboard redirect
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Admin Dashboard Routes (System Admins Only)
Route::middleware(['auth', 'verified'])->group(function () {
    // Group Admin Dashboard Routes
    Route::prefix('group-admin')->name('group-admin.')->middleware('group.admin')->group(function () {
        Route::get('/dashboard', [GroupAdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/groups/{group}/loans', [GroupAdminDashboardController::class, 'loans'])->name('loans');
        Route::get('/groups/{group}/savings', [GroupAdminDashboardController::class, 'savings'])->name('savings');
        Route::get('/groups/{group}/members', [GroupAdminDashboardController::class, 'members'])->name('members');
        Route::get('/groups/{group}/transactions', [GroupAdminDashboardController::class, 'transactions'])->name('transactions');
        Route::get('/groups/{group}/reports', [GroupAdminDashboardController::class, 'reports'])->name('reports');
    });

    // Member Dashboard Routes
    Route::prefix('member')->name('member.')->group(function () {
        Route::get('/dashboard', [MemberDashboardController::class, 'index'])->name('dashboard');
        Route::get('/loans', [MemberDashboardController::class, 'myLoans'])->name('loans');
        Route::get('/savings', [MemberDashboardController::class, 'mySavings'])->name('savings');
        Route::get('/transactions', [MemberDashboardController::class, 'myTransactions'])->name('transactions');
    });

    // Profile Routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
require __DIR__.'/admin.php';
