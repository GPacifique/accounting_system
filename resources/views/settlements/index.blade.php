@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Settlement Periods</h1>
        <p class="text-gray-600 mt-2">Manage group settlement cycles, calculate payouts, and track member payments</p>
    </div>

    <!-- Create New Period Button -->
    <div class="mb-6">
        <button @click="showCreateModal = true" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition">
            + Create New Settlement Period
        </button>
    </div>

    <!-- Settlement Periods List -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Summary Cards -->
        @foreach($periods as $period)
            <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-blue-600">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="text-lg font-bold text-gray-800">{{ $period->period_name }}</h3>
                        <p class="text-sm text-gray-600">
                            {{ $period->start_date->format('M d, Y') }} - {{ $period->end_date->format('M d, Y') }}
                        </p>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $period->status === 'finalized' ? 'bg-green-100 text-green-800' : ($period->status === 'closed' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800') }}">
                        {{ ucfirst($period->status) }}
                    </span>
                </div>

                @php $summary = $period->getSettlementSummary(); @endphp

                <div class="space-y-2 mb-4 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Members Settled:</span>
                        <span class="font-semibold">{{ $summary['settled_members'] }} / {{ $summary['total_members'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Total Due:</span>
                        <span class="font-semibold text-green-600">{{ number_format($summary['total_due'], 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Total Paid:</span>
                        <span class="font-semibold">{{ number_format($summary['total_paid'], 2) }}</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2 mt-3">
                        <div class="bg-green-600 h-2 rounded-full" style="width: {{ $summary['settlement_percentage'] }}%"></div>
                    </div>
                    <p class="text-center text-xs text-gray-500">{{ $summary['settlement_percentage'] }}% Settled</p>
                </div>

                <div class="flex gap-2 pt-4 border-t">
                    <a href="{{ route('settlements.show', [$group, $period]) }}" class="flex-1 bg-blue-100 text-blue-700 py-2 rounded hover:bg-blue-200 text-center text-sm font-semibold transition">
                        View Details
                    </a>
                    @if($period->status === 'active')
                        <button @click="generateSettlements({{ $period->id }})" class="flex-1 bg-purple-100 text-purple-700 py-2 rounded hover:bg-purple-200 text-sm font-semibold transition">
                            Generate
                        </button>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    @if(count($periods) == 0)
        <div class="text-center py-12 bg-gray-50 rounded-lg">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m0 0h6m-6-6H6m0 0H0"></path>
            </svg>
            <p class="mt-4 text-gray-600">No settlement periods found. Create one to get started.</p>
        </div>
    @endif
</div>

<!-- Create Period Modal -->
<div x-show="showCreateModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-xl font-bold text-gray-800">Create Settlement Period</h2>
        </div>

        <form @submit.prevent="submitForm" class="p-6 space-y-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Period Name *</label>
                <input type="text" x-model="form.period_name" placeholder="e.g., Q1 2025" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Start Date *</label>
                    <input type="date" x-model="form.start_date" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">End Date *</label>
                    <input type="date" x-model="form.end_date" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Savings Target (Optional)</label>
                <input type="number" x-model="form.total_savings_target" step="0.01" placeholder="0.00" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Notes (Optional)</label>
                <textarea x-model="form.notes" rows="3" placeholder="Any additional notes..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
            </div>

            <div class="flex gap-3 pt-4">
                <button type="button" @click="showCreateModal = false" class="flex-1 px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 font-semibold transition">
                    Cancel
                </button>
                <button type="submit" class="flex-1 px-4 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700 font-semibold transition">
                    Create Period
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    Alpine.data('settlementDashboard', () => ({
        showCreateModal: false,
        form: {
            period_name: '',
            start_date: '',
            end_date: '',
            total_savings_target: '',
            notes: '',
        },

        generateSettlements(periodId) {
            if (!confirm('Generate settlements for all members in this period?')) {
                return;
            }

            fetch(`/api/groups/{{ $group->id }}/settlement-periods/${periodId}/generate`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
            })
            .then(r => r.json())
            .then(data => {
                if (data.message) {
                    alert(data.message + ` (${data.count} settlements generated)`);
                    location.reload();
                } else {
                    alert(data.error || 'Error generating settlements');
                }
            });
        },

        submitForm() {
            fetch(`/api/groups/{{ $group->id }}/settlement-periods`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify(this.form),
            })
            .then(r => r.json())
            .then(data => {
                if (data.message) {
                    alert('Settlement period created successfully!');
                    this.showCreateModal = false;
                    location.reload();
                } else {
                    alert(data.error || 'Error creating period');
                }
            });
        },
    }))
</script>
@endsection
