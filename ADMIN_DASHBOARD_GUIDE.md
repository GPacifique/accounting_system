# System Admin Dashboard Documentation

## Overview

The System Admin Dashboard is a comprehensive management interface for system administrators to manage all aspects of the ItSinda platform including users, groups, loans, savings, and transactions.

## Access & Permissions

- **Who Can Access**: Only users with `is_admin = true`
- **Route Prefix**: `/admin`
- **Middleware Protection**: `auth` + `admin` middleware
- **Default Admin**: admin@itsinda.local

## Features & Modules

### 1. Dashboard Overview (`/admin/dashboard`)

**Controller**: `AdminDashboardController@index`

**Features:**
- System-wide statistics cards:
  - Total Users
  - Total Groups & Active Groups
  - Active Loans & Total Loans
  - Total Savings & Transactions
  - Financial totals (Loan & Savings amounts)

- Recent Data Tables:
  - Recent 5 users
  - Recent 5 groups
  - Recent 10 transactions

- Quick Action Links:
  - Manage Users
  - Manage Groups
  - View Loans
  - View Savings
  - View Transactions
  - View Reports

---

### 2. Users Management

#### List Users (`/admin/users`)
**Route**: `admin.users.index`
**Controller**: `AdminDashboardController@users`

**Features:**
- Paginated list of all system users (20 per page)
- Display columns:
  - User ID
  - Name
  - Email
  - Role (Admin/User badge)
  - Email verification status
  - Join date
  - Action buttons (Edit, Delete)

#### Edit User (`/admin/users/{user}/edit`)
**Route**: `admin.users.edit`
**Controller**: `AdminDashboardController@editUser`

**Features:**
- Edit user details:
  - Full name
  - Email address
  - Admin status toggle
  - Email verification status
- View associated groups
- User metadata (ID, created date, last updated)

#### Update User (`/admin/users/{user}`)
**Route**: `admin.users.update`
**Controller**: `AdminDashboardController@updateUser`
**Method**: PUT

**Validation Rules:**
```
name: required|string|max:255
email: required|email|unique:users,email,{user_id}
is_admin: boolean
email_verified_at: nullable|date
```

#### Delete User (`/admin/users/{user}`)
**Route**: `admin.users.destroy`
**Controller**: `AdminDashboardController@deleteUser`
**Method**: DELETE

**Protection:**
- Cannot delete currently logged-in admin
- Confirmation required

---

### 3. Groups Management

#### List Groups (`/admin/groups`)
**Route**: `admin.groups.index`
**Controller**: `AdminDashboardController@groups`

**Features:**
- Paginated list of all groups (20 per page)
- Display columns:
  - Group ID
  - Group name
  - Assigned admin
  - Member count
  - Status (Active/Inactive/Suspended)
  - Creation date
  - Action buttons (View, Edit)

#### View Group Details (`/admin/groups/{group}`)
**Route**: `admin.groups.show`
**Controller**: `AdminDashboardController@showGroup`

**Features:**
- Group information:
  - ID, Name, Description
  - Assigned administrator
  - Status
  - Creation date
- Quick statistics:
  - Total members
  - Total loans
  - Active savings
- Members table (paginated, 15 per page):
  - Member ID
  - Member name & email
  - Role in group
  - Join date

#### Edit Group (`/admin/groups/{group}/edit`)
**Route**: `admin.groups.edit`
**Controller**: `AdminDashboardController@editGroup`

**Features:**
- Edit group details:
  - Name
  - Description
  - Group administrator (dropdown of non-admin users)
  - Status (Active/Inactive/Suspended)
- Display group metadata

#### Update Group (`/admin/groups/{group}`)
**Route**: `admin.groups.update`
**Controller**: `AdminDashboardController@updateGroup`
**Method**: PUT

**Validation Rules:**
```
name: required|string|max:255
description: nullable|string
admin_id: nullable|exists:users,id
status: required|in:active,inactive,suspended
```

---

### 4. Loans Management

#### List Loans (`/admin/loans`)
**Route**: `admin.loans.index`
**Controller**: `AdminDashboardController@loans`

**Features:**
- Paginated list of all loans (20 per page)
- Display columns:
  - Loan ID
  - Member name
  - Group name
  - Loan amount
  - Interest rate
  - Status (Active/Paid/Defaulted)
  - Disbursement date
  - View action

#### View Loan Details (`/admin/loans/{loan}`)
**Route**: `admin.loans.show`
**Controller**: `AdminDashboardController@showLoan`

**Features:**
- Loan information:
  - Member & group
  - Amount, interest rate, period
  - Status, application date, disbursement date
- Financial summary:
  - Total amount
  - Total paid (with progress)
  - Outstanding balance
  - Number of payments
- Payments table (paginated):
  - Payment ID, amount, date, method
- Charges table (if any):
  - Charge ID, type, amount, date added

---

### 5. Savings Management

#### List Savings (`/admin/savings`)
**Route**: `admin.savings.index`
**Controller**: `AdminDashboardController@savings`

**Features:**
- Paginated list of all savings accounts (20 per page)
- Display columns:
  - Account ID
  - Member name
  - Group name
  - Current balance
  - Interest rate
  - Status
  - Account opened date
  - View action

