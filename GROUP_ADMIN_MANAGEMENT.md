# Group Admin Dashboard - Member, Loan & Savings Management with Deadline Tracking

## Overview

The Group Admin Dashboard provides complete management capabilities for group admins to:
- Manage group members and their roles
- Track loans with deadline monitoring
- Monitor savings accounts
- Track deadlines and overdue loans

## Dashboard Features

### 1. **Statistics Overview**

#### Key Metrics Displayed:
- **Total Members**: Active group members count
- **Active Loans**: Number of active loans
- **Total Loaned**: Sum of all principal amounts
- **Savings Balance**: Total savings across all members
- **Overdue Loans**: Count of loans past maturity date (highlighted in red when > 0)

### 2. **Deadline Management**

#### Upcoming Loan Deadlines (Next 30 Days)
```
Table shows:
- Member name who borrowed
- Loan amount (principal)
- Maturity date
- Days remaining (color-coded)
- Status indicator
```

**Color Coding:**
- 🟢 Green (7+ days): Healthy
- 🟡 Yellow (1-7 days): Caution - approaching deadline
- 🔴 Red (Overdue): Past maturity date

#### Overdue Loans Alert
When a loan passes its maturity date:
- Alert banner appears at top
- Loan is highlighted in red
- Shows days overdue and outstanding balance
- Takes priority in member status display

### 3. **Members with Active Loans**

Comprehensive member management table showing:

| Column | Description |
|--------|-------------|
| Name | Member's full name |
| Role | Member's role in group (admin, member, treasurer, secretary) |
| Active Loans | Number of currently active loans |
| Loan Amount | Total principal of active loans |
| Savings | Total savings balance |
| Next Deadline | Next loan maturity date with days remaining |

**Row Highlighting:**
- Members with overdue loans highlighted in red
- Quick visual identification of problem areas

### 4. **Member Status Indicators**

For each member, the dashboard shows:

```php
If Member has Overdue Loans:
  ⚠️ Overdue (red badge)

If Member has Upcoming Deadline:
  - Date (color-coded by urgency)
  - Days remaining in parentheses
  - Yellow if ≤7 days, Green if >7 days

If No Active Loans:
  "No active loans"
```

### 5. **Quick Actions Sidebar**

Easy access to key operations:
- 📊 View All Loans
- 💾 View All Savings
- 👥 Manage Members
- 📈 View Reports

### 6. **Recent Savings Activity**

Shows latest savings updates:
- Member name
- Current balance
- Time since last update (relative time)

## Data Relationships

### Member → Loans → Deadlines

```
Group
  ├── GroupMembers
  │   ├── User (basic info)
  │   ├── Loans (assigned to member)
  │   │   ├── principal_amount
  │   │   ├── maturity_date (deadline)
  │   │   ├── remaining_balance
  │   │   ├── status (active/completed)
  │   │   └── charges (pending/overdue)
  │   └── Savings
  │       └── balance
  └── ...
```

### Deadline Tracking Flow

1. **Loan Created** → assigned `maturity_date` (issued_at + duration_months)
2. **Dashboard Check** → compares maturity_date with current date
3. **30 Days Before** → appears in "Upcoming Deadlines"
4. **Day Before** → yellow highlight (caution)
5. **Maturity Date Passed** → red highlight (overdue)
6. **Member View** → shows next deadline or overdue status

## Controller Logic

### GroupAdminDashboardController::index()

```php
// Retrieves:
$stats = [
    'total_members' => active member count,
    'active_loans' => count of status='active' loans,
    'total_loans' => all loans count,
    'total_loan_amount' => sum of principal_amount,
    'total_savings_balance' => sum of savings balance,
    'overdue_loans' => count of loans with maturity_date < now(),
    'pending_charges' => sum of pending loan charges,
];

// Gets upcoming deadlines (next 30 days)
$upcoming_loans = Loan::where('maturity_date', '>', now())
    ->where('maturity_date', '<=', now()->addDays(30))
    ->get();

// Gets overdue loans
$overdue_loans = Loan::where('maturity_date', '<', now())
    ->get();

// Maps member details with deadline info
$member_details = members.map(member => {
    upcoming_deadline: member's next loan deadline,
    has_overdue: boolean if any overdue loans exist
});
```

## View Sections

### Main Content Area (2/3 width)

1. **Overdue Alert**: Appears only if overdue loans exist
2. **Upcoming Deadlines Table**: Loans due within 30 days
3. **Overdue Loans Table**: Loans past maturity date (if any)
4. **Members Table**: All members with deadline indicators

### Sidebar (1/3 width)

1. **Quick Actions**: Navigation shortcuts
2. **Recent Savings**: Latest savings activity
3. **Group Info**: Group details and status

## Visual Design

### Color Scheme
- 🔵 Blue: Members, information
- 🟢 Green: Active, healthy, savings
- 🟡 Yellow: Caution, approaching deadline
- 🔴 Red: Overdue, urgent action needed
- ⚫ Gray: Neutral, completed

### Typography Hierarchy
- H1 (4xl): Page title
- H2 (xl): Section headers
- H3 (lg): Subsection headers
- Body: Table and list content
- Caption: Meta information (dates, counts)

### Icons Used
- 📅 Upcoming deadlines
- 👥 Members management
- ⚠️ Alerts and warnings
- 💾 Savings
- 📊 Loans and reports
- 📈 Analytics

## Key Features in Action

### Scenario 1: Member Has Overdue Loan
```
Member: John Doe
- Row highlighted in red
- "⚠️ Overdue" badge in deadline column
- Overdue loan shown in separate red table above
- Days overdue displayed
- Outstanding balance shown
```

### Scenario 2: Deadline Approaching (5 Days)
```
Member: Jane Smith
- Row normal background
- "Mar 29 (5d)" with yellow badge in deadline column
- Appears in upcoming deadlines table with yellow "5 days" badge
- Amount and status shown
```

### Scenario 3: No Active Loans
```
Member: Bob Wilson
- Row normal background
- "No active loans" in deadline column
- Not shown in upcoming/overdue tables
```

## Dashboard Workflow

1. **Admin Logs In** → Redirected to group-admin dashboard
2. **Views Statistics** → Sees quick overview at top
3. **Checks Alerts** → If overdue loans exist, red alert visible
4. **Reviews Deadlines** → Upcoming loans table shows next 30 days
5. **Monitors Members** → Member table shows status at a glance
6. **Takes Action** → Clicks quick actions or member name to drill down

## Route References

```
Dashboard:        route('group-admin.dashboard', $group)
View All Loans:   route('group-admin.loans', $group)
View All Savings: route('group-admin.savings', $group)
Manage Members:   route('group-admin.members', $group)
View Reports:     route('group-admin.reports', $group)
```

## Database Queries Optimized

- All member data loaded with relationships (user, loans, savings)
- Loans include member and charge information
- Dates calculated in PHP for flexibility
- Pagination available on full list views

## Future Enhancements

- [ ] Export deadline report to CSV/PDF
- [ ] Email notifications for approaching deadlines
- [ ] Loan payment tracking and receipts
- [ ] Custom deadline alerts
- [ ] Member performance analytics
- [ ] Charge payment scheduling
- [ ] Savings withdrawal approval workflow
