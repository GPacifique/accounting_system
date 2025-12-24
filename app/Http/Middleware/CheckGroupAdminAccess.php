<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\GroupMember;

class CheckGroupAdminAccess
{
    /**
     * Handle an incoming request.
     * Ensures user has admin role in at least one group
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Redirect system admins to admin dashboard
        if (Auth::user()->is_admin) {
            return redirect()->route('admin.dashboard');
        }

        // Check if user is a group admin
        $groupAdmin = GroupMember::where('user_id', Auth::user()->id)
            ->where('role', 'admin')
            ->where('status', 'active')
            ->first();

        if (!$groupAdmin) {
            return redirect()->route('member.dashboard')
                ->with('warning', 'You do not have admin access to any group.');
        }

        return $next($request);
    }
}
