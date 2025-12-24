<?php

namespace App\Http\Controllers\Admin;

use App\Models\Group;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GroupApprovalController extends Controller
{
    /**
     * Show admin dashboard with pending groups
     */
    public function dashboard()
    {
        $this->ensureIsAdmin();

        $pendingGroups = Group::where('approval_status', 'pending')
                             ->with('creator')
                             ->orderBy('created_at', 'asc')
                             ->paginate(10);

        $approvedCount = Group::where('approval_status', 'approved')->count();
        $rejectedCount = Group::where('approval_status', 'rejected')->count();
        $totalUsers = User::count();
        $totalGroups = Group::count();

        return view('admin.dashboard', compact(
            'pendingGroups',
            'approvedCount',
            'rejectedCount',
            'totalUsers',
            'totalGroups'
        ));
    }

    /**
     * Show group approval details
     */
    public function show(Group $group)
    {
        $this->ensureIsAdmin();

        $group->load('creator', 'members.user', 'loans', 'savings');

        return view('admin.group-detail', compact('group'));
    }

    /**
     * Approve a group
     */
    public function approve(Request $request, Group $group)
    {
        $this->ensureIsAdmin();

        if ($group->approval_status === 'approved') {
            return back()->with('warning', 'This group is already approved');
        }

        DB::beginTransaction();

        try {
            $group->update([
                'approval_status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);

            DB::commit();

            // TODO: Send notification to group creator

            return back()->with('success', "Group '{$group->name}' has been approved successfully!");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors('Failed to approve group: ' . $e->getMessage());
        }
    }

    /**
     * Reject a group
     */
    public function reject(Request $request, Group $group)
    {
        $this->ensureIsAdmin();

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        if ($group->approval_status !== 'pending') {
            return back()->with('warning', 'Only pending groups can be rejected');
        }

        DB::beginTransaction();

        try {
            $group->update([
                'approval_status' => 'rejected',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'rejection_reason' => $validated['rejection_reason'],
            ]);

            DB::commit();

            // TODO: Send notification to group creator with reason

            return redirect()->route('admin.dashboard')
                           ->with('success', "Group '{$group->name}' has been rejected.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors('Failed to reject group: ' . $e->getMessage());
        }
    }

    /**
     * List all users for admin oversight
     */
    public function users()
    {
        $this->ensureIsAdmin();

        $users = User::with('groups')
                    ->paginate(20);

        return view('admin.users', compact('users'));
    }

    /**
     * List all groups for admin oversight
     */
    public function groups()
    {
        $this->ensureIsAdmin();

        $groups = Group::with('creator', 'approver')
                      ->orderBy('approval_status', 'asc')
                      ->paginate(20);

        return view('admin.groups', compact('groups'));
    }

    /**
     * Ensure user is system admin
     */
    private function ensureIsAdmin()
    {
        if (!Auth::check() || !Auth::user()->is_admin) {
            abort(403, 'Unauthorized');
        }
    }
}
