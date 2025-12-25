# RBAC Dashboard Middleware - Implementation Checklist

## Quick Setup

- [ ] Update/create middleware files
  - [ ] `DashboardRedirect.php` (NEW)
  - [ ] `AdminMiddleware.php` (ENHANCED)
  - [ ] `CheckGroupAdminAccess.php` (ENHANCED)
  - [ ] `CheckMemberAccess.php` (NEW)

- [ ] Register middleware in `app/Http/Kernel.php`
  - [ ] Add `'dashboard.redirect'` entry
  - [ ] Add `'member.access'` entry
  - [ ] Verify other entries exist

- [ ] Update routes in `routes/web.php`
  - [ ] Add `'dashboard.redirect'` to main `/dashboard` route
  - [ ] Add `'admin'` middleware to admin routes
  - [ ] Add `'group.admin'` middleware to group-admin routes
  - [ ] Add `'member.access'` middleware to member routes

---

## Testing Checklist

### Admin Access Tests
- [ ] System admin can access `/admin/dashboard`
- [ ] System admin accessing `/group-admin` redirects to `/admin/dashboard`
- [ ] System admin accessing `/member` redirects to `/admin/dashboard`

### Group Admin Access Tests
- [ ] Group admin can access `/group-admin/dashboard`
- [ ] Group admin accessing `/admin` redirects to `/group-admin/dashboard`
- [ ] Group admin accessing `/member` redirects to `/member/dashboard`
- [ ] Non-group-admin accessing `/group-admin` redirects to `/member/dashboard`

### Member Access Tests
- [ ] Member can access `/member/dashboard`
- [ ] Member accessing `/admin` shows 403 error
- [ ] Member accessing `/group-admin` redirects to `/member/dashboard`
- [ ] Non-member accessing `/member` redirects to `/dashboard`

### Main Dashboard Tests
- [ ] Admin accessing `/dashboard` redirects to `/admin/dashboard`
- [ ] Group admin accessing `/dashboard` redirects to `/group-admin/dashboard`
- [ ] Member accessing `/dashboard` redirects to `/member/dashboard`
- [ ] Non-authenticated user redirects to `/login`

---

## Controller Updates

- [ ] Update `DashboardController`
  - [ ] Verify it handles auto-redirect logic
  - [ ] Test with all user types

- [ ] Update `AdminDashboardController`
  - [ ] No changes needed - middleware handles validation

- [ ] Update `GroupAdminDashboardController`
  - [ ] Access `$request->group_admin` if needed
  - [ ] Access `$request->admin_group_id` if needed

- [ ] Update `MemberDashboardController`
  - [ ] Access `$request->user_member` if needed
  - [ ] Scope queries to authenticated user

---

## View Updates

- [ ] Add flash message displays
  - [ ] In `layouts/app.blade.php` or main layout
  - [ ] Show warning messages
  - [ ] Show info messages

```blade
@if ($message = Session::get('warning'))
    <div class="alert alert-warning alert-dismissible">
        {{ $message }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if ($message = Session::get('info'))
    <div class="alert alert-info alert-dismissible">
        {{ $message }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
```

---

## Database Verification

- [ ] Verify user has correct `is_admin` flag for system admins
- [ ] Verify group_members have correct `role` values
- [ ] Verify group_members have correct `status` values
- [ ] Test with:
  ```sql
  -- Check admin users
  SELECT * FROM users WHERE is_admin = true;
  
  -- Check group admins
  SELECT * FROM group_members WHERE role = 'admin' AND status = 'active';
  
  -- Check members
  SELECT * FROM group_members WHERE role IN ('member', 'treasurer') AND status = 'active';
  ```

---

## Configuration Checks

- [ ] Verify in `config/auth.php`
  - [ ] Default guard is correct

- [ ] Verify in `.env`
  - [ ] APP_DEBUG is appropriate
  - [ ] APP_ENV is set correctly

---

## Route Registration Checklist

### Main Dashboard
```php
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified', 'dashboard.redirect'])
    ->name('dashboard');
```
- [ ] Auth middleware added
- [ ] Verified middleware added
- [ ] dashboard.redirect middleware added
- [ ] Route name is 'dashboard'

