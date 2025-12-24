<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Group;
use App\Models\Loan;
use App\Models\Saving;
use App\Models\Transaction;
use App\Models\GroupMember;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    /**
     * Show the admin dashboard with system overview
     */
    public function index(): View
    {
        // Only system admins can access
        if (!auth()->user()->is_admin) {
            abort(403, 'Unauthorized access');
        }

        $stats = [
            'total_users' => User::count(),
            'total_groups' => Group::count(),
            'active_groups' => Group::where('status', 'active')->count(),
            'total_members' => GroupMember::count(),
            'total_loans' => Loan::count(),
            'active_loans' => Loan::where('status', 'active')->count(),
            'total_savings' => Saving::count(),
            'total_transactions' => Transaction::count(),
            'loan_amount_total' => Loan::sum('principal_amount') ?? 0,
            'savings_amount_total' => Saving::sum('balance') ?? 0,
        ];

        $recent_users = User::latest()->take(5)->get();
        $recent_groups = Group::latest()->take(5)->get();
        $recent_loans = Loan::latest()->take(5)->with('member')->get();
        $recent_savings = Saving::latest()->take(5)->with('member')->get();
        $recent_transactions = Transaction::latest()->take(10)->with('user')->get();

        return view('admin.dashboard', compact('stats', 'recent_users', 'recent_groups', 'recent_loans', 'recent_savings', 'recent_transactions'));
    }

    /**
     * Show users management page
     */
    public function users(): View
    {
        if (!auth()->user()->is_admin) {
            abort(403, 'Unauthorized access');
        }

        $users = User::paginate(20);
        return view('admin.users.index', compact('users'));
    }

    /**
     * Show user details and edit form
     */
    public function editUser(User $user): View
    {
        if (!auth()->user()->is_admin) {
            abort(403, 'Unauthorized access');
        }

        $groups = $user->groups()->get();
        return view('admin.users.edit', compact('user', 'groups'));
    }

    /**
     * Update user details
     */
    public function updateUser(User $user)
    {
        if (!auth()->user()->is_admin) {
            abort(403, 'Unauthorized access');
        }

        $validated = request()->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'is_admin' => 'boolean',
            'email_verified_at' => 'nullable|date',
        ]);

        $user->update($validated);

        return redirect()->route('admin.users.edit', $user)
            ->with('success', 'User updated successfully');
    }

    /**
     * Delete user
     */
    public function deleteUser(User $user)
    {
        if (!auth()->user()->is_admin) {
            abort(403, 'Unauthorized access');
        }

        // Prevent deleting the current admin
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users')
                ->with('error', 'Cannot delete your own account');
        }

        $user->delete();

        return redirect()->route('admin.users')
            ->with('success', 'User deleted successfully');
    }

    /**
     * Show groups management page
     */
    public function groups(): View
    {
        if (!auth()->user()->is_admin) {
            abort(403, 'Unauthorized access');
        }

        $groups = Group::with('admin')->paginate(20);
        return view('admin.groups.index', compact('groups'));
    }

    /**
     * Show group details and members
     */
    public function showGroup(Group $group): View
    {
        if (!auth()->user()->is_admin) {
            abort(403, 'Unauthorized access');
        }

        $members = $group->members()->paginate(15);
        return view('admin.groups.show', compact('group', 'members'));
    }

    /**
     * Edit group details
     */
    public function editGroup(Group $group): View
    {
        if (!auth()->user()->is_admin) {
            abort(403, 'Unauthorized access');
        }

        $admins = User::where('is_admin', false)->get();
        return view('admin.groups.edit', compact('group', 'admins'));
    }

    /**
     * Update group details
     */
    public function updateGroup(Group $group)
    {
        if (!auth()->user()->is_admin) {
            abort(403, 'Unauthorized access');
        }

        $validated = request()->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'admin_id' => 'nullable|exists:users,id',
            'status' => 'required|in:active,inactive,suspended',
        ]);

        $group->update($validated);

        return redirect()->route('admin.groups.show', $group)
            ->with('success', 'Group updated successfully');
    }

    /**
     * Show loans management page
     */
    public function loans(): View
    {
        if (!auth()->user()->is_admin) {
            abort(403, 'Unauthorized access');
        }

        $loans = Loan::with('member.user', 'group')->paginate(20);
        return view('admin.loans.index', compact('loans'));
    }

    /**
     * Show loan details
     */
    public function showLoan(Loan $loan): View
    {
        if (!auth()->user()->is_admin) {
            abort(403, 'Unauthorized access');
        }

        $payments = $loan->payments()->paginate(10);
        $charges = $loan->charges()->get();
        return view('admin.loans.show', compact('loan', 'payments', 'charges'));
    }

    /**
     * Show savings management page
     */
    public function savings(): View
    {
        if (!auth()->user()->is_admin) {
            abort(403, 'Unauthorized access');
        }

        $savings = Saving::with('member.user', 'group')->paginate(20);
        return view('admin.savings.index', compact('savings'));
    }

    /**
     * Show saving details
     */
    public function showSaving(Saving $saving): View
    {
        if (!auth()->user()->is_admin) {
            abort(403, 'Unauthorized access');
        }

        $transactions = $saving->transactions()->paginate(10);
        return view('admin.savings.show', compact('saving', 'transactions'));
    }

    /**
     * Show transactions log
     */
    public function transactions(): View
    {
        if (!auth()->user()->is_admin) {
            abort(403, 'Unauthorized access');
        }

        $transactions = Transaction::with('user', 'loggable')
            ->latest()
            ->paginate(30);

        return view('admin.transactions.index', compact('transactions'));
    }

    /**
     * Show system reports
     */
    public function reports(): View
    {
        if (!auth()->user()->is_admin) {
            abort(403, 'Unauthorized access');
        }

        $report_data = [
            'total_loan_amount' => Loan::sum('amount') ?? 0,
            'total_loan_paid' => Loan::where('status', 'paid')->sum('amount') ?? 0,
            'total_loan_pending' => Loan::where('status', '!=', 'paid')->sum('amount') ?? 0,
            'total_savings_amount' => Saving::sum('balance') ?? 0,
            'avg_loan_amount' => Loan::avg('amount') ?? 0,
            'avg_saving_balance' => Saving::avg('balance') ?? 0,
            'loans_by_status' => Loan::selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->get(),
            'groups_by_status' => Group::selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->get(),
            'top_groups_by_members' => Group::withCount('members')
                ->orderByDesc('members_count')
                ->take(10)
                ->get(),
        ];

        return view('admin.reports.index', compact('report_data'));
    }

    /**
     * Show system settings
     */
    public function settings(): View
    {
        if (!auth()->user()->is_admin) {
            abort(403, 'Unauthorized access');
        }

        return view('admin.settings.index');
    }
}
