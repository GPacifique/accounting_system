@extends('layouts.app')

@section('title', 'My Dashboard')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white shadow">
        <div class="max-w-7xl mx-auto py-6 px-4">
            <h1 class="text-3xl font-bold text-gray-900">My Dashboard</h1>
            <p class="text-gray-600 mt-2">View your loans, savings, and group activities</p>
        </div>
    </div>

    <div class="max-w-7xl mx-auto py-12 px-4">
        <!-- Notifications -->
        @if($errors->any())
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                <p class="text-red-800 font-semibold">⚠ Access Restricted</p>
                <p class="text-red-700 text-sm">You can only view records related to your account.</p>
            </div>
        @endif

        <!-- My Groups -->
        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <h2 class="text-xl font-bold text-gray-900 mb-4">My Groups</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @forelse($groups as $group)
                    <div class="border rounded-lg p-4 hover:shadow-lg transition">
                        <h3 class="font-semibold text-gray-900">{{ $group->name }}</h3>
                        <p class="text-sm text-gray-600 mt-1">{{ $group->members_count ?? 0 }} members</p>
                        <div class="mt-3 pt-3 border-t">
                            <p class="text-xs text-gray-500">Joined: {{ $group->pivot->created_at->format('M d, Y') }}</p>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full text-center py-8">
                        <p class="text-gray-500">You haven't joined any groups yet.</p>
                        <a href="{{ route('admin.groups.index') }}" class="text-blue-500 hover:text-blue-700 font-semibold mt-2 inline-block">
                            Browse Groups →
                        </a>
                    </div>
                @endforelse
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column (2/3) -->
            <div class="lg:col-span-2">
                <!-- My Loans -->
                <div class="bg-white rounded-lg shadow p-6 mb-8">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-bold text-gray-900">My Loans</h2>
                        <span class="text-2xl font-bold text-blue-600">{{ count($loans) }}</span>
                    </div>

                    @if($loans->count() > 0)
                        <div class="space-y-4">
                            @foreach($loans as $loan)
                                <div class="border rounded-lg p-4 hover:bg-gray-50">
                                    <div class="flex items-center justify-between mb-2">
                                        <h3 class="font-semibold text-gray-900">{{ $loan->group->name }}</h3>
                                        <span class="px-2 py-1 rounded-full text-xs font-bold {{ $loan->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                            {{ ucfirst($loan->status) }}
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-600">Amount: {{ number_format($loan->amount, 2) }}</p>
                                    <p class="text-sm text-gray-600">Paid: {{ number_format($loan->paid_amount, 2) }} / Remaining: {{ number_format($loan->amount - $loan->paid_amount, 2) }}</p>
                                    <p class="text-xs text-gray-500 mt-2">Issued: {{ $loan->created_at->format('M d, Y') }} | Due: {{ $loan->due_date->format('M d, Y') }}</p>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <div class="grid grid-cols-3 gap-4 text-center">
                                <div>
                                    <p class="text-xs text-gray-500 uppercase">Total Loaned</p>
                                    <p class="text-lg font-bold text-blue-600">{{ number_format($loan_stats['total_loaned'], 2) }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 uppercase">Total Paid</p>
                                    <p class="text-lg font-bold text-green-600">{{ number_format($loan_stats['total_paid'], 2) }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 uppercase">Outstanding</p>
                                    <p class="text-lg font-bold text-red-600">{{ number_format($loan_stats['outstanding'], 2) }}</p>
                                </div>
                            </div>
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-8">You have no loans.</p>
                    @endif
                </div>

                <!-- My Savings -->
                <div class="bg-white rounded-lg shadow p-6 mb-8">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-bold text-gray-900">My Savings</h2>
                        <span class="text-2xl font-bold text-green-600">{{ count($savings) }}</span>
                    </div>

                    @if($savings->count() > 0)
                        <div class="space-y-4">
                            @foreach($savings as $saving)
                                <div class="border rounded-lg p-4 hover:bg-gray-50">
                                    <div class="flex items-center justify-between mb-2">
                                        <h3 class="font-semibold text-gray-900">{{ $saving->group->name }}</h3>
                                        <span class="px-2 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800">
                                            Active
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-600">Current Balance: {{ number_format($saving->balance, 2) }}</p>
                                    <p class="text-sm text-gray-600">Total Saved: {{ number_format($saving->total_saved, 2) }}</p>
                                    <p class="text-xs text-gray-500 mt-2">Opened: {{ $saving->created_at->format('M d, Y') }}</p>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <div class="text-center">
                                <p class="text-xs text-gray-500 uppercase">Total Savings Balance</p>
                                <p class="text-2xl font-bold text-green-600">{{ number_format($savings->sum('balance'), 2) }}</p>
                            </div>
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-8">You have no savings accounts.</p>
                    @endif
                </div>

                <!-- Recent Transactions -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">Recent Transactions</h2>

                    @if($transactions->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50 border-b">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Date</th>
                                        <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Type</th>
                                        <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700">Group</th>
                                        <th class="px-4 py-2 text-right text-sm font-semibold text-gray-700">Amount</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    @foreach($transactions as $transaction)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3 text-sm text-gray-600">{{ $transaction->created_at->format('M d, Y') }}</td>
                                            <td class="px-4 py-3 text-sm">
                                                <span class="px-2 py-1 rounded text-xs font-semibold {{ $transaction->type === 'loan_payment' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                                    {{ ucfirst(str_replace('_', ' ', $transaction->type)) }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-600">{{ $transaction->group->name ?? 'N/A' }}</td>
                                            <td class="px-4 py-3 text-sm font-semibold text-right">{{ number_format($transaction->amount, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">
                            {{ $transactions->links() }}
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-8">No transactions yet.</p>
                    @endif
                </div>
            </div>

            <!-- Right Column (1/3) -->
            <div>
                <!-- Profile Summary -->
                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">My Profile</h3>
                    <div class="space-y-3">
                        <div>
                            <p class="text-xs text-gray-500 uppercase">Name</p>
                            <p class="text-sm font-semibold text-gray-900">{{ Auth::user()->name }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase">Email</p>
                            <p class="text-sm text-gray-600">{{ Auth::user()->email }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase">Role</p>
                            <span class="px-2 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800">
                                Group Member
                            </span>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase">Member Since</p>
                            <p class="text-sm text-gray-600">{{ Auth::user()->created_at->format('M d, Y') }}</p>
                        </div>
                    </div>
                    <a href="{{ route('profile.edit') }}" class="mt-4 block px-4 py-2 bg-blue-50 hover:bg-blue-100 rounded-lg text-blue-600 font-semibold text-center transition">
                        Edit Profile
                    </a>
                </div>

                <!-- Your Balance Summary -->
                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Balance Summary</h3>
                    <div class="space-y-3">
                        <div class="border-b pb-3">
                            <p class="text-xs text-gray-500 uppercase">Total Loaned</p>
                            <p class="text-2xl font-bold text-blue-600">{{ number_format($loan_stats['total_loaned'] ?? 0, 2) }}</p>
                        </div>
                        <div class="border-b pb-3">
                            <p class="text-xs text-gray-500 uppercase">Loan Outstanding</p>
                            <p class="text-2xl font-bold text-red-600">{{ number_format($loan_stats['outstanding'] ?? 0, 2) }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase">Total Savings</p>
                            <p class="text-2xl font-bold text-green-600">{{ number_format($savings->sum('balance') ?? 0, 2) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Access Information -->
                <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                    <h4 class="font-semibold text-blue-900 mb-2">📋 Access Information</h4>
                    <p class="text-xs text-blue-800">
                        As a group member, you can view:
                    </p>
                    <ul class="text-xs text-blue-800 mt-2 space-y-1 ml-3">
                        <li>✓ Your personal loans</li>
                        <li>✓ Your savings accounts</li>
                        <li>✓ Your transactions</li>
                        <li>✓ Group membership info</li>
                    </ul>
                    <p class="text-xs text-blue-800 mt-2">
                        You <strong>cannot</strong> edit or delete records. Contact your Group Admin for changes.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
