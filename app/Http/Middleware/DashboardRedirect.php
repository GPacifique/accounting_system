<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\GroupMember;

/**
 * DashboardRedirect Middleware
 *
 * Intelligently redirects users to the appropriate dashboard based on their role
 * and access level. This middleware supports RBAC (Role-Based Access Control)
 * with three-tier hierarchy:
 *
 * TIER 1 (Highest): System Admin (is_admin=true)
 *   → Redirects to: /admin/dashboard
 *   → Access: Full system control
 *
 * TIER 2 (Mid): Group Admin (role='admin' in any active group)
 *   → Redirects to: /group-admin/dashboard
 *   → Access: Manage assigned groups
 *
 * TIER 3 (Basic): Regular Member (member in one or more groups)
 *   → Redirects to: /member/dashboard
 *   → Access: View-only own financial records
 */
class DashboardRedirect
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user) {
            return $next($request);
        }

        // Get the current route name
        $currentRoute = $request->route()?->getName();

        // Skip redirect if already on a dashboard route
        if ($this->isOnDashboardRoute($currentRoute)) {
            return $next($request);
        }

        // TIER 1: System Admin - Highest Priority
        if ($user->is_admin) {
            return $this->redirectIfNotOnRoute($request, 'admin.dashboard', '/admin/dashboard');
        }

        // TIER 2: Group Admin - Mid Priority
        $groupAdmin = $this->getUserGroupAdminRole($user);
        if ($groupAdmin) {
            return $this->redirectIfNotOnRoute($request, 'group-admin.*', '/group-admin/dashboard');
        }

        // TIER 3: Regular Member - Lowest Priority
        $member = $this->getUserGroupMemberRole($user);
        if ($member) {
            return $this->redirectIfNotOnRoute($request, 'member.*', '/member/dashboard');
        }

        // No role found - continue with normal request
        return $next($request);
    }

    /**
     * Check if user is a group admin
     * Returns the GroupMember record if user is admin of at least one group
     */
    private function getUserGroupAdminRole($user)
    {
        return GroupMember::where('user_id', $user->id)
            ->where('role', 'admin')
            ->where('status', 'active')
            ->with('group')
            ->first();
    }

    /**
     * Check if user is a group member
     * Returns the GroupMember record if user is member of at least one group
     */
    private function getUserGroupMemberRole($user)
    {
        return GroupMember::where('user_id', $user->id)
            ->whereIn('role', ['member', 'treasurer'])
            ->where('status', 'active')
            ->with('group')
            ->first();
    }

    /**
     * Check if current route is a dashboard route
     * Prevents infinite redirect loops
     */
    private function isOnDashboardRoute(?string $route): bool
    {
        $dashboardRoutes = [
            'admin.dashboard',
            'group-admin.dashboard',
            'member.dashboard',
            'dashboard',
        ];

        if (!$route) {
            return false;
        }

        foreach ($dashboardRoutes as $dashRoute) {
            if ($route === $dashRoute || str_starts_with($route, $dashRoute . '.')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Redirect if not on specified route
     */
    private function redirectIfNotOnRoute(Request $request, string $routePattern, string $redirectPath): Response
    {
        $currentRoute = $request->route()?->getName();

        // Check if current route matches pattern
        if ($this->routeMatches($currentRoute, $routePattern)) {
            // Already on correct route, continue
            return app()->make('pipe')->handle($request, []);
        }

        // Not on correct route, redirect
        return redirect($redirectPath);
    }

    /**
     * Check if route name matches pattern (supports wildcards)
     */
    private function routeMatches(?string $route, string $pattern): bool
    {
        if (!$route) {
            return false;
        }

        // Convert pattern with * to regex
        $regexPattern = str_replace('*', '.*', preg_quote($pattern, '/'));
        return preg_match("/^{$regexPattern}$/", $route) === 1;
    }
}
