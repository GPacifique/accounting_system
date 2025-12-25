# RBAC Dashboard Middleware - Complete Guide

## Overview

The updated middleware system provides intelligent, role-based dashboard redirection ensuring users are always routed to the correct dashboard based on their role hierarchy.

---

## Role Hierarchy

```
┌─────────────────────────────────────────────────┐
│ TIER 1: System Admin (is_admin = true)          │
│ Dashboard: /admin/dashboard                     │
│ Access: Full system-wide control                │
└─────────────────────────────────────────────────┘
                    ↓
┌─────────────────────────────────────────────────┐
│ TIER 2: Group Admin (role='admin' in group)     │
│ Dashboard: /group-admin/dashboard               │
│ Access: Manage assigned groups & members        │
└─────────────────────────────────────────────────┘
                    ↓
┌─────────────────────────────────────────────────┐
│ TIER 3: Regular Member (role='member' or        │
│        role='treasurer' in group)               │
│ Dashboard: /member/dashboard                    │
│ Access: View-only own records & data            │
└─────────────────────────────────────────────────┘
```

---

## Middleware Components

### 1. **DashboardRedirect** (NEW)
**Purpose:** Global middleware for automatic dashboard route redirection
**File:** `app/Http/Middleware/DashboardRedirect.php`

**Features:**
- Checks user's role hierarchy
- Automatically redirects to appropriate dashboard
- Prevents redirect loops on dashboard routes
- Caches role checks for performance

**Usage:**
```php
// In middleware groups or routes
Route::middleware('dashboard.redirect')->group(function() {
    // Routes here get automatic redirect to correct dashboard
});
```

**Flow:**
1. Check if user is System Admin → `/admin/dashboard`
2. Check if user is Group Admin → `/group-admin/dashboard`
3. Check if user is Group Member → `/member/dashboard`
4. Continue if already on dashboard route

---

### 2. **AdminMiddleware** (ENHANCED)
**Purpose:** Enforce System Admin access with intelligent redirects
**File:** `app/Http/Middleware/AdminMiddleware.php`

**Features:**
- Validates is_admin = true
- Redirects Group Admins to group-admin dashboard
- Redirects Members to member dashboard
- Provides user feedback via flash messages

**Usage:**
```php
Route::middleware('admin')->group(function() {
    Route::get('/admin/dashboard', ...);
    Route::post('/admin/users/create', ...);
});
```

**Behavior:**
- ✅ User is System Admin → Allow access
- ❌ User is Group Admin → Redirect to group-admin dashboard
- ❌ User is Member → Redirect to member dashboard
- ❌ User not authenticated → Redirect to login

---

### 3. **CheckGroupAdminAccess** (ENHANCED)
**Purpose:** Enforce Group Admin access with role validation
**File:** `app/Http/Middleware/CheckGroupAdminAccess.php`

**Features:**
- Validates user has 'admin' role in at least one group
- Redirects System Admins (they should use admin routes)
- Redirects Members to member dashboard
- Stores group_admin info in request
- Handles inactive/deleted group members

**Usage:**
```php
Route::middleware('group.admin')->group(function() {
    Route::get('/group-admin/dashboard', ...);
    Route::post('/groups/{group}/update', ...);
});
```

**Behavior:**
- ✅ User is Group Admin → Allow access + store group info
- ❌ User is System Admin → Redirect to admin dashboard
- ❌ User is Member → Redirect to member dashboard
- ❌ User not in any group → Redirect to main dashboard

---

### 4. **CheckMemberAccess** (NEW)
**Purpose:** Enforce Member access with role validation
**File:** `app/Http/Middleware/CheckMemberAccess.php`

**Features:**
- Validates user is member of at least one group
- Accepts 'member' or 'treasurer' roles
- Redirects System Admins to admin dashboard
- Redirects Group Admins to group-admin dashboard
- Stores member info in request
- Only allows 'active' status members

**Usage:**
```php
Route::middleware('member.access')->group(function() {
    Route::get('/member/dashboard', ...);
    Route::get('/member/loans', ...);
});
```

**Behavior:**
- ✅ User is Member (active) → Allow access + store member info
- ❌ User is System Admin → Redirect to admin dashboard
- ❌ User is Group Admin → Redirect to group-admin dashboard
- ❌ User not in any group → Redirect to main dashboard

---

## Middleware Configuration

