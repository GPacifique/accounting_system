<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\GroupMember;
use App\Services\SavingsService;
use App\Services\ReportingService;
use Illuminate\Http\Request;

class SavingsController extends Controller
{
    public function __construct(
        protected SavingsService $savingsService,
        protected ReportingService $reportingService,
    ) {}

    /**
     * Get savings for all group members
     */
    public function index(Group $group)
    {
        $this->authorize('view', $group);

        $savings = $group->savings()
            ->with('member.user')
            ->paginate(20);

        return response()->json([
            'savings' => $savings,
            'total_group_savings' => $this->savingsService->getGroupTotalSavings($group),
        ]);
    }

    /**
     * Get member's savings details
     */
    public function show(Group $group, GroupMember $member)
    {
        if ($member->group_id !== $group->id) {
            return response()->json(['error' => 'Member not found'], 404);
        }

        $this->authorize('view', $member);

        $savings = $this->savingsService->getSavings($member);
        $summary = $this->savingsService->getSavingsSummary($member);

        return response()->json([
            'savings' => $savings,
            'summary' => $summary,
            'transactions' => $savings->transactions()
                ->orderBy('transaction_date', 'desc')
                ->limit(20)
                ->get(),
        ]);
    }

    /**
     * Record a deposit
     */
    public function deposit(Request $request, Group $group, GroupMember $member)
    {
        if ($member->group_id !== $group->id) {
            return response()->json(['error' => 'Member not found'], 404);
        }

        $this->authorize('update', $group);

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:255',
        ]);

        try {
            $this->savingsService->deposit(
                member: $member,
                amount: (float)$validated['amount'],
                description: $validated['description'] ?? 'Savings deposit'
            );

            $summary = $this->savingsService->getSavingsSummary($member);

            return response()->json([
                'message' => 'Deposit recorded successfully',
                'savings' => $this->savingsService->getSavings($member),
                'summary' => $summary,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Record a withdrawal
     */
    public function withdraw(Request $request, Group $group, GroupMember $member)
    {
        if ($member->group_id !== $group->id) {
            return response()->json(['error' => 'Member not found'], 404);
        }

        $this->authorize('update', $group);

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:255',
        ]);

        try {
            $success = $this->savingsService->withdraw(
                member: $member,
                amount: (float)$validated['amount'],
                description: $validated['description'] ?? 'Savings withdrawal'
            );

            if (!$success) {
                return response()->json([
                    'error' => 'Insufficient balance for withdrawal'
                ], 400);
            }

            $summary = $this->savingsService->getSavingsSummary($member);

            return response()->json([
                'message' => 'Withdrawal recorded successfully',
                'savings' => $this->savingsService->getSavings($member),
                'summary' => $summary,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Add interest to member's savings
     */
    public function addInterest(Request $request, Group $group, GroupMember $member)
    {
        if ($member->group_id !== $group->id) {
            return response()->json(['error' => 'Member not found'], 404);
        }

        $this->authorize('update', $group);

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
        ]);

        try {
            $this->savingsService->addInterest(
                member: $member,
                amount: (float)$validated['amount']
            );

            return response()->json([
                'message' => 'Interest added successfully',
                'savings' => $this->savingsService->getSavings($member),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Get member statement
     */
    public function memberStatement(Request $request, Group $group, GroupMember $member)
    {
        if ($member->group_id !== $group->id) {
            return response()->json(['error' => 'Member not found'], 404);
        }

        $this->authorize('view', $member);

        $from = $request->input('from') ? \Carbon\Carbon::parse($request->input('from')) : null;
        $to = $request->input('to') ? \Carbon\Carbon::parse($request->input('to')) : null;

        $statement = $this->reportingService->getMemberStatement($member, $from, $to);

        return response()->json($statement);
    }
}
