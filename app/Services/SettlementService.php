<?php

namespace App\Services;

use App\Models\Group;
use App\Models\SettlementPeriod;
use App\Models\Settlement;
use App\Models\GroupMember;
use App\Models\Loan;
use App\Models\Saving;
use App\Models\Penalty;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;

class SettlementService
{
    /**
     * Create a new settlement period for a group
     */
    public function createSettlementPeriod(
        Group $group,
        string $periodName,
        Carbon $startDate,
        Carbon $endDate,
        ?float $savingsTarget = null,
        ?string $notes = null
    ): SettlementPeriod {
        return SettlementPeriod::create([
            'group_id' => $group->id,
            'period_name' => $periodName,
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'status' => 'active',
            'total_savings_target' => $savingsTarget,
            'notes' => $notes,
        ]);
    }

    /**
     * Generate settlements for all active members in a period
     */
    public function generateSettlements(SettlementPeriod $period): void
    {
        $group = $period->group;
        $activeMembers = $group->members()->where('status', 'active')->get();

        foreach ($activeMembers as $member) {
            $this->createMemberSettlement($period, $member);
        }

        // Update period totals
        $this->updatePeriodTotals($period);
    }

    /**
     * Create individual member settlement
     */
    public function createMemberSettlement(SettlementPeriod $period, GroupMember $member): Settlement
    {
        // Check if settlement already exists
        $existing = Settlement::where('settlement_period_id', $period->id)
            ->where('member_id', $member->id)
            ->first();

        if ($existing) {
            return $existing;
        }

        // Calculate member's financial position
        $breakdown = $this->calculateMemberSettlementBreakdown($period, $member);

        $settlement = Settlement::create([
            'settlement_period_id' => $period->id,
            'member_id' => $member->id,
            'original_savings' => $breakdown['savings'],
            'interest_earned' => $breakdown['interest'],
            'penalties_applied' => $breakdown['penalties'],
            'penalties_waived' => $breakdown['penalties_waived'],
            'total_due' => $breakdown['total_due'],
            'amount_paid' => 0,
            'amount_pending' => $breakdown['total_due'],
            'status' => 'pending',
            'due_date' => $period->end_date,
            'notes' => $breakdown['notes'] ?? null,
        ]);

        return $settlement;
    }

    /**
     * Calculate settlement breakdown for a member
     */
    public function calculateMemberSettlementBreakdown(SettlementPeriod $period, GroupMember $member): array
    {
        // Get savings during period
        $savings = $this->getSavingsDuringPeriod($member, $period);

        // Get interest earned from loans given to others
        $interest = $this->getInterestEarnedDuringPeriod($member, $period);

        // Get active penalties (not waived)
        $penalties = $this->getPenaltiesDuringPeriod($member, $period);

        $totalDue = $savings + $interest + ($penalties['applied'] - $penalties['waived']);

        return [
            'savings' => $savings,
            'interest' => $interest,
            'penalties' => $penalties['applied'],
            'penalties_waived' => $penalties['waived'],
            'total_due' => max(0, $totalDue),
            'notes' => $this->generateSettlementNotes($savings, $interest, $penalties),
        ];
    }

    /**
     * Get total savings during a settlement period
     */
    private function getSavingsDuringPeriod(GroupMember $member, SettlementPeriod $period): float
    {
        $saving = $member->savings()->first();

        if (!$saving) {
            return 0;
        }

        // Count deposits during period
        return $saving->transactions()
            ->where('type', 'savings_deposit')
            ->whereBetween('transaction_date', [
                $period->start_date->toDateString(),
                $period->end_date->toDateString(),
            ])
            ->sum('amount');
    }

    /**
     * Get interest earned from loans given to other members during period
     */
    private function getInterestEarnedDuringPeriod(GroupMember $member, SettlementPeriod $period): float
    {
        // If this member took loans, we need to know how much interest
        // they generated through those loans if the system works that way
        // OR if they gave personal loans to others (more likely for a group)

        // Get all loans issued to this member during period
        $loansIssued = Loan::where('member_id', $member->id)
            ->where('group_id', $member->group_id)
            ->whereBetween('issued_at', [
                $period->start_date->toDateString(),
                $period->end_date->toDateString(),
            ])
            ->get();

        $totalInterest = 0;

        foreach ($loansIssued as $loan) {
            // Sum charges/interest paid during period
            $interestPaid = $loan->charges()
                ->where('status', 'paid')
                ->whereBetween('paid_at', [
                    $period->start_date->toDateString(),
                    $period->end_date->toDateString(),
                ])
                ->sum('amount_paid');

            $totalInterest += $interestPaid;
        }

        return $totalInterest;
    }

    /**
     * Get penalties for a member during period
     */
    private function getPenaltiesDuringPeriod(GroupMember $member, SettlementPeriod $period): array
    {
        $penalties = Penalty::where('member_id', $member->id)
            ->whereBetween('applied_at', [
                $period->start_date->toDateString(),
                $period->end_date->toDateString(),
            ])
            ->get();

        $applied = 0;
        $waived = 0;

        foreach ($penalties as $penalty) {
            if ($penalty->waived) {
                $waived += $penalty->amount;
            } else {
                $applied += $penalty->amount;
            }
        }

        return [
            'applied' => $applied,
            'waived' => $waived,
        ];
    }