All middleware is registered in `app/Http/Kernel.php`:

```php
protected $routeMiddleware = [
    // ... other middleware ...
    
    'group.admin' => \App\Http\Middleware\CheckGroupAdminAccess::class,
    'member.access' => \App\Http\Middleware\CheckMemberAccess::class,
    'dashboard.redirect' => \App\Http\Middleware\DashboardRedirect::class,
    'admin' => \App\Http\Middleware\AdminMiddleware::class,
    'admin.check' => \App\Http\Middleware\CheckAdminStatus::class,
];
```

---

## Implementation in Routes

### Example Route Structure

```php
// Main dashboard with auto-redirect based on role
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified', 'dashboard.redirect'])
    ->name('dashboard');

// System Admin Routes - Enforce admin role
Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])
        ->name('admin.dashboard');
    Route::post('/admin/users/create', ...);
    // ... other admin routes
});

// Group Admin Routes - Enforce group admin role
Route::middleware(['auth', 'verified', 'group.admin'])->group(function () {
    Route::get('/group-admin/dashboard', [GroupAdminDashboardController::class, 'index'])
        ->name('group-admin.dashboard');
    Route::post('/groups/{group}/update', ...);
    // ... other group admin routes
});

// Member Routes - Enforce member access
Route::middleware(['auth', 'verified', 'member.access'])->group(function () {
    Route::get('/member/dashboard', [MemberDashboardController::class, 'index'])
        ->name('member.dashboard');
    Route::get('/member/loans', [MemberDashboardController::class, 'myLoans'])
        ->name('member.loans');
    // ... other member routes
});
```

---

## Access Control Matrix

| User Type | Can Access | Redirects To |
|-----------|-----------|--------------|
| System Admin accessing `/dashboard` | Yes | `/admin/dashboard` |
| System Admin accessing `/group-admin/*` | No | `/admin/dashboard` with info message |
| System Admin accessing `/member/*` | No | `/admin/dashboard` with info message |
| Group Admin accessing `/dashboard` | Yes | `/group-admin/dashboard` |
| Group Admin accessing `/admin/*` | No | `/group-admin/dashboard` with warning |
| Group Admin accessing `/member/*` | No | `/member/dashboard` with warning |
| Member accessing `/dashboard` | Yes | `/member/dashboard` |
| Member accessing `/admin/*` | No | `/login` or error page |
| Member accessing `/group-admin/*` | No | `/member/dashboard` with warning |
| Non-member accessing any protected route | No | `/dashboard` or `/login` |

---

## Flash Messages

Middleware automatically provides user feedback:

```php
// AdminMiddleware
->with('warning', 'You do not have system admin access. Showing group admin dashboard.');

// CheckGroupAdminAccess
->with('warning', 'You do not have admin access to any group. Showing member dashboard instead.');

// CheckMemberAccess
->with('warning', 'You are not an active member of any group.');
```

Display in Blade template:
```blade
@if ($message = Session::get('warning'))
    <div class="alert alert-warning">
        {{ $message }}
    </div>
@endif
```

---

## Request Data Injection

Middleware stores useful data in the request:

### CheckGroupAdminAccess
```php
// Available in controller
$groupAdmin = $request->group_admin;    // GroupMember model
$adminGroupId = $request->admin_group_id; // Group ID
```

### CheckMemberAccess
```php
// Available in controller
$userMember = $request->user_member;    // GroupMember model
```

### DashboardRedirect
No data injection (informational only)

---

## Usage Examples

### Example 1: Protect Admin Routes
```php
Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index']);
    Route::resource('users', AdminUsersController::class);
});

// Non-admin users automatically redirected to their dashboard
```

### Example 2: Protect Group Admin Routes
```php
Route::prefix('group-admin')->middleware(['auth', 'group.admin'])->group(function () {
    Route::get('/dashboard', [GroupAdminController::class, 'index']);
    Route::resource('groups.members', GroupMembersController::class);
    
    // In controller:
    public function index(Request $request) {
        $groupAdmin = $request->group_admin;
        $group = $groupAdmin->group;
        // ...
    }
});

// System admins and non-admins automatically redirected
```

### Example 3: Protect Member Routes
```php
Route::prefix('member')->middleware(['auth', 'member.access'])->group(function () {
    Route::get('/dashboard', [MemberDashboardController::class, 'index']);
    Route::get('/loans', [MemberDashboardController::class, 'myLoans']);
    
    // In controller:
    public function myLoans(Request $request) {
        $member = $request->user_member;
        $loans = $member->loans;
        // ...
    }
});

// Admins automatically redirected to their dashboards
```

