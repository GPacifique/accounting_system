<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    /**
     * Get user's group memberships
     */
    public function groupMembers()
    {
        return $this->hasMany(GroupMember::class);
    }

    /**
     * Get user's groups through group members
     */
    public function groups()
    {
        return $this->belongsToMany(Group::class, 'group_members')
                    ->withPivot('role', 'status', 'joined_at', 'left_at')
                    ->withTimestamps();
    }

    /**
     * Get user's active groups
     */
    public function activeGroups()
    {
        return $this->groups()
                    ->where('group_members.status', 'active')
                    ->where('groups.status', 'active');
    }

    /**
     * Check if user belongs to a specific group
     */
    public function belongsToGroup(Group|int $group): bool
    {
        $groupId = $group instanceof Group ? $group->id : $group;

        return $this->groupMembers()
                    ->where('group_id', $groupId)
                    ->where('status', 'active')
                    ->exists();
    }

    /**
     * Check if user is admin of a group
     */
    public function isGroupAdmin(Group|int $group): bool
    {
        $groupId = $group instanceof Group ? $group->id : $group;

        return $this->groupMembers()
                    ->where('group_id', $groupId)
                    ->where('role', 'admin')
                    ->where('status', 'active')
                    ->exists();
    }

    /**
     * Check if user is treasurer of a group
     */
    public function isGroupTreasurer(Group|int $group): bool
    {
        $groupId = $group instanceof Group ? $group->id : $group;

        return $this->groupMembers()
                    ->where('group_id', $groupId)
                    ->where('role', 'treasurer')
                    ->where('status', 'active')
                    ->exists();
    }

    /**
     * Get user's role in a specific group
     */
    public function getGroupRole(Group|int $group): ?string
    {
        $groupId = $group instanceof Group ? $group->id : $group;

        return $this->groupMembers()
                    ->where('group_id', $groupId)
                    ->where('status', 'active')
                    ->value('role');
    }

    /**
     * Get current group from session or first active group
     */
    public function getCurrentGroup(): ?Group
    {
        $groupId = session('current_group_id');

        if ($groupId && $this->belongsToGroup($groupId)) {
            return Group::find($groupId);
        }

        return $this->activeGroups()->first();
    }

    /**
     * Set current group in session
     */
    public function setCurrentGroup(Group|int $group): void
    {
        $groupId = $group instanceof Group ? $group->id : $group;

        if ($this->belongsToGroup($groupId)) {
            session(['current_group_id' => $groupId]);
        }
    }

    /**
     * Get user's system roles
     */
    public function userRoles()
    {
        return $this->hasMany(UserRole::class);
    }

    /**
     * Get user's roles through user roles
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }

    /**
     * Check if user has a specific role
     */
    public function hasRole(string|array $roles): bool
    {
        if (is_string($roles)) {
            return $this->roles()->where('name', $roles)->exists();
        }

        return $this->roles()->whereIn('name', $roles)->exists();
    }

    /**
     * Check if user has a specific permission through roles
     */
    public function hasPermission(string $permission): bool
    {
        return $this->roles()
            ->whereHas('permissions', function ($query) use ($permission) {
                $query->where('name', $permission);
            })
            ->exists();
    }

    /**
     * Get all permissions for user through roles
     */
    public function getPermissions()
    {
        return $this->roles()
            ->with('permissions')
            ->get()
            ->flatMap(function ($role) {
                return $role->permissions;
            })
            ->unique('id');
    }

    /**
     * Get groups where user is an admin (for group admin dashboard)
     */
    public function groupAdminGroups()
    {
        return $this->groupMembers()
                    ->where('role', 'admin')
                    ->where('status', 'active')
                    ->with('group')
                    ->get()
                    ->pluck('group');
    }

    /**
     * Check if user is member of any group
     */
    public function isMemberOfGroup(): bool
    {
        return $this->groupMembers()
                    ->where('status', 'active')
                    ->exists();
    }

    /**
     * Check if user is group admin of any group
     */
    public function isGroupAdminOfAny(): bool
    {
        return $this->groupMembers()
                    ->where('role', 'admin')
                    ->where('status', 'active')
                    ->exists();
    }
}
