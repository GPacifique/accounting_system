# Group Admin Dashboard Documentation

## Overview
The Group Admin Dashboard allows group administrators to manage their specific group with full control over members, loans, and savings.

## Access
- **Route**: `GET /group-admin/dashboard`
- **Named Route**: `route('group-admin.dashboard')`
- **Requirement**: User with `role = 'admin'` in group_members table
- **Middleware**: `auth` (with authorization check in controller)

## Automatic Authorization
When accessing the dashboard, the controller automatically identifies which group the user administers:

```php
$group = GroupMember::where('user_id', $user->id)
    ->where('role', 'admin')
    ->where('status', 'active')
    ->with('group')
    ->first()
    ->group;
```

## Dashboard Features

### 1. Group Statistics
Key metrics for the administered group:

```
┌──────────────────────────────┐
│ Total Members    │ 42        │
│ Active Loans     │ 18        │
│ Total Loaned     │ 850,000   │
│ Outstanding      │ 350,000   │
│ Savings Balance  │ 600,000   │
│ Member Growth    │ +5 month  │
└──────────────────────────────┘
```

### 2. Member Management
Control who belongs to the group:

#### View Members
```
GET /group-admin/groups/{group}/members
Display:
  - Name
  - Email
  - Role (admin/treasurer/secretary/member)
  - Join Date
  - Status

Pagination: 15 members per page
```

#### Add New Member
```
POST /group-admin/groups/{group}/members
Form Fields:
  - User ID (select existing user)
  - Role (dropdown: member/treasurer/secretary)
Action:
  - Creates group_member record
  - Sends notification to user
```

#### Update Member Role
```
PUT /group-admin/groups/{group}/members/{member}
Form:
  - Role (dropdown)
Action:
  - Updates member role
  - Cannot demote own admin role
  - Logs change
```

#### Remove Member
```
DELETE /group-admin/groups/{group}/members/{member}
Action:
  - Sets status to 'inactive'
  - Records left_at timestamp
  - Cannot remove self
  - Notification sent to member
```

### 3. Loan Management
Monitor and manage group loans:

#### View Group Loans
```
GET /group-admin/groups/{group}/loans
Display:
  - Member name
  - Loan amount
  - Paid amount
  - Outstanding amount
  - Status
  - Due date
Pagination: 15 per page
```

#### Loan Statistics
```
Shown on dashboard:
- Total loaned amount
- Active loans count
- Paid loans count
- Default loans count
- Average loan amount
- Payment rate
```

### 4. Savings Management
Track member savings:

#### View Group Savings
```
GET /group-admin/groups/{group}/savings
Display:
  - Member name
  - Current balance
  - Total deposited
  - Deposits count
  - Last activity
Pagination: 15 per page
```

#### Savings Statistics
```
Shown on dashboard:
- Total savings balance
- Total deposits
- Savings accounts count
- Average balance
- Growth trend
```

### 5. Transactions
View all group financial transactions:

```
GET /group-admin/groups/{group}/transactions
Displays:
  - Transaction date
  - Type (loan disbursement, payment, deposit, etc.)
  - Member
  - Amount
  - Status

Filterable by:
  - Date range
  - Type
  - Member

Pagination: 20 per page
```

### 6. Financial Reports
Generate group-specific reports:

```
GET /group-admin/groups/{group}/reports
Reports available:
  - Loan Summary
  - Savings Summary
  - Member Financial Status
  - Cash Flow
  - Loan Performance
  - Default Analysis
```

### 7. Group Settings
Edit group information:

```
GET  /group-admin/groups/{group}/edit
PUT  /group-admin/groups/{group}
Fields:
  - Group Name (required)
  - Description (optional)
  - Status (active/inactive)
  - Rules & Guidelines (optional)
```

## Controller Methods

```php
class GroupAdminDashboardController extends Controller
{
    // Dashboard
    public function index() {}                              // Main dashboard
    
    // Members
    public function manageMembers(Group $group) {}          // List members
    public function addMember(Group $group) {}              // Add member
    public function updateMemberRole(Group $group, 
                                     GroupMember $member) {} // Update role
    public function removeMember(Group $group, 
                                 GroupMember $member) {}    // Remove member
    
    // Loans
    public function loans(Group $group) {}                  // View loans
    
    // Savings
    public function savings(Group $group) {}                // View savings
    
    // Transactions
    public function transactions(Group $group) {}           // View transactions
    
    // Reports
    public function reports(Group $group) {}                // View reports
    
    // Group settings
    public function editGroup(Group $group) {}              // Edit form
    public function updateGroup(Group $group) {}            // Update info
    
    // Authorization
    private function authorizeGroupAdmin(Group $group) {}   // Check permission
}
```

## Routes

