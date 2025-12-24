# System Reference & Visual Guide

## System Architecture Diagram

```
┌─────────────────────────────────────────────────────────────┐
│                     SAVINGS & LOANS SYSTEM                  │
└─────────────────────────────────────────────────────────────┘

┌──────────────────────┐
│   Frontend Layer     │
│  (Views/React/Vue)   │
└──────────┬───────────┘
           │
┌──────────▼──────────────────────────────────────────────────┐
│         REST API Layer (Controllers)                        │
│  ┌─────────────────┐  ┌──────────────────┐                │
│  │ LoanController  │  │SavingsController │  MoreControllers│
│  └────────┬────────┘  └────────┬─────────┘                │
└───────────┼──────────────────────┼─────────────────────────┘
            │                      │
┌───────────▼──────────────────────▼─────────────────────────┐
│        Service Layer (Business Logic)                      │
│  ┌──────────────────┐  ┌───────────────┐  ┌────────────┐ │
│  │  LoanService     │  │SavingsService │  │Reporting   │ │
│  │                  │  │               │  │Service     │ │
│  │ - createLoan()   │  │ - deposit()   │  │ - reports()│ │
│  │ - approveLoan()  │  │ - withdraw()  │  │ - metrics()│ │
│  │ - disburseLoan() │  │ - addInterest │  │ - summary()│ │
│  │ - recordPayment()│  │               │  │            │ │
│  └────────┬─────────┘  └────────┬──────┘  └─────┬──────┘ │
└───────────┼───────────────────────┼──────────────┼────────┘
            │                       │              │
┌───────────▼──────────────────────▼──────────────▼────────┐
│           Model Layer (Data & Relationships)            │
│  ┌────────┐  ┌──────────┐  ┌───────┐  ┌──────────────┐ │
│  │ Group  │  │GroupMbr  │  │ Loan  │  │ LoanCharge   │ │
│  └────────┘  └──────────┘  └───────┘  └──────────────┘ │
│  ┌───────────┐  ┌──────────────┐  ┌────────────────┐   │
│  │ LoanPaymt │  │  Saving      │  │ Transaction    │   │
│  └───────────┘  └──────────────┘  └────────────────┘   │
└────────────────────────────────────────────────────────┘
            │
┌───────────▼──────────────────────────────────────────────┐
│          Database Layer (7 Tables)                       │
│  ┌─────────┐  ┌──────────────┐  ┌────────────────────┐ │
│  │ groups  │  │ group_members│  │ loans              │ │
│  └─────────┘  └──────────────┘  └────────────────────┘ │
│  ┌──────────────┐  ┌──────────────┐  ┌─────────────┐   │
│  │ loan_charges │  │ loan_payments│  │ savings     │   │
│  └──────────────┘  └──────────────┘  └─────────────┘   │
│                    ┌──────────────────┐                  │
│                    │  transactions    │                  │
│                    └──────────────────┘                  │
└──────────────────────────────────────────────────────────┘
```

---

## Entity Relationship Diagram

```
┌──────────────────────────────────────────────────────────────────┐
│                          GROUP                                   │
│  ┌─────────────────────────────────────────────────────────────┐ │
│  │ id (PK) | name | description | created_by | status         │ │
│  │ total_savings | total_loans_issued | total_interest_earned │ │
│  └─────────────────────────────────────────────────────────────┘ │
└──────────┬──────────────────────────────────────────────────┬────┘
           │ 1:N                                              │ 1:N
           │                                                  │
    ┌──────▼──────────────────────┐            ┌─────────────▼──────────────┐
    │    GROUP_MEMBERS            │            │      LOANS                 │
    │ ┌──────────────────────────┐ │            │ ┌──────────────────────┐  │
    │ │ id (PK)                  │ │            │ │ id (PK)              │  │
    │ │ group_id, user_id (FKs)  │ │            │ │ group_id, member_id  │  │
    │ │ role (admin/treasurer/  │ │            │ │ principal_amount     │  │
    │ │        member)           │ │            │ │ monthly_charge       │  │
    │ │ current_savings          │ │            │ │ remaining_balance    │  │
    │ │ total_contributed        │ │            │ │ duration_months      │  │
    │ │ total_borrowed           │ │            │ │ issued_at, maturity_ │  │
    │ │ outstanding_loans        │ │            │ │ date, paid_off_at    │  │
    │ └──────────────────────────┘ │            │ │ status (pending/     │  │
    └────────────┬─────────────────┘            │ │ approved/active/     │  │
                 │                              │ │ completed/defaulted) │  │
                 │ 1:N                          │ └──────────────────────┘  │
                 │                              └────────────┬──────────────┘
                 │                                           │ 1:N
        ┌────────▼────────────────┐            ┌───────────▼──────────────┐
        │      SAVINGS            │            │   LOAN_CHARGES           │
        │ ┌────────────────────┐  │            │ ┌──────────────────────┐ │
        │ │ id (PK)            │  │            │ │ id (PK)              │ │
        │ │ group_id,member_id │  │            │ │ loan_id (FK)         │ │
        │ │ current_balance    │  │            │ │ month_number         │ │
        │ │ total_deposits     │  │            │ │ charge_amount        │ │
        │ │ total_withdrawals  │  │            │ │ due_date             │ │
        │ │ interest_earned    │  │            │ │ status (pending/paid │ │
        │ │ last_deposit_date  │  │            │ │ /overdue/waived)     │ │
        │ └────────────────────┘  │            │ └──────────────────────┘ │
        └────────┬─────────────────┘            └──────────────────────────┘
                 │
                 │ 1:N
    ┌────────────▼──────────────┐
    │   TRANSACTIONS            │
    │ ┌──────────────────────┐  │
    │ │ id (PK)              │  │
    │ │ group_id, member_id  │  │
    │ │ type (deposit/       │  │
    │ │       withdrawal/    │  │
    │ │       loan_payment)  │  │
    │ │ amount, balance_after│  │
    │ │ reference, date      │  │
    │ └──────────────────────┘  │
    └───────────────────────────┘

Additional:
    LOAN_PAYMENTS (tracks all payment records for auditing)
```

