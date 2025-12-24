# Quick Reference Guide - Three-Tier Dashboard System

## Quick Navigation

### For Developers
- **Main Routing Logic**: [DashboardController.php](app/Http/Controllers/DashboardController.php)
- **System Admin Controller**: [AdminDashboardController.php](app/Http/Controllers/Admin/AdminDashboardController.php)
- **Group Admin Controller**: [GroupAdminDashboardController.php](app/Http/Controllers/GroupAdminDashboardController.php)
- **Member Controller**: [MemberDashboardController.php](app/Http/Controllers/MemberDashboardController.php)
- **Routes**: [routes/web.php](routes/web.php) (lines 32-63)
- **Admin Routes**: [routes/admin.php](routes/admin.php)

### For Users
- **System Admin**: Read [ADMIN_DASHBOARD_GUIDE.md](ADMIN_DASHBOARD_GUIDE.md)
- **Group Admin**: Read [GROUP_ADMIN_DASHBOARD_GUIDE.md](GROUP_ADMIN_DASHBOARD_GUIDE.md)
- **Member**: Read [MEMBER_DASHBOARD_GUIDE.md](MEMBER_DASHBOARD_GUIDE.md)
- **All Roles**: Read [RBAC_COMPLETE_GUIDE.md](RBAC_COMPLETE_GUIDE.md)

### For System Overview
- **Complete Implementation**: [THREE_TIER_DASHBOARD_IMPLEMENTATION.md](THREE_TIER_DASHBOARD_IMPLEMENTATION.md)

---

## Login Credentials

### TIER 1: System Admin
```
Email:    admin@itsinda.local
Password: AdminPassword123!
```

### TIER 2: Group Admin
```
Email:    groupadmin@example.com
Password: GroupAdminPass123!
```

### TIER 3: Member
```
Email:    demo@example.com
Password: DemoPassword123!
```

---

## Dashboard URLs

| Role | URL | Access |
|------|-----|--------|
| System Admin | `/admin/dashboard` | Full system |
| Group Admin | `/group-admin/dashboard` | Single group |
| Member | `/member/dashboard` | Personal only |
| Router | `/dashboard` | Auto-routes to above |

---

## What Each Role Can Do

### System Admin ✅
```
✅ Manage all users
✅ Manage all groups
✅ View all loans
✅ View all savings
✅ View all transactions
✅ Generate reports
✅ System settings
```

### Group Admin ✅
```
✅ Manage group members
✅ View group loans
✅ View group savings
✅ View group transactions
✅ Generate group reports
✅ Edit group info
❌ Cannot access other groups
```

### Member ✅
```
✅ View personal loans (read-only)
✅ View personal savings (read-only)
✅ Make loan payments
✅ Deposit/withdraw savings
✅ View transactions
✅ Edit own profile
❌ Cannot edit/delete records
❌ Cannot view other members' data
```

---

## How It Works

```
1. User logs in
   ↓
2. Visit /dashboard
   ↓
3. System checks:
   - is_admin = true? → Admin dashboard
   - Has admin role in group? → Group admin dashboard
   - Otherwise → Member dashboard
   ↓
4. User sees appropriate dashboard
```

---

## Database Indicators

### Who is System Admin?
```sql
SELECT * FROM users WHERE is_admin = true;
```

### Who is Group Admin?
```sql
SELECT * FROM group_members 
WHERE role = 'admin' AND status = 'active';
```

### Who is Member?
```sql
SELECT * FROM group_members 
WHERE role IN ('member', 'treasurer', 'secretary') 
AND status = 'active';
```

---

## Common Routes

### System Admin Routes
```
GET    /admin/dashboard           Main dashboard
GET    /admin/users               List users
PUT    /admin/users/{user}        Update user
GET    /admin/groups              List groups
GET    /admin/loans               List all loans
GET    /admin/reports             View reports
```

### Group Admin Routes
```
GET    /group-admin/dashboard     Main dashboard
GET    /group-admin/groups/{id}/members
POST   /group-admin/groups/{id}/members
PUT    /group-admin/groups/{id}/members/{mid}
DELETE /group-admin/groups/{id}/members/{mid}
GET    /group-admin/groups/{id}/loans
GET    /group-admin/groups/{id}/reports
```

### Member Routes
```
GET    /member/dashboard          Main dashboard
GET    /member/loans              My loans (view-only)
GET    /member/savings            My savings (view-only)
GET    /member/transactions       My transactions
GET    /member/profile            My profile
PUT    /member/profile            Update profile
```

---

## Authorization Checks

### For System Admin Routes
```php
// In admin.php:
Route::middleware('admin.check')->group(function () {
    // All routes here require is_admin = true
});
```

### For Group Admin Routes
```php
// In GroupAdminDashboardController:
private function authorizeGroupAdmin(Group $group) {
    // Verifies user is admin in this group
}
```

### For Member Routes
```php
// In MemberDashboardController:
protected function verifyOwnership($model) {
    // Verifies user owns the record
}
```

