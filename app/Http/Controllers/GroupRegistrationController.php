<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\GroupMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GroupRegistrationController extends Controller
{
    /**
     * Show group registration form
     */
    public function create()
    {
        return view('groups.register');
    }

    /**
     * Store new group registration (pending approval)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:groups',
            'description' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();

        try {
            // Create group in pending status
            $group = Group::create([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'created_by' => Auth::id(),
                'status' => 'active', // Can operate but needs approval
                'approval_status' => 'pending', // Awaiting admin approval
            ]);

            // Assign creator as group admin
            GroupMember::create([
                'group_id' => $group->id,
                'user_id' => Auth::id(),
                'role' => 'admin',
                'status' => 'active',
                'joined_at' => now(),
            ]);

            DB::commit();

            return redirect()->route('groups.show', $group)
                           ->with('success', 'Group registered successfully! It is awaiting system admin approval.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors('Failed to create group: ' . $e->getMessage());
        }
    }

    /**
     * Show group admin's member management page
     */
    public function manageMembers(Group $group)
    {
        // Verify user is group admin
        if (!Auth::user()->isGroupAdmin($group)) {
            abort(403, 'Only group admin can manage members');
        }

        $members = $group->members()
                        ->with('user')
                        ->where('status', 'active')
                        ->paginate(15);

        $pendingCount = $group->members()
                             ->where('status', 'pending')
                             ->count();

        return view('groups.manage-members', compact('group', 'members', 'pendingCount'));
    }

    /**
     * Create new member account by group admin
     */
    public function createMemberAccount(Request $request, Group $group)
    {
        // Verify user is group admin
        if (!Auth::user()->isGroupAdmin($group)) {
            abort(403, 'Only group admin can create members');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        DB::beginTransaction();

        try {
            // Create user (no auth, just created for group)
            $user = \App\Models\User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => bcrypt($validated['password']),
            ]);

            // Add as group member with role
            GroupMember::create([
                'group_id' => $group->id,
                'user_id' => $user->id,
                'role' => 'member',
                'status' => 'active',
                'joined_at' => now(),
            ]);

            DB::commit();

            return back()->with('success', "Member '{$user->name}' created successfully!");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors('Failed to create member: ' . $e->getMessage());
        }
    }

    /**
     * Group admin adds existing user to group
     */
    public function addExistingMember(Request $request, Group $group)
    {
        // Verify user is group admin
        if (!Auth::user()->isGroupAdmin($group)) {
            abort(403, 'Only group admin can manage members');
        }

        $validated = $request->validate([
            'email' => 'required|string|email|exists:users,email',
            'role' => 'required|in:member,treasurer',
        ]);

        $user = \App\Models\User::where('email', $validated['email'])->first();

        // Check if already a member
        if ($group->members()->where('user_id', $user->id)->exists()) {
            return back()->with('error', 'User is already a member of this group');
        }

        GroupMember::create([
            'group_id' => $group->id,
            'user_id' => $user->id,
            'role' => $validated['role'],
            'status' => 'active',
            'joined_at' => now(),
        ]);

        return back()->with('success', "Member '{$user->name}' added successfully!");
    }
}