### Admin Routes
```php
Route::prefix('admin')->middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::get('/dashboard', ...)->name('admin.dashboard');
    Route::resource('users', ...);
    // ...
});
```
- [ ] Auth middleware added
- [ ] Verified middleware added
- [ ] Admin middleware added
- [ ] Dashboard route name is 'admin.dashboard'

### Group Admin Routes
```php
Route::prefix('group-admin')->middleware(['auth', 'verified', 'group.admin'])->group(function () {
    Route::get('/dashboard', ...)->name('group-admin.dashboard');
    Route::resource('groups', ...);
    // ...
});
```
- [ ] Auth middleware added
- [ ] Verified middleware added
- [ ] group.admin middleware added
- [ ] Dashboard route name is 'group-admin.dashboard'

### Member Routes
```php
Route::prefix('member')->middleware(['auth', 'verified', 'member.access'])->group(function () {
    Route::get('/dashboard', ...)->name('member.dashboard');
    Route::get('/loans', ...)->name('member.loans');
    // ...
});
```
- [ ] Auth middleware added
- [ ] Verified middleware added
- [ ] member.access middleware added
- [ ] Dashboard route name is 'member.dashboard'

---

## Navigation Updates

- [ ] Update main navigation menu
  - [ ] Show/hide links based on user role
  - [ ] Use `auth()->user()->is_admin` for system admin
  - [ ] Create helper method for group admin check
  - [ ] Create helper method for member check

```blade
@if(auth()->user()->is_admin)
    <a href="{{ route('admin.dashboard') }}">Admin Dashboard</a>
@elseif(auth()->user()->hasGroupAdminRole())
    <a href="{{ route('group-admin.dashboard') }}">Group Admin</a>
@else
    <a href="{{ route('member.dashboard') }}">My Account</a>
@endif
```

---

## Helper Methods to Create

- [ ] Add to User model
  ```php
  public function hasGroupAdminRole(): bool
  public function getGroupAdminRoles(): Collection
  public function hasGroupMemberRole(): bool
  public function getGroupMemberships(): Collection
  ```

- [ ] Add to helpers file (if using)
  ```php
  function userDashboardRoute(): string
  function canAccessAdmin(): bool
  function canAccessGroupAdmin(): bool
  function canAccessMember(): bool
  ```

---

## Logging & Monitoring

- [ ] Add logging to middleware
  ```php
  Log::info('User redirect', ['user_id' => $user->id, 'from' => $route, 'to' => 'admin.dashboard']);
  ```

- [ ] Monitor unusual redirect patterns
- [ ] Track failed access attempts

---

## Documentation

- [ ] Create `RBAC_DASHBOARD_MIDDLEWARE_GUIDE.md` ✓
- [ ] Document middleware in team wiki
- [ ] Create troubleshooting guide
- [ ] Document access matrix
- [ ] Add code comments to middleware

---

## Final Verification

- [ ] Run `php artisan optimize`
- [ ] Clear all caches: `php artisan cache:clear`
- [ ] Test all routes with real users
- [ ] Verify all redirects work
- [ ] Check flash messages appear
- [ ] Verify no infinite loops
- [ ] Test with inactive/suspended users
- [ ] Test with role changes (promote/demote user)

---

## Rollback Plan

If issues occur:
1. [ ] Restore original middleware files
2. [ ] Revert Kernel.php changes
3. [ ] Revert route changes
4. [ ] Clear caches
5. [ ] Test to confirm rollback

---

## Notes

- All middleware uses efficient role checking with eager loading
- Flash messages provide user feedback on redirects
- Role hierarchy is strictly enforced
- No 403 errors - all access denials result in redirects
- Request injection reduces database queries in controllers

---

## Sign-Off

- [ ] All checklist items completed
- [ ] All tests passing
- [ ] No console errors
- [ ] No database errors
- [ ] Documentation complete
- [ ] Team trained on new behavior

**Implemented by:** _______________  
**Date:** _______________  
**Tested by:** _______________  
