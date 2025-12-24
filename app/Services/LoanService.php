<?php

namespace App\Services;

use App\Models\Loan;
use App\Models\LoanCharge;
use App\Models\GroupMember;
use App\Models\Transaction;
use App\Notifications\TransactionRecorded;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Notification;

class LoanService
{
    /**
     * Create a new loan for a member
     */
    public function createLoan(
        GroupMember $member,
        float $principal,
        float $monthlyCharge,
        int $durationMonths,
        ?string $notes = null
    ): Loan {
        $issuedDate = now();
        $maturityDate = $issuedDate->addMonths($durationMonths);

        $loan = Loan::create([
            'group_id' => $member->group_id,
            'member_id' => $member->id,
            'principal_amount' => $principal,
            'monthly_charge' => $monthlyCharge,
            'remaining_balance' => $principal,
            'duration_months' => $durationMonths,
            'months_paid' => 0,
            'total_charged' => 0,
            'total_principal_paid' => 0,
            'issued_at' => $issuedDate->toDateString(),
            'maturity_date' => $maturityDate->toDateString(),
            'status' => 'pending',
            'notes' => $notes,
        ]);

        // Generate loan charges schedule
        $this->generateLoanCharges($loan);

        return $loan;
    }

    /**
     * Generate monthly charges for a loan
     */
    public function generateLoanCharges(Loan $loan): void
    {
        $dueDate = Carbon::parse($loan->issued_at)->addMonth();

        for ($month = 1; $month <= $loan->duration_months; $month++) {
            LoanCharge::create([
                'loan_id' => $loan->id,
                'month_number' => $month,
                'charge_amount' => $loan->monthly_charge,
                'due_date' => $dueDate->toDateString(),
                'status' => 'pending',
                'amount_paid' => 0,
            ]);

            $dueDate = $dueDate->addMonth();
        }
    }

    /**
     * Approve a loan
     */
    public function approveLoan(Loan $loan): void
    {
        if ($loan->status !== 'pending') {
            throw new Exception('Only pending loans can be approved');
        }

        $loan->update(['status' => 'approved']);
    }

    /**
     * Disburse a loan (transfer funds to member)
     */
    public function disburseLoan(Loan $loan, string $paymentMethod = 'cash'): void
    {
        if (!in_array($loan->status, ['pending', 'approved'])) {
            throw new Exception('Only pending or approved loans can be disbursed');
        }

        // Create transaction for loan disbursement
        $transaction = Transaction::create([
            'group_id' => $loan->group_id,
            'member_id' => $loan->member_id,
            'type' => 'loan_disburse',
            'amount' => $loan->principal_amount,
            'balance_after' => 0, // To be calculated by transaction handler
            'description' => "Loan disbursement - {$loan->principal_amount}",
            'reference' => $loan->id,
            'transaction_date' => now()->toDateString(),
        ]);

        $loan->update(['status' => 'active', 'disbursed_at' => now()->toDateString()]);

        // Notify member of loan disbursement
        Notification::send($loan->member->user, new TransactionRecorded($transaction, 'loan_disbursement'));
    }

