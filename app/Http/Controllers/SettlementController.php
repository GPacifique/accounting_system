<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\SettlementPeriod;
use App\Models\Settlement;
use App\Models\GroupMember;
use App\Services\SettlementService;
use App\Services\PenaltyService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SettlementController extends Controller
{
    public function __construct(
        protected SettlementService $settlementService,
        protected PenaltyService $penaltyService,
    ) {}

    /**
     * Get all settlement periods for a group
     */
    public function index(Group $group)
    {
        $this->authorize('view', $group);

        $periods = $group->settlementPeriods()
            ->orderBy('start_date', 'desc')
            ->paginate(20);

        return response()->json([
            'periods' => $periods,
        ]);
    }

    /**
     * Create a new settlement period
     */
    public function store(Request $request, Group $group)
    {
        $this->authorize('update', $group);

        $validated = $request->validate([
            'period_name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'total_savings_target' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        $period = $this->settlementService->createSettlementPeriod(
            group: $group,
            periodName: $validated['period_name'],
            startDate: Carbon::parse($validated['start_date']),
            endDate: Carbon::parse($validated['end_date']),
            savingsTarget: $validated['total_savings_target'] ? (float)$validated['total_savings_target'] : null,
            notes: $validated['notes'] ?? null
        );

        return response()->json([
            'message' => 'Settlement period created successfully',
            'period' => $period,
        ], 201);
    }

    /**
     * Get settlement period details
     */
    public function show(Group $group, SettlementPeriod $period)
    {
        if ($period->group_id !== $group->id) {
            return response()->json(['error' => 'Period not found'], 404);
        }

        $this->authorize('view', $group);

        $summary = $period->getSettlementSummary();

        return response()->json([
            'period' => $period,
            'summary' => $summary,
            'settlements' => $period->settlements()->with('member.user')->paginate(20),
        ]);
    }

    /**
     * Generate settlements for period
     */
    public function generateSettlements(Group $group, SettlementPeriod $period)
    {
        if ($period->group_id !== $group->id) {
            return response()->json(['error' => 'Period not found'], 404);
        }

        $this->authorize('update', $group);

        // Check if already generated
        if ($period->settlements()->exists()) {
            return response()->json([
                'message' => 'Settlements already generated for this period',
                'count' => $period->settlements()->count(),
            ]);
        }

        try {
            $this->settlementService->generateSettlements($period);

            return response()->json([
                'message' => 'Settlements generated successfully',
                'count' => $period->settlements()->count(),
                'summary' => $period->getSettlementSummary(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Close settlement period
     */
    public function close(Group $group, SettlementPeriod $period)
    {
        if ($period->group_id !== $group->id) {
            return response()->json(['error' => 'Period not found'], 404);
        }

        $this->authorize('update', $group);

        try {
            $this->settlementService->closeSettlementPeriod($period);

            return response()->json([
                'message' => 'Settlement period closed successfully',
                'period' => $period,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Finalize settlement period
     */
    public function finalize(Group $group, SettlementPeriod $period)
    {
        if ($period->group_id !== $group->id) {
            return response()->json(['error' => 'Period not found'], 404);
        }

        $this->authorize('update', $group);

        try {
            // Validate before finalizing
            $issues = $this->settlementService->validatePeriodSettlements($period);

            if (!empty($issues)) {
                return response()->json([
                    'error' => 'Cannot finalize: unresolved settlement issues',
                    'issues' => $issues,
                ], 422);
            }

            $this->settlementService->finalizeSettlementPeriod($period);

            return response()->json([
                'message' => 'Settlement period finalized successfully',
                'period' => $period,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Get member's settlement details
     */
    public function showMemberSettlement(Group $group, SettlementPeriod $period, GroupMember $member)
    {
        if ($period->group_id !== $group->id || $member->group_id !== $group->id) {
            return response()->json(['error' => 'Not found'], 404);
        }

        $statement = $this->settlementService->getMemberSettlementStatement($member, $period);

        if (!$statement) {
            return response()->json(['error' => 'Settlement not found for this member'], 404);
        }

        return response()->json($statement);
    }

    /**
     * Record settlement payment
     */
    public function recordPayment(Request $request, Group $group, SettlementPeriod $period, Settlement $settlement)
    {
        if ($period->group_id !== $group->id || $settlement->settlement_period_id !== $period->id) {
            return response()->json(['error' => 'Not found'], 404);
        }

        $this->authorize('update', $group);

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|max:50',
            'reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $this->settlementService->recordSettlementPayment(
                settlement: $settlement,
                amount: (float)$validated['amount'],
                paymentMethod: $validated['payment_method'],
                reference: $validated['reference'] ?? null,
                notes: $validated['notes'] ?? null,
                recordedByUserId: auth()->id()
            );

            return response()->json([
                'message' => 'Payment recorded successfully',
                'settlement' => $settlement->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Get group settlement summary
     */
    public function groupSummary(Group $group)
    {
        $this->authorize('view', $group);

        $summary = $this->settlementService->getGroupSettlementSummary($group);

        return response()->json($summary);
    }

    /**
     * Get settlement validation report
     */
    public function validationReport(Group $group, SettlementPeriod $period)
    {
        if ($period->group_id !== $group->id) {
            return response()->json(['error' => 'Period not found'], 404);
        }

        $this->authorize('view', $group);

        $issues = $this->settlementService->validatePeriodSettlements($period);

        return response()->json([
            'issues_count' => count($issues),
            'issues' => $issues,
            'can_finalize' => empty($issues),
        ]);
    }
}
