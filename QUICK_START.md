# Quick Start Guide - Savings & Loans System

## Installation & Setup (5 minutes)

### 1. Run Migrations
```bash
php artisan migrate
```

This creates all 7 database tables.

### 2. Create a Test Group
```bash
php artisan tinker

# In tinker shell:
$group = \App\Models\Group::create([
    'name' => 'Test Savings Group',
    'description' => 'A test group for the system',
    'created_by' => 1,
    'status' => 'active'
]);
```

---

## Core Operations (Copy & Paste Examples)

### Setup Services
```php
use App\Services\LoanService;
use App\Services\SavingsService;
use App\Services\ReportingService;

$loanService = app(LoanService::class);
$savingsService = app(SavingsService::class);
$reportingService = app(ReportingService::class);
```

### Add a Member to Group
```php
$member = $group->members()->create([
    'user_id' => 2,                    // Existing user
    'role' => 'member',                // or 'admin', 'treasurer'
    'status' => 'active',
    'joined_at' => now(),
]);

// Initialize savings account
$savingsService->initializeSavings($member);
```

### Create & Manage a Loan

```php
// Create loan: 10,000 principal, 500/month for 12 months
$loan = $loanService->createLoan(
    member: $member,
    principal: 10000,
    monthlyCharge: 500,
    durationMonths: 12,
    notes: 'Business startup'
);

// Approve it
$loanService->approveLoan($loan);

// Give the money to member
$loanService->disburseLoan($loan);

// Record a payment (400 toward principal, 100 toward charges)
$loanService->recordLoanPayment(
    loan: $loan,
    principalPaid: 400,
    chargesPaid: 100,
    paymentMethod: 'cash'
);

// Check status
$summary = $loanService->getLoanSummary($loan);
dump($summary);
// Output shows: remaining_balance, payment_progress, is_overdue, etc.
```

### Manage Savings

```php
// Member deposits 1000
$savingsService->deposit($member, 1000, 'Monthly savings');

// Member withdraws 500
$savingsService->withdraw($member, 500, 'Emergency need');

// Add interest (50)
$savingsService->addInterest($member, 50);

// Get summary
$summary = $savingsService->getSavingsSummary($member);
// Shows: current_balance, total_deposits, interest_earned, etc.
```

### Generate Reports

```php
// Group financial overview
$summary = $reportingService->getGroupFinancialSummary($group);
// Shows total savings, loans issued, defaults, etc.

// Member statement (last 6 months)
$statement = $reportingService->getMemberStatement(
    $member,
    from: now()->subMonths(6),
    to: now()
);

// Loan metrics (repayment rate, default rate, etc)
$metrics = $reportingService->getLoanMetrics($group);

// Default/overdue report
$defaults = $reportingService->getDefaultReport($group);
```

---

## Testing the API (Using Postman/curl)

### 1. Create a Loan
```bash
curl -X POST http://localhost/api/groups/1/loans \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "member_id": 1,
    "principal_amount": 10000,
    "monthly_charge": 500,
    "duration_months": 12,
    "notes": "Test loan"
  }'
```

### 2. Record a Payment
```bash
curl -X POST http://localhost/api/groups/1/loans/1/payment \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "principal_paid": 400,
    "charges_paid": 100,
    "payment_method": "cash",
    "notes": "Monthly payment"
  }'
```

### 3. Record a Deposit
```bash
curl -X POST http://localhost/api/groups/1/savings/member/1/deposit \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "amount": 1000,
    "description": "Monthly contribution"
  }'
```

### 4. Get Loan Summary
```bash
curl -X GET http://localhost/api/groups/1/loans/1 \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

## Key Formulas (For Custom Calculations)

```
Total Loan Cost = Principal + (Monthly Charge × Duration)
Example: 10000 + (500 × 12) = 16000

Payment Progress = (Total Paid / Principal) × 100
Example: (4000 / 10000) × 100 = 40%

Remaining Balance = Principal - Total Principal Paid
Example: 10000 - 4000 = 6000

Outstanding Charges = Sum of unpaid monthly charges
Example: 8 × 500 = 4000 (if 4 months paid)

