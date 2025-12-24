# Authentication & Group Assignment Guide

## Overview

The system implements a **multi-group authentication system** where:
- Users authenticate with email/password
- Groups are verified as the first security check
- Users can belong to multiple groups with different roles
- Group access is enforced at the middleware level

## Authentication Flow

### 1. User Registration
- User provides: name, email, password, group
- User is added to the selected group as a **member**
- Session is set with the group ID
- Redirected to group dashboard

```php
// Example registration
POST /register
{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "secret123",
    "password_confirmation": "secret123",
    "group_id": 1
}
```

### 2. User Login
1. User provides email/password
2. Credentials validated (LoginRequest)
3. User's active groups checked:
   - **No groups** → Error message, logout
   - **One group** → Auto-select, redirect to group dashboard
   - **Multiple groups** → Show group selection page

```php
// Example login
POST /login
{
    "email": "john@example.com",
    "password": "secret123",
    "remember": true
}
```

### 3. Group Selection (Multiple Groups)
If user belongs to multiple groups:

```php
// Get group selection view
GET /select-group

// Confirm group selection
POST /confirm-group
{
    "group_id": 1
}
```

### 4. Access Protected Routes
All routes under `/groups/{group}/*` require:
- User is authenticated (`auth` middleware)
- User has active group assignment (`group.assignment` middleware)
- User has access to the specific group (`group.access` middleware)

## Middleware Stack

### CheckGroupAssignment Middleware
**Purpose:** Ensure user has at least one active group before accessing dashboard

**Location:** `app/Http/Middleware/CheckGroupAssignment.php`

**Flow:**
1. Check if user is authenticated
2. Get user's active groups
3. If no groups: redirect to error/group-not-assigned page
4. If one group: auto-select and store in session
5. If multiple: redirect to group selection

```php
// Usage in routes
Route::middleware('group.assignment')->group(function () {
    Route::get('/dashboard', ...);
});
```

### VerifyGroupAccess Middleware
**Purpose:** Verify user has access to the specific group in route

**Location:** `app/Http/Middleware/VerifyGroupAccess.php`

**Flow:**
1. Extract group from route parameter
2. Check if user belongs to this group
3. If not: abort with 403 Forbidden

```php
// Usage in routes
Route::middleware('group.access')->prefix('groups/{group}')->group(function () {
    Route::get('/', ...);
});
```

### VerifyGroupRole Middleware
**Purpose:** Enforce role-based access (admin, treasurer, member)

**Location:** `app/Http/Middleware/VerifyGroupRole.php`

**Flow:**
1. Get user and group from request
2. Verify user belongs to group
3. Verify user has required role
4. If not: abort with 403 Forbidden

```php
// Usage in routes - only admins and treasurers can approve loans
Route::post('/loans/{loan}/approve', [...])
    ->middleware('group.role:admin,treasurer');
```

## User Model Methods

### Group Relationships

```php
$user->groupMembers();           // Get all group memberships
$user->groups();                 // Get all groups (with pivot)
$user->activeGroups();           // Get only active groups
```

### Group Checks

```php
$user->belongsToGroup($group);   // Check if user belongs to group
$user->isGroupAdmin($group);     // Check if user is admin
$user->isGroupTreasurer($group); // Check if user is treasurer
$user->getGroupRole($group);     // Get user's role ('admin', 'treasurer', 'member')
```

### Group Session Management

```php
$user->getCurrentGroup();        // Get current group from session
$user->setCurrentGroup($group);  // Set current group in session
```

## Route Structure

### Authentication Routes
```
GET  /                          - Welcome page
GET  /login                     - Login form
POST /login                     - Submit login (group check happens after)
GET  /register                  - Registration form
POST /register                  - Submit registration (creates group membership)
GET  /select-group              - Group selection (multiple groups)
POST /confirm-group             - Confirm group selection
POST /logout                    - Logout (clears session)
```

### Protected Routes (Group-based)
```
GET  /dashboard                           - User dashboard
GET  /groups/{group}                      - Group detail
GET  /groups/{group}/members              - Group members list
POST /groups/{group}/members              - Add member to group
GET  /groups/{group}/loans                - Loans in group
POST /groups/{group}/loans                - Create loan
GET  /groups/{group}/loans/{loan}         - Loan detail
POST /groups/{group}/loans/{loan}/approve - Approve loan (admin/treasurer)
```

## Implementation Example

### Route Definition
```php
Route::middleware('auth')->group(function () {
    // Group selection for multiple groups
    Route::get('/select-group', [AuthenticatedSessionController::class, 'selectGroup'])
        ->name('groups.select');
    
    // Protected routes with group checks
    Route::middleware('group.assignment')->group(function () {
        Route::get('/dashboard', fn() => view('dashboard'))
            ->name('dashboard');

        // Routes with specific group access
        Route::middleware('group.access')->prefix('groups/{group}')->group(function () {
            Route::get('/', [GroupController::class, 'show'])
                ->name('groups.show');
            
            // Admin/treasurer only
            Route::post('/loans/{loan}/approve', [LoanController::class, 'approve'])
                ->middleware('group.role:admin,treasurer')
                ->name('loans.approve');
        });
    });
});
```

