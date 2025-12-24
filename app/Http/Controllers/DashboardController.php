<?php

namespace App\Http\Controllers;

use App\Models\GroupMember;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class DashboardController extends Controller
{
    /**
     * Route user to appropriate dashboard based on their role
     * TIER 1: System Admin (is_admin=true) → Admin Dashboard (full system access)
     * TIER 2: Group Admin (admin role in group) → Group Admin Dashboard (manage single group)
     * TIER 3: Regular Member → Member Dashboard (view-only own records)
     */
    public function index()
    {
        $user = Auth::user();

        // TIER 1: System Admin - Full system access
        if ($user->is_admin) {
            return redirect()->route('admin.dashboard');
        }

        // TIER 2: Check if user is admin of any group
        $groupAdmin = GroupMember::where('user_id', $user->id)
            ->where('role', 'admin')
            ->where('status', 'active')
            ->with('group')
            ->first();

        if ($groupAdmin) {
            return redirect()->route('group-admin.dashboard');
        }

        // TIER 3: Regular group member - View-only access
        return redirect()->route('member.dashboard');
    }
}
