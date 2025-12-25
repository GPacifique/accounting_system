@extends('layouts.admin')

@section('title', 'Create New Loan')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-2xl">
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Create New Loan</h1>
        <a href="{{ route('admin.loans.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
            Back to Loans
        </a>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ route('admin.loans.store') }}">
            @csrf

            <!-- Group Member -->
            <div class="mb-6">
                <label for="group_member_id" class="block text-sm font-bold text-gray-700 mb-2">Group Member</label>
                <select
                    id="group_member_id"
                    name="group_member_id"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 @error('group_member_id') border-red-500 @enderror"
                    required
                >
                    <option value="">-- Select a Member --</option>
                    @foreach($members as $member)
                        <option value="{{ $member->id }}" {{ old('group_member_id') == $member->id ? 'selected' : '' }}>
                            {{ $member->user->name }} ({{ $member->group->name }})
                        </option>
                    @endforeach
                </select>
                @error('group_member_id')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Principal Amount -->
            <div class="mb-6">
                <label for="principal_amount" class="block text-sm font-bold text-gray-700 mb-2">Principal Amount</label>
                <input
                    type="number"
                    id="principal_amount"
                    name="principal_amount"
                    value="{{ old('principal_amount') }}"
                    step="0.01"
                    min="0"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 @error('principal_amount') border-red-500 @enderror"
                    placeholder="0.00"
                    required
                >
                @error('principal_amount')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Interest Rate -->
            <div class="mb-6">
                <label for="interest_rate" class="block text-sm font-bold text-gray-700 mb-2">Interest Rate (%)</label>
                <input
                    type="number"
                    id="interest_rate"
                    name="interest_rate"
                    value="{{ old('interest_rate') }}"
                    step="0.01"
                    min="0"
                    max="100"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 @error('interest_rate') border-red-500 @enderror"
                    placeholder="0.00"
                    required
                >
                @error('interest_rate')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Loan Term (Months) -->
            <div class="mb-6">
                <label for="loan_term_months" class="block text-sm font-bold text-gray-700 mb-2">Loan Term (Months)</label>
                <input
                    type="number"
                    id="loan_term_months"
                    name="loan_term_months"
                    value="{{ old('loan_term_months') }}"
                    min="1"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 @error('loan_term_months') border-red-500 @enderror"
                    placeholder="12"
                    required
                >
                @error('loan_term_months')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Status -->
            <div class="mb-6">
                <label for="status" class="block text-sm font-bold text-gray-700 mb-2">Status</label>
                <select
                    id="status"
                    name="status"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 @error('status') border-red-500 @enderror"
                    required
                >
                    <option value="">-- Select Status --</option>
                    <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ old('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="disbursed" {{ old('status') == 'disbursed' ? 'selected' : '' }}>Disbursed</option>
                    <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="defaulted" {{ old('status') == 'defaulted' ? 'selected' : '' }}>Defaulted</option>
                    <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
                @error('status')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Description -->
            <div class="mb-6">
                <label for="description" class="block text-sm font-bold text-gray-700 mb-2">Description (Optional)</label>
                <textarea
                    id="description"
                    name="description"
                    rows="4"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 @error('description') border-red-500 @enderror"
                    placeholder="Enter loan details..."
                >{{ old('description') }}</textarea>
                @error('description')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Form Actions -->
            <div class="flex justify-between gap-3 pt-6 border-t">
                <a href="{{ route('admin.loans.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Cancel
                </a>
                <button
                    type="submit"
                    class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-6 rounded"
                >
                    Create Loan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
