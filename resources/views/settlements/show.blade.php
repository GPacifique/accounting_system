@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <a href="{{ route('settlements.index', $group) }}" class="text-blue-600 hover:text-blue-800 mb-4 inline-flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            Back to Periods
        </a>
        <h1 class="text-3xl font-bold text-gray-800">{{ $period->period_name }}</h1>
        <p class="text-gray-600 mt-2">{{ $period->start_date->format('M d, Y') }} - {{ $period->end_date->format('M d, Y') }}</p>
    </div>

    <!-- Summary Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white p-6 rounded-lg shadow">
            <p class="text-sm font-semibold opacity-90">Total Members</p>
            <p class="text-3xl font-bold mt-2">{{ $summary['total_members'] }}</p>
        </div>

        <div class="bg-gradient-to-br from-green-500 to-green-600 text-white p-6 rounded-lg shadow">
            <p class="text-sm font-semibold opacity-90">Settled</p>
            <p class="text-3xl font-bold mt-2">{{ $summary['settled_members'] }}</p>
            <p class="text-xs opacity-75 mt-2">{{ $summary['settlement_percentage'] }}% Complete</p>
        </div>

        <div class="bg-gradient-to-br from-purple-500 to-purple-600 text-white p-6 rounded-lg shadow">
            <p class="text-sm font-semibold opacity-90">Total Due</p>
            <p class="text-3xl font-bold mt-2">{{ number_format($summary['total_due'], 2) }}</p>
        </div>

        <div class="bg-gradient-to-br from-orange-500 to-orange-600 text-white p-6 rounded-lg shadow">
            <p class="text-sm font-semibold opacity-90">Amount Paid</p>
            <p class="text-3xl font-bold mt-2">{{ number_format($summary['total_paid'], 2) }}</p>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="flex gap-3 mb-6">
        @if($period->status === 'active')
            <button @click="generateSettlements()" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-lg font-semibold transition">
                Generate Settlements
            </button>
        @endif

        @if($period->status === 'closed')
            <button @click="finalizePeriod()" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold transition">
                Finalize Period
            </button>
        @endif

        <button @click="showValidationReport()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition">
            View Validation Report
        </button>

        <button @click="exportSettlements()" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-lg font-semibold transition">
            Export to CSV
        </button>
    </div>

    <!-- Settlements Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-100 border-b-2 border-gray-300">
                <tr>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Member Name</th>
                    <th class="px-6 py-4 text-right text-sm font-semibold text-gray-700">Savings</th>
                    <th class="px-6 py-4 text-right text-sm font-semibold text-gray-700">Interest</th>
                    <th class="px-6 py-4 text-right text-sm font-semibold text-gray-700">Penalties</th>
                    <th class="px-6 py-4 text-right text-sm font-semibold text-gray-700">Total Due</th>
                    <th class="px-6 py-4 text-right text-sm font-semibold text-gray-700">Paid</th>
                    <th class="px-6 py-4 text-center text-sm font-semibold text-gray-700">Status</th>
                    <th class="px-6 py-4 text-center text-sm font-semibold text-gray-700">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($settlements as $settlement)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4">
                            <div class="font-semibold text-gray-800">{{ $settlement->member->user->name }}</div>
                            <div class="text-xs text-gray-500">ID: {{ $settlement->member->id }}</div>
                        </td>
                        <td class="px-6 py-4 text-right font-semibold">{{ number_format($settlement->original_savings, 2) }}</td>
                        <td class="px-6 py-4 text-right text-green-600 font-semibold">{{ number_format($settlement->interest_earned, 2) }}</td>
                        <td class="px-6 py-4 text-right">
                            <span class="text-red-600 font-semibold">{{ number_format($settlement->penalties_applied, 2) }}</span>
                            @if($settlement->penalties_waived > 0)
                                <div class="text-xs text-green-600">-{{ number_format($settlement->penalties_waived, 2) }} waived</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right font-bold text-lg">{{ number_format($settlement->total_due, 2) }}</td>
                        <td class="px-6 py-4 text-right font-semibold">{{ number_format($settlement->amount_paid, 2) }}</td>
                        <td class="px-6 py-4 text-center">
                            <span class="px-3 py-1 rounded-full text-xs font-semibold
                                {{ $settlement->status === 'paid' ? 'bg-green-100 text-green-800' :
                                   ($settlement->status === 'partial' ? 'bg-blue-100 text-blue-800' :
                                    ($settlement->status === 'overdue' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800')) }}">
                                {{ ucfirst($settlement->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <a href="{{ route('settlements.member-statement', [$group, $period, $settlement->member]) }}" class="text-blue-600 hover:text-blue-800 text-sm font-semibold">
                                Details
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                            No settlements yet. Generate settlements to get started.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($settlements->hasPages())
        <div class="mt-6">
            {{ $settlements->links() }}
        </div>
    @endif
</div>

<script>
    Alpine.data('settlementPeriod', () => ({
        generateSettlements() {
            if (!confirm('Generate settlements for all active members?')) return;

            fetch(`/api/groups/{{ $group->id }}/settlement-periods/{{ $period->id }}/generate`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
            })
            .then(r => r.json())
            .then(data => {
                alert(data.message || 'Settlements generated');
                location.reload();
            })
            .catch(e => alert('Error: ' + e.message));
        },

        finalizePeriod() {
            if (!confirm('Finalize this settlement period? This cannot be undone.')) return;

            fetch(`/api/groups/{{ $group->id }}/settlement-periods/{{ $period->id }}/finalize`, {
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
            })
            .then(r => r.json())
            .then(data => {
                alert(data.message || 'Period finalized');
                location.reload();
            })
            .catch(e => alert('Error: ' + e.message));
        },

        exportSettlements() {
            window.location.href = `/groups/{{ $group->id }}/settlement-periods/{{ $period->id }}/export`;
        },

        showValidationReport() {
            fetch(`/api/groups/{{ $group->id }}/settlement-periods/{{ $period->id }}/validation-report`)
            .then(r => r.json())
            .then(data => {
                if (data.can_finalize) {
                    alert('✓ All settlements are valid. Ready to finalize!');
                } else {
                    let msg = 'Issues found:\n\n';
                    data.issues.forEach(issue => {
                        msg += `• ${issue.member}: ${issue.issue}\n`;
                    });
                    alert(msg);
                }
            });
        },
    }))
</script>
@endsection
