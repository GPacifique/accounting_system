<?php

namespace App\Services;

use App\Models\Group;
use App\Models\GroupMember;
use Carbon\Carbon;

class ReportingService
{
    /**
     * Get complete financial summary for a group
     */
    public function getGroupFinancialSummary(Group $group): array
    {
        $loans = $group->loans();
        $activeLoans = $loans->where('status', 'active');
        $completedLoans = $loans->where('status', 'completed');
        $defaultedLoans = $loans->where('status', 'defaulted');

        $totalSavings = $group->members()->sum('current_savings');
        $totalOutstandingLoans = $group->members()->sum('outstanding_loans');

        return [
            'group_name' => $group->name,
            'total_members' => $group->members()->where('status', 'active')->count(),
            'total_savings' => $totalSavings,
            'savings_distribution' => [
                'total_contributed' => $group->members()->sum('total_contributed'),
                'total_withdrawn' => $group->members()->sum('total_withdrawn'),
            ],
            'loans' => [
                'total_issued' => $loans->sum('principal_amount'),
                'active_loans' => $activeLoans->count(),
                'active_amount' => $activeLoans->sum('remaining_balance'),
                'completed_loans' => $completedLoans->count(),
                'completed_amount' => $completedLoans->sum('principal_amount'),
                'defaulted_loans' => $defaultedLoans->count(),
                'defaulted_amount' => $defaultedLoans->sum('remaining_balance'),
            ],
            'charges' => [
                'total_earned' => $group->total_interest_earned,
                'pending' => $group->loans()->with('charges')
                    ->get()
                    ->flatMap(fn($loan) => $loan->charges)
                    ->where('status', 'pending')
                    ->sum('charge_amount'),
                'overdue' => $group->loans()->with('charges')
                    ->get()
                    ->flatMap(fn($loan) => $loan->charges)
                    ->where('status', 'overdue')
                    ->sum('charge_amount'),
            ],
        ];
    }

    /**
     * Get member detailed statement
     */
    public function getMemberStatement(GroupMember $member, ?Carbon $from = null, ?Carbon $to = null): array
    {
        $from = $from ?? now()->subMonths(12);
        $to = $to ?? now();

        $transactions = $member->transactions()
            ->whereBetween('transaction_date', [$from->toDateString(), $to->toDateString()])
            ->orderBy('transaction_date', 'desc')
            ->get();

        $savings = $member->savings()->first();
        $loans = $member->loans()->where('status', '!=', 'pending')->get();

        return [
            'member_name' => $member->user->name ?? 'N/A',
            'member_id' => $member->id,
            'joined_at' => $member->joined_at,
            'role' => $member->role,
            'status' => $member->status,
            'savings' => [
                'current_balance' => $savings?->current_balance ?? 0,
                'total_contributed' => $savings?->total_deposits ?? 0,
                'total_withdrawn' => $savings?->total_withdrawals ?? 0,
                'interest_earned' => $savings?->interest_earned ?? 0,
            ],
            'loans' => [
                'total_borrowed' => $member->total_borrowed,
                'total_repaid' => $member->total_repaid,
                'outstanding' => $member->outstanding_loans,
                'active_count' => $loans->where('status', 'active')->count(),
            ],
            'recent_transactions' => $transactions->map(fn($t) => [
                'date' => $t->transaction_date,
                'type' => $t->type,
                'amount' => $t->amount,
                'balance_after' => $t->balance_after,
                'description' => $t->description,
            ])->toArray(),
        ];
    }

    /**
     * Get loan default report
     */
    public function getDefaultReport(Group $group): array
    {
        $defaultedLoans = $group->loans()
            ->where('status', 'defaulted')
            ->with('member.user')
            ->get();

        $overdueCharges = $group->loans()
            ->where('status', 'active')
            ->with(['charges' => function ($q) {
                $q->where('status', 'overdue');
            }])
            ->get();

        return [
            'defaulted_loans' => $defaultedLoans->map(fn($loan) => [
                'loan_id' => $loan->id,
                'member_name' => $loan->member->user->name,
                'principal' => $loan->principal_amount,
                'outstanding_balance' => $loan->remaining_balance,
                'issued_at' => $loan->issued_at,
                'notes' => $loan->notes,
            ])->toArray(),
            'overdue_charges' => collect($overdueCharges)
                ->flatMap(fn($loan) => $loan->charges->map(fn($charge) => [
                    'member_name' => $loan->member->user->name,
                    'loan_id' => $loan->id,
                    'month' => $charge->month_number,
                    'due_date' => $charge->due_date,
                    'amount' => $charge->charge_amount,
                    'days_overdue' => $charge->getDaysOverdue(),
                ]))
                ->toArray(),
        ];
    }

    /**
     * Get loan performance metrics
     */
    public function getLoanMetrics(Group $group): array
    {
        $loans = $group->loans()->where('status', '!=', 'pending')->get();
        $totalLoans = $loans->count();

        if ($totalLoans === 0) {
            return [
                'repayment_rate' => 0,
                'default_rate' => 0,
                'completion_rate' => 0,
                'average_loan_amount' => 0,
                'total_interest_earned' => 0,
            ];
        }

        $completedLoans = $loans->where('status', 'completed')->count();
        $defaultedLoans = $loans->where('status', 'defaulted')->count();
        $activeLoans = $loans->where('status', 'active')->count();

        return [
            'total_loans' => $totalLoans,
            'active_loans' => $activeLoans,
            'completed_loans' => $completedLoans,
            'defaulted_loans' => $defaultedLoans,
            'repayment_rate' => round(($completedLoans / $totalLoans) * 100, 2) . '%',
            'default_rate' => round(($defaultedLoans / $totalLoans) * 100, 2) . '%',
            'completion_rate' => round(($completedLoans / $totalLoans) * 100, 2) . '%',
            'average_loan_amount' => round($loans->avg('principal_amount'), 2),
            'total_amount_loaned' => $loans->sum('principal_amount'),
            'total_interest_earned' => $group->total_interest_earned,
        ];
    }
}
