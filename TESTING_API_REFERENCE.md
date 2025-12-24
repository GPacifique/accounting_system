# System Testing & API Reference Guide

## Complete Route Reference

### Public Routes (No Authentication Required)
```
GET  /                 - Welcome page
GET  /login            - Login form
POST /login            - Login submission
```

### Authentication Routes (Requires Login)
```
GET  /select-group                 - Select group (if multiple groups)
POST /confirm-group                - Confirm selected group
POST /logout                       - Logout
```

### Group Registration & Management (Requires Login + Group Assignment)
```
GET  /groups/register              - Show group registration form
POST /groups                       - Create new group (pending approval)
```

### Member Management (Requires Login + Group Access + Admin Role)
```
GET  /groups/{group}/members       - Member management page
POST /groups/{group}/members/create     - Create new member account
POST /groups/{group}/members/add-existing - Add existing user to group
```

### Loan Routes (Requires Login + Group Access)
```
GET  /groups/{group}/loans                           - List loans
GET  /groups/{group}/loans/create                    - Create loan form
POST /groups/{group}/loans                          - Store new loan
GET  /groups/{group}/loans/{loan}                   - View loan details
GET  /groups/{group}/loans/{loan}/edit              - Edit form
PUT  /groups/{group}/loans/{loan}                   - Update loan

POST /groups/{group}/loans/{loan}/record-payment    - Record payment (owner or admin/treasurer)
```

### Loan Admin Routes (Requires Admin/Treasurer Role)
```
POST /groups/{group}/loans/{loan}/approve           - Approve loan
POST /groups/{group}/loans/{loan}/disburse          - Disburse loan
POST /groups/{group}/loans/{loan}/default           - Mark as default
GET  /groups/{group}/loans-statistics               - Loan statistics
GET  /groups/{group}/loans-report                   - Default report
```

### Savings Routes (Requires Login + Group Access)
```
GET  /groups/{group}/savings                        - List savings
GET  /groups/{group}/savings/{saving}               - View savings details
POST /groups/{group}/savings/{saving}/deposit       - Deposit funds
POST /groups/{group}/savings/{saving}/withdraw      - Withdraw funds
GET  /groups/{group}/member/{member}/statement      - Member statement
```

### Savings Admin Routes (Requires Admin/Treasurer Role)
```
POST /groups/{group}/savings/{saving}/interest      - Add interest
```

### Admin Routes (Requires System Admin)
```
GET  /admin/dashboard                               - Pending groups list
GET  /admin/groups/{group}                          - Review group
POST /admin/groups/{group}/approve                  - Approve group
POST /admin/groups/{group}/reject                   - Reject group
GET  /admin/users                                   - View all users
GET  /admin/groups                                  - View all groups
```

## HTTP Status Codes Used

```
200 OK                 - Success
201 Created           - Resource created
204 No Content        - Success (no response body)
400 Bad Request       - Validation error
401 Unauthorized      - Not authenticated
403 Forbidden         - Not authorized
404 Not Found         - Resource not found
422 Unprocessable     - Validation error
500 Server Error      - Server error
```

## Testing the System - Step by Step

### Test Case 1: Complete Registration & Approval Flow

**Step 1: Create User Account (Manual or via Tinker)**
```bash
php artisan tinker

# Create test user
User::create([
    'name' => 'John Admin',
    'email' => 'john@example.com',
    'password' => bcrypt('password123'),
    'is_admin' => false  # Not admin yet
])

# Create system admin
User::create([
    'name' => 'System Admin',
    'email' => 'admin@system.com',
    'password' => bcrypt('admin123'),
    'is_admin' => true
])
```

**Step 2: Login as User**
```
POST /login
{
    "email": "john@example.com",
    "password": "password123"
}

Expected:
- Session created
- Redirect to dashboard or group selection
```

**Step 3: Register New Group**
```
GET /groups/register
# Shows form

POST /groups
{
    "name": "Community Savings Group",
    "description": "A group for community members to save together"
}

Expected Response (201):
{
    "id": 1,
    "name": "Community Savings Group",
    "description": "...",
    "created_by": 1,
    "status": "active",
    "approval_status": "pending",
    "created_at": "2025-12-24T10:00:00Z"
}
```

**Step 4: Verify Group in Pending List**
```
Logout from user account

POST /login
{
    "email": "admin@system.com",
    "password": "admin123"
}

GET /admin/dashboard
# Should show "Community Savings Group" in pending list
```

**Step 5: Review & Approve Group**
```
GET /admin/groups/1
# Shows group details, creator, etc.

POST /admin/groups/1/approve
{
    "admin_notes": "Approved after verification"
}

Expected:
- Group approval_status = "approved"
- Timestamp recorded
- Message: "Group approved successfully"
```

