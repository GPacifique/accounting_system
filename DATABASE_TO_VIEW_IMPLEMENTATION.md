# Weekly Deposit Amount - Database to View Implementation

## Overview
This document details how the "Weekly Deposit Amount" flows from the database through the application to the views.

---

## 1. Database Layer

### Table: `savings`
```sql
CREATE TABLE savings (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    group_id BIGINT NOT NULL FOREIGN KEY → groups(id),
    member_id BIGINT NOT NULL FOREIGN KEY → group_members(id),
    current_balance DECIMAL(13,2) NOT NULL DEFAULT 0.00,  -- Weekly deposit amount
    total_deposits DECIMAL(13,2) NOT NULL DEFAULT 0.00,
    total_withdrawals DECIMAL(13,2) NOT NULL DEFAULT 0.00,
    interest_earned DECIMAL(13,2) NOT NULL DEFAULT 0.00,
    last_deposit_date DATE NULL,
    last_withdrawal_date DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

**Key Column:**
- `current_balance` = Weekly deposit amount (amount member deposits every week)

---

## 2. Model Layer

### File: `app/Models/Saving.php`

**Database Casting:**
```php
protected $casts = [
    'current_balance' => 'decimal:2',      // Ensures 2 decimal places
    'total_deposits' => 'decimal:2',
    'total_withdrawals' => 'decimal:2',
    'interest_earned' => 'decimal:2',
    'last_deposit_date' => 'date',
    'last_withdrawal_date' => 'date',
];
```

**Accessors (Computed Properties):**
```php
/**
 * Get the current balance (accumulated savings)
 * Formula: total_deposits - total_withdrawals + interest_earned
 * Example: 5000 - 1000 + 200 = 4200
 */
public function getBalanceAttribute(): float
{
    return (float) ($this->total_deposits - $this->total_withdrawals + $this->interest_earned);
}

/**
 * Alias for balance (same value, different name)
 */
public function getTotalSavedAttribute(): float
{
    return $this->balance;
}
```

**Usage in Code:**
```php
$saving = Saving::find(1);

// From database (direct column)
$saving->current_balance;  // 1000.00 (weekly deposit)

// Computed (accessor)
$saving->balance;          // 4200.00 (total accumulated)
$saving->total_saved;      // 4200.00 (alias for balance)