Member Net Worth = Savings - Outstanding Loans
Example: 5000 - 6000 = -1000 (debt exceeds savings)
```

---

## Important Methods Reference

### On Loan Model
```php
$loan->charges()              // Get all charges
$loan->payments()             // Get all payments
$loan->pendingCharges()       // Unpaid charges
$loan->overdueCharges()       // Overdue charges
$loan->isOverdue()            // Boolean
$loan->isFullyPaid()          // Boolean
$loan->getPaymentProgress()   // Returns percentage
$loan->getTotalLoanCost()     // Principal + all charges
$loan->getTotalOutstandingCharges()
$loan->getNextDueDate()
```

### On GroupMember Model
```php
$member->loans()
$member->savings()
$member->transactions()
$member->getActiveLoanCount()
$member->getTotalOutstandingLoans()
$member->isAdmin(), isTreasurer(), isMember()
$member->isActive()
```

### On Saving Model
```php
$saving->deposit($amount, $description)
$saving->withdraw($amount, $description)
$saving->addInterest($amount)
$saving->transactions()
$saving->current_balance
$saving->total_deposits
$saving->total_withdrawals
$saving->interest_earned
```

---

## Database Queries (Useful)

### Find Active Loans
```php
$loans = Loan::where('status', 'active')
    ->with('member.user')
    ->get();
```

### Find Overdue Charges
```php
$overdue = LoanCharge::where('status', 'overdue')
    ->with('loan.member.user')
    ->get();
```

### Get Member's Total Debt
```php
$debt = $member->loans()
    ->where('status', 'active')
    ->sum('remaining_balance');
```

### Get All Defaulted Loans in Group
```php
$defaulted = $group->loans()
    ->where('status', 'defaulted')
    ->with('member.user')
    ->get();
```

### Get Monthly Savings Trend
```php
$trend = Transaction::where('group_id', $group->id)
    ->where('type', 'deposit')
    ->groupBy(DB::raw('MONTH(transaction_date)'))
    ->sum('amount');
```

---

## Common Tasks & Solutions

### Task: Create Full Loan Cycle
```php
// 1. Create loan
$loan = $loanService->createLoan($member, 5000, 250, 12);

// 2. Approve
$loanService->approveLoan($loan);

// 3. Disburse
$loanService->disburseLoan($loan);

// 4. Make 3 payments
for ($i = 0; $i < 3; $i++) {
    $loanService->recordLoanPayment($loan, 300, 250);
    $loan->refresh();
}

// 5. Check status
echo "Balance: " . $loan->remaining_balance;
echo "Progress: " . $loan->getPaymentProgress() . "%";
```

### Task: Generate Monthly Interest
```php
// For all savings in group
$group->members()->each(function($member) use ($savingsService) {
    $savings = $savingsService->getSavings($member);
    $interest = $savings->current_balance * 0.01; // 1% interest
    $savingsService->addInterest($member, $interest);
});
```

### Task: Auto-Mark Overdue
```php
// Run periodically (e.g., daily job)
app(LoanService::class)->updateOverdueCharges();

// Or manually check
$group->loans()->with('charges')->each(function($loan) {
    $loan->charges->each(fn($c) => $c->markOverdueIfNeeded());
});
```

### Task: Export Member Data
```php
$statement = $reportingService->getMemberStatement($member);

// Convert to CSV
$csv = array_to_csv($statement['recent_transactions']);
// Save to file
```

---

## Schema Diagram

```
Group (1) ──────────────────────┐
    ├──→ (N) GroupMembers ───┐  │
    ├──→ (N) Loans ────────┐ │  │
    └──→ (N) Transactions  │ │  │
                           │ │  │
Member/Savings             │ │  │
    ├──→ (1) Loan ─┐       │ │  │
    │   ├──→ (N) LoanCharges
    │   ├──→ (N) LoanPayments
    │   └──→ (N) Transactions
    │
    ├──→ (1) Saving ───┐
    │   └──→ (N) Transactions
    │
    └──→ (N) Transactions
```

---

## Troubleshooting

**Issue: "Balance insufficient for withdrawal"**
- Solution: Check `$saving->current_balance` before withdrawal

**Issue: "Only active loans can receive payments"**
- Solution: Loan must have `status = 'active'`. Approve and disburse first.

**Issue: Charges not generating**
- Solution: Call `generateLoanCharges($loan)` during loan creation (automatic in `createLoan`)

**Issue: Payment not splitting correctly**
- Solution: Pass both `principalPaid` and `chargesPaid` to `recordLoanPayment`

---

## Production Checklist

- [ ] Run migrations
- [ ] Create authorization Policies
- [ ] Add input validation (Form Requests)
- [ ] Add error handling
- [ ] Test all endpoints
- [ ] Setup logging
- [ ] Add transaction rollback on failure
- [ ] Implement rate limiting
- [ ] Setup audit logging
- [ ] Create admin dashboard
- [ ] Setup notifications
- [ ] Create member portal