---

## Data Flow Diagram

### Loan Creation & Payment Flow
```
Member Requests Loan
    ↓
[LoanService::createLoan()]
    ├→ Creates Loan record (status=pending)
    ├→ Generates LoanCharges (N monthly records)
    └→ Returns Loan object
    ↓
Admin Reviews & Approves
    ↓
[LoanService::approveLoan()]
    └→ Updates status to "approved"
    ↓
Treasurer Disburses Funds
    ↓
[LoanService::disburseLoan()]
    ├→ Creates Transaction (loan_disburse)
    ├→ Updates status to "active"
    └→ Funds ready for member
    ↓
Member Makes Payment
    ↓
[LoanService::recordLoanPayment()]
    ├→ Creates LoanPayment record
    ├→ Splits payment (principal + charges)
    ├→ Updates LoanCharges status
    ├→ Updates Loan balance
    ├→ Updates GroupMember balance
    ├→ Creates Transaction record
    └→ Checks if fully paid
         ├→ If Yes: status = "completed"
         └→ If No: status remains "active"
    ↓
Report Generated
    ↓
[ReportingService::getLoanSummary()]
    └→ Shows all metrics & history
```

### Savings Operations Flow
```
Member Deposits Savings
    ↓
[SavingsService::deposit()]
    ├→ Get/create Saving account
    ├→ [Saving::deposit()]
    │  ├→ Increases current_balance
    │  ├→ Increases total_deposits
    │  ├→ Updates last_deposit_date
    │  ├→ Updates GroupMember totals
    │  └→ Creates Transaction record
    └→ Return Saving object
    ↓
Member Requests Withdrawal
    ↓
[SavingsService::withdraw()]
    ├→ Validate sufficient balance
    ├→ [Saving::withdraw()]
    │  ├→ Decreases current_balance
    │  ├→ Increases total_withdrawals
    │  ├→ Updates GroupMember totals
    │  └→ Creates Transaction record
    └→ Returns success boolean
    ↓
Interest Calculation (Monthly/Yearly)
    ↓
[SavingsService::addInterest()]
    ├→ [Saving::addInterest()]
    │  ├→ Increases current_balance
    │  ├→ Increases interest_earned
    │  └→ Creates Transaction record
    └→ Return updated Saving
```

---

## State Diagram - Loan Lifecycle

```
                ┌─────────────────────┐
                │       START         │
                └──────────┬──────────┘
                           │
                           ▼
                  ┌────────────────┐
                  │    PENDING     │ ← Loan Created
                  └────────┬───────┘
                           │ approveLoan()
                           ▼
                  ┌────────────────┐
                  │   APPROVED     │ ← Approved by admin
                  └────────┬───────┘
                           │ disburseLoan()
                           ▼
                  ┌────────────────┐
                  │     ACTIVE     │ ← Funds disbursed
                  └────────┬───────┘
                     │     │     │
          ┌──────────┘     │     └──────────┐
          │                │                │
          │ (full payment) │                │ defaultLoan()
          │                │                │
          ▼                ▼                ▼
    ┌──────────┐    ┌──────────┐    ┌───────────┐
    │COMPLETED │    │ DEFAULTED│    │ Other     │
    │(All paid)│    │ (Failed) │    │ Routes    │
    └──────────┘    └──────────┘    └───────────┘
          │                │
          ▼                ▼
    ┌───────────────────────────┐
    │      END (Archived)       │
    └───────────────────────────┘

Transitions:
PENDING → APPROVED → ACTIVE → COMPLETED
            ↑         ↓ ↓
            └─────────┘ DEFAULTED
```

