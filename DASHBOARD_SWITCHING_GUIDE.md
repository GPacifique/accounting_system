# Dashboard Switching & Logout Guide

## Overview
Enhanced the admin dashboard to allow users to switch between different dashboard views and logout easily. This feature supports the three-tier dashboard system: System Admin, Group Admin, and Member dashboards.

## Features Added

### 1. Dashboard Switcher Section
**Location:** Admin dashboard (`/admin/dashboard`)

A prominent card section displays available dashboards:
- **System Admin Dashboard** - For system administrators to manage users, groups, loans, savings, and transactions
- **Group Admin Dashboard** - Available only if the user is an admin of at least one group
- **Member Dashboard** - Available only if the user is a member of at least one group

Disabled/grayed-out dashboards show explanatory text for why they're not available.

### 2. User Menu Dropdown
**Location:** Top-right corner of admin layout (navigation bar)

Enhanced the existing user menu dropdown with:
- **Switch Dashboard** section heading
- Conditional links to other dashboards based on user roles
- Additional options:
  - Settings
  - Profile
  - Logout (red/destructive styling)

**Access:** Click on username in top-right corner of any admin page

### 3. Smart Dashboard Visibility
- **System Admin** link always appears (for system admins)
- **Group Admin** link only shows if user is an admin of any group
- **Member** link only shows if user is a member of any group
- Disabled states show why access is not available

## Implementation Details

### Files Modified

#### 1. `resources/views/layouts/admin.blade.php`
- Updated user menu dropdown structure
- Added dashboard switcher section in dropdown
- Added conditional rendering based on user roles
- Enhanced styling with color-coded links

#### 2. `resources/views/admin/dashboard.blade.php`
- Added "Switch Dashboard" section below welcome banner
- Grid layout with 3 dashboard options (responsive)
- Color-coded cards (indigo, green, purple)
- Shows descriptions for each dashboard
- Disabled state for unavailable dashboards

#### 3. `app/Models/User.php`
- Added `groupAdminGroups()` method - Returns groups where user is an admin
- Added `isMemberOfGroup()` method - Check if user is a member of any group
- Added `isGroupAdminOfAny()` method - Check if user is an admin of any group

### New User Model Methods

```php
// Get groups where user is an admin
$groups = auth()->user()->groupAdminGroups();

// Check if user is member of any group
if (auth()->user()->isMemberOfGroup()) {
    // Show member dashboard option
}

// Check if user is admin of any group
if (auth()->user()->isGroupAdminOfAny()) {
    // Show group admin dashboard option
}
```

## User Experience

### Dashboard Switching Flow

1. **From Admin Dashboard:**
   - See prominent "Switch Dashboard" card
   - Click on desired dashboard option
   - Redirected to that dashboard

2. **From Any Page:**
   - Click username in top-right corner
   - Select desired dashboard from dropdown
   - Logout option visible with red styling

### Logout Options

**Option 1 - From Dashboard Switcher Card:**
- Not directly available but visible in menu

**Option 2 - From User Menu Dropdown:**
1. Click username in top-right corner
2. Click "Logout" (red button at bottom)
3. Logged out and redirected to login page

**Option 3 - Profile/Settings:**
- Can also logout from profile page if needed

## Visual Design

### Color Scheme
- **System Admin:** Indigo (blue) - `from-indigo-50 to-indigo-100`
- **Group Admin:** Green - `from-green-50 to-green-100`
- **Member:** Purple - `from-purple-50 to-purple-100`
- **Disabled/Unavailable:** Gray - `from-gray-50 to-gray-100`

### Styling
- Gradient backgrounds for active options
- Border color matches role theme
- Hover effects with darker borders and shadows
- 50% opacity for disabled states

## Responsive Design
- Desktop: 3-column grid for dashboard switcher
- Tablet: 2-column layout
- Mobile: 1-column stacked layout

## Security Considerations

1. **Role-Based Access:**
   - Links only appear if user has appropriate role
   - Backend routing middleware still validates access
   - No unauthorized access possible

2. **Logout Security:**
   - Uses POST form with CSRF token
   - Proper session termination
   - Secure logout implementation

3. **User Menu:**
   - Only shows options appropriate for current user
   - Conditional rendering prevents information leakage

## Testing

### Test Cases

1. **System Admin User:**
   - See all three dashboard options in switcher
   - Can access all dashboards
   - User menu shows all dashboard options

2. **Group Admin User:**
   - See System Admin and Group Admin options (active)
   - See Member option if also a member
   - Disabled Group Admin if not admin of any group

3. **Regular Member:**
   - See System Admin (disabled) if not a system admin
   - See Group Admin (disabled) if not a group admin
   - See Member option (active)

4. **New User:**
   - Only System Admin option available (if admin)
   - Other options disabled with explanatory text

### Manual Testing Steps

1. Login as system admin
2. Verify "Switch Dashboard" card appears
3. Click each dashboard option
4. Click username to verify dropdown
5. Test logout functionality
6. Login as group admin
7. Verify appropriate options appear
8. Login as member only
9. Verify member option is active

## Future Enhancements

- Dashboard shortcuts/recent activity
- Save last visited dashboard preference
- Quick navigation breadcrumbs
- Dashboard-specific quick actions
- User activity log showing dashboard access
- SSO integration for switching dashboards

## Troubleshooting

### Dashboard Switcher Not Appearing
- Check if user has roles (group admin or member of group)
- Verify methods in User model are defined
- Clear application cache: `php artisan cache:clear`

### User Menu Dropdown Not Working
- Check CSS is loading properly (Tailwind)
- Verify JavaScript is enabled in browser
- Check browser console for errors

### Logout Not Working
- Clear browser cookies
- Verify session configuration
- Check CSRF token is being sent
- Review Laravel logs for errors

## File Locations

| File | Changes |
|------|---------|
| `resources/views/layouts/admin.blade.php` | User menu dropdown with dashboard switcher |
| `resources/views/admin/dashboard.blade.php` | Dashboard switcher card section |
| `app/Models/User.php` | Helper methods for role checking |

## Caching Notes

After deployment, run:
```bash
php artisan config:cache
php artisan view:clear
php artisan cache:clear
```

This ensures the updated User model methods and views are loaded.