### Example 4: Smart Dashboard with Auto-Redirect
```php
Route::get('/dashboard', function (Request $request) {
    // This will automatically redirect based on role
})
->middleware(['auth', 'verified', 'dashboard.redirect'])
->name('dashboard');

// User flow:
// System Admin → /admin/dashboard
// Group Admin → /group-admin/dashboard
// Member → /member/dashboard
```

---

## Security Features

✅ **Role Hierarchy Enforcement**
- Prevents privilege escalation
- Users can't access higher-tier dashboards

✅ **Intelligent Redirection**
- No 403 errors - graceful redirects
- Users see appropriate dashboard for their role

✅ **Status Validation**
- Only 'active' members bypass checks
- Inactive/suspended members denied access

✅ **Request Injection**
- Pre-loaded group/member data for controllers
- Reduces database queries

✅ **Loop Prevention**
- Dashboard redirect middleware skips itself
- No infinite redirect scenarios

✅ **User Feedback**
- Flash messages explain redirects
- Better UX than error pages

---

## Testing the Middleware

### Test System Admin Access
```php
$admin = User::factory()->create(['is_admin' => true]);
$this->actingAs($admin)
    ->get('/admin/dashboard')
    ->assertOk();
```

### Test Group Admin Access
```php
$user = User::factory()->create();
$group = Group::factory()->create();
GroupMember::create([
    'user_id' => $user->id,
    'group_id' => $group->id,
    'role' => 'admin',
    'status' => 'active',
]);

$this->actingAs($user)
    ->get('/group-admin/dashboard')
    ->assertOk();
```

### Test Member Access
```php
$user = User::factory()->create();
$group = Group::factory()->create();
GroupMember::create([
    'user_id' => $user->id,
    'group_id' => $group->id,
    'role' => 'member',
    'status' => 'active',
]);

$this->actingAs($user)
    ->get('/member/dashboard')
    ->assertOk();
```

### Test Redirect on Wrong Access
```php
$admin = User::factory()->create(['is_admin' => true]);

// Admin trying to access group-admin area
$this->actingAs($admin)
    ->get('/group-admin/dashboard')
    ->assertRedirect('/admin/dashboard');
```

---

## Performance Considerations

### Query Optimization
Middleware uses `.with('group')` for eager loading:
```php
GroupMember::where('user_id', $user->id)
    ->where('role', 'admin')
    ->with('group')  // Eager load related group
    ->first();
```

### Caching Suggestion
For high-traffic applications, cache role checks:
```php
Cache::remember("user_role_{$user->id}", 3600, function () {
    return $this->getUserGroupAdminRole($user);
});
```

---

## Troubleshooting

### Issue: Infinite Redirect Loop
**Solution:** Middleware checks `isOnDashboardRoute()` to skip already-on-dashboard routes

### Issue: User Seeing Wrong Dashboard
**Solution:** Check role hierarchy in database:
```sql
SELECT * FROM group_members WHERE user_id = ? AND status = 'active';
```

### Issue: Admin Redirected to Group Admin
**Solution:** Ensure user has `is_admin = true` flag:
```sql
UPDATE users SET is_admin = true WHERE id = ?;
```

### Issue: Routes Not Protected
**Solution:** Verify middleware is registered in Kernel and applied to routes

---

## Best Practices

✅ **Always use hierarchy:**
- Admin middleware before group admin middleware
- Group admin middleware before member middleware

✅ **Provide feedback:**
- Use flash messages to explain redirects
- Don't silently redirect users

✅ **Test role transitions:**
- Test when user's role changes
- Test when group membership changes

✅ **Monitor access logs:**
- Track unusual redirect patterns
- Identify unauthorized access attempts

✅ **Keep middleware lean:**
- Delegate complex logic to controllers/services
- Middleware should only validate roles

---

## Summary

The updated RBAC middleware system provides:

✅ Intelligent role-based routing  
✅ Graceful redirects instead of errors  
✅ Clear role hierarchy enforcement  
✅ User-friendly error messages  
✅ Request data pre-loading  
✅ Security against privilege escalation  
✅ Easy to test and maintain  

This ensures users are always working in the correct dashboard for their role!
