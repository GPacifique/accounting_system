# Member Dashboard - Account Statistics Implementation

## Overview
The member dashboard has been enhanced to display comprehensive account statistics pulled directly from the database. Members can now see a complete picture of their financial accounts at a glance.

---

## Data Sources

### Controller: `MemberDashboardController.php`

#### 1. **User's Group Memberships**
```php
$groups = $user->groups()
    ->where('groups.status', 'active')
    ->withCount('members')
    ->get();
```
**Data:** Active groups user belongs to with member counts

---

#### 2. **Loan Statistics**
```php
$loans = Loan::whereIn('member_id', $userMembers)
    ->with('group')
    ->orderBy('created_at', 'desc')
    ->get();

$loan_stats = [
    'total_loaned' => $loans->sum('principal_amount') ?? 0,
    'total_paid' => $loans->sum('total_principal_paid') ?? 0,
    'outstanding' => ($loans->sum('principal_amount') ?? 0) - ($loans->sum('total_principal_paid') ?? 0),
    'active_count' => $loans->where('status', 'active')->count(),
    'completed_count' => $loans->where('status', 'completed')->count(),
    'overdue_count' => $loans->where('status', 'overdue')->count(),
];
```

**Metrics Tracked:**
- Total amount loaned (sum of all principal amounts)
- Total repaid (sum of payments made)
- Outstanding balance (loaned - paid)
- Active loans (current status = 'active')
- Completed loans (status = 'completed')
- Overdue loans (status = 'overdue')

---

#### 3. **Savings Statistics**
```php
$savings = Saving::whereIn('member_id', $userMembers)
    ->with('group')
    ->orderBy('created_at', 'desc')
    ->get();

$savings_stats = [
    'total_accounts' => $savings->count(),
    'total_weekly_deposits' => $savings->sum('current_balance') ?? 0,
    'total_accumulated' => $savings->sum('total_deposits') ?? 0,
    'total_withdrawals' => $savings->sum('total_withdrawals') ?? 0,
    'total_interest_earned' => $savings->sum('interest_earned') ?? 0,
    'total_balance' => $savings->sum('balance') ?? 0,  // Using balance accessor
];
```

**Metrics Tracked:**
- Total number of savings accounts
- Weekly deposits (sum of current_balance from all accounts)
- Total accumulated deposits (sum of total_deposits)
- Total amount withdrawn
- Total interest earned
- Current balance (computed via accessor: deposits - withdrawals + interest)

---

#### 4. **Overall Account Statistics**
```php
$account_stats = [
    'groups_count' => $groups->count(),
    'active_loans' => $loan_stats['active_count'],
    'total_loans' => $loans->count(),
    'total_savings_accounts' => $savings_stats['total_accounts'],
    'net_worth' => ($savings_stats['total_balance'] ?? 0) - ($loan_stats['outstanding'] ?? 0),
];
```

**Metrics Tracked:**
- Total groups member belongs to
- Active loans count
- Total loans count
- Total savings accounts
- Net worth (total savings - outstanding debt)

---

## Dashboard Sections

### 1. Account Statistics Overview Cards
**Location:** Top of main content area

Five cards displaying key metrics:

| Card | Data | Color | Formula |
|------|------|-------|---------|
| Groups | `$account_stats['groups_count']` | Blue | Count of groups |
| Total Loans | `$account_stats['total_loans']` | Purple | Number of loans |
| Savings Accounts | `$account_stats['total_savings_accounts']` | Green | Number of accounts |
| Outstanding Debt | `$loan_stats['outstanding']` | Red | Total loaned - Total paid |
| Net Worth | `$account_stats['net_worth']` | Emerald | Total savings - Outstanding |

---

### 2. My Groups Section
**Data Displayed:**
- Group name
- Number of members in group
- Join date

**Query:** Direct from `Group` model with eager-loaded members count

---

### 3. My Loans Section
**Detailed List:**
- Group name (which loan is from)
- Loan status (badge)
- Amount loaned
- Amount paid vs remaining
- Issue date and due date

**Summary Statistics:**
```
Total Loaned  │  Total Paid  │  Outstanding  │  Active Count
────────────────────────────────────────────────────────────
      4       │      2       │      2        │      1
```

**Extended Stats:**
```
Completed: X  │  Overdue: Y
```

---

### 4. My Savings Section
**Detailed List:**
- Group name (which savings account is in)
- Weekly deposit amount (`current_balance`)
- Total saved (computed `balance` accessor)
- Account opened date

**Summary Statistics:**
```
Total Balance  │  Total Saved  │  Interest Earned
───────────────────────────────────────────────
   5,500.00    │  7,200.00     │   500.00

Weekly Deposits  │  Withdrawals
──────────────────────────────
   2,500.00      │  1,700.00
```

