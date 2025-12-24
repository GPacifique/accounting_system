<?php

namespace App\Policies;

use App\Models\Saving;
use App\Models\User;

class SavingPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Saving $saving): bool
    {
        // User can view if:
        // 1. It's their own savings account
        // 2. They are group admin/treasurer

        if ($user->id === $saving->member->user_id) {
            return true;
        }

        // Check if user is admin/treasurer of the group
        return $user->isGroupAdmin($saving->group_id) ||
               $user->isGroupTreasurer($saving->group_id);
    }

    /**
     * Determine whether the user can deposit.
     */
    public function deposit(User $user, Saving $saving): bool
    {
        // Only own account or group admin/treasurer
        if ($user->id === $saving->member->user_id) {
            return true;
        }

        return $user->isGroupAdmin($saving->group_id) ||
               $user->isGroupTreasurer($saving->group_id);
    }

    /**
     * Determine whether the user can withdraw.
     */
    public function withdraw(User $user, Saving $saving): bool
    {
        // Only own account or group admin/treasurer
        if ($user->id === $saving->member->user_id) {
            return true;
        }

        return $user->isGroupAdmin($saving->group_id) ||
               $user->isGroupTreasurer($saving->group_id);
    }

    /**
     * Determine whether the user can add interest.
     */
    public function addInterest(User $user, Saving $saving): bool
    {
        // Only admin/treasurer can add interest
        return $user->isGroupAdmin($saving->group_id) ||
               $user->isGroupTreasurer($saving->group_id);
    }

    /**
     * Determine whether the user can view member statement.
     */
    public function viewStatement(User $user, User $member, Saving $saving): bool
    {
        // Member can view own statement
        if ($user->id === $member->id) {
            return true;
        }

        // Admin/treasurer can view any member's statement
        return $user->isGroupAdmin($saving->group_id) ||
               $user->isGroupTreasurer($saving->group_id);
    }
}