**Step 6: Logout & Login as Group Admin**
```
POST /logout

POST /login
{
    "email": "john@example.com",
    "password": "password123"
}

GET /dashboard
# Shows user's group
```

**Step 7: Create Member Account**
```
GET /groups/1/members
# Shows member management page

POST /groups/1/members/create
{
    "name": "Jane Member",
    "email": "jane@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}

Expected:
- User "Jane Member" created
- Added to group as member
- Can login with provided credentials
```

**Step 8: Verify Member Data Isolation**
```
POST /login
{
    "email": "jane@example.com",
    "password": "password123"
}

GET /groups/1/loans
# Returns only Jane's loans (empty if none)

GET /groups/1/savings
# Returns only Jane's savings (empty if none)

# Try accessing another member's data
GET /groups/1/savings/10  (if exists for another member)
# Should return 403 Forbidden
```

### Test Case 2: Admin Can View All Member Data

**Setup:** Create second member in group

```
POST /groups/1/members/create
{
    "name": "Bob Member",
    "email": "bob@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

**Test: Admin Views All Member Data**
```
Logout and login as group admin (john@example.com)

GET /groups/1/loans
# Returns loans from Jane AND Bob

GET /groups/1/savings
# Returns savings from Jane AND Bob

GET /groups/1/savings/10  (Bob's savings)
# Returns data - admin can see
```

**Test: Member Cannot See Other Data**
```
Logout and login as Jane (jane@example.com)

GET /groups/1/savings/10  (Bob's savings)
# Returns 403 Forbidden
```

### Test Case 3: Loan Workflow with Role-Based Access

**Setup:** Create loan request as member
```
Login as Jane (jane@example.com)

POST /groups/1/loans
{
    "principal_amount": 10000,
    "monthly_charge": 500,
    "duration_months": 12,
    "notes": "For business"
}

Expected Response:
{
    "id": 1,
    "status": "pending",
    "created_at": "2025-12-24T11:00:00Z"
}
```

**Test: Member Cannot Approve (Should be 403)**
```
Logged in as Jane

POST /groups/1/loans/1/approve
# Returns 403 Forbidden
```

**Test: Admin Can Approve**
```
Logout, login as admin

POST /groups/1/loans/1/approve
# Returns 200 OK
# Loan status changed to "approved"
```

**Test: Admin Can Disburse**
```
Logged in as admin

POST /groups/1/loans/1/disburse
# Returns 200 OK
# Loan status changed to "active"
# Funds disbursed
```

**Test: Member Can Record Payment**
```
Logout, login as Jane

POST /groups/1/loans/1/record-payment
{
    "principal_paid": 1000,
    "charges_paid": 500
}

Expected:
- Payment recorded
- Balance updated
```

### Test Case 4: Rejection Workflow

**Setup:** Register another group (don't approve first one)

```
Login as different user

POST /groups
{
    "name": "Another Group",
    "description": "Test rejection"
}
# Creates group with approval_status = pending
```

**Test: Admin Rejects with Reason**
```
Login as admin

GET /admin/dashboard
# Shows both pending groups

POST /admin/groups/2/reject
{
    "rejection_reason": "Duplicate group name detected in system"
}

Expected:
- approval_status = "rejected"
- rejection_reason stored
```

**Verify:** Group cannot be used until approved
```
The rejected group won't function in member management
```

## Request/Response Examples

### Register Group Request
```http
POST /groups HTTP/1.1
Host: localhost
Content-Type: application/json
X-CSRF-TOKEN: {{ csrf_token }}

{
    "name": "Savings Club Alpha",
    "description": "Community savings group"
}
```

### Register Group Response
```json
{
    "id": 1,
    "name": "Savings Club Alpha",
    "description": "Community savings group",
    "created_by": 1,
    "status": "active",
    "approval_status": "pending",
    "total_savings": 0,
    "total_loans_issued": 0,
    "total_interest_earned": 0,
    "created_at": "2025-12-24T10:00:00Z",
    "updated_at": "2025-12-24T10:00:00Z"
}
```

### Approve Group Request
```http
POST /admin/groups/1/approve HTTP/1.1
Host: localhost
Content-Type: application/json
X-CSRF-TOKEN: {{ csrf_token }}

{}
```

### Approve Group Response
```json
{
    "message": "Group 'Savings Club Alpha' has been approved successfully!",
    "group": {
        "id": 1,
        "approval_status": "approved",
        "approved_by": 5,
        "approved_at": "2025-12-24T11:00:00Z"
    }
}
```

### Create Member Request
```http
POST /groups/1/members/create HTTP/1.1
Host: localhost
Content-Type: application/json
X-CSRF-TOKEN: {{ csrf_token }}

{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "SecurePass123",
    "password_confirmation": "SecurePass123"
}
```

### Create Member Response
```json
{
    "message": "Member 'John Doe' created successfully!",
    "user": {
        "id": 10,
        "name": "John Doe",
        "email": "john@example.com"
    },
    "group_member": {
        "group_id": 1,
        "user_id": 10,
        "role": "member",
        "status": "active",
        "joined_at": "2025-12-24T11:30:00Z"
    }
}
```

### View Own Savings (Member) Request
```http
GET /groups/1/savings HTTP/1.1
Host: localhost
Authorization: Bearer {{ access_token }}
```

### View Own Savings Response
```json
{
    "data": [
        {
            "id": 5,
            "group_id": 1,
            "member_id": 15,
            "current_balance": "1500.00",
            "total_deposits": "2000.00",
            "total_withdrawals": "500.00",
            "interest_earned": "0.00",
            "last_deposit_date": "2025-12-24",
            "last_withdrawal_date": null,
            "created_at": "2025-12-24T10:00:00Z"
        }
    ]
}
```

### Try to View Another Member's Savings (Should Fail)
```http
GET /groups/1/savings/10 HTTP/1.1
Host: localhost
Authorization: Bearer {{ member_token }}
```

### Response (403 Forbidden)
```json
{
    "message": "Unauthorized",
    "exception": "Access denied"
}
```

### Admin Views All Savings
```http
GET /groups/1/savings HTTP/1.1
Host: localhost
Authorization: Bearer {{ admin_token }}
```

### Response (All Member Data)
```json
{
    "data": [
        {
            "id": 5,
            "member_id": 15,
            "member_name": "John Doe",
            "current_balance": "1500.00"
        },
        {
            "id": 10,
            "member_id": 20,
            "member_name": "Jane Smith",
            "current_balance": "2500.00"
        }
    ]
}
```

## Postman Collection Format

If using Postman, here's example for each endpoint:

### Group Registration
```
Name: Register Group
Method: POST
URL: {{base_url}}/groups
Headers: Content-Type: application/json, X-CSRF-TOKEN: {{csrf}}
Body (JSON):
{
    "name": "Group Name",
    "description": "Description"
}
```

### Admin Approve
```
Name: Approve Group
Method: POST
URL: {{base_url}}/admin/groups/{{group_id}}/approve
Headers: Content-Type: application/json, X-CSRF-TOKEN: {{csrf}}
Body: {}
```

### Create Member
```
Name: Create Member Account
Method: POST
URL: {{base_url}}/groups/{{group_id}}/members/create
Headers: Content-Type: application/json, X-CSRF-TOKEN: {{csrf}}
Body (JSON):
{
    "name": "Member Name",
    "email": "member@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

## Validation Rules

### Group Registration
```
name: required, string, max 255, unique
description: optional, string, max 1000
```

### Create Member
```
name: required, string, max 255
email: required, email, max 255, unique
password: required, min 8, confirmed
```

### Add Existing Member
```
email: required, exists in users table
role: required, in [member, treasurer]
```

### Reject Group
```
rejection_reason: required, string, max 1000
```

## Debug Checklist

- [ ] Migrations ran: `php artisan migrate`
- [ ] System admin created with `is_admin = true`
- [ ] User can register group
- [ ] Group appears in admin dashboard
- [ ] Admin can approve/reject
- [ ] Group admin can create members
- [ ] Members see only own data
- [ ] Admin sees all data
- [ ] Role restrictions enforced
- [ ] Policies block unauthorized access

## Performance Tips

1. **Use eager loading** in controllers
2. **Index frequently queried columns** (group_id, user_id)
3. **Cache group approval status** if many checks
4. **Use select() to limit fields** in list endpoints
5. **Paginate results** for member/loan lists

## Common Errors & Solutions

### Error: 403 Unauthorized
- Check user is logged in
- Check user has access to group
- Check user has required role
- Check policy allows action

### Error: 404 Not Found
- Check resource exists
- Check group ID is correct
- Check resource belongs to group

### Error: 422 Validation Error
- Check all required fields present
- Check email is unique (for user creation)
- Check group name is unique
- Check passwords match

### Error: Group Shows Pending
- Admin must approve first
- Check admin dashboard for pending groups
- Click approve when ready

## Ready to Test! 🧪

Run the system through these test cases to verify:
1. Registration & approval workflow
2. Member creation & management
3. Data isolation
4. Role-based permissions
5. Policy enforcement
