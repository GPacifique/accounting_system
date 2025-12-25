@extends('layouts.app')

@section('title', 'Record Loan Interest - ' . $group->name)

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-gradient-to-r from-red-600 to-red-800 text-white shadow">
        <div class="max-w-4xl mx-auto py-8 px-4">
            <h1 class="text-3xl font-bold">Record Loan Interest</h1>
            <p class="text-red-100 mt-2">{{ $group->name }}</p>
        </div>
    </div>

    <div class="max-w-4xl mx-auto py-12 px-4">
        <!-- Back Link -->
        <div class="mb-6">
            <a href="{{ route('group-admin.dashboard') }}" class="text-blue-600 hover:text-blue-800 font-semibold flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Dashboard
            </a>
        </div>

        <!-- Form Card -->
        <div class="bg-white rounded-lg shadow-sm p-8">
            <form action="{{ route('group-admin.store-interest', $group) }}" method="POST" class="space-y-6">
                @csrf

                <!-- Loan Selection -->
                <div>
                    <label for="loan_id" class="block text-sm font-semibold text-gray-700 mb-2">
                        Loan
                        <span class="text-red-600">*</span>
                    </label>
                    <select name="loan_id" id="loan_id" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent @error('loan_id') border-red-500 @enderror">
                        <option value="">-- Select Loan --</option>
                        @foreach($loans as $loan)
                            <option value="{{ $loan->id }}" {{ old('loan_id') == $loan->id ? 'selected' : '' }}>
                                {{ $loan->member->user->name }} - RWF {{ number_format($loan->principal_amount, 2) }} (Due: {{ $loan->maturity_date->format('M d, Y') }})
                            </option>
                        @endforeach
                    </select>
                    @error('loan_id')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Interest Amount -->
                <div>
                    <label for="interest_amount" class="block text-sm font-semibold text-gray-700 mb-2">
                        Interest Amount
                        <span class="text-red-600">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-4 top-3 text-gray-500 font-semibold">RWF</span>
                        <input type="number" name="interest_amount" id="interest_amount" step="0.01" min="0.01" required
                            class="w-full pl-12 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent @error('interest_amount') border-red-500 @enderror"
                            value="{{ old('interest_amount') }}"
                            placeholder="0.00">
                    </div>
                    @error('interest_amount')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Transaction Date -->
                <div>
                    <label for="transaction_date" class="block text-sm font-semibold text-gray-700 mb-2">
                        Transaction Date
                    </label>
                    <input type="date" name="transaction_date" id="transaction_date"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent @error('transaction_date') border-red-500 @enderror"
                        value="{{ old('transaction_date', today()->format('Y-m-d')) }}">
                    @error('transaction_date')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">
                        Notes
                    </label>
                    <textarea name="description" id="description" rows="4"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent @error('description') border-red-500 @enderror"
                        placeholder="Optional notes about this interest charge...">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Buttons -->
                <div class="flex gap-4 pt-6 border-t">
                    <button type="submit" class="flex-1 px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        Record Interest
                    </button>
                    <a href="{{ route('group-admin.dashboard') }}" class="flex-1 px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Cancel
                    </a>
                </div>
            </form>
        </div>

        <!-- Info Box -->
        <div class="mt-8 bg-blue-50 border-l-4 border-blue-500 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-blue-900 mb-2">ℹ️ How to Record Interest</h3>
            <ul class="text-blue-800 space-y-2 text-sm">
                <li>• Select the loan that is earning interest</li>
                <li>• Enter the interest amount being charged in RWF</li>
                <li>• Use the transaction date to record when the interest was charged</li>
                <li>• Add any notes about the interest charge (optional)</li>
                <li>• Click "Record Interest" to confirm and save the transaction</li>
            </ul>
        </div>
    </div>
</div>
@endsection
