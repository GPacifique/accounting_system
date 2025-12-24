@extends('layouts.admin')

@section('title', 'View Saving')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Savings Account #{{ $saving->id }}</h1>
        <a href="{{ route('admin.savings.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
            Back to Savings
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Saving Info -->
        <div class="md:col-span-2 bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Account Information</h2>
            <div class="space-y-3">
                <div class="py-2 border-b">
                    <p class="text-xs text-gray-500 uppercase">Member</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $saving->member?->user?->name ?? 'N/A' }}</p>
                </div>
                <div class="py-2 border-b">
                    <p class="text-xs text-gray-500 uppercase">Group</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $saving->group?->name ?? 'N/A' }}</p>
                </div>
                <div class="py-2 border-b">
                    <p class="text-xs text-gray-500 uppercase">Current Balance</p>
                    <p class="text-2xl font-bold text-green-600">{{ number_format($saving->balance, 2) }}</p>
                </div>
                <div class="py-2 border-b">
                    <p class="text-xs text-gray-500 uppercase">Interest Rate</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $saving->interest_rate }}%</p>
                </div>
                <div class="py-2 border-b">
                    <p class="text-xs text-gray-500 uppercase">Account Type</p>
                    <p class="text-lg font-semibold text-gray-900">{{ ucfirst($saving->account_type ?? 'Regular') }}</p>
                </div>
                <div class="py-2">
                    <p class="text-xs text-gray-500 uppercase">Account Opened</p>
                    <p class="text-sm text-gray-700">{{ $saving->created_at->format('M d, Y H:i A') }}</p>
                </div>
            </div>
        </div>

        <!-- Summary -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Account Summary</h3>
            <div class="space-y-4">
                <div class="py-3 bg-green-50 rounded-lg text-center">
                    <p class="text-xs text-gray-600 uppercase">Current Balance</p>
                    <p class="text-2xl font-bold text-green-600">{{ number_format($saving->balance, 2) }}</p>
                </div>
                <div class="py-3 bg-blue-50 rounded-lg text-center">
                    <p class="text-xs text-gray-600 uppercase">Total Deposits</p>
                    <p class="text-2xl font-bold text-blue-600">{{ number_format($transactions->where('type', 'deposit')->sum('amount'), 2) }}</p>
                </div>
                <div class="py-3 bg-red-50 rounded-lg text-center">
                    <p class="text-xs text-gray-600 uppercase">Total Withdrawals</p>
                    <p class="text-2xl font-bold text-red-600">{{ number_format($transactions->where('type', 'withdrawal')->sum('amount'), 2) }}</p>
                </div>
                <div class="py-3 bg-yellow-50 rounded-lg text-center">
                    <p class="text-xs text-gray-600 uppercase">Total Transactions</p>
                    <p class="text-2xl font-bold text-yellow-600">{{ $transactions->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden mb-8">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-bold text-gray-900">Account Transactions ({{ $transactions->total() }})</h2>
        </div>
        <table class="w-full">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Transaction ID</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Type</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Amount</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Balance After</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Date</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Notes</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($transactions as $transaction)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 text-sm font-semibold text-gray-900">#{{ $transaction->id }}</td>
                        <td class="px-6 py-4 text-sm">
                            <span class="px-3 py-1 rounded-full text-xs font-bold {{ $transaction->type === 'deposit' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ ucfirst($transaction->type) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm font-semibold {{ $transaction->type === 'deposit' ? 'text-green-600' : 'text-red-600' }}">
                            {{ $transaction->type === 'deposit' ? '+' : '-' }}{{ number_format($transaction->amount, 2) }}
                        </td>
                        <td class="px-6 py-4 text-sm font-semibold text-blue-600">{{ number_format($transaction->balance_after, 2) }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $transaction->created_at->format('M d, Y H:i A') }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $transaction->notes ?? '-' }}</td>
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
    <div class="mt-6">
        {{ $transactions->links() }}
    </div>
</div>
@endsection
