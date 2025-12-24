# Complete Role-Based Access Control (RBAC) Guide

## System Overview

This application implements a three-tier role-based access control system to manage permissions and access levels:

```
┌─────────────────────────────────────────────────────────────────────┐
│                    THREE-TIER DASHBOARD SYSTEM                      │
├─────────────────────────────────────────────────────────────────────┤
│                                                                       │
│  TIER 1: SYSTEM ADMIN                                               │
│  ├─ is_admin = true                                                 │
│  ├─ Full system access & control                                    │
│  ├─ Dashboard: /admin/dashboard                                     │
│  └─ Controller: AdminDashboardController                            │
│                                                                       │
│  TIER 2: GROUP ADMIN                                                │
│  ├─ admin role in group                                             │
│  ├─ Manage single group only                                        │
│  ├─ Dashboard: /group-admin/dashboard                               │
│  └─ Controller: GroupAdminDashboardController                       │
│                                                                       │
│  TIER 3: MEMBER                                                     │
│  ├─ member/treasurer/secretary role in group                        │
│  ├─ View-only own records                                           │
│  ├─ Dashboard: /member/dashboard                                    │
│  └─ Controller: MemberDashboardController                           │
│                                                                       │
└─────────────────────────────────────────────────────────────────────┘
```

---

## TIER 1: SYSTEM ADMIN

### Description
The System Administrator has **unrestricted access** to the entire system. They manage all users, groups, loans, and savings across the organization.

### Access
- **Identity**: User with `is_admin = true` in users table
- **Entry Point**: `GET /dashboard` → redirects to `GET /admin/dashboard`
- **Dashboard Route**: `route('admin.dashboard')`

### Capabilities

#### User Management
```
GET    /admin/users                    → List all users
GET    /admin/users/{user}/edit        → Edit user details
PUT    /admin/users/{user}             → Update user
DELETE /admin/users/{user}             → Delete user
```

#### Group Management
```
GET    /admin/groups                   → List all groups
GET    /admin/groups/{group}           → View group details
GET    /admin/groups/{group}/edit      → Edit group
PUT    /admin/groups/{group}           → Update group
```

#### Financial Management
```
GET    /admin/loans                    → View all loans system-wide
GET    /admin/loans/{loan}             → View loan details
GET    /admin/savings                  → View all savings system-wide
GET    /admin/savings/{saving}         → View savings details
```

#### System Reports
```
GET    /admin/transactions             → View all transactions
GET    /admin/reports                  → System-wide financial reports
GET    /admin/settings                 → System settings
```

### Test Account
```
Email:    admin@itsinda.local
Password: AdminPassword123!
is_admin: true
```

### Middleware Stack
```
Route Middleware: 'auth' → 'admin.check'
Kernel: CheckAdminStatus / AdminMiddleware
```

---

## TIER 2: GROUP ADMIN

### Description
Group Administrators manage a **single group** and all its members. They have full control over group operations but cannot access other groups or system-wide settings.

### Access
- **Identity**: User with `role = 'admin'` in group_members table
- **Entry Point**: `GET /dashboard` → redirects to `GET /group-admin/dashboard`
- **Dashboard Route**: `route('group-admin.dashboard')`

### Capabilities

#### Group Overview
```
GET    /group-admin/dashboard                    → Group overview & statistics
GET    /group-admin/groups/{group}/edit          → Edit group info
PUT    /group-admin/groups/{group}               → Update group info
```

#### Member Management
```
GET    /group-admin/groups/{group}/members       → List group members
POST   /group-admin/groups/{group}/members       → Add new member
PUT    /group-admin/groups/{group}/members/{id}  → Update member role
DELETE /group-admin/groups/{group}/members/{id}  → Remove member from group
```

#### Financial Management
```
GET    /group-admin/groups/{group}/loans         → View group loans
GET    /group-admin/groups/{group}/savings       → View group savings
GET    /group-admin/groups/{group}/transactions  → View group transactions
GET    /group-admin/groups/{group}/reports       → Group financial reports
```

### Member Roles (within group)
A Group Admin can assign these roles to members:

| Role      | Permissions                           |
|-----------|---------------------------------------|
| **admin** | Full group management access          |
| **treasurer** | Financial operations, reports       |
| **secretary** | Record keeping, documentation       |
| **member** | View-only group member records        |

### Test Account
```
Email:    groupadmin@example.com
Password: GroupAdminPass123!
is_admin: false
Groups:   [Group Name] (admin role)
```

### Authorization Check
```php
GroupMember::where('user_id', $user->id)
    ->where('role', 'admin')
    ->where('status', 'active')
    ->exists()
```

