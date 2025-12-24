,# Database Seeders Documentation

## Overview
The seeders are Laravel database seeding classes that populate the database with initial data. They are located in the `database/seeders/` directory and are used to set up test users and system data.

## Seeders List

### 1. DatabaseSeeder
**File:** `database/seeders/DatabaseSeeder.php`

**Purpose:** Main seeder orchestrator that coordinates the execution of all other seeders.

**Functionality:**
- Acts as the entry point for all database seeding operations
- Uses the `WithoutModelEvents` trait to disable model events during seeding for better performance
- Calls the `AdminUserSeeder` class

**Methods:**
- `run()`: Executes the seeding process by calling other seeders via the `$this->call()` method

**Usage:**
```bash
php artisan db:seed                    # Run all seeders
php artisan db:seed --class=DatabaseSeeder  # Run this specific seeder
```

---

### 2. AdminUserSeeder
**File:** `database/seeders/AdminUserSeeder.php`

**Purpose:** Creates initial system users including admin and demo accounts for testing and development.

**Functionality:**
Creates three user accounts with predefined credentials:

1. **System Administrator**
   - Email: `admin@itsinda.local`
   - Password: `AdminPassword123!`
   - Name: System Administrator
   - Role: Admin (`is_admin = true`)
   - Status: Email verified

2. **Demo User (Member)**
   - Email: `demo@example.com`
   - Password: `DemoPassword123!`
   - Name: Demo User
   - Role: Member
   - Status: Email verified

3. **Group Administrator**
   - Email: `groupadmin@example.com`
   - Password: `GroupAdminPass123!`
   - Name: Group Administrator
   - Role: Group Admin
   - Status: Email verified

**Key Features:**
- Uses `User::firstOrCreate()` to prevent duplicate entries when seeding is run multiple times
- All passwords are hashed using Laravel's `Hash::make()` function
- All email addresses are pre-verified with `email_verified_at = now()`
- Supports multiple user roles for testing different access levels

**Methods:**
- `run()`: Executes the user creation process

---

## Execution Flow

```
DatabaseSeeder::run()
    ↓
AdminUserSeeder::run()
    ↓
Creates 3 system users in the database
```

## Running Seeders

### Run All Seeders
```bash
php artisan db:seed
```

### Run Specific Seeder
```bash
php artisan db:seed --class=AdminUserSeeder
```

### Reset Database and Run Seeders
```bash
php artisan migrate:fresh --seed
```

### Reset Database without Seeding
```bash
php artisan migrate:fresh
```

## Development Notes

- All three user accounts are created with `email_verified_at` set to `now()`, meaning they are pre-verified for immediate use
- The `firstOrCreate()` method ensures idempotency - running seeders multiple times won't create duplicate users
- Passwords are hardcoded for development/testing purposes only and should be changed in production

---

## Role-Based Access Control (RBAC)

The system implements three distinct user roles with specific permissions and restrictions:

### System Administrator (`is_admin = true`)
**Permissions:**
- ✅ Full access to all system features
- ✅ No restrictions on data viewing or management
- ✅ Can manage all users, groups, loans, savings, and transactions
- ✅ Access to system reports and analytics
- ✅ Can manage other group admins and members

**Test User:** admin@itsinda.local

---

### Group Administrator
**Permissions:**
- ✅ Manage group data and group membership
- ✅ View all members within their group
- ✅ Manage loans and savings for their group members
- ✅ View transactions related to their group
- ❌ Cannot view or manage other groups' data
- ❌ Cannot view or manage other groups' members
- ❌ Cannot manage system-wide settings

**Restrictions:**
- Data access is limited to their assigned group only
- Cannot see financial records of other groups
- Cannot modify group settings outside their own group

**Test User:** groupadmin@example.com

---

### Member
**Permissions:**
- ✅ View records related to their own user ID
- ✅ View their own loans, savings, and transaction history
- ❌ Cannot edit any records
- ❌ Cannot view other members' data
- ❌ Cannot manage groups or memberships
- ❌ Read-only access

**Restrictions:**
- Strictly view-only access
- Only see personal financial records
- No administrative capabilities

**Test User:** demo@example.com

---

## Authorization Implementation Checklist

To implement this RBAC system effectively, ensure the following:

1. **Policy Classes** - Create authorization policies in `app/Policies/`
   - [ ] Implement policies for Group, GroupMember, Loan, Saving, Transaction models
   - [ ] Check `is_admin` flag for System Admin bypass
   - [ ] Verify group ownership for Group Admin operations
   - [ ] Restrict Member access to own records only

2. **Gate Definitions** - Define authorization gates in `app/Providers/AppServiceProvider.php`
   - [ ] `view-all-groups` → System Admin only
   - [ ] `manage-group` → System Admin or owner Group Admin
   - [ ] `view-member-records` → System Admin or Group Admin of that group or the Member themselves

3. **Middleware** - Apply authorization checks in controllers
   - [ ] Use `@can` directive in views for conditional rendering
   - [ ] Use `$this->authorize()` in controllers for permission checks
   - [ ] Return 403 Forbidden for unauthorized access attempts

4. **Database Queries** - Scope queries by role
   - [ ] System Admin: Query all records
   - [ ] Group Admin: Scope to `where('group_id', auth()->user()->group_id)`
   - [ ] Member: Scope to `where('user_id', auth()->id())`

5. **API Routes** - Protect endpoints with authorization
   - [ ] Add authorization middleware to route groups
   - [ ] Check permissions before returning data
- The admin user has `is_admin = true`, while demo users have `is_admin = false`

## Security Considerations

⚠️ **Important:** The hardcoded credentials in seeders are for development and testing only. In production:
1. Never commit hardcoded passwords to version control
2. Use environment variables or secure credential management
3. Change all default passwords immediately after database setup
4. Remove or modify seeders in production environments

⚠️ **Authorization Security:**
- Always validate user role and group membership on the backend
- Never rely solely on frontend checks for access control
- Implement proper audit logging for sensitive operations
- Regularly audit permissions and group memberships
- Use Laravel's built-in authorization and authentication systems
- Test authorization policies thoroughly before deployment

## Future Enhancements

Consider adding additional seeders for:
- Groups and group members
- Loans and loan details
- Savings accounts
- Test transactions
- System configuration data
