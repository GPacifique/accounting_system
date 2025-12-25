# Group Member Role and Permission Management System

## Overview

This document describes the comprehensive group member role and permission management system implemented in the ItsindaMaster application. This system allows administrators to manage group membership, assign roles, and control member status and permissions.

## Features

### 1. Member Management
- **Add Members**: Assign existing users to groups with specific roles
- **Edit Member Roles**: Update member roles and status at any time
- **Remove Members**: Safely remove members from groups
- **Member Listing**: View all group members with their roles and status

### 2. Member Roles

Each group member can have one of three roles:

#### Admin (Group Administrator)
- Full control over group operations
- Can manage other members
- Can approve financial transactions
- Can modify group settings

#### Treasurer
- Manages financial records and transactions
- Handles loan and savings operations
- Records payments and withdrawals
- Generates financial reports

#### Member (Regular Member)
- Basic access to group features
- Can view own account information
- Can participate in group activities
- Can request loans within group limits

### 3. Member Status

Members can have one of three statuses:

#### Active
- Full access to group features
- Can participate in all group activities
- Can conduct transactions

#### Inactive
- Temporarily disabled
- Cannot access group features
- Cannot conduct transactions
- Can be reactivated later

#### Suspended
- Access revoked due to violation or policy
- Cannot access group features
- Cannot conduct transactions
- Requires administrative action to restore

## Controller Methods

Located in: `app/Http/Controllers/Admin/AdminDashboardController.php`

### groupMembers(Group $group): View
- Display all members of a specific group
- Show member details including role, status, and savings
- Paginated list (15 members per page)

### createGroupMember(Group $group): View
- Display form to add new member to group
- Show available users not already in the group
- Allow role and status selection

### storeGroupMember(Group $group)
- Validate and store new member in group
- Assign initial role and status
- Prevent duplicate membership
- Set joined_at timestamp

### editGroupMember(Group $group, GroupMember $member): View
- Display form to update member role and status
- Show member financial statistics
- Allow role and status modification

### updateGroupMember(Group $group, GroupMember $member)
- Update member role and status
- Validate group membership
- Redirect with success message

### deleteGroupMember(Group $group, GroupMember $member)
- Remove member from group permanently
- Soft delete for data integrity
- Redirect with success message

## Routes

All routes are nested under the admin prefix and require admin authentication.

```
Route Group: /admin/groups/{group}/members

GET     /                               admin.groups.members.index       (List members)
GET     /create                         admin.groups.members.create      (Show create form)
POST    /                               admin.groups.members.store       (Store new member)
GET     /{member}/edit                  admin.groups.members.edit        (Show edit form)
PUT     /{member}                       admin.groups.members.update      (Update member)
DELETE  /{member}                       admin.groups.members.destroy     (Delete member)
```

## Views

### resources/views/admin/groups/members/index.blade.php
- Display paginated list of group members
- Show member name, email, role, status, joined date, and savings
- Provide edit and remove actions for each member
- Add new member button

**Key Features:**
- Role badges with color coding
- Status indicators with color coding
- Member savings display
- Quick action buttons

### resources/views/admin/groups/members/create.blade.php
- Form to select user and assign role
- Role descriptions and guidance
- Status selection with explanations
- Validation error display

**Form Fields:**
- User selection (dropdown of available users)
- Role selection (member, treasurer, admin)
- Status selection (active, inactive, suspended)

### resources/views/admin/groups/members/edit.blade.php
- Edit member role and status
- Display member information
- Show member financial statistics
- Include member removal section

**Display Sections:**
- Member information card
- Role selection with descriptions
- Status selection with explanations
- Member statistics (savings, contributions, loans, balance)
- Danger zone for member removal

## Database Schema

### group_members table

