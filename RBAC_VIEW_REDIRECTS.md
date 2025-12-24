# RBAC View Redirects Implementation

## Overview
This document describes the Role-Based Access Control (RBAC) view redirect system implemented in the application.

## Redirect Flow Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                  User Visits /dashboard                         │
│                   (DashboardController)                         │
└────────────────────┬────────────────────────────────────────────┘
                     │
        ┌────────────┼────────────┐
        │            │            │
        ▼            ▼            ▼
    ┌────────┐  ┌──────────┐  ┌────────┐
    │ TIER 1 │  │ TIER 2   │  │ TIER 3 │
    │ Admin  │  │ Group    │  │ Member │
    │        │  │ Admin    │  │        │
    └────┬───┘  └────┬─────┘  └────┬───┘
         │           │             │
         ▼           ▼             ▼
    /admin/    /group-admin/   /member/
    dashboard  dashboard       dashboard
```

## Role Hierarchy

### TIER 1: System Admin
- **Condition**: `user->is_admin == true`
- **Redirect**: `/dashboard` → `/admin/dashboard`
- **Route Name**: `admin.dashboard`
- **Middleware**: `auth`, `verified`, `admin`
- **Access**: Full system access

### TIER 2: Group Admin
- **Condition**: User has `admin` role in a group (in `group_members` table)
- **Redirect**: `/dashboard` → `/group-admin/dashboard`
- **Route Name**: `group-admin.dashboard`
- **Middleware**: `auth`, `verified`, `group.admin`
- **Access**: Manage single group and its members

### TIER 3: Regular Member
- **Condition**: User has `member`, `treasurer`, or `secretary` role in a group
- **Redirect**: `/dashboard` → `/member/dashboard`
- **Route Name**: `member.dashboard`
- **Middleware**: `auth`, `verified`
- **Access**: View-only access to own records

## Implementation Details

### 1. Dashboard Controller (Entry Point)
**File**: `app/Http/Controllers/DashboardController.php`

```php
public function index()
{
    $user = Auth::user();

    // TIER 1: System Admin
    if ($user->is_admin) {
        return redirect()->route('admin.dashboard');
    }

    // TIER 2: Group Admin
    $groupAdmin = GroupMember::where('user_id', $user->id)
        ->where('role', 'admin')
        ->where('status', 'active')
        ->first();

    if ($groupAdmin) {
        return redirect()->route('group-admin.dashboard');
    }

    // TIER 3: Regular Member
    return redirect()->route('member.dashboard');
}
```

**Logic**:
1. Check if user is a system admin
2. Check if user is a group admin
3. Default to member dashboard (regular group member)

### 2. Web Routes Configuration
**File**: `routes/web.php`

#### Main Dashboard Route
```php
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');
```

#### Group Admin Routes
```php
Route::prefix('group-admin')->name('group-admin.')->middleware('group.admin')->group(function () {
    Route::get('/dashboard', [GroupAdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/groups/{group}/loans', [GroupAdminDashboardController::class, 'loans'])->name('loans');
    Route::get('/groups/{group}/savings', [GroupAdminDashboardController::class, 'savings'])->name('savings');
    Route::get('/groups/{group}/members', [GroupAdminDashboardController::class, 'members'])->name('members');
    Route::get('/groups/{group}/transactions', [GroupAdminDashboardController::class, 'transactions'])->name('transactions');
    Route::get('/groups/{group}/reports', [GroupAdminDashboardController::class, 'reports'])->name('reports');
});
```

#### Member Routes
```php
Route::prefix('member')->name('member.')->group(function () {
    Route::get('/dashboard', [MemberDashboardController::class, 'index'])->name('dashboard');
    Route::get('/loans', [MemberDashboardController::class, 'myLoans'])->name('loans');
    Route::get('/savings', [MemberDashboardController::class, 'mySavings'])->name('savings');
    Route::get('/transactions', [MemberDashboardController::class, 'myTransactions'])->name('transactions');
});
```

### 3. Middleware Stack

#### CheckGroupAdminAccess (group.admin)
**File**: `app/Http/Middleware/CheckGroupAdminAccess.php`

```php
public function handle(Request $request, Closure $next): Response
{
    // Redirect system admins to admin dashboard
    if (Auth::user()->is_admin) {
        return redirect()->route('admin.dashboard');
    }

    // Check if user is a group admin
    $groupAdmin = GroupMember::where('user_id', Auth::user()->id)
        ->where('role', 'admin')
        ->where('status', 'active')
        ->first();

    if (!$groupAdmin) {
        return redirect()->route('member.dashboard')
            ->with('warning', 'You do not have admin access to any group.');
    }

    return $next($request);
}
```

**Purpose**: Ensures only group admins can access group admin routes

#### AdminMiddleware (admin)
**File**: `app/Http/Middleware/AdminMiddleware.php`

**Purpose**: Ensures only system admins can access admin routes

## Request Flow Examples

### Example 1: System Admin Access
1. User logs in (is_admin = true)
2. User visits `/dashboard`
3. DashboardController detects is_admin = true
4. Redirects to `/admin/dashboard`
5. AdminMiddleware verifies is_admin
6. Admin dashboard displayed

### Example 2: Group Admin Access
1. User logs in (is_admin = false)
2. User has admin role in a group
3. User visits `/dashboard`
4. DashboardController checks group_members table
5. Finds admin role entry
6. Redirects to `/group-admin/dashboard`
7. CheckGroupAdminAccess middleware verifies group admin status
8. Group admin dashboard displayed

### Example 3: Regular Member Access
1. User logs in (is_admin = false)
2. User is not a group admin
3. User visits `/dashboard`
4. DashboardController redirects to `/member/dashboard`
5. Member dashboard displayed (view-only)

## Database Tables Used

### users
- `id`: Primary key
- `is_admin`: Boolean flag for system admin

### group_members
- `user_id`: Foreign key to users
- `group_id`: Foreign key to groups
- `role`: Enum (admin, member, treasurer, secretary)
- `status`: Enum (active, inactive, suspended)

## Route Names Reference

| Route | Name | Controller | Access Level |
|-------|------|------------|--------------|
| `/dashboard` | `dashboard` | DashboardController | Auth + Verified |
| `/admin/dashboard` | `admin.dashboard` | AdminDashboardController | System Admin |
| `/group-admin/dashboard` | `group-admin.dashboard` | GroupAdminDashboardController | Group Admin |
| `/member/dashboard` | `member.dashboard` | MemberDashboardController | All Members |

## Blade Template Usage

### Accessing Dashboard Route
```blade
<!-- Generic dashboard (will redirect based on role) -->
<a href="{{ route('dashboard') }}">Dashboard</a>

<!-- Direct access by role -->
<a href="{{ route('admin.dashboard') }}">Admin Dashboard</a>
<a href="{{ route('group-admin.dashboard') }}">Group Admin Dashboard</a>
<a href="{{ route('member.dashboard') }}">Member Dashboard</a>
```

### Conditional Navigation
```blade
@if(auth()->user()->is_admin)
    <li><a href="{{ route('admin.dashboard') }}">Admin Dashboard</a></li>
@elseif(auth()->user()->isGroupAdmin(auth()->user()->getCurrentGroup()))
    <li><a href="{{ route('group-admin.dashboard') }}">Group Admin Dashboard</a></li>
@else
    <li><a href="{{ route('member.dashboard') }}">Member Dashboard</a></li>
@endif
```

## Testing RBAC Redirects

### Test Accounts

1. **System Admin**
   - Email: `admin@itsinda.local`
   - Password: `AdminPassword123!`
   - is_admin: true

2. **Group Admin**
   - Create through group registration
   - Must have `admin` role in group_members table

3. **Regular Member**
   - Create through group registration
   - Has `member`, `treasurer`, or `secretary` role

### Manual Testing Steps

1. **Test System Admin Redirect**
   ```
   Login with admin account
   Visit /dashboard
   Should redirect to /admin/dashboard
   ```

2. **Test Group Admin Redirect**
   ```
   Login with group admin account
   Visit /dashboard
   Should redirect to /group-admin/dashboard
   ```

3. **Test Member Redirect**
   ```
   Login with member account
   Visit /dashboard
   Should redirect to /member/dashboard
   ```

## Security Considerations

1. **Middleware Chain**: All dashboard routes require `auth` and `verified` middleware
2. **Role Verification**: Controllers re-verify user role before displaying sensitive data
3. **Database Queries**: Group membership checked in both controller and middleware
4. **Redirect Safety**: Invalid role attempts redirect to appropriate tier level
5. **Admin Escalation**: System admins cannot be redirected to lower tiers

## Future Enhancements

- [ ] Add role-based sidebar navigation
- [ ] Implement permission-based feature flags
- [ ] Add audit logging for role changes
- [ ] Create RBAC policy classes
- [ ] Add email notifications for role assignments

## Related Files

- `routes/web.php` - Web route definitions
- `routes/admin.php` - Admin-specific routes
- `app/Http/Controllers/DashboardController.php` - Redirect logic
- `app/Http/Middleware/CheckGroupAdminAccess.php` - Group admin middleware
- `app/Http/Middleware/AdminMiddleware.php` - Admin middleware
- `app/Http/Kernel.php` - Middleware registration
