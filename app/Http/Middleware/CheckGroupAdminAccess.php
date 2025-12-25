<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\GroupMember;

/**
 * CheckGroupAdminAccess Middleware
 *
 * Validates that user has group admin access and redirects to appropriate dashboard
 * if they don't. Respects role hierarchy:
 * - System Admin → Redirect to admin dashboard
 * - No Group Admin Role → Redirect to member dashboard
 */
class CheckGroupAdminAccess
{
    /**
     * Handle an incoming request.
     * Ensures user has admin role in at least one group
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // TIER 1: System Admin - Should use admin routes instead
        if ($user->is_admin) {
            return redirect()->route('admin.dashboard')
                ->with('info', 'You have system admin access. Use the admin dashboard.');
        }

        // TIER 2: Check if user is a group admin
        $groupAdmin = GroupMember::where('user_id', $user->id)
            ->where('role', 'admin')
            ->where('status', 'active')
            ->with('group')
            ->first();

        if (!$groupAdmin) {
            // User is not a group admin
            $member = GroupMember::where('user_id', $user->id)
                ->whereIn('status', ['active', 'inactive'])
                ->first();

            if ($member) {
                // User is a regular member
                return redirect()->route('member.dashboard')
                    ->with('warning', 'You do not have admin access to any group. Showing member dashboard instead.');
            } else {
                // User is not in any group
                return redirect()->route('dashboard')
                    ->with('error', 'You are not a member of any group.');
            }
        }

        // Store group admin info in request for use in controllers
        $request->merge([
            'group_admin' => $groupAdmin,
            'admin_group_id' => $groupAdmin->group_id,
        ]);

        return $next($request);
    }
}
