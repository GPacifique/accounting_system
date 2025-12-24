<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckGroupAssignment
{
    /**
     * Handle an incoming request.
     * Ensures user is assigned to at least one group before accessing dashboard
     */
    public function handle(Request $request, Closure $next)
    {
        // If user is not authenticated, let them pass (will be redirected by auth middleware)
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        // Get user's groups
        $groups = $user->groupMembers()->where('status', 'active')->get();

        // If user has no active groups, redirect to group selection/creation
        if ($groups->isEmpty()) {
            return redirect()->route('groups.select')
                ->with('warning', 'You are not assigned to any active groups. Please join or create a group.');
        }

        // If user is in multiple groups, check if they have selected one
        if ($groups->count() > 1) {
            $currentGroupId = session('current_group_id');

            // Check if the selected group belongs to the user
            if (!$currentGroupId || !$groups->pluck('group_id')->contains($currentGroupId)) {
                // Auto-select the first group if no group is selected
                session(['current_group_id' => $groups->first()->group_id]);
            }
        } else {
            // User is in only one group, set it as current
            session(['current_group_id' => $groups->first()->group_id]);
        }

        return $next($request);
    }
}
