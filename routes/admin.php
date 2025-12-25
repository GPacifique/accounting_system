<?php





















































































































































@endsection</div>    </div>        {{ $members->links() }}    <div class="mt-6">    <!-- Pagination -->    </form>        </div>            </button>                Update All Role Assignments            >                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded"                type="submit"            <button            </a>                Cancel            <a href="{{ route('admin.groups.members.index', $group) }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">        <div class="mt-6 flex justify-between gap-3">        <!-- Form Actions -->        </div>            </div>                </table>                    </tbody>                        @endforelse                            </tr>                                </td>                                    No members in this group                                <td colspan="5" class="px-6 py-8 text-center text-gray-500">                            <tr>                        @empty                            </tr>                                </td>                                    </select>                                        <option value="suspended" {{ $member->status === 'suspended' ? 'selected' : '' }}>Suspended</option>                                        <option value="inactive" {{ $member->status === 'inactive' ? 'selected' : '' }}>Inactive</option>                                        <option value="active" {{ $member->status === 'active' ? 'selected' : '' }}>Active</option>                                    >                                        class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 text-sm"                                        name="members[{{ $loop->index }}][status]"                                    <select                                <td class="px-6 py-4 text-sm">                                </td>                                    </select>                                        <option value="member" {{ $member->role === 'member' ? 'selected' : '' }}>Member</option>                                        <option value="treasurer" {{ $member->role === 'treasurer' ? 'selected' : '' }}>Treasurer</option>                                        <option value="admin" {{ $member->role === 'admin' ? 'selected' : '' }}>Admin</option>                                    >                                        class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 text-sm"                                        name="members[{{ $loop->index }}][role]"                                    <select                                    <input type="hidden" name="members[{{ $loop->index }}][id]" value="{{ $member->id }}">                                    </select>                                        <option value="{{ $member->id }}">{{ $member->id }}</option>                                    >                                        style="display: none;"                                        value="{{ $member->id }}"                                        name="members[{{ $loop->index }}][id]"                                    <select                                <td class="px-6 py-4 text-sm">                                </td>                                    </span>                                        {{ ucfirst($member->role) }}                                    <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $roleColors[$member->role] ?? 'bg-gray-100 text-gray-800' }}">                                    @endphp                                        ];                                            'member' => 'bg-gray-100 text-gray-800'                                            'treasurer' => 'bg-blue-100 text-blue-800',                                            'admin' => 'bg-purple-100 text-purple-800',                                        $roleColors = [                                    @php                                <td class="px-6 py-4 text-sm">                                </td>                                    {{ $member->user->email }}                                <td class="px-6 py-4 text-sm text-gray-600">                                </td>                                    {{ $member->user->name }}                                <td class="px-6 py-4 text-sm text-gray-900 font-medium">                            <tr class="hover:bg-gray-50">                        @forelse ($members as $member)                    <tbody class="divide-y">                    </thead>                        </tr>                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Status</th>                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">New Role</th>                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Current Role</th>                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Email</th>                            <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Member Name</th>                        <tr>                    <thead class="bg-gray-100 border-b">                <table class="w-full">            <div class="overflow-x-auto">        <div class="bg-white rounded-lg shadow-lg overflow-hidden">        <!-- Members Table -->        @method('PUT')        @csrf    <form method="POST" action="{{ route('admin.groups.role-assignments.update', $group) }}">    @endif        </div>            {{ $message }}        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">    @if ($message = Session::get('success'))    <!-- Success Messages -->    </div>        </div>            </div>                <p class="text-xs mt-1">Regular member</p>                <p class="font-semibold">Member</p>            <div class="text-sm text-blue-800">            </div>                <p class="text-xs mt-1">Manages finances</p>                <p class="font-semibold">Treasurer</p>            <div class="text-sm text-blue-800">            </div>                <p class="text-xs mt-1">Full control over group</p>                <p class="font-semibold">Admin</p>            <div class="text-sm text-blue-800">        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">        <h3 class="font-semibold text-blue-900 mb-2">Available Roles</h3>    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">    <!-- Info Box -->    </div>        </div>            </a>                View Permissions            <a href="{{ route('admin.groups.permissions', $group) }}" class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">            </a>                Back to Members            <a href="{{ route('admin.groups.members.index', $group) }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">        <div class="flex gap-3">        </div>            <p class="text-gray-500 mt-1">Manage member roles and permissions in bulk</p>            <h1 class="text-3xl font-bold text-gray-900">Role Assignments - {{ $group->name }}</h1>        <div>    <div class="flex items-center justify-between mb-8">    <!-- Header --><div class="container mx-auto px-4 py-8">@section('content')@section('title', 'Group Role Assignments - ' . $group->name)use Illuminate\Support\Facades\Route;
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
