# Member Dashboard Documentation

## Overview
The Member Dashboard provides **view-only access** to personal financial records. Members can see their loans, savings, and transactions but cannot edit or delete any records.

## Access
- **Route**: `GET /member/dashboard`
- **Named Route**: `route('member.dashboard')`
- **Requirement**: User authenticated with group membership
- **Middleware**: `auth` (view-only in controller)

## Automatic Access
When a user logs in and accesses `/dashboard`, the system automatically checks:
1. Are they a System Admin? → Redirect to admin dashboard
2. Are they a Group Admin? → Redirect to group admin dashboard
3. Otherwise → Show member dashboard

## Dashboard Features

### 1. My Dashboard Overview
Personal summary dashboard showing:

```
┌─────────────────────────────────────┐
│ My Groups              │ 2          │
│ Active Loans           │ 1          │
│ Total Loaned           │ 100,000    │
│ Outstanding Balance    │ 45,000     │
│ Total Savings          │ 50,000     │
│ Savings Balance        │ 50,000     │
└─────────────────────────────────────┘
```

### 2. My Groups
View all groups you belong to:

```
GET /member/dashboard
Shows:
  - Group name
  - Member count
  - Your join date
  - Group status
  - Your role in group

Pagination: 10 groups per page
```

Groups displayed are those with `status = 'active'` and your membership is `active`.

### 3. My Loans
View personal loan records (view-only):

```
GET /member/loans
Displays:
  - Group name
  - Loan amount
  - Amount paid
  - Outstanding amount
  - Status (pending/active/paid/defaulted)
  - Issue date
  - Due date

Actions Available:
  ✅ View details
  ✅ Download statement
  ❌ Edit loan
  ❌ Delete loan
  ❌ Create new loan
```

#### Loan Statistics (Personal)
```
Summary shown:
- Total loaned to you
- Total paid so far
- Outstanding balance
- Number of active loans
- Payment compliance rate
```

#### Loan Detail View
When clicking on a loan:
```
Shows:
  - Full loan details
  - Disbursement info
  - Payment history (all payments made)
  - Interest details
  - Status timeline
  - Due date & grace period info
```

### 4. My Savings
View personal savings accounts (view-only):

```
GET /member/savings
Displays:
  - Group name
  - Current balance
  - Total saved
  - Transaction count
  - Last activity date
  - Status (active)

Actions Available:
  ✅ View account details
  ✅ Deposit money
  ✅ Withdraw money
  ✅ View transaction history
  ❌ Delete account
```

#### Savings Statistics
```
Summary shown:
- Total savings balance (all accounts)
- Total deposited
- Total withdrawn
- Number of accounts
- Average balance
- Savings growth
```

#### Deposit/Withdraw
Members can perform transactions but cannot create/delete savings accounts. Accounts must be created by Group Admin.

### 5. My Transactions
View personal financial transactions:

```
GET /member/transactions
Displays:
  - Transaction date
  - Type (loan payment, deposit, withdrawal, interest, etc.)
  - Group
  - Amount
  - Running balance

Filterable by:
  - Date range
  - Transaction type
  - Group

Pagination: 10 per page
```

#### Transaction Types Visible
- Loan disbursement
- Loan payment
- Interest on savings
- Savings deposit
- Savings withdrawal
- Manual adjustments (by admin)

### 6. My Profile
View and edit own profile:

```
GET  /member/profile
PUT  /member/profile
Editable fields:
  - Name (required)
  - Email (required, unique)
  - Phone (optional)

Cannot edit:
  ❌ Member ID
  ❌ Join dates
  ❌ Admin status
  ❌ Password (separate form)
```

### 7. Access Information
Helpful information for members:

```
Displayed on dashboard:
- Your current role
- Access restrictions explanation
- Instructions for contacting Group Admin
- Tips for managing loans/savings
```

## Controller Methods