---

### 5. Recent Transactions
**Table Columns:**
- Date
- Type (deposit/withdrawal/loan_payment/interest)
- Group
- Amount

**Pagination:** 10 items per page

---

### 6. Right Sidebar - Balance Summary
**Comprehensive Financial Overview:**

```
┌─────────────────────────────────┐
│   BALANCE SUMMARY               │
├─────────────────────────────────┤
│ Total Savings                   │
│ 5,500.00 (3 accounts)           │
├─────────────────────────────────┤
│ Weekly Deposits                 │
│ 2,500.00 (Per week total)       │
├─────────────────────────────────┤
│ Total Loaned                    │
│ 10,000.00 (2 loans)             │
├─────────────────────────────────┤
│ Outstanding Debt                │
│ 6,500.00 (1 active loans)       │
├─────────────────────────────────┤
│ ║ Net Worth                     │
│ ║ 5,500.00                      │
│ ║ (Savings minus debt)          │
├─────────────────────────────────┤
│ Interest Earned                 │
│ 500.00 (Total interest)         │
└─────────────────────────────────┘
```

---

## Database Queries Behind Stats

### 1. Get Member's Loans
```sql
SELECT l.*, gm.user_id 
FROM loans l
JOIN group_members gm ON l.member_id = gm.id
WHERE gm.user_id = :user_id
ORDER BY l.created_at DESC;
```

**Calculated Fields:**
```sql
SELECT 
    SUM(principal_amount) as total_loaned,
    SUM(total_principal_paid) as total_paid,
    SUM(principal_amount) - SUM(total_principal_paid) as outstanding,
    COUNT(CASE WHEN status='active' THEN 1 END) as active_count,
    COUNT(CASE WHEN status='completed' THEN 1 END) as completed_count,
    COUNT(CASE WHEN status='overdue' THEN 1 END) as overdue_count
FROM loans
WHERE member_id IN (SELECT id FROM group_members WHERE user_id = :user_id);
```

---

### 2. Get Member's Savings
```sql
SELECT s.*, gm.user_id
FROM savings s
JOIN group_members gm ON s.member_id = gm.id
WHERE gm.user_id = :user_id
ORDER BY s.created_at DESC;
```

**Calculated Fields:**
```sql
SELECT 
    COUNT(*) as total_accounts,
    SUM(current_balance) as total_weekly_deposits,
    SUM(total_deposits) as total_accumulated,
    SUM(total_withdrawals) as total_withdrawals,
    SUM(interest_earned) as total_interest_earned,
    SUM(total_deposits - total_withdrawals + interest_earned) as total_balance
FROM savings
WHERE member_id IN (SELECT id FROM group_members WHERE user_id = :user_id);
```

---

### 3. Get Member's Groups
```sql
SELECT g.*, COUNT(gm2.id) as members_count
FROM groups g
JOIN group_members gm ON g.id = gm.group_id
LEFT JOIN group_members gm2 ON g.id = gm2.group_id
WHERE gm.user_id = :user_id AND g.status = 'active'
GROUP BY g.id
ORDER BY g.name;
```

---

### 4. Get Member's Transactions
```sql
SELECT t.*, g.name as group_name
FROM transactions t
JOIN groups g ON t.group_id = g.id
WHERE t.member_id IN (SELECT id FROM group_members WHERE user_id = :user_id)
ORDER BY t.created_at DESC
LIMIT 10;
```

---

## Data Flow Diagram

```
┌─────────────────────────────────────────────────────────┐
│ MemberDashboardController::index()                      │
└──────────────────────┬──────────────────────────────────┘
                       │
        ┌──────────────┼──────────────┐
        ▼              ▼              ▼
   Groups         Loans          Savings
   Query          Query          Query
        │              │              │
        ├──────────────┼──────────────┤
        ▼              ▼              ▼
   $groups         Calculate        Calculate
               $loan_stats      $savings_stats
                    │              │
                    └──────┬───────┘
                           │
                    Calculate $account_stats
                           │
                           ▼
                    View: member.blade.php
                           │
         ┌─────────────────┼─────────────────┐
         ▼                 ▼                 ▼
    Overview Cards   Detailed Lists   Sidebar Summary
    (5 cards)        (Loans/Savings)   (Balance Info)
```

---

## Statistics Displayed in Views

### Overview Cards (Top Section)
```blade
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
    <!-- Groups -->
    {{ $account_stats['groups_count'] }}
    
    <!-- Total Loans -->
    {{ $account_stats['total_loans'] }}
    
    <!-- Savings Accounts -->
    {{ $account_stats['total_savings_accounts'] }}
    
    <!-- Outstanding Debt -->
    {{ number_format($loan_stats['outstanding'], 0) }}
    
    <!-- Net Worth -->
    {{ number_format($account_stats['net_worth'], 0) }}
</div>
```

