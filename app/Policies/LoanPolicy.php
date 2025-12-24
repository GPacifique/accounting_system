<?php

namespace App\Policies;

use App\Models\Loan;
use App\Models\User;

class LoanPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Loan $loan): bool
    {
        // User can view if:
        // 1. It's their own loan
        // 2. They are group admin/treasurer

        if ($user->id === $loan->member->user_id) {
            return true;
        }

        return $user->isGroupAdmin($loan->group_id) ||
               $user->isGroupTreasurer($loan->group_id);
    }

    /**
     * Determine whether the user can create a loan.
     */
    public function create(User $user, $group): bool
    {
        // Any group member can request a loan
        return $user->belongsToGroup($group);
    }

    /**
     * Determine whether the user can record payment.
     */
    public function recordPayment(User $user, Loan $loan): bool
    {
        // Only own loan or group admin/treasurer
        if ($user->id === $loan->member->user_id) {
            return true;
        }

        return $user->isGroupAdmin($loan->group_id) ||
               $user->isGroupTreasurer($loan->group_id);
    }

    /**
     * Determine whether the user can approve loan.
     */
    public function approve(User $user, Loan $loan): bool
    {
        // Only admin/treasurer can approve
        return $user->isGroupAdmin($loan->group_id) ||
               $user->isGroupTreasurer($loan->group_id);
    }

    /**
     * Determine whether the user can disburse loan.
     */
    public function disburse(User $user, Loan $loan): bool
    {
        // Only admin/treasurer can disburse
        return $user->isGroupAdmin($loan->group_id) ||
               $user->isGroupTreasurer($loan->group_id);
    }

    /**
     * Determine whether the user can mark loan as default.
     */
    public function markDefault(User $user, Loan $loan): bool
    {
        // Only admin can mark default
        return $user->isGroupAdmin($loan->group_id);
    }
}
