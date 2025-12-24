@extends('layouts.app')

@section('title', 'Group Admin Dashboard - ' . $group->name)

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-gradient-to-r from-indigo-600 to-indigo-800 text-white shadow">
        <div class="max-w-7xl mx-auto py-8 px-4">
            <h1 class="text-4xl font-bold">{{ $group->name }} - Group Admin Dashboard</h1>
            <p class="text-indigo-100 mt-2">Manage members, loans, and savings with deadline tracking</p>
        </div>
    </div>

    <div class="max-w-7xl mx-auto py-12 px-4">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
            <div class="bg-white rounded-lg shadow-sm p-5 border-l-4 border-blue-500">
                <p class="text-gray-500 text-xs font-semibold uppercase tracking-wider">Total Members</p>
                <p class="text-3xl font-bold text-blue-600 mt-3">{{ $stats['total_members'] }}</p>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-5 border-l-4 border-green-500">
                <p class="text-gray-500 text-xs font-semibold uppercase tracking-wider">Active Loans</p>
                <p class="text-3xl font-bold text-green-600 mt-3">{{ $stats['active_loans'] }}</p>
                <p class="text-xs text-gray-500 mt-1">of {{ $stats['total_loans'] }} total</p>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-5 border-l-4 border-yellow-500">
                <p class="text-gray-500 text-xs font-semibold uppercase tracking-wider">Total Loaned</p>
                <p class="text-2xl font-bold text-yellow-600 mt-3">{{ number_format($stats['total_loan_amount'], 2) }}</p>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-5 border-l-4 border-purple-500">
                <p class="text-gray-500 text-xs font-semibold uppercase tracking-wider">Savings Balance</p>
                <p class="text-2xl font-bold text-purple-600 mt-3">{{ number_format($stats['total_savings_balance'], 2) }}</p>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-5 border-l-4 {{ $stats['overdue_loans'] > 0 ? 'border-red-500' : 'border-gray-300' }}">
                <p class="text-gray-500 text-xs font-semibold uppercase tracking-wider">Overdue Loans</p>
                <p class="text-3xl font-bold {{ $stats['overdue_loans'] > 0 ? 'text-red-600' : 'text-gray-600' }} mt-3">{{ $stats['overdue_loans'] }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content (Left 2/3) -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Overdue Loans Alert -->
                @if($overdue_loans->count() > 0)
                    <div class="bg-red-50 border-l-4 border-red-500 rounded-lg p-6">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4v.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-lg font-bold text-red-900">⚠️ Overdue Loans Alert</h3>
                                <p class="text-red-700 mt-2">You have {{ $overdue_loans->count() }} loan(s) past their maturity date. Please take action.</p>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Upcoming Deadlines -->
                <div class="bg-white rounded-lg shadow-sm">
                    <div class="border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">📅 Upcoming Loan Deadlines</h2>
                            <p class="text-sm text-gray-600 mt-1">Next 30 days</p>
                        </div>
                        <a href="{{ route('group-admin.loans', $group) }}" class="text-blue-500 hover:text-blue-700 font-semibold text-sm">View All</a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 border-b">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Member</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Maturity Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Days Left</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @forelse($upcoming_loans as $loan)
                                    @php
                                        $daysLeft = now()->diffInDays($loan->maturity_date);
                                        $urgency = $daysLeft <= 7 ? 'bg-yellow-50' : 'hover:bg-gray-50';
                                    @endphp
                                    <tr class="{{ $urgency }} transition">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <p class="font-semibold text-gray-900">{{ $loan->member->user->name }}</p>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                            {{ number_format($loan->principal_amount, 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                            {{ $loan->maturity_date->format('M d, Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $daysLeft <= 7 ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                                {{ $daysLeft }} days
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <span class="px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">Active</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                            ✓ No upcoming deadlines in the next 30 days
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Overdue Loans Table -->
                @if($overdue_loans->count() > 0)
                    <div class="bg-white rounded-lg shadow-sm border-l-4 border-red-500">
                        <div class="border-b border-gray-200 px-6 py-4">
                            <h2 class="text-xl font-bold text-red-900">⚠️ Overdue Loans</h2>
                            <p class="text-sm text-red-700 mt-1">These loans have passed their maturity date</p>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-red-50 border-b">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-red-800 uppercase">Member</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-red-800 uppercase">Amount</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-red-800 uppercase">Due Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-red-800 uppercase">Days Overdue</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-red-800 uppercase">Outstanding</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    @foreach($overdue_loans as $loan)
                                        @php
                                            $daysOverdue = $loan->maturity_date->diffInDays(now());
                                        @endphp
                                        <tr class="bg-red-50 hover:bg-red-100 transition">
                                            <td class="px-6 py-4 whitespace-nowrap font-semibold text-red-900">{{ $loan->member->user->name }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">{{ number_format($loan->principal_amount, 2) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-red-700">{{ $loan->maturity_date->format('M d, Y') }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 py-1 rounded-full text-xs font-semibold bg-red-200 text-red-900">
                                                    {{ $daysOverdue }}+ days
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-red-900">
                                                {{ number_format($loan->remaining_balance, 2) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                <!-- Members with Deadline Info -->
                <div class="bg-white rounded-lg shadow-sm">
                    <div class="border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                        <h2 class="text-xl font-bold text-gray-900">👥 Members with Active Loans</h2>
                        <a href="{{ route('group-admin.members', $group) }}" class="text-blue-500 hover:text-blue-700 font-semibold text-sm">View All</a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 border-b">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Role</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Active Loans</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Loan Amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Savings</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Next Deadline</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @forelse($member_details as $detail)
                                    <tr class="{{ $detail['has_overdue'] ? 'bg-red-50' : 'hover:bg-gray-50' }} transition">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <p class="font-semibold {{ $detail['has_overdue'] ? 'text-red-900' : 'text-gray-900' }}">
                                                {{ $detail['user']->name }}
                                            </p>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <span class="px-2 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                                                {{ ucfirst($detail['member']->role) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                            {{ $detail['active_loans'] }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                            {{ number_format($detail['total_loan_amount'], 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                            {{ number_format($detail['savings_balance'], 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            @if($detail['has_overdue'])
                                                <span class="px-2 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800">⚠️ Overdue</span>
                                            @elseif($detail['upcoming_deadline'])
                                                @php
                                                    $daysUntil = now()->diffInDays($detail['upcoming_deadline']);
                                                @endphp
                                                <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $daysUntil <= 7 ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800' }}">
                                                    {{ $detail['upcoming_deadline']->format('M d') }} ({{ $daysUntil }}d)
                                                </span>
                                            @else
                                                <span class="text-gray-500">No active loans</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                            No members with active loans
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Sidebar (Right 1/3) -->
            <div class="space-y-6">
                <!-- Quick Actions -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.5 13a3 3 0 01-.369-5.98 5 5 0 1111.753.102A4.5 4.5 0 2815.5 13H11V9.413l1.293 1.293a1 1 0 001.414-1.414l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 9.414V13H5.5z" clip-rule="evenodd"></path>
                        </svg>
                        Quick Actions
                    </h3>
                    <div class="space-y-2">
                        <a href="{{ route('group-admin.loans', $group) }}" class="block px-4 py-3 bg-blue-50 hover:bg-blue-100 rounded-lg text-blue-600 font-medium transition">
                            📊 View All Loans
                        </a>
                        <a href="{{ route('group-admin.savings', $group) }}" class="block px-4 py-3 bg-green-50 hover:bg-green-100 rounded-lg text-green-600 font-medium transition">
                            💾 View All Savings
                        </a>
                        <a href="{{ route('group-admin.members', $group) }}" class="block px-4 py-3 bg-purple-50 hover:bg-purple-100 rounded-lg text-purple-600 font-medium transition">
                            👥 Manage Members
                        </a>
                        <a href="{{ route('group-admin.reports', $group) }}" class="block px-4 py-3 bg-yellow-50 hover:bg-yellow-100 rounded-lg text-yellow-600 font-medium transition">
                            📈 View Reports
                        </a>
                    </div>
                </div>

                <!-- Recent Savings -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Recent Savings Activity</h3>
                    <div class="space-y-3">
                        @forelse($recent_savings as $saving)
                            <div class="border-l-4 border-green-500 pl-4 py-2">
                                <p class="font-semibold text-gray-900 text-sm">{{ $saving->member->user->name }}</p>
                                <p class="text-xs text-gray-600">Balance: {{ number_format($saving->balance, 2) }}</p>
                                <p class="text-xs text-gray-500 mt-1">{{ $saving->updated_at->diffForHumans() }}</p>
                            </div>
                        @empty
                            <p class="text-gray-500 text-center py-4 text-sm">No savings accounts yet</p>
                        @endforelse
                    </div>
                </div>

                <!-- Group Info -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Group Information</h3>
                    <div class="space-y-4">
                        <div>
                            <p class="text-xs text-gray-500 uppercase font-semibold">Group Name</p>
                            <p class="text-sm font-semibold text-gray-900 mt-1">{{ $group->name }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase font-semibold">Status</p>
                            <span class="inline-flex mt-1 px-2 py-1 rounded-full text-xs font-bold {{ $group->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ ucfirst($group->status) }}
                            </span>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase font-semibold">Created</p>
                            <p class="text-sm text-gray-600 mt-1">{{ $group->created_at->format('M d, Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

    <div class="max-w-7xl mx-auto py-12 px-4">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
                <p class="text-gray-500 text-sm font-semibold uppercase">Total Members</p>
                <p class="text-3xl font-bold text-blue-600 mt-2">{{ $stats['total_members'] }}</p>
            </div>

            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
                <p class="text-gray-500 text-sm font-semibold uppercase">Active Loans</p>
                <p class="text-3xl font-bold text-green-600 mt-2">{{ $stats['active_loans'] }}</p>
                <p class="text-xs text-gray-500 mt-1">of {{ $stats['total_loans'] }} total</p>
            </div>

            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-yellow-500">
                <p class="text-gray-500 text-sm font-semibold uppercase">Total Loan Amount</p>
                <p class="text-3xl font-bold text-yellow-600 mt-2">{{ number_format($stats['total_loan_amount'], 2) }}</p>
            </div>

            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-purple-500">
                <p class="text-gray-500 text-sm font-semibold uppercase">Savings Balance</p>
                <p class="text-3xl font-bold text-purple-600 mt-2">{{ number_format($stats['total_savings_balance'], 2) }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column (2/3) -->
            <div class="lg:col-span-2">
                <!-- Group Members -->
                <div class="bg-white rounded-lg shadow p-6 mb-8">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-bold text-gray-900">Group Members</h2>
                        <a href="{{ route('groups.manage-members', $group) }}" class="text-blue-500 hover:text-blue-700 font-semibold">
                            Manage Members
                        </a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 border-b">
                                <tr>
                                    <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Name</th>
                                    <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Email</th>
                                    <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Role</th>
                                    <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Joined</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                @forelse($members as $member)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 text-sm font-semibold text-gray-900">{{ $member->user->name }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-600">{{ $member->user->email }}</td>
                                        <td class="px-4 py-3 text-sm">
                                            <span class="px-2 py-1 rounded-full text-xs font-bold {{ $member->role === 'admin' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800' }}">
                                                {{ ucfirst($member->role) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-600">{{ $member->created_at->format('M d, Y') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-3 text-center text-gray-500">No members yet</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $members->links() }}
                    </div>
                </div>

                <!-- Recent Loans -->
                <div class="bg-white rounded-lg shadow p-6 mb-8">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-bold text-gray-900">Recent Loans</h2>
                        <a href="{{ route('loans.index', $group) }}" class="text-blue-500 hover:text-blue-700 font-semibold">
                            View All
                        </a>
                    </div>
                    <div class="space-y-3">
                        @forelse($recent_loans as $loan)
                            <div class="border rounded-lg p-3 hover:bg-gray-50">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="font-semibold text-gray-900">{{ $loan->member->user->name }}</p>
                                        <p class="text-sm text-gray-600">Amount: {{ number_format($loan->amount, 2) }}</p>
                                    </div>
                                    <span class="px-2 py-1 rounded-full text-xs font-bold {{ $loan->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                        {{ ucfirst($loan->status) }}
                                    </span>
                                </div>
                            </div>
                        @empty
                            <p class="text-gray-500 text-center py-4">No loans yet</p>
                        @endforelse
                    </div>
                </div>

                <!-- Recent Savings -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-bold text-gray-900">Recent Savings</h2>
                        <a href="{{ route('savings.index', $group) }}" class="text-blue-500 hover:text-blue-700 font-semibold">
                            View All
                        </a>
                    </div>
                    <div class="space-y-3">
                        @forelse($recent_savings as $saving)
                            <div class="border rounded-lg p-3 hover:bg-gray-50">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="font-semibold text-gray-900">{{ $saving->member->user->name }}</p>
                                        <p class="text-sm text-gray-600">Balance: {{ number_format($saving->balance, 2) }}</p>
                                    </div>
                                    <span class="px-2 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800">
                                        Active
                                    </span>
                                </div>
                            </div>
                        @empty
                            <p class="text-gray-500 text-center py-4">No savings accounts yet</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Right Column (1/3) -->
            <div>
                <!-- Quick Actions -->
                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Quick Actions</h3>
                    <div class="space-y-2">
                        <a href="{{ route('loans.create', $group) }}" class="block px-4 py-2 bg-blue-50 hover:bg-blue-100 rounded-lg text-blue-600 font-semibold transition">
                            Create New Loan
                        </a>
                        <a href="{{ route('groups.manage-members', $group) }}" class="block px-4 py-2 bg-green-50 hover:bg-green-100 rounded-lg text-green-600 font-semibold transition">
                            Manage Members
                        </a>
                        <a href="{{ route('groups.edit', $group) }}" class="block px-4 py-2 bg-yellow-50 hover:bg-yellow-100 rounded-lg text-yellow-600 font-semibold transition">
                            Edit Group
                        </a>
                        <a href="{{ route('loans.report', $group) }}" class="block px-4 py-2 bg-purple-50 hover:bg-purple-100 rounded-lg text-purple-600 font-semibold transition">
                            View Reports
                        </a>
                    </div>
                </div>

                <!-- Group Info -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Group Information</h3>
                    <div class="space-y-3">
                        <div>
                            <p class="text-xs text-gray-500 uppercase">Group Name</p>
                            <p class="text-sm font-semibold text-gray-900">{{ $group->name }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase">Status</p>
                            <span class="px-2 py-1 rounded-full text-xs font-bold {{ $group->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ ucfirst($group->status) }}
                            </span>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase">Members</p>
                            <p class="text-sm font-semibold text-gray-900">{{ $stats['total_members'] }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase">Created</p>
                            <p class="text-sm text-gray-600">{{ $group->created_at->format('M d, Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
