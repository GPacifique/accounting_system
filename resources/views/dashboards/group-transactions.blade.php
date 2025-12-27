@extends('layouts.app')

@section('title', 'Group Transactions - ' . $group->name)

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-gradient-to-r from-indigo-600 to-indigo-800 text-white shadow">
        <div class="max-w-6xl mx-auto py-8 px-4">
            <h1 class="text-3xl font-bold">Group Transactions</h1>
            <p class="text-indigo-100 mt-2">{{ $group->name }}</p>
        </div>
    </div>

    <div class="max-w-6xl mx-auto py-12 px-4">
        <!-- Back Link -->
        <div class="mb-6">
            <a href="{{ route('group-admin.dashboard') }}" class="text-blue-600 hover:text-blue-800 font-semibold flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Dashboard
            </a>
        </div>

        <!-- Search Box -->
        <div class="mb-6">
            <form method="GET" class="flex gap-2">
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search by member name, type, description, or reference..."
                    class="flex-1 px-4 py-2 text-sm text-gray-900 placeholder-gray-500 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
                />
                <button
                    type="submit"
                    class="px-6 py-2 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition"
                >
                    Search
                </button>
                @if(request('search'))
                    <a href="{{ route('group-admin.transactions', $group) }}" class="px-6 py-2 bg-gray-400 text-white font-semibold rounded-lg hover:bg-gray-500 transition">
                        Clear
                    </a>
                @endif
            </form>
        </div>

        <!-- Transactions Table -->
        <div class="bg-white rounded-lg shadow-sm">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-900">All Transactions</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-blue-600 text-white">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase">Member</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase">Balance</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase">Description</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($transactions as $transaction)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 whitespace-nowrap font-semibold text-gray-900">{{ $transaction->member->user->name ?? 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold {{ str_contains($transaction->type, 'savings') ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                        {{ str_replace('_', ' ', ucfirst($transaction->type)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">{{ number_format($transaction->amount, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ number_format($transaction->balance_after, 2) }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $transaction->description }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $transaction->transaction_date->format('M d, Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                    No transactions found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($transactions->hasPages())
                <div class="px-6 py-4 border-t">
                    {{ $transactions->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
