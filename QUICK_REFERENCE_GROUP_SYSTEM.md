# Quick Reference - Group Registration & Approval System

## New System Workflow

### For End Users

**Group Admin:**
1. Login/Register
2. Click "Register New Group"
3. Enter group name and description
4. System creates group (status: pending)
5. Wait for system admin approval
6. Once approved, go to "Manage Members"
7. Create accounts for group members
8. Members can now login and access their data

**System Admin:**
1. Login
2. Go to Admin Dashboard (/admin/dashboard)
3. See list of pending groups
4. Click group to view details
5. Click "Approve" or "Reject"
6. If reject, provide reason

**Group Member:**
1. Login with account created by admin
2. See their group
3. Access their loans and savings only
4. Cannot see other member's data

## Database Tables & Fields

### Groups Table (Updated)
```
id, name, description, created_by, status, 
approval_status, approved_by, approved_at, 
rejection_reason, total_savings, total_loans_issued, 
total_interest_earned, timestamps
```

### Users Table (Updated)
```
... existing fields ..., is_admin
```

## Key Controllers

### GroupRegistrationController
- `create()` → `/groups/register` (GET) - Registration form
- `store()` → `/groups` (POST) - Create group
- `manageMembers()` → `/groups/{id}/members` (GET) - Member mgmt page
- `createMemberAccount()` → POST member creation
- `addExistingMember()` → POST invite existing user

### GroupApprovalController
- `dashboard()` → `/admin/dashboard` (GET) - Pending groups
- `show()` → `/admin/groups/{id}` (GET) - Group review
- `approve()` → `/admin/groups/{id}/approve` (POST)
- `reject()` → `/admin/groups/{id}/reject` (POST)
- `users()` → `/admin/users` (GET)
- `groups()` → `/admin/groups` (GET)

## Key Policies

### SavingPolicy
- **view()** - Own account or admin/treasurer
- **deposit()** - Own account or admin/treasurer
- **withdraw()** - Own account or admin/treasurer
- **addInterest()** - Admin/treasurer only
- **viewStatement()** - Own or admin/treasurer

### LoanPolicy
- **view()** - Own loan or admin/treasurer
- **create()** - Any group member
- **recordPayment()** - Own loan or admin/treasurer
- **approve()** - Admin/treasurer only
- **disburse()** - Admin/treasurer only
- **markDefault()** - Admin only

## Routes Added

```
GET  /groups/register                    - Register group form
POST /groups                             - Create group (pending)

GET  /groups/{group}/members             - Member management (admin)
POST /groups/{group}/members/create      - Create member account
POST /groups/{group}/members/add-existing - Add existing user

GET  /admin/dashboard                    - Pending groups list
GET  /admin/groups/{group}               - Review group before approval
POST /admin/groups/{group}/approve       - Approve group
POST /admin/groups/{group}/reject        - Reject group with reason
GET  /admin/users                        - View all users
GET  /admin/groups                       - View all groups
```

## Middleware Stack

```
web middleware (sessions, csrf, etc.)
    ↓
auth middleware (require login)
    ↓
group.assignment (require group member)
    ↓
group.access (require access to specific group)
    ↓
group.role (require specific role - optional)
    ↓
admin.check (require system admin - for /admin routes)
```

## Form Requests

### StoreGroupRequest
- Validates group name (unique, max 255)
- Validates description (max 1000)
- Authorizes: authenticated users

### CreateMemberRequest
- Validates name, email, password
- Email must be unique
- Password must be confirmed
- Authorizes: group admin only

### AddExistingMemberRequest
- Validates email exists in system
- Validates role (member or treasurer)
- Authorizes: group admin only

## Migrations to Run

```bash
php artisan migrate
```

**Migration 1:** Add approval system to groups
- `approval_status` enum (pending, approved, rejected)
- `approved_by` foreign key
- `approved_at` timestamp
- `rejection_reason` text

**Migration 2:** Add is_admin flag to users
- `is_admin` boolean (default: false)

## Create System Admin

```bash
php artisan tinker

User::create([
    'name' => 'System Admin',
    'email' => 'admin@example.com',
    'password' => bcrypt('password123'),
    'is_admin' => true
])
```

## Data Isolation Example

### Member Viewing Own Savings
```
GET /groups/1/savings/5
├─ User is authenticated ✓
├─ User in group 1 ✓
├─ Policy check: User owns saving 5? ✓
└─ Result: Granted
```

### Member Trying to View Another Member's Savings
```
GET /groups/1/savings/10
├─ User is authenticated ✓
├─ User in group 1 ✓
├─ Policy check: User owns saving 10? ✗
├─ Policy check: User is admin/treasurer? ✗
└─ Result: 403 Forbidden
```

## Approval Status Flow

```
[User Creates Group]
         ↓
    status = active
    approval_status = pending
         ↓
[Admin Reviews in Dashboard]
         ↓
    [Approve] → approval_status = approved ✓
    [Reject]  → approval_status = rejected ✗
         ↓
[Group Can Operate]
```

## Key Files Created

### Controllers (2)
- `app/Http/Controllers/GroupRegistrationController.php`
- `app/Http/Controllers/Admin/GroupApprovalController.php`

### Policies (2)
- `app/Policies/LoanPolicy.php`
- `app/Policies/SavingPolicy.php`

### Middleware (1)
- `app/Http/Middleware/CheckAdminStatus.php`

### Form Requests (3)
- `app/Http/Requests/StoreGroupRequest.php`
- `app/Http/Requests/CreateMemberRequest.php`
- `app/Http/Requests/AddExistingMemberRequest.php`

### Migrations (2)
- `2025_12_24_000001_add_approval_system_to_groups.php`
- `2025_12_24_000002_add_is_admin_to_users.php`

### Updated Files (3)
- `app/Models/Group.php` - Added approval fields
- `app/Models/User.php` - Added is_admin field
- `app/Http/Kernel.php` - Registered middleware
- `routes/web.php` - Added all routes

## Security Summary

✅ Groups must be approved before full operation
✅ Multi-level authorization (auth → group → role → policy)
✅ Members can only access their own data
✅ Admin/treasurer can access all member data
✅ System admin controls group approvals
✅ Rejection reasons recorded for audit
✅ Role-based restrictions enforced
✅ Policies prevent unauthorized access

## Next: Create Blade Views

1. `resources/views/groups/register.blade.php`
2. `resources/views/groups/manage-members.blade.php`
3. `resources/views/admin/dashboard.blade.php`
4. `resources/views/admin/group-detail.blade.php`
5. `resources/views/admin/users.blade.php`
6. `resources/views/admin/groups.blade.php`

Ready to create the Blade templates?
