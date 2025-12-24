@extends('layouts.admin')

@section('title', 'Edit User - ' . $user->name)

@section('content')
<div class="container mx-auto px-4 py-8 max-w-2xl">
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Edit User: {{ $user->name }}</h1>
        <a href="{{ route('admin.users.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
            Back to Users
        </a>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ route('admin.users.update', $user) }}">
            @csrf
            @method('PUT')

            <!-- User ID Info -->
            <div class="mb-6 p-4 bg-gray-100 rounded-lg">
                <p class="text-sm text-gray-600"><strong>User ID:</strong> #{{ $user->id }}</p>
                <p class="text-sm text-gray-600"><strong>Created:</strong> {{ $user->created_at->format('M d, Y H:i A') }}</p>
                <p class="text-sm text-gray-600"><strong>Last Updated:</strong> {{ $user->updated_at->format('M d, Y H:i A') }}</p>
            </div>

            <!-- Name -->
            <div class="mb-6">
                <label for="name" class="block text-sm font-bold text-gray-700 mb-2">Full Name</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name', $user->name) }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 @error('name') border-red-500 @enderror"
                    required
                >
                @error('name')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email -->
            <div class="mb-6">
                <label for="email" class="block text-sm font-bold text-gray-700 mb-2">Email Address</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email', $user->email) }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 @error('email') border-red-500 @enderror"
                    required
                >
                @error('email')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Admin Status -->
            <div class="mb-6">
                <label class="flex items-center space-x-3 cursor-pointer">
                    <input
                        type="checkbox"
                        name="is_admin"
                        value="1"
                        {{ old('is_admin', $user->is_admin) ? 'checked' : '' }}
                        class="w-4 h-4 text-blue-600 rounded focus:ring-2 focus:ring-blue-500"
                    >
                    <span class="text-sm font-bold text-gray-700">System Administrator</span>
                </label>
                <p class="text-xs text-gray-500 mt-1 ml-7">Check this box to grant admin privileges to this user</p>
            </div>

            <!-- Email Verification -->
            <div class="mb-6">
                <label class="flex items-center space-x-3 cursor-pointer">
                    <input
                        type="checkbox"
                        id="email_verified"
                        name="email_verified_at"
                        value="{{ now() }}"
                        {{ $user->email_verified_at ? 'checked' : '' }}
                        class="w-4 h-4 text-green-600 rounded focus:ring-2 focus:ring-green-500"
                    >
                    <span class="text-sm font-bold text-gray-700">Email Verified</span>
                </label>
                <p class="text-xs text-gray-500 mt-1 ml-7">Check to mark email as verified</p>
            </div>

            <!-- Associated Groups -->
            @if($groups->count() > 0)
                <div class="mb-6">
                    <h3 class="text-sm font-bold text-gray-700 mb-3">Associated Groups</h3>
                    <div class="space-y-2">
                        @foreach($groups as $group)
                            <div class="px-4 py-2 bg-blue-50 border border-blue-200 rounded-lg">
                                <p class="text-sm font-semibold text-gray-900">{{ $group->name }}</p>
                                <p class="text-xs text-gray-600">Role: <span class="font-semibold">{{ $group->pivot->role ?? 'Member' }}</span></p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Action Buttons -->
            <div class="flex space-x-4 mt-8">
                <button
                    type="submit"
                    class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-6 rounded transition"
                >
                    Save Changes
                </button>
                <a
                    href="{{ route('admin.users.index') }}"
                    class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-6 rounded transition"
                >
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
