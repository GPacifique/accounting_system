@extends('layouts.admin')

@section('title', 'Create New Saving')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-2xl">
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Create New Saving</h1>
        <a href="{{ route('admin.savings.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
            Back to Savings
        </a>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ route('admin.savings.store') }}">
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

            <!-- Current Balance -->
            <div class="mb-6">
                <label for="current_balance" class="block text-sm font-bold text-gray-700 mb-2">Current Balance</label>
                <input
                    type="number"
                    id="current_balance"
                    name="current_balance"
                    value="{{ old('current_balance') }}"
                    step="0.01"
                    min="0"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 @error('current_balance') border-red-500 @enderror"
                    placeholder="0.00"
                    required
                >
                @error('current_balance')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Interest Rate -->
            <div class="mb-6">
                <label for="interest_rate" class="block text-sm font-bold text-gray-700 mb-2">Interest Rate (%) - Optional</label>
                <input
                    type="number"
                    id="interest_rate"
                    name="interest_rate"
                    value="{{ old('interest_rate', 0) }}"
                    step="0.01"
                    min="0"
                    max="100"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 @error('interest_rate') border-red-500 @enderror"
                    placeholder="0.00"
                >
                @error('interest_rate')
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
                    <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="dormant" {{ old('status') == 'dormant' ? 'selected' : '' }}>Dormant</option>
                    <option value="closed" {{ old('status') == 'closed' ? 'selected' : '' }}>Closed</option>
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
                    placeholder="Enter saving details..."
                >{{ old('description') }}</textarea>
                @error('description')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Form Actions -->
            <div class="flex justify-between gap-3 pt-6 border-t">
                <a href="{{ route('admin.savings.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Cancel
                </a>
                <button
                    type="submit"
                    class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-6 rounded"
                >
                    Create Saving
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