#### View Savings Details (`/admin/savings/{saving}`)
**Route**: `admin.savings.show`
**Controller**: `AdminDashboardController@showSaving`

**Features:**
- Account information:
  - Member & group
  - Current balance, interest rate, account type
  - Account opening date
- Account summary:
  - Current balance
  - Total deposits
  - Total withdrawals
  - Total transactions
- Transactions table (paginated):
  - Transaction ID
  - Type (Deposit/Withdrawal)
  - Amount (with +/- indicator)
  - Balance after transaction
  - Transaction date
  - Notes

---

### 6. Transactions Log (`/admin/transactions`)

**Route**: `admin.transactions`
**Controller**: `AdminDashboardController@transactions`

**Features:**
- System-wide transaction log (30 per page)
- Display columns:
  - Transaction ID
  - User who performed action
  - Entity type (User, Group, Loan, etc.)
  - Action performed (Created/Updated/Deleted/Other)
  - Related entity ID
  - Timestamp
- Complete audit trail of all system activities

---

### 7. System Reports (`/admin/reports`)

**Route**: `admin.reports`
**Controller**: `AdminDashboardController@reports`

**Features:**
- Financial Summary Cards:
  - Total loan amount disbursed
  - Total loan amount paid
  - Pending loan amounts
  - Total savings balance

- Key Metrics:
  - Average loan amount
  - Average saving balance
  - Loan collection rate (%)

- Loans by Status:
  - Count of loans in each status

- Groups by Status:
  - Count of groups in each status

- Top 10 Groups by Member Count:
  - Ranked list with member counts and status

---

### 8. Settings (`/admin/settings`)

**Route**: `admin.settings`
**Controller**: `AdminDashboardController@settings`

**Features:**
- System configuration information
- Quick admin actions:
  - System backup
  - Clear cache
  - View system logs
  - Email configuration
  - Data management

- Security checklist:
  - Password hashing status
  - Authentication status
  - CSRF protection status
  - Rate limiting status

---

## Routes Configuration

Register routes in `routes/admin.php`:

```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminDashboardController;

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    
    // Users routes...
    // Groups routes...
    // Loans routes...
    // Savings routes...
    // etc.
});
```

Include in main `routes/web.php`:
```php
include base_path('routes/admin.php');
```

---

## Middleware Configuration

### AdminMiddleware

**Location**: `app/Http/Middleware/AdminMiddleware.php`

```php
<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check() || !auth()->user()->is_admin) {
            abort(403, 'Unauthorized access. Admin privileges required.');
        }
        return $next($request);
    }
}
```

**Register in** `app/Http/Kernel.php`:
```php
protected $routeMiddleware = [
    // ... other middleware
    'admin' => \App\Http\Middleware\AdminMiddleware::class,
];
```

---

## Views Structure

```
resources/views/
├── layouts/
│   └── admin.blade.php          # Admin layout template
├── admin/
│   ├── dashboard.blade.php      # Dashboard overview
│   ├── users/
│   │   ├── index.blade.php      # Users list
│   │   └── edit.blade.php       # Edit user form
│   ├── groups/
│   │   ├── index.blade.php      # Groups list
│   │   ├── show.blade.php       # Group details
│   │   └── edit.blade.php       # Edit group form
│   ├── loans/
│   │   ├── index.blade.php      # Loans list
│   │   └── show.blade.php       # Loan details
│   ├── savings/
│   │   ├── index.blade.php      # Savings list
│   │   └── show.blade.php       # Savings details
│   ├── transactions/
│   │   └── index.blade.php      # Transactions log
│   ├── reports/
│   │   └── index.blade.php      # System reports
│   └── settings/
│       └── index.blade.php      # Admin settings
```

---

## Key Features Summary

✅ **Full Admin Access**: Manage all users, groups, loans, and savings
✅ **Real-time Statistics**: Dashboard with live system metrics
✅ **User Management**: Create, edit, delete users, manage roles
✅ **Group Management**: Oversee all groups and their members
✅ **Financial Tracking**: Monitor all loans and savings accounts
✅ **Transaction Audit**: Complete audit trail of all system activities
✅ **Comprehensive Reports**: Financial summaries and analytics
✅ **Responsive Design**: Works on desktop and tablet devices
✅ **Secure Access**: Protected by admin middleware
✅ **Error Handling**: Prevents self-deletion and shows friendly error messages

---

## Security Considerations

1. **Authentication Required**: All admin routes require login with admin privileges
2. **CSRF Protection**: All forms are protected with CSRF tokens
3. **Authorization Checks**: Explicit checks ensure admin status before access
4. **Audit Logging**: All admin actions are logged in transaction table
5. **Data Validation**: All input is validated server-side
6. **Prevention of Self-Deletion**: Admin cannot delete their own account

---

## Future Enhancements

- [ ] Export reports to PDF/Excel
- [ ] Advanced filtering and search on all lists
- [ ] Bulk operations (bulk delete, bulk edit)
- [ ] User activity dashboard
- [ ] Group performance analytics
- [ ] Loan portfolio analysis
- [ ] Savings goals tracking
- [ ] Two-factor authentication for admin accounts
- [ ] Role-based admin sub-groups
- [ ] System backup scheduling
- [ ] Email notification configuration

