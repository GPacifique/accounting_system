<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\GroupMember;
use App\Models\Loan;
use App\Models\Saving;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;

class GroupAdminDashboardController extends Controller
{
    /**
     * Show the group admin dashboard
     * Only accessible to members with admin role in their group
     */
    public function index()
    {
        $user = Auth::user();

        // Get the group where user is admin
        $group = GroupMember::where('user_id', $user->id)
            ->where('role', 'admin')
            ->where('status', 'active')
            ->with('group')
            ->first()
            ->group ?? null;

        if (!$group) {
            return redirect()->route('dashboard')
                ->with('error', 'You must be a group admin to access this dashboard.');
        }

        // Get active members with their loan and savings info
        $members = $group->members()
            ->where('status', 'active')
            ->with(['user', 'loans', 'savings'])
            ->get();

        // Get group statistics
        $stats = [
            'total_members' => $members->count(),
            'active_loans' => $group->loans()->where('status', 'active')->count(),
            'total_loans' => $group->loans()->count(),
            'total_loan_amount' => $group->loans()->sum('principal_amount'),
            'total_savings_balance' => $group->savings()->get()->sum('balance'),
            'overdue_loans' => $group->loans()->where('status', 'active')->where('maturity_date', '<', now())->count(),
            'pending_charges' => $group->loans()->get()->sum(function($loan) {
                return optional($loan->pendingCharges())->sum('charge_amount') ?? 0;
            }),
        ];

        // Get loans with upcoming deadlines (next 30 days)
        $upcoming_loans = $group->loans()
            ->where('status', 'active')
            ->where('maturity_date', '>', now())
            ->where('maturity_date', '<=', now()->addDays(30))
            ->with(['member.user', 'charges'])
            ->orderBy('maturity_date')
            ->get();

        // Get overdue loans
        $overdue_loans = $group->loans()
            ->where('status', 'active')
            ->where('maturity_date', '<', now())
            ->with(['member.user', 'charges'])
            ->orderBy('maturity_date')
            ->get();

        // Get recent savings with member info
        $recent_savings = $group->savings()
            ->with(['member.user'])
            ->orderBy('updated_at', 'desc')
            ->take(5)
            ->get();

        // Get recent loans with member info
        $recent_loans = $group->loans()
            ->with(['member.user'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Get member details with deadline info
        $member_details = $members->map(function($member) {
            return [
                'member' => $member,
                'user' => $member->user,
                'active_loans' => $member->loans->where('status', 'active')->count(),
                'total_loan_amount' => $member->loans->where('status', 'active')->sum('principal_amount'),
                'savings_balance' => $member->savings->sum('balance'),
                'upcoming_deadline' => $member->loans()
                    ->where('status', 'active')
                    ->where('maturity_date', '>', now())
                    ->orderBy('maturity_date')
                    ->value('maturity_date'),
                'has_overdue' => $member->loans()
                    ->where('status', 'active')
                    ->where('maturity_date', '<', now())
                    ->exists(),
            ];
        });

        return view('dashboards.group-admin', compact(
            'group',
            'stats',
            'members',
            'member_details',
            'upcoming_loans',
            'overdue_loans',
            'recent_loans',
            'recent_savings'
        ));
    }

    /**
     * Show all loans for the group
     */
    public function loans(Group $group)
    {
        $this->authorizeGroupAdmin($group);

        $query = $group->loans()->with('member');

        // Search functionality
        if (request('search')) {
            $search = request('search');
            $query->whereHas('member.user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $loans = $query->paginate(15);

        return view('dashboards.group-loans', compact('group', 'loans'));
    }

    /**
     * Show all savings for the group
     */
    public function savings(Group $group)
    {
        $this->authorizeGroupAdmin($group);

        $query = $group->savings()->with('member');

        // Search functionality
        if (request('search')) {
            $search = request('search');
            $query->whereHas('member.user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $savings = $query->paginate(15);

        return view('dashboards.group-savings', compact('group', 'savings'));
    }

    /**
     * Show group members management
     */
    public function members(Group $group)
    {
        $this->authorizeGroupAdmin($group);

        $query = $group->members()->with('user');

        // Search functionality
        if (request('search')) {
            $search = request('search');
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $members = $query->paginate(15);

        return view('dashboards.group-members', compact('group', 'members'));
    }

    /**
     * Show group transactions
     */
    public function transactions(Group $group)
    {
        $this->authorizeGroupAdmin($group);

        $query = Transaction::where('group_id', $group->id)
            ->with('createdByUser', 'group', 'member');

        // Search functionality
        if (request('search')) {
            $search = request('search');
            $query->whereHas('member.user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })
            ->orWhere('type', 'like', "%{$search}%")
            ->orWhere('description', 'like', "%{$search}%")
            ->orWhere('reference', 'like', "%{$search}%");
        }

        $transactions = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('dashboards.group-transactions', compact('group', 'transactions'));
    }

    /**
     * Show group financial reports
     */
    public function reports(Group $group)
    {
        $this->authorizeGroupAdmin($group);

        $stats = [
            'total_loans' => $group->loans()->sum('principal_amount'),
            'total_principal_paid' => $group->loans()->sum('total_principal_paid'),
            'outstanding' => ($group->loans()->sum('principal_amount') ?? 0) - ($group->loans()->sum('total_principal_paid') ?? 0),
            'total_savings' => $group->savings()->sum('current_balance'),
            'total_members' => $group->members()->where('status', 'active')->count(),
            'active_loans' => $group->loans()->where('status', 'active')->count(),
        ];

        return view('dashboards.group-reports', compact('group', 'stats'));
    }

    /**
     * Show form to record member savings
     */
    public function recordSavings(Group $group)
    {
        $this->authorizeGroupAdmin($group);

        $members = $group->members()
            ->where('status', 'active')
            ->with('user')
            ->get();

        return view('dashboards.group-record-savings', compact('group', 'members'));
    }

    /**
     * Store member savings record
     */
    public function storeSavings(Group $group)
    {
        $this->authorizeGroupAdmin($group);

        $validated = request()->validate([
            'member_id' => 'required|exists:group_members,id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:255',
            'transaction_date' => 'nullable|date',
        ]);

        // Verify member belongs to group
        $member = GroupMember::where('id', $validated['member_id'])
            ->where('group_id', $group->id)
            ->first();

        if (!$member) {
            return back()->with('error', 'Member does not belong to this group.');
        }

        // Get or create saving for this member
        $saving = Saving::firstOrCreate(
            ['group_id' => $group->id, 'member_id' => $member->id],
            [
                'current_balance' => 0,
                'total_deposits' => 0,
            ]
        );

        // Update saving with new deposit
        $saving->increment('current_balance', $validated['amount']);
        $saving->increment('total_deposits', $validated['amount']);
        $saving->update(['last_deposit_date' => $validated['transaction_date'] ?? now()]);

        // Record transaction
        Transaction::create([
            'group_id' => $group->id,
            'member_id' => $member->id,
            'type' => 'deposit',
            'amount' => $validated['amount'],
            'balance_after' => $saving->current_balance,
            'description' => $validated['description'] ?? 'Savings deposit',
            'created_by' => Auth::id(),
            'transaction_date' => $validated['transaction_date'] ?? now(),
        ]);

        return redirect()->route('group-admin.savings', $group)
            ->with('success', 'Savings recorded successfully.');
    }

    /**
     * Show form to record loan interest
     */
    public function recordInterest(Group $group)
    {
        $this->authorizeGroupAdmin($group);

        $loans = $group->loans()
            ->where('status', 'active')
            ->with(['member.user'])
            ->get();

        return view('dashboards.group-record-interest', compact('group', 'loans'));
    }

    /**
     * Store loan interest record
     */
    public function storeInterest(Group $group)
    {
        $this->authorizeGroupAdmin($group);

        $validated = request()->validate([
            'loan_id' => 'required|exists:loans,id',
            'interest_amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:255',
            'transaction_date' => 'nullable|date',
        ]);

        // Verify loan belongs to group
        $loan = Loan::where('id', $validated['loan_id'])
            ->where('group_id', $group->id)
            ->first();

        if (!$loan) {
            return back()->with('error', 'Loan does not belong to this group.');
        }

        // Increment total charged (interest)
        $loan->increment('total_charged', $validated['interest_amount']);

        // Record transaction
        Transaction::create([
            'group_id' => $group->id,
            'member_id' => $loan->member_id,
            'type' => 'interest',
            'amount' => $validated['interest_amount'],
            'balance_after' => $loan->remaining_balance,
            'description' => $validated['description'] ?? 'Loan interest charge',
            'reference' => 'loan_' . $loan->id,
            'created_by' => Auth::id(),
            'transaction_date' => $validated['transaction_date'] ?? now(),
        ]);

        return redirect()->route('group-admin.loans', $group)
            ->with('success', 'Interest recorded successfully.');
    }

    /**
     * Edit group information
     */
    public function editGroup(Group $group)
    {
        $this->authorizeGroupAdmin($group);

        return view('dashboards.group-edit', compact('group'));
    }

    /**
     * Update group information
     */
    public function updateGroup(Group $group)
    {
        $this->authorizeGroupAdmin($group);

        $validated = request()->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $group->update($validated);

        return redirect()->route('group-admin.dashboard')
            ->with('success', 'Group information updated successfully.');
    }

    /**
     * Add member to group
     */
    public function addMember(Group $group)
    {
        $this->authorizeGroupAdmin($group);

        $validated = request()->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|in:member,treasurer,secretary',
        ]);

        // Check if user is already in group
        $existing = GroupMember::where('group_id', $group->id)
            ->where('user_id', $validated['user_id'])
            ->first();

        if ($existing) {
            return back()->with('error', 'User is already a member of this group.');
        }

        GroupMember::create([
            'group_id' => $group->id,
            'user_id' => $validated['user_id'],
            'role' => $validated['role'],
            'status' => 'active',
            'joined_at' => now(),
        ]);

        return redirect()->route('group-admin.manage-members', $group)
            ->with('success', 'Member added successfully.');
    }

    /**
     * Update member role
     */
    public function updateMemberRole(Group $group, GroupMember $member)
    {
        $this->authorizeGroupAdmin($group);

        if ($member->group_id !== $group->id) {
            return back()->with('error', 'Member does not belong to this group.');
        }

        // Cannot demote own admin role
        if ($member->user_id === Auth::id() && request('role') !== 'admin') {
            return back()->with('error', 'You cannot demote your own admin role.');
        }

        $validated = request()->validate([
            'role' => 'required|in:member,treasurer,secretary,admin',
        ]);

        $member->update(['role' => $validated['role']]);

        return back()->with('success', 'Member role updated successfully.');
    }

    /**
     * Remove member from group
     */
    public function removeMember(Group $group, GroupMember $member)
    {
        $this->authorizeGroupAdmin($group);

        if ($member->group_id !== $group->id) {
            return back()->with('error', 'Member does not belong to this group.');
        }

        // Cannot remove self
        if ($member->user_id === Auth::id()) {
            return back()->with('error', 'You cannot remove yourself from the group.');
        }

        $member->update([
            'status' => 'inactive',
            'left_at' => now(),
        ]);

        return back()->with('success', 'Member removed from group.');
    }

    /**
     * Authorize that user is admin of the group
     */
    private function authorizeGroupAdmin(Group $group)
    {
        $user = Auth::user();

        $isAdmin = GroupMember::where('user_id', $user->id)
            ->where('group_id', $group->id)
            ->where('role', 'admin')
            ->where('status', 'active')
            ->exists();

        if (!$isAdmin) {
            abort(403, 'You are not authorized to manage this group.');
        }
    }
}
