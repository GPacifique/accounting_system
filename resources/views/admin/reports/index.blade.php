@extends('layouts.admin')

@section('title', 'System Reports')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-4xl font-bold text-gray-900">System Reports</h1>
        <a href="{{ route('admin.dashboard') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
            Back to Dashboard
        </a>
    </div>

    <!-- Financial Summary -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
            <p class="text-gray-500 text-sm font-semibold uppercase">Total Loan Amount</p>
            <p class="text-3xl font-bold text-green-600 mt-2">{{ number_format($report_data['total_loan_amount'], 2) }}</p>
        </div>

        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
            <p class="text-gray-500 text-sm font-semibold uppercase">Total Loan Paid</p>
            <p class="text-3xl font-bold text-blue-600 mt-2">{{ number_format($report_data['total_loan_paid'], 2) }}</p>
        </div>

        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-yellow-500">
            <p class="text-gray-500 text-sm font-semibold uppercase">Pending Loans</p>
            <p class="text-3xl font-bold text-yellow-600 mt-2">{{ number_format($report_data['total_loan_pending'], 2) }}</p>
        </div>

        <div class="bg-white rounded-lg shadow p-6 border-l-4 border-purple-500">
            <p class="text-gray-500 text-sm font-semibold uppercase">Total Savings</p>
            <p class="text-3xl font-bold text-purple-600 mt-2">{{ number_format($report_data['total_savings_amount'], 2) }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-gray-500 text-sm font-semibold uppercase">Average Loan Amount</p>
            <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($report_data['avg_loan_amount'], 2) }}</p>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-gray-500 text-sm font-semibold uppercase">Average Saving Balance</p>
            <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($report_data['avg_saving_balance'], 2) }}</p>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-gray-500 text-sm font-semibold uppercase">Loan Collection Rate</p>
            <p class="text-3xl font-bold text-gray-900 mt-2">
                {{ $report_data['total_loan_amount'] > 0 ? number_format(($report_data['total_loan_paid'] / $report_data['total_loan_amount']) * 100, 1) : 0 }}%
            </p>
        </div>
    </div>

    <!-- Loans by Status -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Loans by Status</h2>
            <div class="space-y-3">
                @forelse($report_data['loans_by_status'] as $status)
                    <div class="flex items-center justify-between py-2 border-b">
                        <span class="text-gray-700 font-semibold">{{ ucfirst($status->status) }}</span>
                        <span class="inline-block px-3 py-1 rounded-full bg-blue-100 text-blue-800 font-bold">
                            {{ $status->count }}
                        </span>
                    </div>
                @empty
                    <p class="text-gray-500">No loan data available</p>
                @endforelse
            </div>
        </div>

        <!-- Groups by Status -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Groups by Status</h2>
            <div class="space-y-3">
                @forelse($report_data['groups_by_status'] as $status)
                    <div class="flex items-center justify-between py-2 border-b">
                        <span class="text-gray-700 font-semibold">{{ ucfirst($status->status) }}</span>
                        <span class="inline-block px-3 py-1 rounded-full {{ $status->status === 'active' ? 'bg-green-100 text-green-800' : ($status->status === 'suspended' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800') }} font-bold">
                            {{ $status->count }}
                        </span>
                    </div>
                @empty
                    <p class="text-gray-500">No group data available</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Top Groups by Members -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-bold text-gray-900 mb-4">Top Groups by Member Count</h2>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Rank</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Group Name</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Members</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($report_data['top_groups_by_members'] as $index => $group)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 text-sm font-bold text-gray-900">{{ $index + 1 }}</td>
                            <td class="px-6 py-4 text-sm font-semibold text-gray-900">{{ $group->name }}</td>
                            <td class="px-6 py-4 text-sm">
                                <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full font-bold">
                                    {{ $group->members_count }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <span class="px-3 py-1 rounded-full text-xs font-bold {{ $group->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ ucfirst($group->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                                No group data available
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
