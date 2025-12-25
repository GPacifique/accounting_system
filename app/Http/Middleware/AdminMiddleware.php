<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\GroupMember;

/**
 * AdminMiddleware
 *
 * Verifies user has system admin privileges (is_admin = true).
 * Redirects non-admins to their appropriate dashboard level:
 * - Group Admins → /group-admin/dashboard
 * - Regular Members → /member/dashboard
 */
class AdminMiddleware
{
    /**
     * Handle an incoming request.
     * Verifies user has admin privileges (is_admin = true)
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Check if user is system admin
        if ($user->is_admin) {
            // User is admin, allow access
            return $next($request);
        }

        // User is not system admin - redirect based on their role
        $groupAdmin = GroupMember::where('user_id', $user->id)
            ->where('role', 'admin')
            ->where('status', 'active')
            ->first();

        if ($groupAdmin) {
            // User is a group admin
            return redirect()->route('group-admin.dashboard')
                ->with('warning', 'You do not have system admin access. Showing group admin dashboard.');
        }

        // User is a regular member
        return redirect()->route('member.dashboard')
            ->with('error', 'You do not have permission to access admin panel. Showing member dashboard.');
    }
}
