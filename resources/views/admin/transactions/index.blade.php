@extends('layouts.admin')

@section('title', 'Transactions Log')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-4xl font-bold text-gray-900">System Transactions Log</h1>
        <a href="{{ route('admin.dashboard') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
            Back to Dashboard
        </a>
    </div>

    <!-- Transactions Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full">
            <thead class="bg-blue-600 text-white">
                <tr>
                    <th class="px-6 py-4 text-left text-sm font-semibold">Transaction ID</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold">User</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold">Type</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold">Action</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold">Related Entity</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold">Timestamp</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($transactions as $transaction)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 text-sm font-semibold text-gray-900">#{{ $transaction->id }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $transaction->user?->name ?? 'System' }}</td>
                        <td class="px-6 py-4 text-sm">
                            <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-semibold">
                                {{ class_basename($transaction->loggable_type) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <span class="px-3 py-1 rounded-full text-xs font-bold
                                {{ $transaction->action === 'created' ? 'bg-green-100 text-green-800' :
                                   ($transaction->action === 'updated' ? 'bg-yellow-100 text-yellow-800' :
                                   ($transaction->action === 'deleted' ? 'bg-red-100 text-red-800' :
                                   'bg-gray-100 text-gray-800')) }}">
                                {{ ucfirst($transaction->action) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            @if($transaction->loggable_id)
                                #{{ $transaction->loggable_id }}
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $transaction->created_at->format('M d, Y H:i A') }}</td>
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
