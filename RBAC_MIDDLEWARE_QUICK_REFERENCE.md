# RBAC Dashboard Middleware - Quick Reference

## Middleware Summary

| Middleware | File | Purpose | Redirects |
|-----------|------|---------|-----------|
| `dashboard.redirect` | `DashboardRedirect.php` | Auto-route to correct dashboard | Admin → Admin, GroupAdmin → GroupAdmin, Member → Member |
| `admin` | `AdminMiddleware.php` | Enforce system admin access | Non-admins to appropriate dashboard |
| `group.admin` | `CheckGroupAdminAccess.php` | Enforce group admin access | Non-group-admins to member or admin |
| `member.access` | `CheckMemberAccess.php` | Enforce member access | Non-members to dashboard |

---

## Quick Implementation

### Step 1: Register Middleware
**File:** `app/Http/Kernel.php`

```php
protected $routeMiddleware = [
    // ...existing...
    'group.admin' => \App\Http\Middleware\CheckGroupAdminAccess::class,
    'member.access' => \App\Http\Middleware\CheckMemberAccess::class,
    'dashboard.redirect' => \App\Http\Middleware\DashboardRedirect::class,
    'admin' => \App\Http\Middleware\AdminMiddleware::class,
];
```

### Step 2: Apply to Routes
**File:** `routes/web.php`

```php
// Main dashboard (with auto-redirect)
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified', 'dashboard.redirect'])
    ->name('dashboard');

// Admin routes
Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
});

// Group admin routes
Route::middleware(['auth', 'verified', 'group.admin'])->prefix('group-admin')->group(function () {
    Route::get('/dashboard', [GroupAdminDashboardController::class, 'index'])->name('group-admin.dashboard');
});

// Member routes
Route::middleware(['auth', 'verified', 'member.access'])->prefix('member')->group(function () {
    Route::get('/dashboard', [MemberDashboardController::class, 'index'])->name('member.dashboard');
});
```

---

## Redirect Matrix (What Happens When)

```
┌─────────────────────────────────────────────────────────┐
│ User Type       │ Accessing      │ Result              │
├─────────────────────────────────────────────────────────┤
│ Admin           │ /admin/*       │ ✓ Allow             │
│ Admin           │ /group-admin/*  │ ✗ → /admin/dash     │
│ Admin           │ /member/*      │ ✗ → /admin/dash     │
│ Admin           │ /dashboard     │ ✓ → /admin/dash     │
├─────────────────────────────────────────────────────────┤
│ GroupAdmin      │ /admin/*       │ ✗ → /group-admin    │
│ GroupAdmin      │ /group-admin/*  │ ✓ Allow             │
│ GroupAdmin      │ /member/*      │ ✗ → /member/dash    │
│ GroupAdmin      │ /dashboard     │ ✓ → /group-admin    │
├─────────────────────────────────────────────────────────┤
│ Member          │ /admin/*       │ ✗ → 403             │
│ Member          │ /group-admin/*  │ ✗ → /member/dash    │
│ Member          │ /member/*      │ ✓ Allow             │
│ Member          │ /dashboard     │ ✓ → /member/dash    │
├─────────────────────────────────────────────────────────┤
│ Non-Member      │ /*/dashboard   │ ✗ → /dashboard      │
└─────────────────────────────────────────────────────────┘
```

---

## Usage in Controllers

### Access Group Admin Data
```php
class GroupAdminController {
    public function update(Request $request, Group $group) {
        $groupAdmin = $request->group_admin;      // GroupMember model
        $adminGroup = $groupAdmin->group;         // Group model
        $adminGroupId = $request->admin_group_id; // Group ID
        
        // Process update...
    }
}
```

### Access Member Data
```php
class MemberController {
    public function loans(Request $request) {
        $member = $request->user_member;    // GroupMember model
        $loans = $member->loans()->get();   // Get member's loans
        
        return view('member.loans', ['loans' => $loans]);
    }
}
```

---

## Error Messages

Middleware automatically sends flash messages:

| Scenario | Message | Type |
|----------|---------|------|
| Admin accessing group-admin | "You have system admin access..." | info |
| GroupAdmin accessing admin | "You do not have system admin access..." | warning |
| GroupAdmin accessing member | "You do not have admin access..." | warning |
| Member accessing restricted | "You are not an active member..." | warning |

