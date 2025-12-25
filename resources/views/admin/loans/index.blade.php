@extends('layouts.admin')

@section('title', 'Loans Management')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-4xl font-bold text-gray-900">Loans Management</h1>
        <div class="flex gap-3">
            <a href="{{ route('admin.loans.create') }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded flex items-center gap-2">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.586L7.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 10.586V7z" clip-rule="evenodd"></path>
                </svg>
                ➕ Create New Loan
            </a>
            <a href="{{ route('admin.dashboard') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Loans Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full">
            <thead class="bg-blue-600 text-white">
                <tr>
                    <th class="px-6 py-4 text-left text-sm font-semibold">ID</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold">Member</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold">Group</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold">Amount</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold">Interest Rate</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold">Status</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold">Disbursed</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($loans as $loan)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 text-sm font-semibold text-gray-900">#{{ $loan->id }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $loan->member?->user?->name ?? 'N/A' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $loan->group?->name ?? 'N/A' }}</td>
                        <td class="px-6 py-4 text-sm font-semibold text-gray-900">{{ number_format($loan->principal_amount, 2) }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ number_format($loan->monthly_charge, 2) }}</td>
                        <td class="px-6 py-4 text-sm">
                            @if($loan->status === 'active')
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800">Active</span>
                            @elseif($loan->status === 'paid')
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800">Paid</span>
                            @else
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-yellow-100 text-yellow-800">{{ ucfirst($loan->status) }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $loan->disbursement_date?->format('M d, Y') ?? 'Pending' }}</td>
                        <td class="px-6 py-4 text-sm">
                            <a href="{{ route('admin.loans.show', $loan) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-3 rounded text-xs">
                                View
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                            No loans found
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $loans->links() }}
    </div>
</div>
@endsection