```sql
CREATE TABLE group_members (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    group_id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,
    role VARCHAR(50) DEFAULT 'member', -- admin, treasurer, member
    status VARCHAR(50) DEFAULT 'active', -- active, inactive, suspended
    current_savings DECIMAL(15, 2) DEFAULT 0,
    total_contributed DECIMAL(15, 2) DEFAULT 0,
    total_withdrawn DECIMAL(15, 2) DEFAULT 0,
    total_borrowed DECIMAL(15, 2) DEFAULT 0,
    total_repaid DECIMAL(15, 2) DEFAULT 0,
    outstanding_loans DECIMAL(15, 2) DEFAULT 0,
    joined_at DATE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP,
    FOREIGN KEY (group_id) REFERENCES groups(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

## Model Methods

### GroupMember Model

#### Helper Methods for Role Checking:

```php
public function isAdmin(): bool           // Check if member is group admin
public function isTreasurer(): bool       // Check if member is treasurer
public function isMember(): bool          // Check if member is regular member
public function isActive(): bool          // Check if member is active
```

#### Financial Methods:

```php
public function getActiveLoanCount(): int           // Get count of active loans
public function getTotalOutstandingLoans(): float   // Get total unpaid loan balance
```

## Workflow Examples

### Adding a New Member to a Group

1. Navigate to **Admin Dashboard** → **Groups** → Select a Group
2. Click **"Manage Members"** button
3. Click **"+ Add Member"** button
4. Select user from dropdown (shows only non-members)
5. Choose role: Member, Treasurer, or Admin
6. Set initial status: Active, Inactive, or Suspended
7. Click **"Add Member to Group"**

### Changing a Member's Role

1. Go to **Groups** → Select a Group → **"Manage Members"**
2. Click **"Edit"** on the member you want to modify
3. Change role selection if needed
4. Change status if needed
5. Review member statistics
6. Click **"Update Member Role"**

### Removing a Member from a Group

1. Go to **Groups** → Select a Group → **"Manage Members"**
2. Click **"Remove"** button (inline action)
   OR
   Click **"Edit"** and scroll to "Danger Zone" section
3. Confirm removal when prompted
4. Member is soft-deleted from group

## Authorization & Security

### Admin Middleware
All member management routes require:
- User authentication (`auth` middleware)
- Email verification (`verified` middleware)
- Admin status (`AdminMiddleware` checks `is_admin` flag)

### Method-Level Authorization
Each controller method includes:
```php
if (!auth()->user()->is_admin) {
    abort(403, 'Unauthorized access');
}
```

### Data Integrity Checks
- Prevent adding user already in group
- Validate role values against allowed roles
- Validate status values against allowed statuses
- Verify member belongs to group before editing/deleting

## Validation Rules

### Adding Member
```php
'user_id' => 'required|exists:users,id',
'role' => 'required|in:admin,treasurer,member',
'status' => 'required|in:active,inactive,suspended',
```

### Updating Member
```php
'role' => 'required|in:admin,treasurer,member',
'status' => 'required|in:active,inactive,suspended',
```

## User Interface Elements

### Member Table Columns
1. **Member Name**: User's full name
2. **Email**: User's email address
3. **Role**: Visual badge indicating role (color-coded)
4. **Status**: Visual indicator of member status (color-coded)
5. **Joined**: Date member joined the group
6. **Savings**: Current savings balance
7. **Actions**: Edit and Remove buttons

### Color Coding

**Roles:**
- Purple badge: Admin
- Blue badge: Treasurer
- Gray badge: Member

**Status:**
- Green badge: Active
- Yellow badge: Inactive
- Red badge: Suspended

## Integration with Other Systems

### Group Management
- Member management accessible from group detail page
- "Manage Members" button on group view
- Member count displayed on group cards

### Financial System
- Member savings tracked in group_members table
- Loan associations through member_id foreign key
- Transaction associations for member accounting

### Role-Based Access Control
- Member roles affect what features they can access
- Treasurer role provides access to financial operations
- Admin role provides group management capabilities

## Future Enhancements

Potential improvements for the member management system:

1. **Permission Matrix**
   - Granular permissions per role
   - Custom role creation
   - Permission assignment per member

2. **Audit Logging**
   - Track role changes
   - Log member additions/removals
   - Maintain change history

3. **Bulk Operations**
   - Add multiple members at once
   - Bulk role changes
   - Bulk status updates

4. **Member Groups**
   - Create sub-groups within groups
   - Hierarchical member management
   - Department-based organization

5. **Notifications**
   - Notify members when added to group
   - Alert admins of role changes
   - Suspension notifications

6. **Restrictions**
   - Set member permission expiry dates
   - Conditional role assignments
   - Activity-based role updates

## Testing

To test the member management system:

```bash
# List all routes
php artisan route:list | grep members

# Run feature tests
php artisan test --filter=GroupMember

# Test authorization
# Try accessing as non-admin user - should get 403 error
```

## Troubleshooting

### Routes Not Found
```bash
php artisan route:clear
php artisan route:cache
```

### Member Not Found Error
- Verify member belongs to correct group
- Check member hasn't been soft-deleted
- Verify group ID in URL matches group ID in database

### Cannot Add Member
- Check if user already in group
- Verify user exists in system
- Confirm admin authentication

### Update Not Working
- Clear route cache
- Verify form uses PUT method
- Check validation rules
- Verify CSRF token in form

## Related Documentation

- [Admin Dashboard Guide](./ADMIN_DASHBOARD_GUIDE.md)
- [Group Management Guide](./GROUP_ADMIN_MANAGEMENT.md)
- [Roles and Permissions Implementation](./ROLES_PERMISSIONS_GUIDE.md)
- [Three-Tier Dashboard Implementation](./THREE_TIER_DASHBOARD_IMPLEMENTATION.md)
