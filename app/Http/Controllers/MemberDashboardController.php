<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use App\Models\Saving;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;

class MemberDashboardController extends Controller
{
    /**
     * Show the member dashboard
     * Members can only see their own records
     */
    public function index()
    {
        $user = Auth::user();

        // Get user's groups
        $groups = $user->groups()->where('status', 'active')->get();

        // Get user's loans (only their own)
        $loans = Loan::where('user_id', $user->id)
            ->with('group')
            ->orderBy('created_at', 'desc')
            ->get();

        // Get user's savings (only their own)
        $savings = Saving::where('user_id', $user->id)
            ->with('group')
            ->orderBy('created_at', 'desc')
            ->get();

        // Get user's transactions (only their own)
        $transactions = Transaction::where('user_id', $user->id)
            ->with('group')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Calculate loan statistics
        $loan_stats = [
            'total_loaned' => $loans->sum('amount'),
            'total_paid' => $loans->sum('paid_amount'),
            'outstanding' => $loans->sum('amount') - $loans->sum('paid_amount'),
        ];

        return view('dashboards.member', compact(
            'groups',
            'loans',
            'savings',
            'transactions',
            'loan_stats'
        ));
    }

    /**
     * Show user's loans detail page
     * View-only access - cannot edit or delete
     */
    public function myLoans()
    {
        $user = Auth::user();

        $loans = Loan::where('user_id', $user->id)
            ->with('group')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $stats = [
            'total_loaned' => $loans->sum('amount'),
            'total_paid' => $loans->sum('paid_amount'),
            'outstanding' => $loans->sum('amount') - $loans->sum('paid_amount'),
            'active_count' => $loans->where('status', 'active')->count(),
        ];

        return view('dashboards.member-loans', compact('loans', 'stats'));
    }

    /**
     * Show user's savings detail page
     * View-only access - cannot edit or delete
     */
    public function mySavings()
    {
        $user = Auth::user();

        $savings = Saving::where('user_id', $user->id)
            ->with('group')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $stats = [
            'total_balance' => $savings->sum('balance'),
            'total_saved' => $savings->sum('total_saved'),
            'account_count' => $savings->count(),
        ];

        return view('dashboards.member-savings', compact('savings', 'stats'));
    }

    /**
     * Show user's transactions detail page
     * View-only access - cannot edit or delete
     */
    public function myTransactions()
    {
        $user = Auth::user();

        $transactions = Transaction::where('user_id', $user->id)
            ->with('group')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('dashboards.member-transactions', compact('transactions'));
    }

    /**
     * Show user's groups they belong to
     * View-only access
     */
    public function myGroups()
    {
        $user = Auth::user();

        $groups = $user->groups()
            ->where('status', 'active')
            ->withPivot('role', 'status', 'created_at')
            ->paginate(10);

        return view('dashboards.member-groups', compact('groups'));
    }

    /**
     * Show user's profile
     * Can view and edit own profile only
     */
    public function profile()
    {
        $user = Auth::user();

        return view('dashboards.member-profile', compact('user'));
    }

    /**
     * Update user's profile
     * Can only update own profile
     */
    public function updateProfile()
    {
        $user = Auth::user();

        $validated = request()->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
        ]);

        $user->update($validated);

        return redirect()->route('member.profile')
            ->with('success', 'Profile updated successfully.');
    }

    /**
     * Verify that the requested resource belongs to the authenticated user
     * Used as a helper for authorization
     */
    protected function verifyOwnership($model)
    {
        if ($model->user_id !== Auth::id()) {
            abort(403, 'You are not authorized to view this record.');
        }
    }

    /**
     * Prevent members from accessing edit/delete pages
     * Show error message
     */
    public function accessDenied()
    {
        return view('errors.access-denied', [
            'message' => 'As a group member, you have view-only access. Contact your Group Admin for changes.',
        ]);
    }
}