```php
Route::prefix('group-admin')->middleware('auth')->group(function () {
    Route::get('/dashboard', [GroupAdminDashboardController::class, 'index'])
        ->name('group-admin.dashboard');
    
    Route::get('/groups/{group}/loans', 
        [GroupAdminDashboardController::class, 'loans'])
        ->name('group-admin.loans');
    
    Route::get('/groups/{group}/savings', 
        [GroupAdminDashboardController::class, 'savings'])
        ->name('group-admin.savings');
    
    Route::get('/groups/{group}/members', 
        [GroupAdminDashboardController::class, 'manageMembers'])
        ->name('group-admin.manage-members');
    
    Route::get('/groups/{group}/transactions', 
        [GroupAdminDashboardController::class, 'transactions'])
        ->name('group-admin.transactions');
    
    Route::get('/groups/{group}/reports', 
        [GroupAdminDashboardController::class, 'reports'])
        ->name('group-admin.reports');
    
    // Group management
    Route::get('/groups/{group}/edit', 
        [GroupAdminDashboardController::class, 'editGroup'])
        ->name('group-admin.edit-group');
    
    Route::put('/groups/{group}', 
        [GroupAdminDashboardController::class, 'updateGroup'])
        ->name('group-admin.update-group');
    
    // Member management
    Route::post('/groups/{group}/members', 
        [GroupAdminDashboardController::class, 'addMember'])
        ->name('group-admin.add-member');
    
    Route::put('/groups/{group}/members/{member}', 
        [GroupAdminDashboardController::class, 'updateMemberRole'])
        ->name('group-admin.update-member-role');
    
    Route::delete('/groups/{group}/members/{member}', 
        [GroupAdminDashboardController::class, 'removeMember'])
        ->name('group-admin.remove-member');
});
```

## Permissions Matrix

| Feature | Permission | Restrictions |
|---------|-----------|--------------|
| View group overview | ✅ | Own group only |
| Edit group info | ✅ | Own group only |
| View members | ✅ | Own group only |
| Add members | ✅ | Own group only |
| Change member role | ✅ | Cannot demote self |
| Remove members | ✅ | Cannot remove self |
| View group loans | ✅ | Own group only |
| View group savings | ✅ | Own group only |
| View transactions | ✅ | Own group only |
| View reports | ✅ | Own group only |
| View other groups | ❌ | Access denied |
| System admin access | ❌ | Access denied |

## Member Role Hierarchy

```
Admin (Group Admin)
  ├── Full group management
  ├── Member management
  ├── Financial operations
  └── Report generation

Treasurer
  ├── View loans/savings
  ├── Record payments
  ├── Deposit/withdraw
  └── View reports

Secretary
  ├── View loans/savings
  ├── Record keeping
  └── Documentation

Member
  ├── View own records only
  ├── Request loans
  └── View own transactions
```

## Authorization Logic

```php
// Verify user is admin of the group
$isAdmin = GroupMember::where('user_id', $user->id)
    ->where('group_id', $group->id)
    ->where('role', 'admin')
    ->where('status', 'active')
    ->exists();

if (!$isAdmin) {
    abort(403, 'You are not authorized to manage this group.');
}
```

## Best Practices

### Member Management
1. ✅ Clearly communicate roles to members
2. ✅ Review member permissions regularly
3. ✅ Keep member list up-to-date
4. ✅ Archive inactive members (set status to inactive)

### Financial Oversight
1. ✅ Monitor loan repayment rates
2. ✅ Review outstanding balances
3. ✅ Track savings growth
4. ✅ Generate monthly reports

### Group Operations
1. ✅ Keep group information current
2. ✅ Maintain clear communication with members
3. ✅ Document important decisions
4. ✅ Follow group rules and guidelines

### Security
1. ✅ Protect admin credentials
2. ✅ Don't share admin login
3. ✅ Review audit logs
4. ✅ Remove inactive members

## Test Account

```
Email:    groupadmin@example.com
Password: GroupAdminPass123!
is_admin: false
Role:     admin (in one group)
Groups:   [Test Group 1] (admin), [Test Group 2] (member)
```

## Navigation

From Group Admin Dashboard:
```
├── Members
│   ├── View All Members
│   ├── Add New Member
│   ├── Edit Member Role
│   └── Remove Member
├── Loans
│   ├── View Group Loans
│   └── Loan Statistics
├── Savings
│   ├── View Group Savings
│   └── Savings Statistics
├── Transactions
│   └── View All Transactions
├── Reports
│   ├── Loan Summary
│   ├── Savings Summary
│   └── Member Financial Status
└── Settings
    ├── Edit Group Info
    └── Group Rules
```

## Common Tasks

### Add a New Member
```
1. Click "Manage Members"
2. Click "Add New Member"
3. Select user from dropdown
4. Assign role (member/treasurer/secretary)
5. Click "Add Member"
```

### Change Member Role
```
1. Go to Members
2. Find member in list
3. Click "Edit" next to role
4. Select new role
5. Click "Update"
```

### View Financial Report
```
1. Click "Reports"
2. Select report type (Loan Summary, etc.)
3. Click "Generate"
4. View or download PDF
```

### Remove Inactive Member
```
1. Go to Members
2. Click "Remove" next to member
3. Confirm action
4. Member set to inactive, left_at recorded
```

## Limitations & Restrictions

### Cannot Do
- ❌ Access other groups' data
- ❌ Manage users outside the group
- ❌ Delete own admin role
- ❌ Remove self from group
- ❌ Access system admin features
- ❌ Manage system settings
- ❌ View or edit other group admins

### Can Do
- ✅ Manage all members in group
- ✅ View all group financial data
- ✅ Generate group reports
- ✅ Edit group information
- ✅ View all group transactions
- ✅ Assign member roles
- ✅ Monitor loan and savings activity

## Troubleshooting

### Cannot See Dashboard
**Problem**: Redirect or access denied
**Solution**: Check if you have admin role in a group

### Cannot Edit Group Info
**Problem**: Changes not saving
**Solution**: Verify you have admin role, not just member role

### Member Not Added
**Problem**: User not in dropdown
**Solution**: User must have account first; System Admin creates users

### Data Not Showing
**Problem**: Empty tables
**Solution**: Check group has active loans/savings/members

## Conclusion

The Group Admin Dashboard provides all tools needed to manage a group effectively, with clear separation from other groups and the system administration.
