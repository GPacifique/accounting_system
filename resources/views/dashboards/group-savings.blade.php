@extends('layouts.app')

@section('title', 'Group Savings - ' . $group->name)

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-gradient-to-r from-green-600 to-green-800 text-white shadow">
        <div class="max-w-6xl mx-auto py-8 px-4">
            <h1 class="text-3xl font-bold">Group Savings</h1>
            <p class="text-green-100 mt-2">{{ $group->name }}</p>
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

        <!-- Savings Table -->
        <div class="bg-white rounded-lg shadow-sm">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-900">All Savings Accounts</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-blue-600 text-white">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase">Member</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase">Current Balance</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase">Total Deposits</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase">Total Withdrawn</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase">Interest Earned</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase">Last Updated</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($savings as $saving)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 whitespace-nowrap font-semibold text-gray-900">{{ $saving->member->user->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-green-600">{{ number_format($saving->current_balance, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ number_format($saving->total_deposits, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ number_format($saving->total_withdrawals ?? 0, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ number_format($saving->interest_earned ?? 0, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $saving->updated_at->format('M d, Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                    No savings accounts found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($savings->hasPages())
                <div class="px-6 py-4 border-t">
                    {{ $savings->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