```php
class MemberDashboardController extends Controller
{
    // Dashboard summary
    public function index() {}                  // Personal dashboard
    
    // Loan views
    public function myLoans() {}                // List personal loans
    
    // Savings views
    public function mySavings() {}              // List personal savings
    
    // Transactions
    public function myTransactions() {}         // List personal transactions
    
    // Groups
    public function myGroups() {}               // List groups I belong to
    
    // Profile
    public function profile() {}                // View profile
    public function updateProfile() {}          // Update profile
    
    // Access denied
    public function accessDenied() {}           // Show access denied page
    
    // Authorization helper
    protected function verifyOwnership($model) {}
}
```

## Routes

```php
Route::prefix('member')->middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [MemberDashboardController::class, 'index'])
        ->name('member.dashboard');
    
    // Loans
    Route::get('/loans', [MemberDashboardController::class, 'myLoans'])
        ->name('member.loans');
    
    // Savings
    Route::get('/savings', [MemberDashboardController::class, 'mySavings'])
        ->name('member.savings');
    
    // Transactions
    Route::get('/transactions', 
        [MemberDashboardController::class, 'myTransactions'])
        ->name('member.transactions');
    
    // Groups
    Route::get('/groups', [MemberDashboardController::class, 'myGroups'])
        ->name('member.groups');
    
    // Profile
    Route::get('/profile', [MemberDashboardController::class, 'profile'])
        ->name('member.profile');
    
    Route::put('/profile', [MemberDashboardController::class, 'updateProfile'])
        ->name('member.profile-update');
    
    // Access denied
    Route::get('/access-denied', 
        [MemberDashboardController::class, 'accessDenied'])
        ->name('member.access-denied');
});
```

## Permissions Matrix

| Feature | Permission | Notes |
|---------|-----------|-------|
| View own loans | ✅ | View-only |
| Create loans | ❌ | Ask Group Admin |
| Edit loan info | ❌ | Ask Group Admin |
| Delete loan | ❌ | Ask Group Admin |
| Record payment | ✅ | Make payments on own loans |
| View own savings | ✅ | View-only balance |
| Deposit savings | ✅ | Add to own account |
| Withdraw savings | ✅ | From own account |
| View transactions | ✅ | Personal only |
| View profile | ✅ | Own profile only |
| Edit profile | ✅ | Own profile only |
| View other members | ❌ | Cannot see other members' data |
| Manage members | ❌ | Group Admin only |
| System admin access | ❌ | Not available |

## Authorization Logic

```php
// Members can only access their own records
if ($loan->user_id !== Auth::id()) {
    abort(403, 'Cannot access other user\'s records');
}

if ($saving->user_id !== Auth::id()) {
    abort(403, 'Cannot access other user\'s records');
}
```

## Data Isolation

### What Members CAN See
```
✅ Personal loan records
✅ Personal savings accounts
✅ Personal transactions
✅ Groups they belong to
✅ Their own profile
✅ Group information (name, members, status)
```

### What Members CANNOT See
```
❌ Other members' loans
❌ Other members' savings
❌ Other members' transactions
❌ Other members' profiles
❌ Group financial reports
❌ System-wide data
❌ Other groups' data
```

## Workflow Example: Making a Loan Payment

```
1. Member logs in
2. Goes to /member/dashboard
3. Redirected to /member/dashboard (no system/group admin)
4. Views "My Loans" widget
5. Clicks on active loan
6. Views loan details & payment history
7. Clicks "Make Payment"
8. Enters payment amount
9. Submits payment
10. System records transaction
11. Updates loan paid_amount
12. Shows confirmation
```

## Workflow Example: Deposit to Savings

```
1. Member on /member/dashboard
2. Clicks "My Savings"
3. Views savings accounts
4. Clicks on savings account
5. Clicks "Deposit"
6. Enters amount
7. Submits
8. Transaction recorded
9. Balance updated
10. Confirmation shown
```

## Best Practices for Members

### Loan Management
1. ✅ Keep track of due dates
2. ✅ Make timely payments
3. ✅ Save payment receipts
4. ✅ Contact Group Admin for payment issues

### Savings Growth
1. ✅ Deposit regularly
2. ✅ Avoid unnecessary withdrawals
3. ✅ Track savings goal
4. ✅ View transaction history

### Account Security
1. ✅ Keep password private
2. ✅ Don't share login credentials
3. ✅ Review transactions regularly
4. ✅ Report suspicious activity

