<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User;
use App\Models\Group;
use App\Models\GroupMember;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RolePermissionController extends Controller
{
    /**
     * Show roles management page
     */
    public function roles(): View
    {
        $roles = Role::with('permissions')->paginate(15);
        return view('admin.roles.index', compact('roles'));
    }

    /**
     * Show role creation form
     */
    public function createRole(): View
    {
        $permissions = Permission::orderBy('display_name')->get()->groupBy(function ($item) {
            return explode('.', $item->name)[0];
        });

        return view('admin.roles.create', compact('permissions'));
    }

    /**
     * Store new role
     */
    public function storeRole(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:roles|regex:/^[a-z_]+$/',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'permissions' => 'array|exists:permissions,id',
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'display_name' => $validated['display_name'],
            'description' => $validated['description'] ?? null,
            'is_system' => false,
        ]);

        if (!empty($validated['permissions'])) {
            $role->permissions()->sync($validated['permissions']);
        }

        return redirect()->route('admin.roles.index')
            ->with('success', "Role '{$role->display_name}' created successfully");
    }

    /**
     * Show role edit form
     */
    public function editRole(Role $role): View
    {
        if ($role->is_system) {
            abort(403, 'System roles cannot be edited');
        }

        $permissions = Permission::orderBy('display_name')->get()->groupBy(function ($item) {
            return explode('.', $item->name)[0];
        });

        $rolePermissions = $role->permissions()->pluck('id')->toArray();

        return view('admin.roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    /**
     * Update role
     */
    public function updateRole(Request $request, Role $role): RedirectResponse
    {
        if ($role->is_system) {
            abort(403, 'System roles cannot be edited');
        }

        $validated = $request->validate([
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'permissions' => 'array|exists:permissions,id',
        ]);

        $role->update([
            'display_name' => $validated['display_name'],
            'description' => $validated['description'] ?? null,
        ]);

        $role->permissions()->sync($validated['permissions'] ?? []);

        return redirect()->route('admin.roles.index')
            ->with('success', "Role '{$role->display_name}' updated successfully");
    }

    /**
     * Delete role
     */
    public function deleteRole(Role $role): RedirectResponse
    {
        if ($role->is_system) {
            abort(403, 'System roles cannot be deleted');
        }

        if ($role->users()->exists()) {
            return back()->with('error', 'Cannot delete role with assigned users');
        }

        $roleName = $role->display_name;
        $role->delete();

        return redirect()->route('admin.roles.index')
            ->with('success', "Role '{$roleName}' deleted successfully");
    }

    /**
     * Show permissions management page
     */
    public function permissions(): View
    {
        $permissions = Permission::orderBy('name')->paginate(20);
        $grouped = Permission::orderBy('name')->get()->groupBy(function ($item) {
            return explode('.', $item->name)[0];
        });

        return view('admin.permissions.index', compact('permissions', 'grouped'));
    }

    /**
     * Show permission creation form
     */
    public function createPermission(): View
    {
        return view('admin.permissions.create');
    }

    /**
     * Store new permission
     */
    public function storePermission(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:permissions|regex:/^[a-z_\.]+$/',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        Permission::create($validated);

        return redirect()->route('admin.permissions.index')
            ->with('success', "Permission '{$validated['display_name']}' created successfully");
    }

    /**
     * Show permission edit form
     */
    public function editPermission(Permission $permission): View
    {
        return view('admin.permissions.edit', compact('permission'));
    }

    /**
     * Update permission
     */
    public function updatePermission(Request $request, Permission $permission): RedirectResponse
    {
        $validated = $request->validate([
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $permission->update($validated);

        return redirect()->route('admin.permissions.index')
            ->with('success', "Permission '{$permission->display_name}' updated successfully");
    }

    /**
     * Delete permission
     */
    public function deletePermission(Permission $permission): RedirectResponse
    {
        if ($permission->roles()->exists()) {
            return back()->with('error', 'Cannot delete permission assigned to roles');
        }

        $displayName = $permission->display_name;
        $permission->delete();

        return redirect()->route('admin.permissions.index')
            ->with('success', "Permission '{$displayName}' deleted successfully");
    }

    /**
     * Show user roles management page
     */
    public function userRoles(): View
    {
        $users = User::with('userRoles.role')->paginate(20);
        $roles = Role::all();

        return view('admin.user-roles.index', compact('users', 'roles'));
    }

    /**
     * Show user role edit form
     */
    public function editUserRoles(User $user): View
    {
        $roles = Role::all();
        $userRoles = $user->userRoles()->pluck('role_id')->toArray();

        return view('admin.user-roles.edit', compact('user', 'roles', 'userRoles'));
    }

    /**
     * Update user roles
     */
    public function updateUserRoles(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'roles' => 'array|exists:roles,id',
        ]);

        $user->userRoles()->delete();

        if (!empty($validated['roles'])) {
            foreach ($validated['roles'] as $roleId) {
                $user->userRoles()->create([
                    'role_id' => $roleId,
                    'assigned_by' => auth()->id(),
                    'assigned_at' => now(),
                ]);
            }
        }

        return redirect()->route('admin.user-roles.index')
            ->with('success', "User roles updated successfully");
    }

    /**
     * Revoke specific role from user
     */
    public function revokeUserRole(User $user, Role $role): RedirectResponse
    {
        $user->userRoles()->where('role_id', $role->id)->delete();

        return back()->with('success', "Role '{$role->display_name}' revoked from user");
    }

    /**
     * View role assignments for a specific role
     */
    public function roleAssignments(Role $role): View
    {
        $users = $role->users()->with('user')->paginate(20);

        return view('admin.roles.assignments', compact('role', 'users'));
    }

    /**
     * Show group role assignments page
     */
    public function groupRoleAssignments(Group $group): View
    {
        $members = $group->members()
            ->with('user')
            ->orderBy('role')
            ->paginate(20);

        $availableRoles = ['admin', 'treasurer', 'member'];

        return view('admin.groups.role-assignments', compact('group', 'members', 'availableRoles'));
    }

    /**
     * Bulk assign roles to group members
     */
    public function updateGroupRoleAssignments(Group $group, Request $request): RedirectResponse
    {
        if (!auth()->user()->is_admin) {
            abort(403, 'Unauthorized access');
        }

        $validated = $request->validate([
            'members' => 'array',
            'members.*.id' => 'exists:group_members,id',
            'members.*.role' => 'in:admin,treasurer,member',
            'members.*.status' => 'in:active,inactive,suspended',
        ]);

        $updatedCount = 0;

        if (!empty($validated['members'])) {
            foreach ($validated['members'] as $memberData) {
                $member = GroupMember::findOrFail($memberData['id']);

                if ($member->group_id === $group->id) {
                    $member->update([
                        'role' => $memberData['role'],
                        'status' => $memberData['status'],
                    ]);
                    $updatedCount++;
                }
            }
        }

        return redirect()->route('admin.groups.role-assignments.index', $group)
            ->with('success', "{$updatedCount} member role(s) updated successfully");
    }

    /**
     * Show permission matrix for group members
     */
    public function groupPermissionMatrix(Group $group): View
    {
        if (!auth()->user()->is_admin) {
            abort(403, 'Unauthorized access');
        }

        $members = $group->members()->with('user')->get();

        // Define permission matrix by role
        $permissionMatrix = [
            'admin' => [
                'manage_members' => true,
                'manage_finances' => true,
                'approve_loans' => true,
                'approve_savings' => true,
                'view_reports' => true,
                'edit_group' => true,
                'manage_roles' => true,
                'audit_logs' => true,
            ],
            'treasurer' => [
                'manage_members' => false,
                'manage_finances' => true,
                'approve_loans' => true,
                'approve_savings' => true,
                'view_reports' => true,
                'edit_group' => false,
                'manage_roles' => false,
                'audit_logs' => true,
            ],
            'member' => [
                'manage_members' => false,
                'manage_finances' => false,
                'approve_loans' => false,
                'approve_savings' => false,
                'view_reports' => false,
                'edit_group' => false,
                'manage_roles' => false,
                'audit_logs' => false,
            ],
        ];

        return view('admin.groups.permission-matrix', compact('group', 'members', 'permissionMatrix'));
    }
}
