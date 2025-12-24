<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\GroupMember;
use App\Models\Loan;
use App\Services\LoanService;
use App\Services\SavingsService;
use App\Services\ReportingService;
use Illuminate\Http\Request;

class LoanController extends Controller
{
    public function __construct(
        protected LoanService $loanService,
        protected SavingsService $savingsService,
        protected ReportingService $reportingService,
    ) {}

    /**
     * Get all loans for a group
     */
    public function index(Group $group)
    {
        $this->authorize('view', $group);

        $loans = $group->loans()
            ->with('member.user')
            ->paginate(20);

        return response()->json([
            'loans' => $loans,
            'summary' => $this->reportingService->getGroupFinancialSummary($group),
        ]);
    }

    /**
     * Create a new loan for a member
     */
    public function store(Request $request, Group $group)
    {
        $this->authorize('update', $group);

        $validated = $request->validate([
            'member_id' => 'required|exists:group_members,id',
            'principal_amount' => 'required|numeric|min:100',
            'monthly_charge' => 'required|numeric|min:0',
            'duration_months' => 'required|integer|min:1|max:60',
            'notes' => 'nullable|string|max:500',
        ]);

        $member = GroupMember::findOrFail($validated['member_id']);

        if ($member->group_id !== $group->id) {
            return response()->json(['error' => 'Member does not belong to this group'], 403);
        }

        $loan = $this->loanService->createLoan(
            member: $member,
            principal: (float)$validated['principal_amount'],
            monthlyCharge: (float)$validated['monthly_charge'],
            durationMonths: (int)$validated['duration_months'],
            notes: $validated['notes'] ?? null
        );

        return response()->json([
            'message' => 'Loan created successfully',
            'loan' => $loan->load('member.user', 'charges'),
        ], 201);
    }

    /**
     * Get loan details
     */
    public function show(Group $group, Loan $loan)
    {
        if ($loan->group_id !== $group->id) {
            return response()->json(['error' => 'Loan not found'], 404);
        }

        $summary = $this->loanService->getLoanSummary($loan);

        return response()->json([
            'loan' => $loan->load('member.user', 'charges', 'payments'),
            'summary' => $summary,
        ]);
    }

    /**
     * Approve a pending loan
     */
    public function approve(Request $request, Group $group, Loan $loan)
    {
        $this->authorize('update', $group);

        if ($loan->group_id !== $group->id) {
            return response()->json(['error' => 'Loan not found'], 404);
        }

        try {
            $this->loanService->approveLoan($loan);

            return response()->json([
                'message' => 'Loan approved successfully',
                'loan' => $loan,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Disburse loan (give funds to member)
     */
    public function disburse(Request $request, Group $group, Loan $loan)
    {
        $this->authorize('update', $group);

        if ($loan->group_id !== $group->id) {
            return response()->json(['error' => 'Loan not found'], 404);
        }

        try {
            $this->loanService->disburseLoan($loan);

            return response()->json([
                'message' => 'Loan disbursed successfully',
                'loan' => $loan,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Record a payment for a loan
     */
    public function recordPayment(Request $request, Group $group, Loan $loan)
    {
        $this->authorize('update', $group);

        if ($loan->group_id !== $group->id) {
            return response()->json(['error' => 'Loan not found'], 404);
        }

        $validated = $request->validate([
            'principal_paid' => 'required|numeric|min:0',
            'charges_paid' => 'required|numeric|min:0',
            'payment_method' => 'required|string|max:50',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $this->loanService->recordLoanPayment(
                loan: $loan,
                principalPaid: (float)$validated['principal_paid'],
                chargesPaid: (float)$validated['charges_paid'],
                paymentMethod: $validated['payment_method'],
                notes: $validated['notes'] ?? null
            );

            return response()->json([
                'message' => 'Payment recorded successfully',
                'loan' => $loan->fresh(),
                'summary' => $this->loanService->getLoanSummary($loan),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Mark loan as defaulted
     */
    public function markDefault(Request $request, Group $group, Loan $loan)
    {
        $this->authorize('update', $group);

        if ($loan->group_id !== $group->id) {
            return response()->json(['error' => 'Loan not found'], 404);
        }

        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            $this->loanService->defaultLoan($loan, $validated['reason'] ?? '');

            return response()->json([
                'message' => 'Loan marked as defaulted',
                'loan' => $loan,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Get loan statistics for group
     */
    public function statistics(Group $group)
    {
        $this->authorize('view', $group);

        return response()->json(
            $this->reportingService->getLoanMetrics($group)
        );
    }

    /**
     * Get default report
     */
    public function defaultReport(Group $group)
    {
        $this->authorize('view', $group);

        return response()->json(
            $this->reportingService->getDefaultReport($group)
        );
    }
}