### Restrictions
- ❌ Cannot view other groups
- ❌ Cannot access system admin features
- ❌ Cannot change own admin role
- ❌ Cannot remove self from group
- ✅ Can add/remove/manage members
- ✅ Can view all group financial data

---

## TIER 3: MEMBER (VIEW-ONLY)

### Description
Regular group members have **read-only access** to their own records only. They cannot edit, delete, or modify any data.

### Access
- **Identity**: User with member role (member/treasurer/secretary) in group_members table
- **Entry Point**: `GET /dashboard` → redirects to `GET /member/dashboard`
- **Dashboard Route**: `route('member.dashboard')`

### Capabilities

#### Personal View Access
```
GET    /member/dashboard              → View personal dashboard summary
GET    /member/loans                  → View personal loans only
GET    /member/savings                → View personal savings only
GET    /member/transactions           → View personal transactions
GET    /member/groups                 → View groups I belong to
GET    /member/profile                → View my profile
PUT    /member/profile                → Edit my profile only
```

### Visible Data (View-Only)
- ✅ Personal loans (amount, status, payment history)
- ✅ Personal savings (balance, deposit history)
- ✅ Personal transactions
- ✅ Group membership info
- ✅ My profile information

### Disabled Operations
- ❌ Cannot create loans
- ❌ Cannot withdraw savings
- ❌ Cannot view other members' data
- ❌ Cannot manage groups
- ❌ Cannot see system admin features
- ❌ Cannot see group financial reports (unless Group Admin)

### Test Account
```
Email:    demo@example.com
Password: DemoPassword123!
is_admin: false
Groups:   [Test Group 1], [Test Group 2] (member role)
```

### Authorization Check
```php
// Can only access own records
Loan::where('user_id', $user->id)->get();
Saving::where('user_id', $user->id)->get();
```

### Access Denied Routes
Members attempting to access restricted routes see:
```
GET /member/access-denied → "You have view-only access"
```

---

## Complete Feature Access Matrix

| Feature | System Admin | Group Admin | Member |
|---------|:-----------:|:-----------:|:------:|
| View all users | ✅ | ❌ | ❌ |
| Manage users | ✅ | ❌ | ❌ |
| View all groups | ✅ | ❌ | ❌ |
| View own group | ✅ | ✅ | ✅ |
| Manage own group | ✅ | ✅ | ❌ |
| Manage group members | ✅ | ✅ | ❌ |
| View all loans | ✅ | ❌ | ❌ |
| View group loans | ✅ | ✅ | ❌ |
| View own loans | ✅ | ✅ | ✅ |
| Create loans | ✅ | ✅ | ❌ |
| Approve loans | ✅ | ✅ | ❌ |
| Record payments | ✅ | ✅ | ✅ |
| View all savings | ✅ | ❌ | ❌ |
| View group savings | ✅ | ✅ | ❌ |
| View own savings | ✅ | ✅ | ✅ |
| Deposit savings | ✅ | ✅ | ✅ |
| Withdraw savings | ✅ | ✅ | ✅ |
| View reports | ✅ | ✅ | ❌ |
| System settings | ✅ | ❌ | ❌ |

---

## Database Schema

### Users Table
```sql
CREATE TABLE users (
    id BIGINT PRIMARY KEY,
    name VARCHAR(255),
    email VARCHAR(255) UNIQUE,
    password VARCHAR(255),
    is_admin BOOLEAN DEFAULT false,  -- Tier 1 indicator
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### Group Members Table (Pivot)
```sql
CREATE TABLE group_members (
    id BIGINT PRIMARY KEY,
    group_id BIGINT,
    user_id BIGINT,
    role VARCHAR(50),        -- admin/treasurer/secretary/member
    status VARCHAR(50),      -- active/inactive
    joined_at TIMESTAMP,
    left_at TIMESTAMP NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    FOREIGN KEY(group_id) REFERENCES groups(id),
    FOREIGN KEY(user_id) REFERENCES users(id)
);
```

### Controllers & Routes

| Tier | Controller | Route | Purpose |
|------|-----------|-------|---------|
| 1 | AdminDashboardController | /admin/* | System admin features |
| 2 | GroupAdminDashboardController | /group-admin/* | Group management |
| 3 | MemberDashboardController | /member/* | Personal view access |
| Router | DashboardController | /dashboard | Route users to correct tier |

---

## Authentication Flow

```
1. User Logs In
   ↓
2. /dashboard (DashboardController@index)
   ↓
3. Check Conditions:
   ├─ is_admin == true? → /admin/dashboard (TIER 1)
   ├─ admin role in any group? → /group-admin/dashboard (TIER 2)
   └─ else → /member/dashboard (TIER 3)
   ↓
4. Display Role-Appropriate Dashboard
   ├─ TIER 1: System overview, all data
   ├─ TIER 2: Group overview, group data
   └─ TIER 3: Personal overview, personal data