// Other columns
$saving->total_deposits;   // 5000.00
$saving->total_withdrawals; // 1000.00
$saving->interest_earned;  // 200.00
```

---

## 3. Controller Layer

### File: `app/Http/Controllers/AdminDashboardController.php`

**Query Example:**
```php
public function savingsManagement(Group $group)
{
    $savings = Saving::where('group_id', $group->id)
        ->with('member.user', 'group')  // Eager load relationships
        ->paginate(15);
    
    return view('admin.savings.index', [
        'savings' => $savings,
    ]);
}
```

**Eager Loading:**
```php
->with('member.user', 'group')
```
This loads the `GroupMember` and `User` relationships to avoid N+1 queries.

---

## 4. View Layer

### Display Locations & Implementation

#### A. Admin Savings Index
**File:** `resources/views/admin/savings/index.blade.php`

```blade
<table>
    <thead>
        <tr>
            <th>Weekly Deposit Amount</th>
        </tr>
    </thead>
    <tbody>
        @foreach($savings as $saving)
            <tr>
                <td>{{ number_format($saving->current_balance, 2) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
```

**Rendering:**
- Reads from: `$saving->current_balance` (from database)
- Format: `number_format()` → adds thousand separators, 2 decimals
- Example: `1000.00` → displays as `1,000.00`

---

#### B. Admin Savings Details (Show)
**File:** `resources/views/admin/savings/show.blade.php`

```blade
<!-- Account Details Section -->
<div class="py-2 border-b">
    <p class="text-xs text-gray-500 uppercase">Weekly Deposit Amount</p>
    <p class="text-2xl font-bold text-green-600">
        {{ number_format($saving->current_balance, 2) }}
    </p>
</div>

<!-- Account Summary Section -->
<div class="py-3 bg-green-50 rounded-lg text-center">
    <p class="text-xs text-gray-600 uppercase">Weekly Deposit Amount</p>
    <p class="text-2xl font-bold text-green-600">
        {{ number_format($saving->current_balance, 2) }}
    </p>
</div>

<!-- Additional Info Section -->
<div class="py-3 bg-blue-50 rounded-lg text-center">
    <p class="text-xs text-gray-600 uppercase">Total Deposits (Accumulated)</p>
    <p class="text-2xl font-bold text-blue-600">
        {{ number_format($saving->total_deposits, 2) }}
    </p>
</div>
```

**What's Shown:**
- Weekly Deposit Amount: `current_balance` (what they deposit weekly)
- Total Deposits: `total_deposits` (sum of all weekly deposits)
- Total Saved: Computed `balance` accessor

---

#### C. Member Dashboard
**File:** `resources/views/dashboards/member.blade.php`

```blade
@foreach($savings as $saving)
    <div class="border rounded-lg p-4">
        <h3 class="font-semibold text-gray-900">{{ $saving->group->name }}</h3>
        
        <!-- Weekly Deposit Amount from Database -->
        <p class="text-sm text-gray-600">
            Weekly Deposit: {{ number_format($saving->current_balance, 2) }}
        </p>
        
        <!-- Total Saved (Computed) -->
        <p class="text-sm text-gray-600">
            Total Saved: {{ number_format($saving->total_saved, 2) }}
        </p>
        
        <p class="text-xs text-gray-500 mt-2">
            Opened: {{ $saving->created_at->format('M d, Y') }}
        </p>
    </div>
@endforeach
```

**Rendering:**
- Weekly Deposit: From `current_balance` column (direct database read)
- Total Saved: From `total_saved` accessor (computed)

---

#### D. Group Admin Dashboard
**File:** `resources/views/dashboards/group-admin.blade.php`

**Recent Savings Section:**
```blade
@foreach($recent_savings as $saving)
    <div class="border-l-4 border-green-500 pl-4 py-2">
        <p class="font-semibold text-gray-900 text-sm">
            {{ $saving->member->user->name }}
        </p>
        
        <!-- Weekly Deposit Amount -->
        <p class="text-xs text-gray-600">
            Weekly Deposit: {{ number_format($saving->current_balance, 2) }}
        </p>
        
        <!-- Total Saved -->
        <p class="text-xs text-gray-500 mt-1">
            Total Saved: {{ number_format($saving->balance, 2) }}
        </p>
        
        <p class="text-xs text-gray-500 mt-1">
            {{ $saving->updated_at->diffForHumans() }}
        </p>
    </div>
@endforeach
```

---

#### E. Savings Create Form
**File:** `resources/views/admin/savings/create.blade.php`

```blade
<!-- Weekly Deposit Amount Input -->
<div class="mb-6">
    <label for="current_balance" class="block text-sm font-bold text-gray-700 mb-2">
        Weekly Deposit Amount
    </label>
    <input
        type="number"
        id="current_balance"
        name="current_balance"
        value="{{ old('current_balance') }}"
        step="0.01"
        min="0"
        placeholder="0.00"
        required
    >
    <p class="text-gray-500 text-xs mt-1">
        Amount to deposit weekly into the group savings
    </p>
</div>
```

**Input Flow:**
1. User enters amount in form
2. Form submits with `name="current_balance"`
3. Controller receives: `$request->input('current_balance')`
4. Saves to database: `Saving::create(['current_balance' => $amount])`

---

## 5. Complete Data Flow

### Example: Member Opens Savings Account

**Step 1: User Input**
```
User enters: 1,500 (weekly deposit amount)
Form field: name="current_balance"
```

**Step 2: Controller Processing**
```php
$saving = Saving::create([
    'group_id' => $group->id,
    'member_id' => $member->id,
    'current_balance' => 1500.00,  // Stored in database
    'total_deposits' => 0.00,
    'total_withdrawals' => 0.00,
    'interest_earned' => 0.00,
]);
```

**Step 3: Database Storage**
```
Database Table: savings
Row:
- id: 1
- current_balance: 1500.00
- total_deposits: 0.00
- total_withdrawals: 0.00
- interest_earned: 0.00
```

**Step 4: First Weekly Deposit**
```php
$saving->deposit(1500, 'Weekly deposit week 1');
```

**Database After Deposit:**
```
- current_balance: 1500.00 (unchanged - represents weekly amount)
- total_deposits: 1500.00 (incremented)
- last_deposit_date: 2025-12-25
```

**Step 5: View Rendering**
```blade
{{ number_format($saving->current_balance, 2) }}
→ Displays: 1,500.00 (the weekly deposit amount)

{{ number_format($saving->balance, 2) }}
→ Displays: 1,500.00 (calculated: 1500 - 0 + 0)
```

---

## 6. Key Distinctions

| Field | Source | Purpose | Example |
|-------|--------|---------|---------|
| `current_balance` | Database Column | Weekly deposit amount (what member deposits) | 1,500.00 |
| `total_deposits` | Database Column | Sum of all weekly deposits | 4,500.00 |
| `total_withdrawals` | Database Column | Sum of all withdrawals | 500.00 |
| `interest_earned` | Database Column | Interest accrued | 100.00 |
| `balance` | Accessor (Computed) | Total saved (deposits - withdrawals + interest) | 4,100.00 |
| `total_saved` | Accessor (Alias) | Alias for balance | 4,100.00 |

---

## 7. Testing the Flow

### SQL Query to Verify Data
```sql
SELECT 
    id,
    group_id,
    member_id,
    current_balance,
    total_deposits,
    total_withdrawals,
    interest_earned,
    (total_deposits - total_withdrawals + interest_earned) as calculated_balance
FROM savings
WHERE group_id = 1
ORDER BY id DESC;
```

### Laravel Tinker Test
```php
php artisan tinker

$saving = Saving::with('member.user', 'group')->first();

// Database values (direct)
$saving->current_balance;      // 1500.00
$saving->total_deposits;       // 4500.00
$saving->total_withdrawals;    // 500.00
$saving->interest_earned;      // 100.00

// Computed accessor
$saving->balance;              // 4100.00
$saving->total_saved;          // 4100.00

// Formatted for display
number_format($saving->current_balance, 2);  // 1,500.00
number_format($saving->balance, 2);          // 4,100.00
```

### Controller Test
```php
Route::get('/test-savings', function () {
    $saving = Saving::with('member.user', 'group')->first();
    
    return [
        'weekly_deposit' => $saving->current_balance,
        'total_saved' => $saving->balance,
        'formatted_weekly' => number_format($saving->current_balance, 2),
        'formatted_total' => number_format($saving->balance, 2),
    ];
});

// Output:
{
    "weekly_deposit": 1500,
    "total_saved": 4100,
    "formatted_weekly": "1,500.00",
    "formatted_total": "4,100.00"
}
```

---

## 8. Troubleshooting

### Issue: Weekly Deposit Amount Shows as 0 or NULL

**Causes:**
1. Record doesn't exist in database
2. `current_balance` column is NULL
3. Controller not passing value to view

**Fix:**
```php
// In Controller
$saving = Saving::where('id', $id)
    ->with('member.user', 'group')
    ->first();

if (!$saving) {
    abort(404, 'Savings record not found');
}

return view('admin.savings.show', ['saving' => $saving]);

// In View
@if($saving && $saving->current_balance)
    {{ number_format($saving->current_balance, 2) }}
@else
    <span class="text-gray-400">No deposit amount set</span>
@endif
```

### Issue: Value Not Updating After Deposit

**Cause:** `current_balance` is the *weekly* amount, not the accumulated balance

**Expected Behavior:**
```php
$saving->current_balance;  // Still 1500 after deposit
$saving->total_deposits;   // Increases to 3000
$saving->balance;          // Increases (computed)
```

**Correct Code:**
```php
// Wrong - this overwrites weekly amount
$saving->update(['current_balance' => $saving->current_balance + $amount]);

// Right - this increments total deposits
$saving->deposit($amount);  // Uses the deposit() method
```

---

## 9. Database Verification

```sql
-- Check if weekly deposit amounts are set
SELECT user_id, current_balance, total_deposits, COUNT(*) as savings_count
FROM savings
JOIN group_members ON savings.member_id = group_members.id
JOIN users ON group_members.user_id = users.id
GROUP BY user_id;

-- Find accounts with zero weekly deposits
SELECT * FROM savings WHERE current_balance = 0 OR current_balance IS NULL;

-- Calculate member balances
SELECT 
    u.name,
    s.current_balance as weekly_amount,
    s.total_deposits,
    s.total_withdrawals,
    s.interest_earned,
    (s.total_deposits - s.total_withdrawals + s.interest_earned) as calculated_balance
FROM savings s
JOIN group_members gm ON s.member_id = gm.id
JOIN users u ON gm.user_id = u.id
ORDER BY u.name;
```

---

## Summary

✅ Weekly Deposit Amount stored in `savings.current_balance` column  
✅ Automatically cast to `decimal:2` in model  
✅ Displayed using `number_format($saving->current_balance, 2)`  
✅ Accessor `balance` computes accumulated total  
✅ All views updated to show both weekly amount and total saved  
✅ Form input stores value directly to database  
✅ No conversion needed - value flows directly from DB to view  

---

**Version:** 1.0  
**Last Updated:** December 25, 2025  
**Status:** Implementation Complete
