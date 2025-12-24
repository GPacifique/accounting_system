<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\GroupMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GroupController extends Controller
{
    /**
     * Display the group detail page.
     */
    public function show(Group $group)
    {
        // User access verified by middleware

        $activeLoanCount = $group->activeLoanCount();
        $totalSavingsBalance = $group->getTotalSavingsBalance();
        $memberCount = $group->members()->where('status', 'active')->count();

        return view('groups.show', compact('group', 'activeLoanCount', 'totalSavingsBalance', 'memberCount'));
    }

    /**
     * Show the form for editing the group.
     */
    public function edit(Group $group)
    {
        // Only admins can edit group
        if (!Auth::user()->isGroupAdmin($group)) {
            abort(403);
        }

        return view('groups.edit', compact('group'));
    }

    /**
     * Update the group in storage.
     */
    public function update(Request $request, Group $group)
    {
        // Only admins can update group
        if (!Auth::user()->isGroupAdmin($group)) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'registration_fee' => 'nullable|numeric|min:0',
            'loan_approval_required' => 'boolean',
            'status' => 'in:active,inactive',
        ]);

        $group->update($validated);

        return redirect()->route('groups.show', $group)
                       ->with('success', 'Group updated successfully.');
    }

    /**
     * Display group members list.
     */
    public function members(Group $group)
    {
        $members = $group->members()
                        ->with('user')
                        ->where('status', 'active')
                        ->paginate(15);

        return view('groups.members', compact('group', 'members'));
    }

    /**
     * Add a member to the group.
     */
    public function addMember(Request $request, Group $group)
    {
        // Only admins and treasurers can add members
        if (!Auth::user()->isGroupAdmin($group) && !Auth::user()->isGroupTreasurer($group)) {
            abort(403);
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|in:admin,treasurer,member',
        ]);

        // Check if already a member
        $existingMember = GroupMember::where('group_id', $group->id)
                                    ->where('user_id', $validated['user_id'])
                                    ->first();

        if ($existingMember) {
            return back()->with('error', 'User is already a member of this group.');
        }

        GroupMember::create([
            'group_id' => $group->id,
            'user_id' => $validated['user_id'],
            'role' => $validated['role'],
            'status' => 'active',
            'joined_at' => now(),
        ]);

        return back()->with('success', 'Member added successfully.');
    }

    /**
     * Remove a member from the group.
     */
    public function removeMember(Group $group, GroupMember $member)
    {
        // Only admins can remove members
        if (!Auth::user()->isGroupAdmin($group)) {
            abort(403);
        }

        if ($member->group_id !== $group->id) {
            abort(404);
        }

        $member->update([
            'status' => 'inactive',
            'left_at' => now(),
        ]);

        return back()->with('success', 'Member removed from group.');
    }
}
