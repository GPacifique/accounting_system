<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\GroupMember;
use App\Models\Penalty;
use App\Models\Loan;
use App\Services\PenaltyService;
use Illuminate\Http\Request;

class PenaltyController extends Controller
{
    public function __construct(
        protected PenaltyService $penaltyService,
    ) {}

    /**
     * Get all penalties for a member
     */
    public function memberPenalties(Group $group, GroupMember $member)
    {
        if ($member->group_id !== $group->id) {
            return response()->json(['error' => 'Member not found'], 404);
        }

        $this->authorize('view', $group);

        $activePenalties = $this->penaltyService->getActivePenalties($member);
        $history = $this->penaltyService->getPenaltyHistory($member);

        return response()->json([
            'active_penalties' => $activePenalties,
            'history' => $history,
        ]);
    }

    /**
     * Apply penalty to a member
     */
    public function store(Request $request, Group $group, GroupMember $member)
    {
        if ($member->group_id !== $group->id) {
            return response()->json(['error' => 'Member not found'], 404);
        }

        $this->authorize('update', $group);

        $validated = $request->validate([
            'type' => 'required|in:late_payment,violation,default,other',
            'amount' => 'required|numeric|min:0.01',
            'reason' => 'required|string|max:500',
            'loan_id' => 'nullable|exists:loans,id',
        ]);

        try {
            $loan = null;
            if ($validated['loan_id']) {
                $loan = Loan::findOrFail($validated['loan_id']);
                if ($loan->group_id !== $group->id) {
                    return response()->json(['error' => 'Loan not in this group'], 403);
                }
            }

            $penalty = $this->penaltyService->applyPenalty(
                member: $member,
                type: $validated['type'],
                amount: (float)$validated['amount'],
                reason: $validated['reason'],
                loan: $loan
            );

            return response()->json([
                'message' => 'Penalty applied successfully',
                'penalty' => $penalty,
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Waive a penalty
     */
    public function waive(Request $request, Group $group, GroupMember $member, Penalty $penalty)
    {
        if ($penalty->member_id !== $member->id || $member->group_id !== $group->id) {
            return response()->json(['error' => 'Penalty not found'], 404);
        }

        $this->authorize('update', $group);

        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        try {
            $this->penaltyService->waivePenalty(
                penalty: $penalty,
                reason: $validated['reason'],
                waivedByUserId: auth()->id()
            );

            return response()->json([
                'message' => 'Penalty waived successfully',
                'penalty' => $penalty->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Reverse penalty waiver
     */
    public function reverseWaiver(Group $group, GroupMember $member, Penalty $penalty)
    {
        if ($penalty->member_id !== $member->id || $member->group_id !== $group->id) {
            return response()->json(['error' => 'Penalty not found'], 404);
        }

        $this->authorize('update', $group);

        try {
            $this->penaltyService->reverseWaiver($penalty);

            return response()->json([
                'message' => 'Waiver reversed successfully',
                'penalty' => $penalty->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Get group penalty report
     */
    public function groupReport(Group $group)
    {
        $this->authorize('view', $group);

        $report = $this->penaltyService->getGroupPenaltyReport($group);

        return response()->json($report);
    }

    /**
     * Get all penalties for a group
     */
    public function groupPenalties(Group $group)
    {
        $this->authorize('view', $group);

        $penalties = Penalty::where('group_id', $group->id)
            ->with('member.user', 'loan')
            ->orderBy('applied_at', 'desc')
            ->paginate(20);

        return response()->json($penalties);
    }

    /**
     * Delete a penalty (only if not waived and recent)
     */
    public function destroy(Group $group, GroupMember $member, Penalty $penalty)
    {
        if ($penalty->member_id !== $member->id || $member->group_id !== $group->id) {
            return response()->json(['error' => 'Penalty not found'], 404);
        }

        $this->authorize('update', $group);

        if ($penalty->waived) {
            return response()->json(['error' => 'Cannot delete waived penalties'], 403);
        }

        // Only allow deletion within 24 hours of creation
        if ($penalty->created_at->diffInHours(now()) > 24) {
            return response()->json(['error' => 'Can only delete penalties within 24 hours of creation'], 403);
        }

        $penalty->delete();

        return response()->json(['message' => 'Penalty deleted successfully']);
    }
}