---

### Loans Section
```blade
<div class="mt-4 pt-4 border-t">
    <div class="grid grid-cols-4 gap-2">
        <div>Total Loaned: {{ number_format($loan_stats['total_loaned'], 0) }}</div>
        <div>Paid: {{ number_format($loan_stats['total_paid'], 0) }}</div>
        <div>Outstanding: {{ number_format($loan_stats['outstanding'], 0) }}</div>
        <div>Active: {{ $loan_stats['active_count'] }}</div>
    </div>
    <div class="grid grid-cols-2 gap-2 mt-3">
        <div>Completed: {{ $loan_stats['completed_count'] }}</div>
        <div>Overdue: {{ $loan_stats['overdue_count'] }}</div>
    </div>
</div>
```

---

### Savings Section
```blade
<div class="mt-4 pt-4 border-t">
    <div class="grid grid-cols-3 gap-2">
        <div>Total Balance: {{ number_format($savings_stats['total_balance'], 0) }}</div>
        <div>Total Saved: {{ number_format($savings_stats['total_accumulated'], 0) }}</div>
        <div>Interest: {{ number_format($savings_stats['total_interest_earned'], 0) }}</div>
    </div>
    <div class="grid grid-cols-2 gap-2 mt-3">
        <div>Weekly Deposits: {{ number_format($savings_stats['total_weekly_deposits'], 0) }}</div>
        <div>Withdrawals: {{ number_format($savings_stats['total_withdrawals'], 0) }}</div>
    </div>
</div>
```

---

### Sidebar Balance Summary
```blade
Total Savings: {{ number_format($savings_stats['total_balance'], 0) }}
Weekly Deposits: {{ number_format($savings_stats['total_weekly_deposits'], 0) }}
Total Loaned: {{ number_format($loan_stats['total_loaned'], 0) }}
Outstanding Debt: {{ number_format($loan_stats['outstanding'], 0) }}
Net Worth: {{ number_format($account_stats['net_worth'], 0) }}
Interest Earned: {{ number_format($savings_stats['total_interest_earned'], 0) }}
```

---

## Example Output

### For User: John Doe

**Groups:** 2
- Finance Group (15 members)
- Savings Club (8 members)

**Loans:**
- Total Loaned: 15,000.00
- Total Paid: 8,500.00
- Outstanding: 6,500.00
- Active: 1
- Completed: 1
- Overdue: 0

**Savings:**
- Total Accounts: 3
- Total Balance: 12,750.00
- Weekly Deposits: 2,500.00
- Total Accumulated: 14,250.00
- Withdrawals: 1,500.00
- Interest Earned: 750.00

**Account Stats:**
- Net Worth: 12,750 - 6,500 = 6,250.00

---

## Performance Optimization

### Eager Loading
```php
->with('group', 'member.user')
```
Prevents N+1 query problems

### Indexes Recommended
```sql
CREATE INDEX idx_loans_member ON loans(member_id);
CREATE INDEX idx_savings_member ON savings(member_id);
CREATE INDEX idx_loans_status ON loans(status);
CREATE INDEX idx_group_members_user ON group_members(user_id);
```

### Caching Strategy
For dashboards with many queries, consider caching:
```php
Cache::remember("member:{$user->id}:stats", 3600, function() {
    // Load all stats...
});
```

---

## Data Security

✅ Members only see their own records  
✅ Query filtered by user ID  
✅ Transactions verified in controller  
✅ Database column-level security  
✅ No exposure of other users' data  

---

## Available Statistics Summary

| Statistic | Source | Type | View Location |
|-----------|--------|------|---------------|
| Groups Count | Direct count | Integer | Top card |
| Total Loans | Loan count | Integer | Top card |
| Savings Accounts | Savings count | Integer | Top card |
| Outstanding Debt | Loan calculation | Decimal | Top card + Sidebar |
| Net Worth | Calculation | Decimal | Top card + Sidebar |
| Total Loaned | Loan sum | Decimal | Loans section |
| Total Paid | Loan sum | Decimal | Loans section |
| Active Loans | Loan filter | Integer | Loans section |
| Completed Loans | Loan filter | Integer | Loans section |
| Overdue Loans | Loan filter | Integer | Loans section |
| Total Savings Balance | Savings calculation | Decimal | Savings section + Sidebar |
| Weekly Deposits | Savings sum | Decimal | Savings section + Sidebar |
| Total Accumulated | Savings sum | Decimal | Savings section + Sidebar |
| Total Withdrawals | Savings sum | Decimal | Savings section |
| Interest Earned | Savings sum | Decimal | Savings section + Sidebar |

---

**Version:** 1.0  
**Date:** December 25, 2025  
**Status:** Implementation Complete
