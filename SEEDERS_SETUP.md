# Roles and Permissions Seeders Setup Guide

## Overview

The application includes comprehensive seeders to initialize roles and permissions in the database. This guide explains how to set up and use these seeders.

## Seeders Included

### 1. **RolesAndPermissionsSeeder**
Initializes default system roles and permissions with proper permission assignments.

### 2. **AdminUserSeeder** (Existing)
Creates a default admin user account.

### 3. **DatabaseSeeder** (Main)
Orchestrates the execution of all seeders.

---

## Permissions Structure

### User Management Permissions
- `users.view` - View all users
- `users.create` - Create new users
- `users.edit` - Edit user details
- `users.delete` - Delete users
- `users.manage_roles` - Assign/revoke user roles

### Role Management Permissions
- `roles.view` - View all roles
- `roles.create` - Create new roles
- `roles.edit` - Edit role details
- `roles.delete` - Delete roles
- `roles.manage_permissions` - Assign/revoke permissions to roles

### Permission Management Permissions
- `permissions.view` - View all permissions
- `permissions.create` - Create new permissions
- `permissions.edit` - Edit permission details
- `permissions.delete` - Delete permissions

### Group Management Permissions
- `groups.view` - View all groups
- `groups.approve` - Approve pending groups
- `groups.edit` - Edit group details
- `groups.delete` - Delete groups

### Loan Management Permissions
- `loans.view` - View all loans
- `loans.approve` - Approve pending loans
- `loans.manage` - Manage loan details

### Reports Permissions
- `reports.view` - View system reports
- `reports.export` - Export reports

### Settings Permissions
- `settings.view` - View system settings
- `settings.manage` - Manage system settings

---

## Default Roles

### 1. **Super Admin**
**Complete system access with all permissions**
- All user management
- Full role and permission management
- Full group management
- Full loan management
- Report access and export
- System settings management

### 2. **System Admin**
**Administrative access with restricted deletion**
- User management (except delete)
- Role and permission management (view/edit only)
- Group approval and editing
- Loan approval and management
- Report access and export
- System settings management

### 3. **Group Admin**
**Group-level administrative access**
- View groups
- Manage loans in their group
- View reports

### 4. **Auditor**
**Read-only access for auditing**
- View all users
- View all groups
- View all loans
- View and export reports

### 5. **Support Staff**
**Limited access for support operations**
- View users
- View groups
- View loans
- View reports

---

## Running the Seeders

### Option 1: Run All Seeders
```bash
php artisan db:seed
```
This runs the `DatabaseSeeder` which includes all configured seeders.

### Option 2: Run Specific Seeder
```bash
# Run only roles and permissions seeder
php artisan db:seed --class=RolesAndPermissionsSeeder

# Run only admin user seeder
php artisan db:seed --class=AdminUserSeeder
```

### Option 3: Fresh Seed (Reset Database)
**WARNING: This deletes all existing data!**
```bash
php artisan migrate:fresh --seed
```
This will:
1. Drop all tables
2. Re-run all migrations
3. Run all seeders (roles, permissions, and admin user)

---

## Database Tables

The seeders work with these tables:

### `roles`
```sql
- id (int)
- name (varchar) - Unique identifier (e.g., 'super_admin')
- display_name (varchar) - Human-readable name
- description (text) - Role description
- is_system (boolean) - True for built-in roles
- created_at, updated_at
```

### `permissions`
```sql
- id (int)
- name (varchar) - Unique identifier (e.g., 'users.view')
- display_name (varchar) - Human-readable name
- description (text) - Permission description
- created_at, updated_at
```

### `role_permissions` (Pivot Table)
```sql
- role_id (int) - Foreign key to roles
- permission_id (int) - Foreign key to permissions
```

### `user_roles` (Pivot Table)
```sql
- user_id (int) - Foreign key to users
- role_id (int) - Foreign key to roles
```

---

## Adding Custom Permissions

To add custom permissions, edit the `RolesAndPermissionsSeeder.php`:

```php
$permissions = [
    // ... existing permissions ...
    
    // New Custom Permission
    ['name' => 'custom.action', 'display_name' => 'Custom Action', 'description' => 'Description of custom action'],
];
```

Then assign it to roles:

```php
$rolePermissions = [
    'system_admin' => [
        // ... existing permissions ...
        'custom.action',  // Add new permission here
    ],
];
```

Run the seeder:
```bash
php artisan db:seed --class=RolesAndPermissionsSeeder
```

---

## Adding Custom Roles

To add custom roles, edit the `RolesAndPermissionsSeeder.php`:

```php
$rolePermissions = [
    // ... existing roles ...
    
    'custom_role' => [
        'permission.one',
        'permission.two',
        'permission.three',
    ],
];
```

Run the seeder:
```bash
php artisan db:seed --class=RolesAndPermissionsSeeder
```

---

## Verification

After running the seeders, verify the data was created:

```bash
# Check roles
php artisan tinker
>>> App\Models\Role::all();

# Check permissions
>>> App\Models\Permission::all();

# Check role permissions
>>> App\Models\Role::with('permissions')->first();
```

Or query via SQL:
```sql
SELECT * FROM roles;
SELECT * FROM permissions;
SELECT r.name, p.name FROM role_permissions 
    JOIN roles r ON role_permissions.role_id = r.id 
    JOIN permissions p ON role_permissions.permission_id = p.id;
```

---

## Assigning Roles to Users

### Via Artisan Tinker

```bash
php artisan tinker
```

```php
// Find user and role
$user = App\Models\User::find(1);
$role = App\Models\Role::where('name', 'system_admin')->first();

// Assign role
$user->roles()->attach($role->id);

// Or multiple roles
$user->roles()->attach([role_id_1, role_id_2]);

// Check user roles
$user->roles;

// Revoke role
$user->roles()->detach($role->id);
```

### Via Admin Dashboard

Once roles and permissions are seeded:
1. Go to **Admin Dashboard** → **Assign User Roles**
2. Select a user
3. Check/uncheck roles to assign/revoke
4. Save changes

---

## Important Notes

- **System Roles**: Roles marked with `is_system = true` are built-in and shouldn't be deleted
- **Permission Sync**: Using `sync()` replaces all permissions; use `attach()` to add without removing existing
- **Idempotent**: Seeders use `firstOrCreate()` so running them multiple times won't create duplicates
- **Fresh Seed**: Always backup production data before running `migrate:fresh --seed`

---

## Troubleshooting

### "Table doesn't exist" Error
```bash
# Run migrations first
php artisan migrate
# Then run seeders
php artisan db:seed
```

### Duplicate Permissions/Roles
The seeders use `firstOrCreate()` which prevents duplicates. If you get errors:
```bash
# Clear and reseed
php artisan db:seed --class=RolesAndPermissionsSeeder
```

### Permission Not Working
1. Verify permission exists: `php artisan tinker` → `App\Models\Permission::where('name', 'permission.name')->exists()`
2. Verify user role assigned: `user->roles`
3. Verify role has permission: `role->permissions`

---

## Related Files

- [RolesAndPermissionsSeeder](database/seeders/RolesAndPermissionsSeeder.php)
- [Role Model](app/Models/Role.php)
- [Permission Model](app/Models/Permission.php)
- [User Model](app/Models/User.php)
- [Roles and Permissions Migration](database/migrations/2025_12_24_000003_create_roles_and_permissions_tables.php)
