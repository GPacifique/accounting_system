# Group Registration & Approval Workflow

## System Architecture

```
┌─────────────────────────────────────────────────────────────────────┐
│                         WORKFLOW STAGES                             │
└─────────────────────────────────────────────────────────────────────┘

STAGE 1: GROUP REGISTRATION
   ├─ User creates group account
   ├─ User becomes group admin (automatic)
   └─ Status: Active, Approval Status: Pending

STAGE 2: ADMIN APPROVAL
   ├─ System admin reviews group details
   ├─ Admin approves or rejects group
   └─ Status: Approved/Rejected, Approved By: Admin ID

STAGE 3: MEMBER MANAGEMENT
   ├─ Group admin creates member accounts
   │  └─ Member email, password set by admin
   ├─ Group admin invites existing users
   │  └─ User account already exists in system
   └─ Each member gets member role (can be treasurer)

STAGE 4: DATA ACCESS ISOLATION
   ├─ Members see only their own data (loans/savings)
   ├─ Admin/Treasurer see all group member data
   └─ System admin sees all groups
```

## User Roles & Permissions

### Group Admin
- ✅ Create group (becomes admin automatically)
- ✅ Create new member accounts
- ✅ Invite existing users to group
- ✅ Edit group details
- ✅ View all member loans & savings
- ✅ Approve/disburse loans
- ✅ Add interest to savings
- ✅ View reports
- ✅ Remove members
- ❌ Approve group (system admin only)

### Group Treasurer
- ✅ View all member loans & savings
- ✅ Approve/disburse loans
- ✅ Add interest to savings
- ✅ Record loan payments
- ✅ View reports
- ❌ Create/invite members (admin only)
- ❌ Edit group details (admin only)

### Group Member
- ✅ View own loans
- ✅ View own savings
- ✅ Request loans
- ✅ Make loan payments
- ✅ Deposit/withdraw savings
- ❌ View other members' data
- ❌ Approve loans
- ❌ Manage group settings

### System Admin
- ✅ Approve/reject group registrations
- ✅ View all groups
- ✅ View all users
- ✅ View group details before approval
- ✅ Access admin dashboard
- ❌ Direct group operations (must use group admin)

## Database Changes

### Migration 1: Add Approval System to Groups
```php
'approval_status' => enum('pending', 'approved', 'rejected')
'approved_by' => foreignId('users')
'approved_at' => timestamp (nullable)
'rejection_reason' => text (nullable)
```

### Migration 2: Add is_admin to Users
```php
'is_admin' => boolean (default: false)
```

## File Structure Changes

### New Controllers
```
app/Http/Controllers/
├── GroupRegistrationController.php    (Group creation & member management)
└── Admin/
    └── GroupApprovalController.php    (Admin panel for group approval)
```

### New Policies
```
app/Policies/
├── LoanPolicy.php                     (Loan data isolation)
└── SavingPolicy.php                  (Savings data isolation)
```

### New Middleware
```
app/Http/Middleware/
└── CheckAdminStatus.php               (Verify system admin access)
```

### Updated Models
```
app/Models/
├── User.php                           (Added is_admin field)
└── Group.php                          (Added approval_status, approver)
```

## API Endpoints

### Group Registration (Public, Authenticated)
```
GET  /groups/register                  - Registration form
POST /groups                           - Create new group
```

### Group Management (Admin + Treasurer)
```
GET  /groups/{group}/members           - Manage members page
POST /groups/{group}/members/create    - Create member account
POST /groups/{group}/members/add-existing - Add existing user
```

### Admin Panel (System Admin Only)
```
GET  /admin/dashboard                  - Pending groups overview
GET  /admin/groups/{group}             - View group details
POST /admin/groups/{group}/approve     - Approve group
POST /admin/groups/{group}/reject      - Reject group
GET  /admin/users                      - View all users
GET  /admin/groups                     - View all groups
```

## Data Isolation Implementation

### Savings Policy (SavingPolicy.php)
```php
view()        → Own account OR admin/treasurer
deposit()     → Own account OR admin/treasurer
withdraw()    → Own account OR admin/treasurer
addInterest() → Admin/treasurer only
viewStatement()→ Own statement OR admin/treasurer
```

### Loan Policy (LoanPolicy.php)
```php
view()         → Own loan OR admin/treasurer
create()       → Any group member
recordPayment()→ Own loan OR admin/treasurer
approve()      → Admin/treasurer only
disburse()     → Admin/treasurer only
markDefault()  → Admin only
```

## Security Features

1. **Group Approval System**
   - Groups start in pending status
   - Admin must approve before full operations
   - Rejection reason recorded for transparency

2. **Multi-Level Authorization**
   - Authentication: User logged in
   - Group Assignment: User in group
   - Group Access: User has access to this group
   - Role-Based: User has required role
   - Data Isolation: Policy-based access control

3. **Session Management**
   - Current group stored in session
   - Auto-selection for single group users
   - Manual selection for multi-group users

4. **Audit Trail**
   - Group creation recorded with creator
   - Approval/rejection tracked with timestamp
   - Admin ID recorded for audit

## Workflow Example

### Example 1: New Group Registration

```
1. User registers group "ABC Savings Club"
   ├─ Group created with status='active'
   ├─ Approval status='pending'
   └─ User assigned as group admin

2. System admin sees pending group in dashboard
   ├─ Reviews group details
   ├─ Approves group
   └─ Sends notification to admin

3. Group admin logs in
   ├─ Sees group in dashboard
   ├─ Goes to member management
   ├─ Creates member accounts for 10 people
   └─ Members can now access their data

4. Member logs in
   ├─ Sees their group
   ├─ Can view own loans/savings
   ├─ Cannot see other member data
   └─ Can request loan or deposit savings
```

### Example 2: Member Access Control

```
Request: GET /groups/1/savings/5 (member's own savings)
├─ Auth check: ✓ Logged in
├─ Group assignment: ✓ Has groups
├─ Group access: ✓ In group 1
├─ Policy check: ✓ Is owner of saving 5
└─ Result: Access granted

Request: GET /groups/1/savings/10 (different member's savings)
├─ Auth check: ✓ Logged in
├─ Group assignment: ✓ Has groups
├─ Group access: ✓ In group 1
├─ Policy check: ✗ Not owner, not admin/treasurer
└─ Result: 403 Forbidden
```

## Testing Checklist

- [ ] User can register new group
- [ ] Group appears in admin pending list
- [ ] Admin can view group details before approval
- [ ] Admin can approve group with timestamp
- [ ] Admin can reject group with reason
- [ ] Group admin can create member accounts
- [ ] Group admin can invite existing users
- [ ] Member can see own loans/savings
- [ ] Member cannot see other member data
- [ ] Admin/treasurer can see all member data
- [ ] Loan and savings policies enforced
- [ ] Role-based restrictions enforced

## Migration Commands

```bash
# Run migrations
php artisan migrate

# Create admin user (manual or command)
php artisan tinker
> User::create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => bcrypt('password'), 'is_admin' => true])
```

## Next Steps

1. Create Blade views for:
   - Group registration form
   - Admin dashboard
   - Group detail page (for approval)
   - Member management page
   - Member creation form

2. Add notifications for:
   - Group approval notification
   - Group rejection notification
   - Member invitation notification

3. Add form requests for:
   - StoreGroupRequest
   - CreateMemberRequest
   - AddExistingMemberRequest

4. Implement communication module for:
   - Group announcements
   - Member-to-admin messages
   - System notifications
