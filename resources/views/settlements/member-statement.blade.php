@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <a href="{{ route('settlements.show', [$group, $period]) }}" class="text-blue-600 hover:text-blue-800 mb-4 inline-flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            Back to Period
        </a>
        <h1 class="text-3xl font-bold text-gray-800">Settlement Statement</h1>
        <div class="flex justify-between items-start mt-4">
            <div>
                <p class="text-xl font-semibold text-gray-700">{{ $statement['member']['name'] }}</p>
                <p class="text-gray-600">Period: {{ $statement['period']['name'] }}</p>
                <p class="text-sm text-gray-500">{{ $statement['period']['start_date']->format('M d, Y') }} - {{ $statement['period']['end_date']->format('M d, Y') }}</p>
            </div>
            <span class="px-4 py-2 rounded-full text-sm font-semibold
                {{ $statement['status'] === 'paid' ? 'bg-green-100 text-green-800' :
                   ($statement['status'] === 'partial' ? 'bg-blue-100 text-blue-800' :
                    ($statement['is_overdue'] ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800')) }}">
                {{ ucfirst($statement['status']) }}
                @if($statement['is_overdue'])
                    <span class="ml-2">({{ $statement['days_overdue'] }} days overdue)</span>
                @endif
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Breakdown Card -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white p-6">
                    <h2 class="text-2xl font-bold">Settlement Breakdown</h2>
                    <p class="text-blue-100 text-sm mt-1">Detailed calculation of your settlement amount</p>
                </div>

                <div class="p-6 space-y-6">
                    <!-- Original Savings -->
                    <div class="border-l-4 border-blue-500 pl-4">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-gray-700 font-semibold">Original Savings Deposited</span>
                            <span class="text-2xl font-bold text-blue-600">{{ number_format($statement['breakdown']['savings'], 2) }}</span>
                        </div>
                        <p class="text-sm text-gray-500">Total amount you deposited during the period</p>
                    </div>

                    <!-- Interest Earned -->
                    <div class="border-l-4 border-green-500 pl-4">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-gray-700 font-semibold">Interest Earned</span>
                            <span class="text-2xl font-bold text-green-600">+{{ number_format($statement['breakdown']['interest'], 2) }}</span>
                        </div>
                        <p class="text-sm text-gray-500">Interest from loans distributed to members</p>
                    </div>

                    <!-- Penalties -->
                    <div class="border-l-4 border-red-500 pl-4">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-gray-700 font-semibold">Penalties Applied</span>
                            <span class="text-2xl font-bold text-red-600">-{{ number_format($statement['breakdown']['penalties']['applied'], 2) }}</span>
                        </div>
                        @if($statement['breakdown']['penalties']['waived'] > 0)
                            <p class="text-sm text-gray-600">
                                <span class="text-green-600">Waived: {{ number_format($statement['breakdown']['penalties']['waived'], 2) }}</span>
                            </p>
                        @endif
                        <p class="text-sm text-gray-500">Penalties for rule violations or late payments</p>
                    </div>

                    <!-- Total Due -->
                    <div class="bg-gradient-to-r from-blue-50 to-blue-100 rounded-lg p-4 border-2 border-blue-300">
                        <div class="flex justify-between items-center">
                            <span class="text-lg font-bold text-gray-800">TOTAL AMOUNT DUE</span>
                            <span class="text-3xl font-bold text-blue-700">{{ number_format($statement['breakdown']['total_due'], 2) }}</span>
                        </div>
                    </div>

                    <!-- Payment Summary -->
                    <div class="grid grid-cols-3 gap-4 pt-4 border-t-2 border-gray-200">
                        <div class="text-center">
                            <p class="text-sm text-gray-600 mb-1">Amount Paid</p>
                            <p class="text-xl font-bold text-green-600">{{ number_format($statement['breakdown']['paid'], 2) }}</p>
                        </div>
                        <div class="text-center border-l border-r border-gray-300">
                            <p class="text-sm text-gray-600 mb-1">Pending</p>
                            <p class="text-xl font-bold text-orange-600">{{ number_format($statement['breakdown']['pending'], 2) }}</p>
                        </div>
                        <div class="text-center">
                            <p class="text-sm text-gray-600 mb-1">Percentage Paid</p>
                            <p class="text-xl font-bold text-blue-600">
                                @if($statement['breakdown']['total_due'] > 0)
                                    {{ round(($statement['breakdown']['paid'] / $statement['breakdown']['total_due']) * 100) }}%
                                @else
                                    0%
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment History -->
            <div class="bg-white rounded-lg shadow-lg mt-6 overflow-hidden">
                <div class="bg-gradient-to-r from-green-600 to-green-700 text-white p-6">
                    <h2 class="text-xl font-bold">Payment History</h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b-2 border-gray-300">
                            <tr>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Date</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Amount</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Method</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Reference</th>
                                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Notes</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($statement['payments'] as $payment)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 font-semibold text-gray-800">{{ \Carbon\Carbon::parse($payment['date'])->format('M d, Y') }}</td>
                                    <td class="px-6 py-4 font-bold text-green-600">{{ number_format($payment['amount'], 2) }}</td>
                                    <td class="px-6 py-4 text-gray-700">{{ ucfirst(str_replace('_', ' ', $payment['method'])) }}</td>
                                    <td class="px-6 py-4 text-gray-700 text-sm">{{ $payment['reference'] ?? '-' }}</td>
                                    <td class="px-6 py-4 text-gray-700 text-sm">{{ $payment['notes'] ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                        No payments recorded yet
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Actions Sidebar -->
        <div>
            <!-- Quick Actions -->
            @if($statement['breakdown']['pending'] > 0)
                <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Record Payment</h3>
                    <form x-data="{ method: 'cash' }" @submit.prevent="recordPayment" class="space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Amount</label>
                            <input type="number" x-model="paymentAmount" step="0.01" placeholder="0.00" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Payment Method</label>
                            <select x-model="method" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                                <option value="cash">Cash</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="check">Check</option>
                                <option value="mobile_money">Mobile Money</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Reference (Optional)</label>
                            <input type="text" x-model="reference" placeholder="Receipt/Check number" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                        </div>

                        <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 rounded-lg transition">
                            Record Payment
                        </button>
                    </form>
                </div>
            @else
                <div class="bg-green-100 border-l-4 border-green-600 text-green-800 p-6 rounded-lg">
                    <p class="font-bold">✓ Settlement Complete</p>
                    <p class="text-sm mt-2">All amounts have been paid.</p>
                </div>
            @endif

            <!-- Member Details -->
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Member Details</h3>
                <div class="space-y-3 text-sm">
                    <div>
                        <p class="text-gray-600">Full Name</p>
                        <p class="font-semibold text-gray-800">{{ $statement['member']['name'] }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Phone</p>
                        <p class="font-semibold text-gray-800">{{ $statement['member']['phone'] }}</p>
                    </div>
                    <div>
                        <p class="text-gray-600">Status</p>
                        <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800 mt-1">
                            Active Member
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    Alpine.data('settlementStatement', () => ({
        paymentAmount: '',
        reference: '',
        method: 'cash',

        recordPayment() {
            if (!this.paymentAmount || this.paymentAmount <= 0) {
                alert('Please enter a valid amount');
                return;
            }

            fetch(`/api/groups/{{ $group->id }}/settlement-periods/{{ $period->id }}/settlements/{{ $settlement->id }}/payment`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({
                    amount: this.paymentAmount,
                    payment_method: this.method,
                    reference: this.reference || null,
                }),
            })
            .then(r => r.json())
            .then(data => {
                if (data.message) {
                    alert('Payment recorded successfully!');
                    location.reload();
                } else {
                    alert(data.error || 'Error recording payment');
                }
            })
            .catch(e => alert('Error: ' + e.message));
        },
    }))
</script>
@endsection
