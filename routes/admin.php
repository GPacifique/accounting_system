<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\RolePermissionController;
use App\Http\Middleware\AdminMiddleware;

// Admin routes - only for system admins
Route::middleware(['auth', 'verified', AdminMiddleware::class])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Users Management
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [AdminDashboardController::class, 'users'])->name('index');
        Route::get('/create', [AdminDashboardController::class, 'createUser'])->name('create');
        Route::post('/', [AdminDashboardController::class, 'storeUser'])->name('store');
        Route::get('/{user}/edit', [AdminDashboardController::class, 'editUser'])->name('edit');
        Route::put('/{user}', [AdminDashboardController::class, 'updateUser'])->name('update');
        Route::delete('/{user}', [AdminDashboardController::class, 'deleteUser'])->name('destroy');
    });

    // Groups Management
    Route::prefix('groups')->name('groups.')->group(function () {
        Route::get('/', [AdminDashboardController::class, 'groups'])->name('index');
        Route::get('/create', [AdminDashboardController::class, 'createGroup'])->name('create');
        Route::post('/', [AdminDashboardController::class, 'storeGroup'])->name('store');
        Route::get('/{group}', [AdminDashboardController::class, 'showGroup'])->name('show');
        Route::get('/{group}/edit', [AdminDashboardController::class, 'editGroup'])->name('edit');
        Route::put('/{group}', [AdminDashboardController::class, 'updateGroup'])->name('update');

        // Group Members Management
        Route::prefix('{group}/members')->name('members.')->group(function () {
            Route::get('/', [AdminDashboardController::class, 'groupMembers'])->name('index');
            Route::get('/create', [AdminDashboardController::class, 'createGroupMember'])->name('create');
            Route::post('/', [AdminDashboardController::class, 'storeGroupMember'])->name('store');
            Route::get('/{member}/edit', [AdminDashboardController::class, 'editGroupMember'])->name('edit');
            Route::put('/{member}', [AdminDashboardController::class, 'updateGroupMember'])->name('update');
            Route::delete('/{member}', [AdminDashboardController::class, 'deleteGroupMember'])->name('destroy');
        });

        // Group Role Assignments Management
        Route::prefix('{group}/role-assignments')->name('role-assignments.')->group(function () {
            Route::get('/', [RolePermissionController::class, 'groupRoleAssignments'])->name('index');
            Route::put('/', [RolePermissionController::class, 'updateGroupRoleAssignments'])->name('update');
        });

        // Group Permission Matrix
        Route::get('/{group}/permissions', [RolePermissionController::class, 'groupPermissionMatrix'])->name('permissions');
    });

    // Loans Management
    Route::prefix('loans')->name('loans.')->group(function () {
        Route::get('/', [AdminDashboardController::class, 'loans'])->name('index');
        Route::get('/create', [AdminDashboardController::class, 'createLoan'])->name('create');
        Route::post('/', [AdminDashboardController::class, 'storeLoan'])->name('store');
        Route::get('/{loan}', [AdminDashboardController::class, 'showLoan'])->name('show');

        // Loan Repayment Routes
        Route::prefix('{loan}/repayments')->name('repayments.')->group(function () {
            Route::get('/', [AdminDashboardController::class, 'loanRepayments'])->name('index');
            Route::get('/create', [AdminDashboardController::class, 'createRepayment'])->name('create');
            Route::post('/', [AdminDashboardController::class, 'storeRepayment'])->name('store');
            Route::get('/{repayment}/edit', [AdminDashboardController::class, 'editRepayment'])->name('edit');
            Route::put('/{repayment}', [AdminDashboardController::class, 'updateRepayment'])->name('update');
            Route::delete('/{repayment}', [AdminDashboardController::class, 'deleteRepayment'])->name('destroy');
        });
    });

    // Savings Management
    Route::prefix('savings')->name('savings.')->group(function () {
        Route::get('/', [AdminDashboardController::class, 'savings'])->name('index');
        Route::get('/create', [AdminDashboardController::class, 'createSaving'])->name('create');
        Route::post('/', [AdminDashboardController::class, 'storeSaving'])->name('store');
        Route::get('/{saving}', [AdminDashboardController::class, 'showSaving'])->name('show');
    });

    // Transactions Log
    Route::get('/transactions', [AdminDashboardController::class, 'transactions'])->name('transactions');

    // Reports
    Route::get('/reports', [AdminDashboardController::class, 'reports'])->name('reports');

    // Settings
    Route::get('/settings', [AdminDashboardController::class, 'settings'])->name('settings');

    // Roles Management
    Route::prefix('roles')->name('roles.')->group(function () {
        Route::get('/', [RolePermissionController::class, 'roles'])->name('index');
        Route::get('/create', [RolePermissionController::class, 'createRole'])->name('create');
        Route::post('/', [RolePermissionController::class, 'storeRole'])->name('store');
        Route::get('/{role}/edit', [RolePermissionController::class, 'editRole'])->name('edit');
        Route::put('/{role}', [RolePermissionController::class, 'updateRole'])->name('update');
        Route::delete('/{role}', [RolePermissionController::class, 'deleteRole'])->name('destroy');
        Route::get('/{role}/assignments', [RolePermissionController::class, 'roleAssignments'])->name('assignments');
    });

    // Permissions Management
    Route::prefix('permissions')->name('permissions.')->group(function () {
        Route::get('/', [RolePermissionController::class, 'permissions'])->name('index');
        Route::get('/create', [RolePermissionController::class, 'createPermission'])->name('create');
        Route::post('/', [RolePermissionController::class, 'storePermission'])->name('store');
        Route::get('/{permission}/edit', [RolePermissionController::class, 'editPermission'])->name('edit');
        Route::put('/{permission}', [RolePermissionController::class, 'updatePermission'])->name('update');
        Route::delete('/{permission}', [RolePermissionController::class, 'deletePermission'])->name('destroy');
    });

    // User Roles Management
    Route::prefix('user-roles')->name('user-roles.')->group(function () {
        Route::get('/', [RolePermissionController::class, 'userRoles'])->name('index');
        Route::get('/{user}/edit', [RolePermissionController::class, 'editUserRoles'])->name('edit');
        Route::put('/{user}', [RolePermissionController::class, 'updateUserRoles'])->name('update');
        Route::delete('/{user}/{role}', [RolePermissionController::class, 'revokeUserRole'])->name('revoke');
    });
});
