# Member Dashboard - Quick Reference

## Access the Dashboard
**URL:** `/member/dashboard` or `/dashboard` (if logged in as member)

---

## Statistics Displayed

### Top Overview Cards (5 Metrics)
```
┌─────────────┬──────────────┬────────────────┬─────────────┬────────────┐
│   GROUPS    │ TOTAL LOANS  │ SAVINGS ACCTS  │ DEBT OWED   │ NET WORTH  │
├─────────────┼──────────────┼────────────────┼─────────────┼────────────┤
│      2      │      3       │       2        │  6,500.00   │ 6,250.00   │
│   Active    │   Accounts   │  Accounts      │  Due        │ Savings-   │
│  Groups     │   Total      │  Active        │             │ Debt       │
└─────────────┴──────────────┴────────────────┴─────────────┴────────────┘
```

---

## Sections & What They Show

### 1. My Groups
Shows all active groups you're a member of
- Group name
- Member count
- Join date

### 2. My Loans
Lists all your loans with details
- Group name
- Loan status (Active/Completed/Overdue)
- Amount borrowed
- Amount paid + remaining
- Dates (issued & due)

**Summary Cards:**
```
Total Loaned │ Paid    │ Outstanding │ Active │ Completed │ Overdue
10,000.00    │ 3,500   │ 6,500.00    │ 1      │ 2         │ 0
```

### 3. My Savings
Lists all your savings accounts
- Group name
- Weekly deposit amount
- Total saved (accumulated)
- Account opened date

**Summary Cards:**
```
Total Balance │ Total Saved │ Interest   │ Weekly Deposits │ Withdrawals
12,750.00     │ 14,250.00   │ 750.00     │ 2,500.00        │ 1,500.00
```

### 4. Recent Transactions
Table of your last 10 transactions
- Date
- Type (Deposit, Withdrawal, Loan Payment, Interest)
- Group
- Amount

### 5. Right Sidebar - Balance Summary
Complete financial snapshot
- Total Savings (+ account count)
- Weekly Deposits (+ per week)
- Total Loaned (+ loan count)
- Outstanding Debt (+ active loans)
- **Net Worth** (highlighted)
- Interest Earned

---

## Data Sources

### Loans Data
```
Source: loans table
Filtered by: Your group memberships
Calculations:
- Total Loaned = SUM(principal_amount)
- Total Paid = SUM(total_principal_paid)
- Outstanding = Total - Paid
- Active/Completed/Overdue = COUNT by status
```

### Savings Data
```
Source: savings table
Filtered by: Your group memberships
Calculations:
- Balance = total_deposits - total_withdrawals + interest_earned
- Weekly Deposits = current_balance per account
- Interest = SUM(interest_earned)
```

### Transactions Data
```
Source: transactions table
Filtered by: Your group memberships
Sorted by: Most recent first (descending)
Limit: 10 per page
```

---

## What Each Number Means

| Number | Meaning | Formula |
|--------|---------|---------|
| Total Loaned | Total borrowed across all loans | SUM of all loan amounts |
| Total Paid | Amount repaid on loans | SUM of payments made |
| Outstanding | Still owe on loans | Total Loaned - Total Paid |
| Total Savings | Current balance in accounts | Deposits - Withdrawals + Interest |
| Weekly Deposits | Total weekly amount depositing | SUM of current_balance per account |
| Total Accumulated | Total deposits over time | SUM of all deposits |
| Interest Earned | Interest gained | SUM of interest_earned |
| Net Worth | Financial position | Total Savings - Outstanding Debt |

---

## Example Dashboard

### User: Sarah Johnson

**Overview:**
- Groups: 2
- Total Loans: 3
- Savings Accounts: 2
- Outstanding Debt: 6,500.00
- Net Worth: 6,250.00

**Groups:**
1. Finance Circle (12 members) - Joined Nov 15, 2024
2. Savings Group (8 members) - Joined Dec 1, 2024

**Loans:**
1. Loan #1 - Finance Circle
   - Status: Active ✓
   - Amount: 5,000.00
   - Paid: 2,000.00
   - Remaining: 3,000.00
   - Due: Jan 15, 2025

2. Loan #2 - Savings Group
   - Status: Completed ✓
   - Amount: 3,500.00
   - Paid: 3,500.00
   - Remaining: 0.00
   - Due: Dec 10, 2024

3. Loan #3 - Finance Circle
   - Status: Active ✓
   - Amount: 4,500.00
   - Paid: 1,500.00
   - Remaining: 3,000.00
   - Due: Feb 28, 2025

**Loan Summary:**
- Total Loaned: 13,000.00
- Total Paid: 7,000.00
- Outstanding: 6,000.00
- Active: 2 | Completed: 1 | Overdue: 0

**Savings:**
1. Finance Circle Savings
   - Weekly Deposit: 1,500.00
   - Total Saved: 7,500.00
   - Interest: 450.00
   - Opened: Nov 15, 2024

2. Savings Group Account
   - Weekly Deposit: 1,000.00
   - Total Saved: 5,250.00
   - Interest: 300.00
   - Opened: Dec 1, 2024

**Savings Summary:**
- Total Balance: 12,750.00
- Total Saved: 12,750.00
- Weekly Deposits: 2,500.00
- Withdrawals: 0.00
- Interest: 750.00

**Right Sidebar:**
```
┌─────────────────────────────────┐
│  Total Savings: 12,750.00       │
│  (2 accounts)                   │
│                                 │
│  Weekly Deposits: 2,500.00      │
│  (Per week total)               │
│                                 │
│  Total Loaned: 13,000.00        │
│  (3 loans)                      │
│                                 │
│  Outstanding Debt: 6,000.00     │
│  (2 active loans)               │
│                                 │
│  ║ NET WORTH: 6,750.00 ║        │
│  ║ (Savings - Debt)   ║        │
│                                 │
│  Interest Earned: 750.00        │
│  (Total interest)               │
└─────────────────────────────────┘
```

---

## Navigation Links

From Member Dashboard you can:
- **View All Loans** → `/member/loans`
- **View All Savings** → `/member/savings`
- **View Transactions** → `/member/transactions`
- **View Groups** → `/member/groups`
- **Edit Profile** → `/profile/edit`

---

## Can I Edit These Numbers?

**NO** - As a member, you can:
- ✅ View all your data
- ❌ Cannot edit loans, savings, or transactions
- ❌ Cannot delete records

To make changes, contact your Group Admin

---

## Database Tables Used

```
groups
├─ id, name, status, members_count
├─ Relationship: pivot table group_user

loans
├─ id, member_id, group_id, principal_amount, total_principal_paid, status
├─ Relationship: belongs to group_members

savings
├─ id, member_id, group_id, current_balance, total_deposits, total_withdrawals, interest_earned
├─ Relationship: belongs to group_members

transactions
├─ id, member_id, group_id, type, amount, description
├─ Relationship: belongs to groups

group_members
├─ id, user_id, group_id, role, status
├─ Relationship: pivot between users and groups
```

---

## Performance Notes

✅ All data loaded efficiently  
✅ Eager loading prevents N+1 queries  
✅ Summary calculations done in database  
✅ Pagination on large datasets  
✅ No unnecessary data transfers  

---

## Refresh Your Dashboard

The dashboard displays current data from the database. Data updates:
- When deposits/withdrawals are made
- When loan payments are processed
- When interest is accrued
- When new accounts are created

**Refresh your browser to see latest updates**

---

**Version:** 1.0  
**Date:** December 25, 2025  
**Status:** Active