        $loan->update(['status' => 'active']);
    }

    /**
     * Record a loan payment
     */
    public function recordLoanPayment(
        Loan $loan,
        float $principalPaid = 0,
        float $chargesPaid = 0,
        string $paymentMethod = 'cash',
        ?string $notes = null
    ): void {
        if ($loan->status !== 'active') {
            throw new Exception('Loan is not active');
        }

        $totalPaid = $principalPaid + $chargesPaid;

        if ($totalPaid <= 0) {
            throw new Exception('Payment amount must be greater than 0');
        }

        // Record payment
        $loan->payments()->create([
            'principal_paid' => $principalPaid,
            'charges_paid' => $chargesPaid,
            'total_paid' => $totalPaid,
            'payment_date' => now()->toDateString(),
            'payment_method' => $paymentMethod,
            'notes' => $notes,
            'recorded_by' => auth()->id(),
        ]);

        // Update charges if charges are being paid
        if ($chargesPaid > 0) {
            $this->payLoanCharges($loan, $chargesPaid);
        }

        // Update loan balance
        $newBalance = max(0, $loan->remaining_balance - $principalPaid);

        $loan->update([
            'remaining_balance' => $newBalance,
            'total_principal_paid' => $loan->total_principal_paid + $principalPaid,
            'total_charged' => $loan->total_charged + $chargesPaid,
            'months_paid' => $loan->months_paid + 1,
        ]);

        // Check if loan is fully paid
        if ($newBalance <= 0) {
            $loan->update([
                'status' => 'completed',
                'paid_off_at' => now()->toDateString(),
            ]);
        }

        // Record transaction
        $transaction = Transaction::create([
            'group_id' => $loan->group_id,
            'member_id' => $loan->member_id,
            'type' => 'loan_payment',
            'amount' => $totalPaid,
            'description' => "Loan payment - Principal: {$principalPaid}, Charges: {$chargesPaid}",
            'reference' => $loan->id,
            'transaction_date' => now()->toDateString(),
        ]);

        // Update member's loan tracking
        $member = $loan->member;
        $member->update([
            'total_repaid' => $member->total_repaid + $totalPaid,
            'outstanding_loans' => max(0, $member->outstanding_loans - $principalPaid),
        ]);

        // Notify member of payment recorded
        Notification::send($member->user, new TransactionRecorded($transaction, 'loan_payment'));
    }

    /**
     * Pay loan charges
     */
    private function payLoanCharges(Loan $loan, float $amountToPayCharges): void
    {
        $charges = $loan->charges()
            ->whereIn('status', ['pending', 'overdue'])
            ->orderBy('due_date')
            ->get();

        $remainingAmount = $amountToPayCharges;

        foreach ($charges as $charge) {
            if ($remainingAmount <= 0) {
                break;
            }

            $chargeRemaining = $charge->getRemainingAmount();

            if ($chargeRemaining <= 0) {
                continue;
            }

            $payAmount = min($remainingAmount, $chargeRemaining);

            $charge->update([
                'amount_paid' => $charge->amount_paid + $payAmount,
                'status' => ($charge->amount_paid + $payAmount >= $charge->charge_amount) ? 'paid' : $charge->status,
                'paid_at' => now()->toDateString(),
            ]);

            $remainingAmount -= $payAmount;
        }
    }

    /**
     * Mark loan as defaulted
     */
    public function defaultLoan(Loan $loan, string $reason = ''): void
    {
        if ($loan->status === 'completed') {
            throw new Exception('Cannot default a completed loan');
        }

        $loan->update([
            'status' => 'defaulted',
            'notes' => ($loan->notes ? $loan->notes . "\n" : '') . "Defaulted: {$reason}",
        ]);
    }

    /**
     * Check and update overdue charges
     */
    public function updateOverdueCharges(): void
    {
        LoanCharge::where('status', 'pending')
            ->where('due_date', '<', now()->toDateString())
            ->update(['status' => 'overdue']);
    }

    /**
     * Get loan summary for reporting
     */
    public function getLoanSummary(Loan $loan): array
    {
        return [
            'id' => $loan->id,
            'principal' => $loan->principal_amount,
            'monthly_charge' => $loan->monthly_charge,
            'duration_months' => $loan->duration_months,
            'remaining_balance' => $loan->remaining_balance,
            'total_principal_paid' => $loan->total_principal_paid,
            'total_charges_paid' => $loan->total_charged,
            'total_cost' => $loan->getTotalLoanCost(),
            'payment_progress' => $loan->getPaymentProgress(),
            'status' => $loan->status,
            'issued_at' => $loan->issued_at,
            'maturity_date' => $loan->maturity_date,
            'paid_off_at' => $loan->paid_off_at,
            'is_overdue' => $loan->isOverdue(),
            'next_due_date' => $loan->getNextDueDate(),
            'outstanding_charges' => $loan->getTotalOutstandingCharges(),
        ];
    }
}
