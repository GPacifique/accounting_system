# Savings and Loans Management System - Design Document

## System Overview
A multi-group accounting system managing member savings, loans, interest calculations, and repayment schedules.

## Core Entities

### 1. Groups
- Unique group identifier
- Group name, description
- Group admin/treasurer
- Creation date
- Status (active, inactive)

### 2. Members
- User account linked to group
- Membership status
- Registration date
- Contact information
- Account balance

### 3. Savings Accounts
- Member savings balance
- Total contributions
- Interest earned
- Last contribution date
- Account type (regular, fixed)

### 4. Loans
- Loan ID, amount, type
- Member borrowing
- Issue date, maturity date
- Monthly charge/interest rate
- Loan status (pending, active, completed, defaulted)
- Agreed duration (months)

### 5. Loan Charges/Installments
- Monthly interest/charge amount
- Due date
- Payment status (paid, pending, overdue)
- Amount paid
- Payment date

### 6. Transactions
- Transaction ID, type (deposit, withdrawal, loan payment)
- Member involved
- Amount
- Description
- Date
- Balance before/after
- Reference to loan/savings

### 7. Loan Disbursement
- Track when loans are given out
- Outstanding balance
- Repayment schedule

## Key Business Rules

### Loan Processing
1. Member requests loan with amount and duration
2. Group approves/denies loan
3. Monthly charge/interest calculated
4. Installment schedule generated
5. Fund disbursed to member
6. Member pays monthly charges + principal

### Savings Management
1. Members contribute regular savings
2. Interest accrued (optional)
3. Withdrawal tracking
4. Balance management

### Reporting
1. Member account statements
2. Loan status reports
3. Group financial summary
4. Default tracking
5. Interest earned/charged

## Database Relations

```
Groups (1) ──→ (N) Members
       └──→ (N) Transactions
       └──→ (N) Loans

Members (1) ──→ (N) Savings
        └──→ (N) Loans
        └──→ (N) Transactions

Loans (1) ──→ (N) LoanCharges
      └──→ (N) LoanPayments
      └──→ (N) Transactions

Transactions: Polymorphic to Loans, Savings, LoanCharges
```

## Core Features to Implement

1. **Member Management**
   - Add/remove members
   - View member profile
   - Member status tracking

2. **Savings Module**
   - Record deposits
   - Track contributions
   - Calculate interest
   - Process withdrawals

3. **Loan Module**
   - Loan application
   - Approval workflow
   - Disbursement
   - Charge calculation
   - Payment tracking

4. **Accounting**
   - Double-entry bookkeeping (debit/credit)
   - Transaction ledger
   - Balance reconciliation

5. **Reports**
   - Member statements
   - Loan summaries
   - Group financial reports
   - Default reports
   - Interest reports

## Security Considerations

1. Role-based access (Admin, Treasurer, Member)
2. Transaction audit trails
3. Approval workflows
4. Reconciliation checks
5. Permission-based data access
