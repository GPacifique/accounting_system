<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyGroupRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();
        $group = $request->route('group');

        if (!$user || !$group) {
            abort(403, 'Unauthorized');
        }

        // Check if user belongs to the group
        if (!$user->belongsToGroup($group)) {
            abort(403, 'You do not have access to this group.');
        }

        // Check if user has one of the required roles
        $userRole = $user->getGroupRole($group);

        if (!in_array($userRole, $roles)) {
            abort(403, 'You do not have permission to perform this action.');
        }

        return $next($request);
    }
}
