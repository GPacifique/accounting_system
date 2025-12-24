<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifyGroupAccess
{
    /**
     * Verify user has access to the requested group
     */
    public function handle(Request $request, Closure $next)
    {
        $groupId = $request->route('group')?->id;

        // If no group in route, allow (not a group-specific request)
        if (!$groupId) {
            return $next($request);
        }

        $user = auth()->user();

        // Check if user is a member of this group
        $isMember = $user->groupMembers()
            ->where('group_id', $groupId)
            ->where('status', 'active')
            ->exists();

        if (!$isMember) {
            return response()->view('errors.403', [], 403);
        }

        return $next($request);
    }
}