---

## API Request/Response Examples

### Create Loan Request
```json
POST /api/groups/1/loans
{
  "member_id": 5,
  "principal_amount": 10000,
  "monthly_charge": 500,
  "duration_months": 12,
  "notes": "Business equipment"
}
```

Response (201 Created)
```json
{
  "message": "Loan created successfully",
  "loan": {
    "id": 42,
    "group_id": 1,
    "member_id": 5,
    "principal_amount": 10000,
    "monthly_charge": 500,
    "remaining_balance": 10000,
    "duration_months": 12,
    "status": "pending",
    "issued_at": "2025-01-01",
    "maturity_date": "2026-01-01",
    "charges": [...12 monthly charges...],
    "created_at": "2025-01-01T10:00:00Z"
  }
}
```

### Record Payment Request
```json
POST /api/groups/1/loans/42/payment
{
  "principal_paid": 400,
  "charges_paid": 100,
  "payment_method": "cash",
  "notes": "January payment"
}
```

Response
```json
{
  "message": "Payment recorded successfully",
  "loan": {
    "id": 42,
    "principal_amount": 10000,
    "remaining_balance": 9600,
    "total_principal_paid": 400,
    "total_charged": 100,
    "months_paid": 1,
    "status": "active"
  },
  "summary": {
    "principal": 10000,
    "remaining_balance": 9600,
    "total_principal_paid": 400,
    "total_charges_paid": 100,
    "total_cost": 16000,
    "payment_progress": 4.0,
    "is_overdue": false,
    "outstanding_charges": 4900
  }
}
```

### Financial Summary Request
```
GET /api/groups/1/reports/financial-summary
```

Response
```json
{
  "group_name": "Village Savings Group A",
  "total_members": 25,
  "total_savings": 150000,
  "savings_distribution": {
    "total_contributed": 175000,
    "total_withdrawn": 25000
  },
  "loans": {
    "total_issued": 250000,
    "active_loans": 15,
    "active_amount": 120000,
    "completed_loans": 8,
    "completed_amount": 100000,
    "defaulted_loans": 2,
    "defaulted_amount": 30000
  },
  "charges": {
    "total_earned": 45000,
    "pending": 12000,
    "overdue": 3000
  }
}
```

---

## User Roles & Permissions

```
┌─────────────────────────────────────────────────────────┐
│                   MEMBER ROLES                          │
├─────────────────────────────────────────────────────────┤
│ ADMIN                                                   │
│ ├─ Create/edit group                                   │
│ ├─ Add/remove members                                  │
│ ├─ Approve loans                                       │
│ ├─ View all reports                                    │
│ └─ Manage group settings                               │
├─────────────────────────────────────────────────────────┤
│ TREASURER                                               │
│ ├─ Record deposits/withdrawals                         │
│ ├─ Disburse loans                                      │
│ ├─ Record payments                                     │
│ ├─ View financial reports                              │
│ └─ Generate statements                                 │
├─────────────────────────────────────────────────────────┤
│ MEMBER                                                  │
│ ├─ View own savings                                    │
│ ├─ View own loans                                      │
│ ├─ View own statement                                  │
│ ├─ Request deposits                                    │
│ └─ Request withdrawals                                 │
└─────────────────────────────────────────────────────────┘
```

---

## Key Formulas Reference