---

## Middleware Stack

```
/dashboard                     auth
/admin/*                       auth + admin.check
/group-admin/*                 auth + (controller check)
/member/*                      auth + (ownership check)
```

---

## File Locations

### Controllers
```
app/Http/Controllers/
├── DashboardController.php
├── GroupAdminDashboardController.php
├── MemberDashboardController.php
└── Admin/AdminDashboardController.php
```

### Views
```
resources/views/dashboards/
├── admin.blade.php
├── group-admin.blade.php
└── member.blade.php
```

### Routes
```
routes/
├── web.php (lines 32-63: dashboard routes)
└── admin.php (admin-specific routes)
```

### Documentation
```
RBAC_COMPLETE_GUIDE.md
ADMIN_DASHBOARD_GUIDE.md
GROUP_ADMIN_DASHBOARD_GUIDE.md
MEMBER_DASHBOARD_GUIDE.md
THREE_TIER_DASHBOARD_IMPLEMENTATION.md
QUICK_REFERENCE.md (this file)
```

---

## Testing Checklist

- [ ] Login as System Admin → see /admin/dashboard
- [ ] Login as Group Admin → see /group-admin/dashboard
- [ ] Login as Member → see /member/dashboard
- [ ] System Admin can view all users
- [ ] System Admin can view all groups
- [ ] System Admin can view all loans
- [ ] Group Admin can manage their group members
- [ ] Group Admin cannot see other groups
- [ ] Member can see own loans (view-only)
- [ ] Member cannot edit loans
- [ ] Member can make payments
- [ ] Member can deposit/withdraw savings
- [ ] Member cannot see other members' data

---

## Common Tasks

### Promote User to System Admin
```php
User::find($userId)->update(['is_admin' => true]);
```

### Make User a Group Admin
```php
GroupMember::create([
    'user_id' => $userId,
    'group_id' => $groupId,
    'role' => 'admin',
    'status' => 'active',
]);
```

### Remove Member from Group
```php
GroupMember::find($memberId)->update([
    'status' => 'inactive',
    'left_at' => now(),
]);
```

### Check User's Role
```php
$isAdmin = Auth::user()->is_admin;
$isGroupAdmin = GroupMember::where('user_id', Auth::id())
    ->where('role', 'admin')->exists();
$isMember = GroupMember::where('user_id', Auth::id())
    ->exists();
```

---

## Error Messages

### System Admin Accessing Non-Admin Route
```
HTTP 403 Forbidden
"You are not authorized to access this resource"
```

### Group Admin Accessing Other Group
```
HTTP 403 Forbidden
"You are not authorized to manage this group"
```

### Member Trying to Edit Records
```
HTTP 403 Forbidden
"Cannot access other user's records"
```

---

## Performance Tips

### Query Optimization
```php
// Good: Eager load relationships
GroupMember::with('user', 'group')->get();

// Bad: N+1 queries
GroupMember::get()->each(fn($m) => $m->user->name);

// Good: Limit results
Loan::take(20)->get();

// Bad: Fetch all then limit
Loan::get()->take(20);
```

### Caching Routes
```bash
php artisan route:cache     # Cache routes
php artisan route:clear     # Clear route cache
php artisan config:cache    # Cache config
php artisan optimize:clear  # Clear all caches
```

---

## Troubleshooting

### Routes Not Found
```bash
php artisan route:list | grep dashboard
php artisan route:cache && php artisan route:clear
```

### Middleware Issues
```bash
php artisan optimize:clear
php artisan config:cache
```

### Permission Denied
```
Check:
1. is_admin flag in users table
2. role in group_members table
3. status field (must be 'active')
4. Middleware configuration
```

### Dashboard Shows Wrong Content
```
Clear:
1. Browser cache
2. Laravel cache (php artisan cache:clear)
3. Session cache (php artisan session:flush)
4. Rebuild cache (php artisan optimize)
```

---

## Support Resources

| Topic | File |
|-------|------|
| Complete RBAC Guide | RBAC_COMPLETE_GUIDE.md |
| System Admin Details | ADMIN_DASHBOARD_GUIDE.md |
| Group Admin Details | GROUP_ADMIN_DASHBOARD_GUIDE.md |
| Member Details | MEMBER_DASHBOARD_GUIDE.md |
| Implementation Details | THREE_TIER_DASHBOARD_IMPLEMENTATION.md |
| Quick Reference | This file |

---

## Key Takeaways

✅ **Three distinct dashboards** based on user role
✅ **Automatic routing** via DashboardController
✅ **Complete data isolation** by role
✅ **Clear permission matrix** for all features
✅ **Comprehensive documentation** for each role
✅ **Ready-to-use test accounts** for testing
✅ **Production-ready code** with proper error handling
✅ **Easy to extend** - add new roles/features easily

---

**Everything you need to understand, use, and manage the three-tier dashboard system!**
