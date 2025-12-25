<?php

namespace App\Services;

use App\Models\GroupMember;
use App\Models\Loan;
use App\Models\Penalty;
use Carbon\Carbon;
use Exception;

class PenaltyService
{
    /**
     * Apply penalty to a member
     */
    public function applyPenalty(
        GroupMember $member,
        string $type,
        float $amount,
        string $reason,
        ?Loan $loan = null
    ): Penalty {
        if ($amount <= 0) {
            throw new Exception('Penalty amount must be greater than 0');
        }

        return Penalty::create([
            'member_id' => $member->id,
            'group_id' => $member->group_id,
            'loan_id' => $loan?->id,
            'type' => $type,
            'amount' => $amount,
            'reason' => $reason,
            'waived' => false,
            'applied_at' => now(),
        ]);
    }

    /**
     * Apply late payment penalty
     */
    public function applyLatePaymentPenalty(
        Loan $loan,
        float $penaltyAmount,
        ?string $customReason = null
    ): Penalty {
        $member = $loan->member;
        $reason = $customReason ?? "Late payment on loan #{$loan->id}";

        return $this->applyPenalty(
            member: $member,
            type: 'late_payment',
            amount: $penaltyAmount,
            reason: $reason,
            loan: $loan
        );
    }

    /**
     * Apply rule violation penalty
     */
    public function applyViolationPenalty(
        GroupMember $member,
        float $penaltyAmount,
        string $violationDescription
    ): Penalty {
        return $this->applyPenalty(
            member: $member,
            type: 'violation',
            amount: $penaltyAmount,
            reason: "Rule violation: {$violationDescription}"
        );
    }

    /**
     * Apply default penalty (failed to repay loan)
     */
    public function applyDefaultPenalty(
        Loan $loan,
        float $penaltyAmount,
        ?string $customReason = null
    ): Penalty {
        $member = $loan->member;
        $reason = $customReason ?? "Loan default - principal not repaid on loan #{$loan->id}";

        return $this->applyPenalty(
            member: $member,
            type: 'default',
            amount: $penaltyAmount,
            reason: $reason,
            loan: $loan
        );
    }

    /**
     * Waive a penalty
     */
    public function waivePenalty(
        Penalty $penalty,
        string $reason,
        int $waivedByUserId
    ): void {
        if ($penalty->waived) {
            throw new Exception('This penalty is already waived');
        }

        $penalty->waive($reason, $waivedByUserId);
    }

    /**
     * Reverse a penalty waiver
     */
    public function reverseWaiver(Penalty $penalty): void
    {
        if (!$penalty->waived) {
            throw new Exception('This penalty is not waived');
        }

        $penalty->update([
            'waived' => false,
            'waived_reason' => null,
            'waived_at' => null,
            'waived_by' => null,
        ]);
    }

    /**
     * Check for automatic late payment penalties
     */
    public function checkAndApplyLatePaymentPenalties(Loan $loan, float $penaltyAmount): void
    {
        // Get overdue charges
        $overdueCharges = $loan->charges()
            ->where('status', 'overdue')
            ->get();

        if ($overdueCharges->isEmpty()) {
            return;
        }

        // Check if penalty already exists for this loan
        $existing = Penalty::where('loan_id', $loan->id)
            ->where('type', 'late_payment')
            ->where('waived', false)
            ->exists();

        if (!$existing) {
            $this->applyLatePaymentPenalty($loan, $penaltyAmount);
        }
    }

    /**
     * Get member's active penalties
     */
    public function getActivePenalties(GroupMember $member): \Illuminate\Database\Eloquent\Collection
    {
        return $member->penalties()
            ->where('waived', false)
            ->orderBy('applied_at', 'desc')
            ->get();
    }

    /**
     * Get member's total active penalty amount
     */
    public function getTotalActivePenalties(GroupMember $member): float
    {
        return $member->penalties()
            ->where('waived', false)
            ->sum('amount');
    }

    /**
     * Get member's penalty history
     */
    public function getPenaltyHistory(GroupMember $member): array
    {
        $penalties = Penalty::where('member_id', $member->id)
            ->orderBy('applied_at', 'desc')
            ->get();

        $history = [
            'total_applied' => 0,
            'total_waived' => 0,
            'total_active' => 0,
            'count_by_type' => [],
            'penalties' => [],
        ];

        foreach ($penalties as $penalty) {
            $history['total_applied'] += $penalty->amount;

            if ($penalty->waived) {
                $history['total_waived'] += $penalty->amount;
            } else {
                $history['total_active'] += $penalty->amount;
            }

            $type = $penalty->type;
            if (!isset($history['count_by_type'][$type])) {
                $history['count_by_type'][$type] = 0;
            }
            $history['count_by_type'][$type]++;

            $history['penalties'][] = [
                'id' => $penalty->id,
                'type' => $penalty->type,
                'amount' => $penalty->amount,
                'reason' => $penalty->reason,
                'applied_at' => $penalty->applied_at,
                'waived' => $penalty->waived,
                'waived_reason' => $penalty->waived_reason,
                'waived_at' => $penalty->waived_at,
                'loan_id' => $penalty->loan_id,
            ];
        }

        return $history;
    }

    /**
     * Get group-wide penalty report
     */
    public function getGroupPenaltyReport(\App\Models\Group $group): array
    {
        $penalties = Penalty::where('group_id', $group->id)
            ->get();

        $report = [
            'total_penalties_applied' => 0,
            'total_penalties_waived' => 0,
            'total_penalties_active' => 0,
            'count_by_type' => [],
            'members_with_penalties' => [],
        ];

        foreach ($penalties as $penalty) {
            $report['total_penalties_applied'] += $penalty->amount;

            if ($penalty->waived) {
                $report['total_penalties_waived'] += $penalty->amount;
            } else {
                $report['total_penalties_active'] += $penalty->amount;
            }

            $type = $penalty->type;
            if (!isset($report['count_by_type'][$type])) {
                $report['count_by_type'][$type] = [
                    'count' => 0,
                    'amount' => 0,
                ];
            }
            $report['count_by_type'][$type]['count']++;
            $report['count_by_type'][$type]['amount'] += $penalty->amount;
        }

        // Group by member
        $memberPenalties = $penalties->groupBy('member_id');
        foreach ($memberPenalties as $memberId => $memberPens) {
            $member = GroupMember::find($memberId);
            $total = $memberPens->sum('amount');
            $active = $memberPens->where('waived', false)->sum('amount');

            $report['members_with_penalties'][] = [
                'member_id' => $member->id,
                'member_name' => $member->user->name,
                'total_penalties' => $total,
                'active_penalties' => $active,
                'count' => $memberPens->count(),
            ];
        }

        return $report;
    }
}