**Display in Views:**
```blade
@if ($message = Session::get('warning'))
    <div class="alert alert-warning">{{ $message }}</div>
@endif
@if ($message = Session::get('info'))
    <div class="alert alert-info">{{ $message }}</div>
@endif
```

---

## Database Requirements

### User Table
```sql
ALTER TABLE users ADD COLUMN is_admin BOOLEAN DEFAULT false;
```

### GroupMember Table
```sql
-- Must have these columns:
- user_id (foreign key to users)
- group_id (foreign key to groups)
- role (enum: 'admin', 'member', 'treasurer')
- status (enum: 'active', 'inactive', 'suspended')
```

---

## Testing Examples

### Test Admin Access
```php
public function test_admin_can_access_admin_dashboard() {
    $admin = User::factory()->create(['is_admin' => true]);
    $response = $this->actingAs($admin)->get('/admin/dashboard');
    $this->assertEquals(200, $response->status());
}
```

### Test GroupAdmin Redirect
```php
public function test_group_admin_redirects_from_admin_dashboard() {
    $user = User::factory()->create();
    $group = Group::factory()->create();
    GroupMember::create([
        'user_id' => $user->id,
        'group_id' => $group->id,
        'role' => 'admin',
        'status' => 'active'
    ]);
    
    $response = $this->actingAs($user)->get('/admin/dashboard');
    $this->assertRedirect('/group-admin/dashboard');
}
```

### Test Member Access
```php
public function test_member_can_access_member_dashboard() {
    $user = User::factory()->create();
    $group = Group::factory()->create();
    GroupMember::create([
        'user_id' => $user->id,
        'group_id' => $group->id,
        'role' => 'member',
        'status' => 'active'
    ]);
    
    $response = $this->actingAs($user)->get('/member/dashboard');
    $this->assertEquals(200, $response->status());
}
```

---

## Common Issues & Fixes

### Issue: User Stuck in Redirect Loop
**Fix:** Check `isOnDashboardRoute()` method - middleware skips dashboard routes

### Issue: Admin Redirected to Group Admin
**Fix:** Verify `is_admin = true` in database
```sql
SELECT is_admin FROM users WHERE id = ?;
```

### Issue: Group Admin Can't Access Routes
**Fix:** Verify group membership and role
```sql
SELECT * FROM group_members WHERE user_id = ? AND status = 'active';
```

### Issue: Flash Messages Not Showing
**Fix:** Ensure redirect helpers include flash data
```php
return redirect()->route('member.dashboard')
    ->with('warning', 'Message here');
```

---

## Performance Tips

✅ Use `with('group')` for eager loading  
✅ Cache role checks for high-traffic apps  
✅ Index `group_members(user_id, role, status)`  
✅ Monitor middleware execution time  

---

## Security Checklist

✅ Validates `is_admin` flag  
✅ Checks `status = 'active'` for group members  
✅ Prevents privilege escalation  
✅ Validates role existence  
✅ Logs access attempts (optional)  
✅ No direct 403 errors (graceful redirects)  

---

## File Summary

| File | Status | Changes |
|------|--------|---------|
| `app/Http/Middleware/DashboardRedirect.php` | NEW | Global redirect handler |
| `app/Http/Middleware/AdminMiddleware.php` | ENHANCED | Added redirect logic |
| `app/Http/Middleware/CheckGroupAdminAccess.php` | ENHANCED | Added redirect + request injection |
| `app/Http/Middleware/CheckMemberAccess.php` | NEW | Member validation + redirect |
| `app/Http/Kernel.php` | UPDATED | Registered new middleware |
| `routes/web.php` | READY | Needs middleware application |

---

## Next Steps

1. [ ] Copy new middleware files to `app/Http/Middleware/`
2. [ ] Update existing middleware files
3. [ ] Register in `Kernel.php`
4. [ ] Apply to routes
5. [ ] Test all scenarios
6. [ ] Deploy to production

---

**Version:** 1.0  
**Last Updated:** December 25, 2025  
**Status:** Ready for Implementation