```
╔═══════════════════════════════════════════════════════════╗
║                    LOAN FORMULAS                         ║
╠═══════════════════════════════════════════════════════════╣
║ Total Loan Cost                                           ║
║ = Principal + (Monthly Charge × Duration Months)         ║
║ Example: 10,000 + (500 × 12) = 16,000                   ║
╠═══════════════════════════════════════════════════════════╣
║ Payment Progress %                                        ║
║ = (Total Principal Paid / Principal) × 100               ║
║ Example: (4,000 / 10,000) × 100 = 40%                  ║
╠═══════════════════════════════════════════════════════════╣
║ Remaining Balance                                         ║
║ = Principal - Total Principal Paid                       ║
║ Example: 10,000 - 4,000 = 6,000                         ║
╠═══════════════════════════════════════════════════════════╣
║ Outstanding Charges                                       ║
║ = Sum of All Unpaid Monthly Charges                      ║
║ Example: 8 months × 500 = 4,000                         ║
╚═══════════════════════════════════════════════════════════╝

╔═══════════════════════════════════════════════════════════╗
║                   SAVINGS FORMULAS                       ║
╠═══════════════════════════════════════════════════════════╣
║ Current Balance                                           ║
║ = Total Deposits - Total Withdrawals + Interest Earned   ║
║ Example: 5,000 - 1,000 + 100 = 4,100                   ║
╠═══════════════════════════════════════════════════════════╣
║ Savings Growth %                                          ║
║ = (Interest Earned / Total Deposits) × 100               ║
║ Example: (100 / 5,000) × 100 = 2%                      ║
╚═══════════════════════════════════════════════════════════╝

╔═══════════════════════════════════════════════════════════╗
║                    MEMBER METRICS                        ║
╠═══════════════════════════════════════════════════════════╣
║ Total Debt                                                ║
║ = Sum of Remaining Balances on Active Loans              ║
║ Example: Loan1(6000) + Loan2(4000) = 10,000            ║
╠═══════════════════════════════════════════════════════════╣
║ Member Net Worth                                          ║
║ = Savings Balance - Total Debt                           ║
║ Example: 4,100 - 10,000 = -5,900                       ║
╚═══════════════════════════════════════════════════════════╝

╔═══════════════════════════════════════════════════════════╗
║                    GROUP METRICS                         ║
╠═══════════════════════════════════════════════════════════╣
║ Repayment Rate %                                          ║
║ = (Completed Loans / Total Loans) × 100                  ║
║ Example: (8 / 25) × 100 = 32%                          ║
╠═══════════════════════════════════════════════════════════╣
║ Default Rate %                                            ║
║ = (Defaulted Loans / Total Loans) × 100                  ║
║ Example: (2 / 25) × 100 = 8%                           ║
╠═══════════════════════════════════════════════════════════╣
║ Loan Default Value                                        ║
║ = Sum of Remaining Balance on Defaulted Loans            ║
║ Example: 30,000                                          ║
╚═══════════════════════════════════════════════════════════╝
```

---

## Transaction Types Reference

```
┌────────────────────────────────────────────────────────┐
│              TRANSACTION TYPES                         │
├────────────────────────────────────────────────────────┤
│ deposit         │ Member adds savings                 │
│ withdrawal      │ Member removes savings              │
│ loan_disburse   │ Loan funds given to member         │
│ loan_payment    │ Member pays loan (principal+charge)│
│ interest        │ Interest earned on savings          │
│ charge          │ Monthly loan charge/interest        │
│ fee             │ Administrative fee                  │
│ dividend        │ Share of profits (future)           │
└────────────────────────────────────────────────────────┘
```

---

## Status Values Reference

```
LOANS:
  pending    → Created, awaiting approval
  approved   → Approved, awaiting disbursement
  active     → Funds given, payments in progress
  completed  → All paid off
  defaulted  → Member unable/unwilling to pay

LOAN_CHARGES:
  pending    → Due date reached, awaiting payment
  paid       → Successfully paid
  overdue    → Past due date, unpaid
  waived     → Forgiven by group

MEMBERS:
  active     → Current member
  inactive   → Suspended/left
  suspended  → Temporarily blocked

GROUPS:
  active     → Operating normally
  inactive   → Not operating
  suspended  → Temporarily blocked
```

---

## File Structure Summary

```
project/
├── app/
│   ├── Models/
│   │   ├── Group.php
│   │   ├── GroupMember.php
│   │   ├── Loan.php
│   │   ├── LoanCharge.php
│   │   ├── LoanPayment.php
│   │   ├── Saving.php
│   │   └── Transaction.php
│   ├── Services/
│   │   ├── LoanService.php
│   │   ├── SavingsService.php
│   │   └── ReportingService.php
│   └── Http/
│       └── Controllers/
│           ├── LoanController.php
│           └── SavingsController.php
├── database/
│   └── migrations/
│       ├── 2025_01_01_000010_create_groups_table.php
│       ├── 2025_01_01_000011_create_group_members_table.php
│       ├── 2025_01_01_000012_create_loans_table.php
│       ├── 2025_01_01_000013_create_loan_charges_table.php
│       ├── 2025_01_01_000014_create_loan_payments_table.php
│       ├── 2025_01_01_000015_create_savings_table.php
│       └── 2025_01_01_000016_create_transactions_table.php
└── Documentation/
    ├── SYSTEM_DESIGN.md
    ├── IMPLEMENTATION_GUIDE.md
    ├── ARCHITECTURE_SUMMARY.md
    ├── QUICK_START.md
    ├── API_ROUTES.php
    ├── DELIVERY_SUMMARY.md
    └── SYSTEM_REFERENCE.md (this file)
```

---

This visual guide complements the detailed documentation. Refer back to specific sections as needed during development!
