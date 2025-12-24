<?php

namespace App\Services;

use App\Models\GroupMember;
use App\Models\Saving;
use App\Models\Transaction;
use App\Notifications\TransactionRecorded;
use Illuminate\Support\Facades\Notification;

class SavingsService
{
    /**
     * Initialize savings account for a member
     */
    public function initializeSavings(GroupMember $member): Saving
    {
        return Saving::firstOrCreate(
            [
                'group_id' => $member->group_id,
                'member_id' => $member->id,
            ],
            [
                'current_balance' => 0,
                'total_deposits' => 0,
                'total_withdrawals' => 0,
                'interest_earned' => 0,
            ]
        );
    }

    /**
     * Add funds to member's savings
     */
    public function deposit(GroupMember $member, float $amount, string $description = 'Savings deposit'): void
    {
        $savings = $this->getSavings($member);
        $savings->deposit($amount, $description);

        // Create transaction record
        $transaction = Transaction::create([
            'group_id' => $member->group_id,
            'member_id' => $member->id,
            'type' => 'savings_deposit',
            'amount' => $amount,
            'description' => $description,
            'reference' => $savings->id,
            'transaction_date' => now()->toDateString(),
        ]);

        // Notify member of deposit
        Notification::send($member->user, new TransactionRecorded($transaction, 'savings_deposit'));
    }

    /**
     * Withdraw funds from member's savings
     */
    public function withdraw(GroupMember $member, float $amount, string $description = 'Savings withdrawal'): bool
    {
        $savings = $this->getSavings($member);
        $success = $savings->withdraw($amount, $description);

        if ($success) {
            // Create transaction record
            $transaction = Transaction::create([
                'group_id' => $member->group_id,
                'member_id' => $member->id,
                'type' => 'savings_withdrawal',
                'amount' => $amount,
                'description' => $description,

        // Create transaction record
        $transaction = Transaction::create([
            'group_id' => $member->group_id,
            'member_id' => $member->id,
            'type' => 'savings_interest',
            'amount' => $amount,
            'description' => 'Interest added to savings',
            'reference' => $savings->id,
            'transaction_date' => now()->toDateString(),
        ]);

        // Notify member of interest added
        Notification::send($member->user, new TransactionRecorded($transaction, 'savings_interest'));
                'reference' => $savings->id,
                'transaction_date' => now()->toDateString(),
            ]);

            // Notify member of withdrawal
            Notification::send($member->user, new TransactionRecorded($transaction, 'savings_withdrawal'));
        }

        return $success;
    }

    /**
     * Add interest to member's savings
     */
    public function addInterest(GroupMember $member, float $amount): void
    {
        $savings = $this->getSavings($member);
        $savings->addInterest($amount);
    }

    /**
     * Get member's savings account
     */
    public function getSavings(GroupMember $member): Saving
    {
        return $member->savings()->firstOr(function () use ($member) {
            return $this->initializeSavings($member);
        });
    }

    /**
     * Get savings summary
     */
    public function getSavingsSummary(GroupMember $member): array
    {
        $savings = $this->getSavings($member);

        return [
            'current_balance' => $savings->current_balance,
            'total_deposits' => $savings->total_deposits,
            'total_withdrawals' => $savings->total_withdrawals,
            'interest_earned' => $savings->interest_earned,
            'last_deposit_date' => $savings->last_deposit_date,
            'last_withdrawal_date' => $savings->last_withdrawal_date,
            'transaction_count' => $savings->transactions()->count(),
        ];
    }

    /**
     * Get all group members' total savings
     */
    public function getGroupTotalSavings($group): float
    {
        return Saving::where('group_id', $group->id)->sum('current_balance');
    }
}
