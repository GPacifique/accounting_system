# Weekly Deposit Amount - Clarification

## Overview
The **"Current Balance"** field in the savings system has been updated to represent the **weekly deposit amount** that each member contributes to the group savings.

## What Changed

### Field Interpretation
- **Old Label:** "Current Balance"
- **New Label:** "Weekly Deposit Amount"
- **Actual Field:** `current_balance` in the `savings` table

### Purpose
The `current_balance` field stores the **amount each member deposits weekly** into the group savings account, not the total accumulated balance.

## Usage in Views

### Savings Management (Admin View)
**File:** `resources/views/admin/savings/index.blade.php`
```
Column Header: "Weekly Deposit Amount"
Shows: Individual member's weekly deposit amount
```

### Savings Details (Admin View)
**File:** `resources/views/admin/savings/show.blade.php`
```
Section: Account Details
Label: "Weekly Deposit Amount"
Value: Member's weekly deposit amount

Section: Account Summary
Label: "Weekly Deposit Amount"
Value: Member's weekly deposit amount
```

### Create/Edit Savings (Admin View)
**File:** `resources/views/admin/savings/create.blade.php`
```
Label: "Weekly Deposit Amount"
Help Text: "Amount to deposit weekly into the group savings"
Input: Number field (step 0.01, min 0)
```

### Member Dashboard
**File:** `resources/views/dashboards/member.blade.php`
```
Shows: "Weekly Deposit: [amount]"
Displays: Member's personal weekly deposit amount
```

## Database Schema

### Savings Table
```sql
CREATE TABLE savings (
    id BIGINT PRIMARY KEY,
    group_id BIGINT FOREIGN KEY,
    member_id BIGINT FOREIGN KEY,
    current_balance DECIMAL(13,2),  -- Weekly deposit amount
    total_deposits DECIMAL(13,2),   -- Total accumulated deposits
    total_withdrawals DECIMAL(13,2), -- Total accumulated withdrawals
    interest_earned DECIMAL(13,2),   -- Interest earned
    last_deposit_date DATE,
    last_withdrawal_date DATE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

## Example Flow

### Week 1
- Member deposits **1,000** weekly → `current_balance = 1,000`
- Member's accumulated deposits = **1,000**

### Week 2
- Member deposits **1,000** weekly → `current_balance = 1,000` (unchanged)
- Member's accumulated deposits = **2,000** (total_deposits increments)

### Week 4
- If member deposits **1,500** (increased amount) → `current_balance = 1,500`
- Member's accumulated deposits = **4,500** (3 weeks @ 1,000 + 1 week @ 1,500)

## Key Fields Relationship

| Field | Purpose | Updates |
|-------|---------|---------|
| `current_balance` | Weekly deposit amount | When member changes deposit amount |
| `total_deposits` | All deposits accumulated | Every deposit transaction |
| `total_withdrawals` | All withdrawals made | Every withdrawal transaction |
| `interest_earned` | Interest accrued | During settlement periods |

## Controller Implementation

```php
// When creating/updating savings
$saving->update([
    'current_balance' => $request->input('current_balance'), // Weekly amount
    // This is not the same as the balance after deposits
]);

// When recording a deposit
$saving->deposit($amount, 'Weekly deposit');
// This increments:
// - total_deposits += amount
// - Updates member's current_savings in group_members table
```

## Related Tables

### GroupMember Table
```
- current_savings: Current accumulated savings (sum of all deposits - withdrawals)
- total_contributed: Total deposits made
- total_withdrawn: Total withdrawals made
```

### Difference
- **Savings.current_balance** = Weekly deposit amount (what they plan to deposit)
- **GroupMember.current_savings** = Actual accumulated balance (what they have saved)

## API Endpoints Affected

```
GET    /admin/savings                    - List all savings (shows Weekly Deposit Amount)
GET    /admin/savings/{id}               - View savings details
POST   /admin/savings                    - Create savings (input: weekly_deposit)
PUT    /admin/savings/{id}               - Update weekly deposit amount
```

## Notes

✅ This clarifies the purpose of the field for better UX  
✅ Helps distinguish between planned deposits and actual balance  
✅ Makes the savings system logic clearer  
✅ Updated all related views for consistency  
✅ No database changes needed (field name remains same)  

---

**Version:** 1.0  
**Last Updated:** December 25, 2025  
**Status:** Documentation Updated