    /**
     * Generate settlement notes summary
     */
    private function generateSettlementNotes(?float $savings, ?float $interest, ?array $penalties): string
    {
        $notes = [];

        if ($savings > 0) {
            $notes[] = "Savings: {$savings}";
        }

        if ($interest > 0) {
            $notes[] = "Interest earned: {$interest}";
        }

        if ($penalties && $penalties['applied'] > 0) {
            $notes[] = "Penalties: {$penalties['applied']}";
        }

        if ($penalties && $penalties['waived'] > 0) {
            $notes[] = "Penalties waived: {$penalties['waived']}";
        }

        return implode(" | ", $notes);
    }

    /**
     * Update settlement period totals
     */
    public function updatePeriodTotals(SettlementPeriod $period): void
    {
        $settlements = $period->settlements;

        $totalSavings = $settlements->sum('original_savings');
        $totalInterest = $settlements->sum('interest_earned');
        $totalPenalties = $settlements->sum('penalties_applied');
        $totalDue = $settlements->sum('total_due');

        $period->update([
            'total_savings_collected' => $totalSavings,
            'total_interest_earned' => $totalInterest,
            'total_penalties_applied' => $totalPenalties,
            'total_settlement_amount' => $totalDue,
        ]);
    }

    /**
     * Close a settlement period (prevent new settlements but allow payments)
     */
    public function closeSettlementPeriod(SettlementPeriod $period): void
    {
        if ($period->status !== 'active') {
            throw new Exception('Only active settlement periods can be closed');
        }

        $period->update(['status' => 'closed']);
    }

    /**
     * Finalize a settlement period (lock all settlements)
     */
    public function finalizeSettlementPeriod(SettlementPeriod $period): void
    {
        if ($period->status !== 'closed') {
            throw new Exception('Only closed settlement periods can be finalized');
        }

        // Check if all settlements are at least partially paid
        $unpaidCount = $period->settlements()
            ->where('status', 'pending')
            ->count();

        if ($unpaidCount > 0) {
            throw new Exception("Cannot finalize: {$unpaidCount} settlements are still pending payment");
        }

        $period->update([
            'status' => 'finalized',
            'finalized_at' => now(),
        ]);
    }

    /**
     * Record a settlement payment
     */
    public function recordSettlementPayment(
        Settlement $settlement,
        float $amount,
        string $paymentMethod,
        ?string $reference = null,
        ?string $notes = null,
        ?int $recordedByUserId = null
    ): void {
        if ($amount <= 0) {
            throw new Exception('Payment amount must be greater than 0');
        }

        if ($amount > $settlement->amount_pending) {
            throw new Exception("Payment amount exceeds pending amount of {$settlement->amount_pending}");
        }

        $settlement->recordPayment($amount, $paymentMethod, $reference, $notes, $recordedByUserId);
    }

    /**
     * Get settlement summary for a group
     */
    public function getGroupSettlementSummary(Group $group, ?SettlementPeriod $period = null): array
    {
        $query = SettlementPeriod::where('group_id', $group->id);

        if ($period) {
            $query->where('id', $period->id);
        } else {
            // Get latest settlement period
            $query->latest('created_at');
        }

        $latestPeriod = $query->first();

        if (!$latestPeriod) {
            return [
                'status' => 'no_active_period',
                'message' => 'No settlement period found for this group',
            ];
        }

        $summary = $latestPeriod->getSettlementSummary();

        return [
            'period' => [
                'id' => $latestPeriod->id,
                'name' => $latestPeriod->period_name,
                'status' => $latestPeriod->status,
                'start_date' => $latestPeriod->start_date,
                'end_date' => $latestPeriod->end_date,
                'finalized_at' => $latestPeriod->finalized_at,
            ],
            'summary' => $summary,
        ];
    }

    /**
     * Get member's settlement statement
     */
    public function getMemberSettlementStatement(GroupMember $member, SettlementPeriod $period): ?array
    {
        $settlement = Settlement::where('settlement_period_id', $period->id)
            ->where('member_id', $member->id)
            ->first();

        if (!$settlement) {
            return null;
        }

        return [
            'member' => [
                'id' => $member->id,
                'name' => $member->user->name,
                'phone' => $member->user->phone ?? 'N/A',
            ],
            'period' => [
                'name' => $period->period_name,
                'start_date' => $period->start_date,
                'end_date' => $period->end_date,
            ],
            'breakdown' => $settlement->getBreakdown(),
            'payments' => $settlement->payments()
                ->orderBy('payment_date', 'desc')
                ->get()
                ->map(fn($p) => [
                    'amount' => $p->amount,
                    'date' => $p->payment_date,
                    'method' => $p->payment_method,
                    'reference' => $p->reference,
                ])
                ->toArray(),
            'status' => $settlement->status,
            'is_overdue' => $settlement->isOverdue(),
            'days_overdue' => $settlement->getDaysOverdue(),
        ];
    }

    /**
     * Validate settlement data before finalization
     */
    public function validatePeriodSettlements(SettlementPeriod $period): array
    {
        $issues = [];
        $settlements = $period->settlements;

        foreach ($settlements as $settlement) {
            if ($settlement->total_due > 0 && $settlement->amount_paid == 0) {
                $issues[] = [
                    'member' => $settlement->member->user->name,
                    'issue' => 'No payment recorded',
                    'amount_due' => $settlement->total_due,
                ];
            }

            if ($settlement->isOverdue() && $settlement->amount_pending > 0) {
                $issues[] = [
                    'member' => $settlement->member->user->name,
                    'issue' => 'Overdue settlement',
                    'days_overdue' => $settlement->getDaysOverdue(),
                    'amount_pending' => $settlement->amount_pending,
                ];
            }
        }

        return $issues;
    }
}