```

---

## Security Considerations

### Authorization Checks

**System Admin Check**
```php
if (!Auth::user()->is_admin) {
    abort(403, 'Not authorized');
}
```

**Group Admin Check**
```php
$isAdmin = GroupMember::where('user_id', $user->id)
    ->where('group_id', $group->id)
    ->where('role', 'admin')
    ->where('status', 'active')
    ->exists();

if (!$isAdmin) abort(403);
```

**Own Record Check**
```php
if ($record->user_id !== Auth::id()) {
    abort(403, 'Cannot access other user\'s records');
}
```

### Middleware Stack
- `auth` - User must be authenticated
- `admin.check` - User must have is_admin=true
- `group.access` - User must belong to group
- `group.role:admin` - User must be group admin

---

## Testing User Scenarios

### Scenario 1: System Admin Login
```
1. Login as admin@itsinda.local
2. Redirected to /admin/dashboard
3. Can see all system data
4. Can manage users, groups, reports
5. Full system access
```

### Scenario 2: Group Admin Login
```
1. Login as groupadmin@example.com
2. Redirected to /group-admin/dashboard
3. Can see only their group
4. Can manage members, loans, savings
5. Cannot see other groups
```

### Scenario 3: Member Login
```
1. Login as demo@example.com
2. Redirected to /member/dashboard
3. Can see personal loans and savings
4. Cannot create or modify records
5. Contact Group Admin for changes
```

---

## Error Handling

### Access Denied
```
User attempts unauthorized access → HTTP 403
Message: "You are not authorized to access this resource"
Redirect: /member/access-denied
```

### Not Admin of Group
```
User tries to manage group they don't admin → HTTP 403
Message: "You are not the admin of this group"
```

### Own Record Only
```
User tries to view other member's loans → HTTP 403
Message: "Cannot access other user's records"
```

---

## Summary Table

| Aspect | TIER 1 (Admin) | TIER 2 (Group Admin) | TIER 3 (Member) |
|--------|---|---|---|
| **Scope** | System-wide | Single group | Personal only |
| **Data Access** | All data | Group data | Own data |
| **Edit Rights** | Full | Group only | Profile only |
| **Reports** | System reports | Group reports | None |
| **User Management** | Full | Group members | Self only |
| **Database Flag** | `is_admin=true` | `role='admin'` in pivot | member in pivot |
| **Route Prefix** | `/admin` | `/group-admin` | `/member` |

---

## Transition Between Tiers

### Promote to Group Admin
```php
GroupMember::create([
    'user_id' => $userId,
    'group_id' => $groupId,
    'role' => 'admin',      // Becomes Group Admin
    'status' => 'active',
]);
```

### Promote to System Admin
```php
User::find($userId)->update([
    'is_admin' => true      // Becomes System Admin
]);
```

### Demote Member
```php
GroupMember::find($memberId)->update([
    'status' => 'inactive',
    'left_at' => now(),
]);
```

---

## Files Related to RBAC

### Controllers
- `app/Http/Controllers/DashboardController.php` - Router
- `app/Http/Controllers/Admin/AdminDashboardController.php` - TIER 1
- `app/Http/Controllers/GroupAdminDashboardController.php` - TIER 2
- `app/Http/Controllers/MemberDashboardController.php` - TIER 3

### Middleware
- `app/Http/Middleware/CheckAdminStatus.php` - Verify is_admin flag
- `app/Http/Middleware/AdminMiddleware.php` - Admin route protection
- `app/Http/Middleware/CheckGroupAssignment.php` - Verify group membership
- `app/Http/Middleware/VerifyGroupAccess.php` - Verify group access
- `app/Http/Middleware/VerifyGroupRole.php` - Check group role

### Models
- `app/Models/User.php` - `is_admin` attribute, group relationships
- `app/Models/Group.php` - Group members relationship
- `app/Models/GroupMember.php` - Pivot model with role field

### Routes
- `routes/web.php` - Main routes with RBAC logic
- `routes/admin.php` - Admin-specific routes

### Views
- `resources/views/dashboards/admin.blade.php` - TIER 1 dashboard
- `resources/views/dashboards/group-admin.blade.php` - TIER 2 dashboard
- `resources/views/dashboards/member.blade.php` - TIER 3 dashboard

---

## Conclusion

This three-tier RBAC system provides:
- ✅ **Clear separation of responsibilities**
- ✅ **Automatic role-based routing**
- ✅ **Fine-grained permission control**
- ✅ **Secure data isolation**
- ✅ **Easy user management**
- ✅ **Scalable architecture**

Users automatically see the right interface for their role, with appropriate features enabled and disabled.
