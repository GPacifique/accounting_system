@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Welcome Banner -->
    <div class="bg-gradient-to-r from-indigo-600 to-indigo-800 text-white px-6 py-12">
        <div class="container mx-auto max-w-7xl">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-4xl font-bold mb-2">System Administration Dashboard</h1>
                    <p class="text-indigo-100 text-lg">Welcome back, <span class="font-semibold">{{ auth()->user()->name }}</span></p>
                    <p class="text-indigo-200 text-sm mt-1">You have full system access and control</p>
                </div>
                <div class="text-indigo-300 opacity-20">
                    <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 17v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.381z" clip-rule="evenodd"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container mx-auto max-w-7xl px-4 py-8">

    <!-- Statistics Cards -->
    <div class="mb-8">
        <h2 class="text-lg font-bold text-gray-900 mb-4">System Overview</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Users Stat -->
            <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition p-6 border-l-4 border-blue-500">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-gray-500 text-xs font-semibold uppercase tracking-wider">Total Users</p>
                        <p class="text-4xl font-bold text-gray-900 mt-2">{{ $stats['total_users'] }}</p>
                        <p class="text-gray-500 text-xs mt-2">Registered in system</p>
                    </div>
                    <div class="bg-blue-50 rounded-lg p-3">
                        <svg class="w-8 h-8 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Groups Stat -->
            <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition p-6 border-l-4 border-green-500">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-gray-500 text-xs font-semibold uppercase tracking-wider">Total Groups</p>
                        <p class="text-4xl font-bold text-gray-900 mt-2">{{ $stats['total_groups'] }}</p>
                        <p class="text-gray-500 text-xs mt-2">Active: {{ $stats['active_groups'] ?? 0 }}</p>
                    </div>
                    <div class="bg-green-50 rounded-lg p-3">
                        <svg class="w-8 h-8 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M5 3a2 2 0 00-2 2v6h6V5a2 2 0 00-2-2H5zm6 0a2 2 0 00-2 2v6h6V5a2 2 0 00-2-2h-2zm0 10a2 2 0 00-2 2v2h2v-2zm-6 2a2 2 0 00-2 2v2h6v-2a2 2 0 00-2-2H5z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Loans Stat -->
            <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition p-6 border-l-4 border-purple-500">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-gray-500 text-xs font-semibold uppercase tracking-wider">Active Loans</p>
                        <p class="text-4xl font-bold text-gray-900 mt-2">{{ $stats['active_loans'] }}</p>
                        <p class="text-gray-500 text-xs mt-2">Of {{ $stats['total_loans'] }} total</p>
                    </div>
                    <div class="bg-purple-50 rounded-lg p-3">
                        <svg class="w-8 h-8 text-purple-500" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Savings Stat -->
            <div class="bg-white rounded-lg shadow-sm hover:shadow-md transition p-6 border-l-4 border-yellow-500">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-gray-500 text-xs font-semibold uppercase tracking-wider">Total Savings Balance</p>
                        <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($stats['total_savings'] ?? 0, 2) }}</p>
                        <p class="text-gray-500 text-xs mt-2">Across all groups</p>
                    </div>
                    <div class="bg-yellow-50 rounded-lg p-3">
                        <svg class="w-8 h-8 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M5 5a2 2 0 012-2h6a2 2 0 012 2v9h2a1 1 0 110 2h-2v2a2 2 0 01-2 2H7a2 2 0 01-2-2v-2H3a1 1 0 110-2h2V5zm8 1v7H7V6h6z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left Column -->
        <div class="lg:col-span-2">
            <!-- Recent Users -->
            <div class="bg-white rounded-lg shadow-sm mb-8">
                <div class="border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                    <h2 class="text-lg font-bold text-gray-900">Recent Users</h2>
                    <a href="{{ route('admin.users.index') }}" class="inline-flex items-center text-blue-500 hover:text-blue-700 text-sm font-semibold">
                        <span>View All</span>
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Role</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Joined</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recent_users as $user)
                                <tr class="border-b hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 bg-indigo-100 rounded-full flex items-center justify-center">
                                                <span class="text-indigo-700 font-semibold">{{ substr($user->name, 0, 1) }}</span>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm font-semibold text-gray-900">{{ $user->name }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $user->email }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $user->is_admin ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800' }}">
                                            {{ $user->is_admin ? 'System Admin' : 'User' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $user->created_at->format('M d, Y') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <a href="{{ route('admin.users.edit', $user) }}" class="text-blue-500 hover:text-blue-700 font-semibold">Edit</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-6 py-8 text-center text-gray-500">No users found</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Groups -->
            <div class="bg-white rounded-lg shadow-sm">
                <div class="border-b border-gray-200 px-6 py-4 flex items-center justify-between">
                    <h2 class="text-lg font-bold text-gray-900">Recent Groups</h2>
                    <a href="{{ route('admin.groups.index') }}" class="inline-flex items-center text-blue-500 hover:text-blue-700 text-sm font-semibold">
                        <span>View All</span>
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Group Name</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Admin</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Members</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recent_groups as $group)
                                <tr class="border-b hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <p class="text-sm font-semibold text-gray-900">{{ $group->name }}</p>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $group->admin?->name ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm font-semibold text-gray-900">{{ $group->members_count ?? 0 }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $group->status === 'active' ? 'bg-green-100 text-green-800' : ($group->status === 'suspended' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800') }}">
                                            {{ ucfirst($group->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <a href="{{ route('admin.groups.show', $group) }}" class="text-blue-500 hover:text-blue-700 font-semibold">View</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-6 py-8 text-center text-gray-500">No groups found</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right Column - Quick Links -->
        <div>
            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow-sm mb-8">
                <div class="border-b border-gray-200 px-6 py-4">
                    <h3 class="text-lg font-bold text-gray-900 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M5.5 13a3 3 0 01-.369-5.98 5 5 0 1111.753.102A4.5 4.5 0 2815.5 13H11V9.413l1.293 1.293a1 1 0 001.414-1.414l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 9.414V13H5.5z"></path>
                        </svg>
                        Quick Actions
                    </h3>
                </div>
                <div class="p-6 space-y-2">
                    <a href="{{ route('admin.users.index') }}" class="flex items-center px-4 py-3 bg-blue-50 hover:bg-blue-100 rounded-lg transition font-medium text-gray-700">
                        <svg class="w-5 h-5 text-blue-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"></path>
                        </svg>
                        <span>Manage Users</span>
                    </a>
                    <a href="{{ route('admin.groups.index') }}" class="flex items-center px-4 py-3 bg-green-50 hover:bg-green-100 rounded-lg transition font-medium text-gray-700">
                        <svg class="w-5 h-5 text-green-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M5 3a2 2 0 00-2 2v6h6V5a2 2 0 00-2-2H5zm6 0a2 2 0 00-2 2v6h6V5a2 2 0 00-2-2h-2zm0 10a2 2 0 00-2 2v2h2v-2zm-6 2a2 2 0 00-2 2v2h6v-2a2 2 0 00-2-2H5z"></path>
                        </svg>
                        <span>Manage Groups</span>
                    </a>
                    <a href="{{ route('admin.loans.index') }}" class="flex items-center px-4 py-3 bg-purple-50 hover:bg-purple-100 rounded-lg transition font-medium text-gray-700">
                        <svg class="w-5 h-5 text-purple-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z"></path>
                        </svg>
                        <span>View Loans</span>
                    </a>
                    <a href="{{ route('admin.savings.index') }}" class="flex items-center px-4 py-3 bg-yellow-50 hover:bg-yellow-100 rounded-lg transition font-medium text-gray-700">
                        <svg class="w-5 h-5 text-yellow-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M5 5a2 2 0 012-2h6a2 2 0 012 2v9h2a1 1 0 110 2h-2v2a2 2 0 01-2 2H7a2 2 0 01-2-2v-2H3a1 1 0 110-2h2V5zm8 1v7H7V6h6z"></path>
                        </svg>
                        <span>View Savings</span>
                    </a>
                    <a href="{{ route('admin.transactions') }}" class="flex items-center px-4 py-3 bg-indigo-50 hover:bg-indigo-100 rounded-lg transition font-medium text-gray-700">
                        <svg class="w-5 h-5 text-indigo-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M5.5 13a3 3 0 01-.369-5.98 5 5 0 1111.753.102A4.5 4.5 0 2815.5 13H11V9.413l1.293 1.293a1 1 0 001.414-1.414l-3-3a1 1 0 00-1.414 0l-3 3a1 1 0 001.414 1.414L9 9.414V13H5.5z"></path>
                        </svg>
                        <span>Transactions</span>
                    </a>
                    <a href="{{ route('admin.reports') }}" class="flex items-center px-4 py-3 bg-red-50 hover:bg-red-100 rounded-lg transition font-medium text-gray-700">
                        <svg class="w-5 h-5 text-red-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"></path>
                        </svg>
                        <span>View Reports</span>
                    </a>
                </div>
            </div>

            <!-- System Statistics -->
            <div class="bg-white rounded-lg shadow-sm">
                <div class="border-b border-gray-200 px-6 py-4">
                    <h3 class="text-lg font-bold text-gray-900 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"></path>
                        </svg>
                        System Statistics
                    </h3>
                </div>
                <div class="p-6 space-y-4">
                    <div class="flex items-center justify-between py-3 border-b border-gray-100">
                        <span class="text-gray-600 font-medium">Total Members</span>
                        <span class="text-2xl font-bold text-gray-900">{{ $stats['total_members'] ?? 0 }}</span>
                    </div>
                    <div class="flex items-center justify-between py-3 border-b border-gray-100">
                        <span class="text-gray-600 font-medium">Active Groups</span>
                        <span class="text-2xl font-bold text-gray-900">{{ $stats['active_groups'] ?? 0 }}</span>
                    </div>
                    <div class="flex items-center justify-between py-3 border-b border-gray-100">
                        <span class="text-gray-600 font-medium">Total Transactions</span>
                        <span class="text-2xl font-bold text-gray-900">{{ $stats['total_transactions'] ?? 0 }}</span>
                    </div>
                    <div class="flex items-center justify-between py-3 border-b border-gray-100">
                        <span class="text-gray-600 font-medium">Total Loan Amount</span>
                        <span class="text-lg font-bold text-gray-900">{{ number_format($stats['loan_amount_total'] ?? 0, 2) }}</span>
                    </div>
                    <div class="flex items-center justify-between py-3">
                        <span class="text-gray-600 font-medium">Total Savings</span>
                        <span class="text-lg font-bold text-gray-900">{{ number_format($stats['savings_amount_total'] ?? 0, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="bg-white rounded-lg shadow-sm mt-8">
        <div class="border-b border-gray-200 px-6 py-4 flex items-center justify-between">
            <h2 class="text-lg font-bold text-gray-900">Recent Transactions</h2>
            <a href="{{ route('admin.transactions') }}" class="inline-flex items-center text-blue-500 hover:text-blue-700 text-sm font-semibold">
                <span>View All</span>
                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Action</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recent_transactions as $transaction)
                        <tr class="border-b hover:bg-gray-50 transition">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $transaction->user?->name ?? 'System' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-xs font-semibold">
                                    {{ class_basename($transaction->loggable_type) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ ucfirst(str_replace('_', ' ', $transaction->action)) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $transaction->created_at->format('M d, Y h:i A') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-6 py-8 text-center text-gray-500">No transactions found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    </div>
@endsection