## Test Account

```
Email:    demo@example.com
Password: DemoPassword123!
is_admin: false
Role:     member (in groups)
Groups:   [Test Group 1], [Test Group 2]
```

## Navigation

From Member Dashboard:
```
├── My Loans
│   ├── View All Personal Loans
│   ├── View Loan Details
│   └── Make Payment
├── My Savings
│   ├── View All Accounts
│   ├── View Account Details
│   ├── Deposit Money
│   └── Withdraw Money
├── My Transactions
│   ├── View All Transactions
│   └── Filter by Type/Date
├── My Groups
│   └── View Groups I Belong To
└── My Profile
    ├── View Profile
    └── Edit Profile
```

## Common Tasks

### View Your Loans
```
1. Click "My Loans" on dashboard
2. See all personal loans
3. Click loan for details
4. View payment history
5. Make payment if needed
```

### Deposit to Savings
```
1. Go to "My Savings"
2. Click on savings account
3. Click "Deposit"
4. Enter amount
5. Confirm deposit
6. See updated balance
```

### Check Transaction History
```
1. Click "My Transactions"
2. View recent transactions
3. Filter by type (optional)
4. Filter by date (optional)
5. Download if needed
```

### Update Your Profile
```
1. Go to "My Profile"
2. Click "Edit Profile"
3. Update name/email/phone
4. Click "Save Changes"
5. Confirmation shown
```

## Limitations

### Cannot Do
- ❌ Create loans (request through Group Admin)
- ❌ Edit loan details
- ❌ Delete loans/savings
- ❌ View other members' records
- ❌ Manage group
- ❌ Approve loans
- ❌ Change member roles
- ❌ View system settings
- ❌ Access financial reports
- ❌ Change password (separate form)

### Can Do
- ✅ View personal loans
- ✅ View personal savings
- ✅ Make loan payments
- ✅ Deposit/withdraw savings
- ✅ View transactions
- ✅ Update profile
- ✅ View group info
- ✅ Change password
- ✅ Download statements
- ✅ View transaction history

## Access Denied Message

When members try to access restricted areas:

```
📋 Access Information

As a group member, you can view:
✓ Your personal loans
✓ Your savings accounts
✓ Your transactions
✓ Group membership info

You cannot edit or delete records.
Contact your Group Admin for changes.
```

## Error Messages

### Trying to Edit Loan
```
"You do not have permission to edit this loan.
Please contact your Group Admin for assistance."
```

### Trying to View Other Member's Data
```
"You cannot access other user's records.
This data is private to its owner."
```

### Insufficient Savings Balance
```
"Insufficient balance for withdrawal.
Current balance: 50,000
Requested withdrawal: 60,000"
```

## FAQ for Members

**Q: Can I apply for a new loan?**
A: Loans are created by Group Admin. Contact your admin to request a loan.

**Q: Can I transfer my loan to another member?**
A: No, loans are personal. Contact your Group Admin for assistance.

**Q: How do I know my loan status?**
A: Check "My Loans" page for current status and payment info.

**Q: Can I see what other members are saving?**
A: No, savings information is private. You can only view your own.

**Q: Who can access my data?**
A: Only you and your Group Admin can view your records. System Admin can for oversight.

**Q: What if I need to withdraw all my savings?**
A: Submit withdrawal request on savings page. Approval may be required by Group Admin.

## Troubleshooting

### Cannot See Loans
**Problem**: Loan list is empty
**Solution**: You may not have requested loans yet. Contact Group Admin.

### Payment Not Recorded
**Problem**: Payment submitted but not showing
**Solution**: Refresh page. If still missing, contact Group Admin.

### Cannot Edit Profile
**Problem**: Changes not saving
**Solution**: Verify email is unique. Try again or contact Group Admin.

### Seeing Other Members' Data
**Problem**: Can see loans/savings of other members
**Solution**: This should not happen. Report to System Admin.

## Conclusion

The Member Dashboard provides a clear, view-only interface for members to track their personal financial records while maintaining data privacy and security. Members can manage their own transactions while Group Admins handle record creation and system-wide oversight.
