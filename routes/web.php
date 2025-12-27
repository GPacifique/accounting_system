<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GroupAdminDashboardController;
use App\Http\Controllers\MemberDashboardController;
use App\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Chat Routes (accessible to all, with middleware on specific actions)
Route::prefix('chat')->name('chat.')->group(function () {
    Route::get('/', [ChatController::class, 'show'])->middleware(['auth', 'verified'])->name('show');
    Route::post('/start', [ChatController::class, 'start'])->middleware(['auth', 'verified'])->name('start');
    Route::get('{chat}/window', [ChatController::class, 'window'])->middleware(['auth', 'verified'])->name('window');
    Route::post('{chat}/message', [ChatController::class, 'sendMessage'])->middleware(['auth', 'verified'])->name('send-message');
    Route::get('{chat}/messages', [ChatController::class, 'getMessages'])->middleware(['auth', 'verified'])->name('get-messages');
    Route::post('{chat}/close', [ChatController::class, 'close'])->middleware(['auth', 'verified'])->name('close');
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
        Route::get('/groups/{group}/record-savings', [GroupAdminDashboardController::class, 'recordSavings'])->name('record-savings');
        Route::post('/groups/{group}/record-savings', [GroupAdminDashboardController::class, 'storeSavings'])->name('store-savings');
        Route::get('/groups/{group}/record-interest', [GroupAdminDashboardController::class, 'recordInterest'])->name('record-interest');
        Route::post('/groups/{group}/record-interest', [GroupAdminDashboardController::class, 'storeInterest'])->name('store-interest');
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
