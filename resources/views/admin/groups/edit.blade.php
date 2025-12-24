@extends('layouts.admin')

@section('title', 'Edit Group - ' . $group->name)

@section('content')
<div class="container mx-auto px-4 py-8 max-w-2xl">
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Edit Group: {{ $group->name }}</h1>
        <a href="{{ route('admin.groups.show', $group) }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
            Back to Group
        </a>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ route('admin.groups.update', $group) }}">
            @csrf
            @method('PUT')

            <!-- Group Name -->
            <div class="mb-6">
                <label for="name" class="block text-sm font-bold text-gray-700 mb-2">Group Name</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name', $group->name) }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 @error('name') border-red-500 @enderror"
                    required
                >
                @error('name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Description -->
            <div class="mb-6">
                <label for="description" class="block text-sm font-bold text-gray-700 mb-2">Description</label>
                <textarea
                    id="description"
                    name="description"
                    rows="4"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 @error('description') border-red-500 @enderror"
                >{{ old('description', $group->description) }}</textarea>
                @error('description')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Group Administrator -->
            <div class="mb-6">
                <label for="admin_id" class="block text-sm font-bold text-gray-700 mb-2">Group Administrator</label>
                <select
                    id="admin_id"
                    name="admin_id"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 @error('admin_id') border-red-500 @enderror"
                >
                    <option value="">Select Administrator (Optional)</option>
                    @foreach($admins as $admin)
                        <option value="{{ $admin->id }}" {{ old('admin_id', $group->admin_id) == $admin->id ? 'selected' : '' }}>
                            {{ $admin->name }} ({{ $admin->email }})
                        </option>
                    @endforeach
                </select>
                @error('admin_id')
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
                    <option value="active" {{ old('status', $group->status) === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ old('status', $group->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="suspended" {{ old('status', $group->status) === 'suspended' ? 'selected' : '' }}>Suspended</option>
                </select>
                @error('status')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Current Info -->
            <div class="mb-6 p-4 bg-gray-100 rounded-lg">
                <p class="text-sm text-gray-600"><strong>Created:</strong> {{ $group->created_at->format('M d, Y H:i A') }}</p>
                <p class="text-sm text-gray-600"><strong>Last Updated:</strong> {{ $group->updated_at->format('M d, Y H:i A') }}</p>
                <p class="text-sm text-gray-600"><strong>Total Members:</strong> {{ $group->members_count ?? 0 }}</p>
            </div>

            <!-- Action Buttons -->
            <div class="flex space-x-4 mt-8">
                <button
                    type="submit"
                    class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-6 rounded transition"
                >
                    Save Changes
                </button>
                <a
                    href="{{ route('admin.groups.show', $group) }}"
                    class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-6 rounded transition"
                >
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