### Controller Usage
```php
public function store(Request $request)
{
    $user = $request->user();
    $group = $user->getCurrentGroup(); // Get current group
    
    // Verify user has permission in this group
    if (!$user->isGroupAdmin($group)) {
        abort(403);
    }
    
    // Create loan
    $loan = Loan::create([...]);
    
    return redirect()->route('loans.show', [$group, $loan]);
}
```

### Blade Template Usage
```blade
<!-- Check group membership -->
@if(Auth::user()->belongsToGroup($group))
    <p>Welcome to {{ $group->name }}</p>
@endif

<!-- Check role -->
@if(Auth::user()->isGroupAdmin($group))
    <a href="{{ route('loans.approve', $group) }}">Approve Loans</a>
@endif

<!-- Get current group -->
<h1>{{ Auth::user()->getCurrentGroup()->name }}</h1>
```

## Security Features

### 1. Authentication First
- All protected routes require `auth` middleware
- Session is regenerated after login
- Remember-me functionality available

### 2. Group Assignment Verification
- User must have at least one active group
- Group status checked in `active_at` and `status` fields
- Members must have `active` status

### 3. Group Access Control
- User must belong to the specific group in route
- Multiple group support with session-based selection
- Each request verifies group membership

### 4. Role-Based Access
- Three roles: admin, treasurer, member
- Sensitive operations require admin/treasurer
- Role verified at middleware level

### 5. CSRF Protection
- All POST/PUT/DELETE requests protected
- Token verified before processing
- Handled by `VerifyCsrfToken` middleware

### 6. Rate Limiting
- Login attempts rate limited (5 per minute)
- Lockout mechanism after threshold
- Implemented in LoginRequest

## Error Handling

### No Groups Assigned
```
Message: "Your account has no active group assignments. 
         Please contact an administrator."
Redirect: /login
```

### Group Access Denied
```
Status: 403 Forbidden
Message: "You do not have access to this group."
```

### Insufficient Role
```
Status: 403 Forbidden
Message: "You do not have permission to perform this action."
```

## Database Requirements

### Users Table
```sql
CREATE TABLE users (
    id BIGINT PRIMARY KEY,
    name VARCHAR(255),
    email VARCHAR(255) UNIQUE,
    email_verified_at TIMESTAMP,
    password VARCHAR(255),
    remember_token VARCHAR(100),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### Group Members Table
```sql
CREATE TABLE group_members (
    id BIGINT PRIMARY KEY,
    group_id BIGINT REFERENCES groups(id),
    user_id BIGINT REFERENCES users(id),
    role ENUM('admin', 'treasurer', 'member'),
    status ENUM('active', 'inactive'),
    balance DECIMAL(15, 2) DEFAULT 0,
    joined_at TIMESTAMP,
    left_at TIMESTAMP,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

## Testing Authentication

### Test User Registration
```bash
POST /register HTTP/1.1
Content-Type: application/json

{
    "name": "Test User",
    "email": "test@example.com",
    "password": "TestPassword123!",
    "password_confirmation": "TestPassword123!",
    "group_id": 1
}
```

### Test Login
```bash
POST /login HTTP/1.1
Content-Type: application/json

{
    "email": "test@example.com",
    "password": "TestPassword123!",
    "remember": true
}
```

### Test Protected Route
```bash
GET /dashboard HTTP/1.1
Authorization: Bearer {session_token}
```

### Test Group Access
```bash
GET /groups/1 HTTP/1.1
Authorization: Bearer {session_token}
X-CSRF-Token: {csrf_token}
```

## Common Issues & Solutions

### Issue: User redirected to /select-group but isn't in multiple groups
**Solution:** Check GroupMember status is 'active' and Group status is 'active'

### Issue: 403 error on group access
**Solution:** Verify user has GroupMember record for the group with status='active'

### Issue: Session group not persisting
**Solution:** Ensure session middleware is properly configured in `config/session.php`

### Issue: Role check not working
**Solution:** Verify role value matches exactly ('admin', 'treasurer', or 'member')

## Best Practices

1. **Always use middleware** for group verification
   - Don't rely on client-side checks
   - Apply at route level, not in controller

2. **Check role before sensitive operations**
   - Approve loans, disburse funds, mark defaults
   - Use `group.role` middleware

3. **Use session for group selection**
   - Store current_group_id in session
   - Retrieve with `$user->getCurrentGroup()`

4. **Validate group in form requests**
   - Check user belongs to group
   - Prevent cross-group operations

5. **Log security events**
   - Failed login attempts
   - Unauthorized access attempts
   - Role-based operation attempts

## Next Steps

1. ✅ Create authentication controllers
2. ✅ Implement group middleware
3. ✅ Add User relationships
4. ⬜ Create authentication views (login.blade.php, register.blade.php, select-group.blade.php)
5. ⬜ Create form requests for inputs
6. ⬜ Add authorization policies
7. ⬜ Create dashboard and group views
8. ⬜ Add tests for authentication flow
