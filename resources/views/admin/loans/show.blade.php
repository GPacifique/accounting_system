@extends('layouts.admin')

@section('title', 'View Loan')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Loan Details #{{ $loan->id }}</h1>
        <a href="{{ route('admin.loans.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
            Back to Loans
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Loan Info -->
        <div class="md:col-span-2 bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Loan Information</h2>
            <div class="space-y-3">
                <div class="py-2 border-b">
                    <p class="text-xs text-gray-500 uppercase">Member</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $loan->member?->user?->name ?? 'N/A' }}</p>
                </div>
                <div class="py-2 border-b">
                    <p class="text-xs text-gray-500 uppercase">Group</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $loan->group?->name ?? 'N/A' }}</p>
                </div>
                <div class="py-2 border-b">
                    <p class="text-xs text-gray-500 uppercase">Loan Amount</p>
                    <p class="text-2xl font-bold text-green-600">{{ number_format($loan->principal_amount, 2) }}</p>
                </div>
                <div class="py-2 border-b">
                    <p class="text-xs text-gray-500 uppercase">Monthly Charge</p>
                    <p class="text-lg font-semibold text-gray-900">{{ number_format($loan->monthly_charge, 2) }}</p>
                </div>
                <div class="py-2 border-b">
                    <p class="text-xs text-gray-500 uppercase">Duration (Months)</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $loan->duration_months }}</p>
                </div>
                <div class="py-2 border-b">
                    <p class="text-xs text-gray-500 uppercase">Months Paid</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $loan->months_paid }}</p>
                </div>
                <div class="py-2 border-b">
                    <p class="text-xs text-gray-500 uppercase">Status</p>
                    <span class="inline-block px-3 py-1 rounded-full text-xs font-bold {{ $loan->status === 'active' ? 'bg-green-100 text-green-800' : ($loan->status === 'paid' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800') }}">
                        {{ ucfirst($loan->status) }}
                    </span>
                </div>
                <div class="py-2 border-b">
                    <p class="text-xs text-gray-500 uppercase">Issue Date</p>
                    <p class="text-sm text-gray-700">{{ $loan->issued_at?->format('M d, Y') ?? 'N/A' }}</p>
                </div>
                <div class="py-2">
                    <p class="text-xs text-gray-500 uppercase">Maturity Date</p>
                    <p class="text-sm text-gray-700">{{ $loan->maturity_date?->format('M d, Y') ?? 'N/A' }}</p>
                </div>
            </div>
        </div>

        <!-- Summary -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Summary</h3>
            <div class="space-y-4">
                <div class="py-3 bg-green-50 rounded-lg text-center">
                    <p class="text-xs text-gray-600 uppercase">Principal Amount</p>
                    <p class="text-2xl font-bold text-green-600">{{ number_format($loan->principal_amount, 2) }}</p>
                </div>
                <div class="py-3 bg-blue-50 rounded-lg text-center">
                    <p class="text-xs text-gray-600 uppercase">Total Paid</p>
                    <p class="text-2xl font-bold text-blue-600">{{ number_format($loan->total_principal_paid, 2) }}</p>
                </div>
                <div class="py-3 bg-yellow-50 rounded-lg text-center">
                    <p class="text-xs text-gray-600 uppercase">Remaining Balance</p>
                    <p class="text-2xl font-bold text-yellow-600">{{ number_format($loan->remaining_balance, 2) }}</p>
                </div>
                <div class="py-3 bg-purple-50 rounded-lg text-center">
                    <p class="text-xs text-gray-600 uppercase">Total Charges</p>
                    <p class="text-2xl font-bold text-purple-600">{{ number_format($loan->total_charged, 2) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Payments Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden mb-8">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-bold text-gray-900">Loan Payments ({{ $payments->total() }})</h2>
        </div>
        <table class="w-full">
            <thead class="bg-blue-600 text-white">
                <tr>
                    <th class="px-6 py-4 text-left text-sm font-semibold">Payment ID</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold">Amount</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold">Payment Date</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold">Method</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($payments as $payment)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 text-sm font-semibold text-gray-900">#{{ $payment->id }}</td>
                        <td class="px-6 py-4 text-sm font-semibold text-green-600">{{ number_format($payment->amount, 2) }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $payment->payment_date->format('M d, Y') }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ ucfirst($payment->payment_method ?? 'Unknown') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                            No payments recorded
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Charges Table -->
    @if($charges->count() > 0)
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-bold text-gray-900">Loan Charges ({{ $charges->count() }})</h2>
            </div>
            <table class="w-full">
                <thead class="bg-blue-600 text-white">
                    <tr>
                        <th class="px-6 py-4 text-left text-sm font-semibold">Charge ID</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold">Type</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold">Amount</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold">Date Added</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach($charges as $charge)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 text-sm font-semibold text-gray-900">#{{ $charge->id }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ ucfirst($charge->charge_type) }}</td>
                            <td class="px-6 py-4 text-sm font-semibold text-red-600">{{ number_format($charge->amount, 2) }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $charge->created_at->format('M d, Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
