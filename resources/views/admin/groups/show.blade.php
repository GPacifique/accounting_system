@extends('layouts.admin')

@section('title', 'View Group - ' . $group->name)

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Group: {{ $group->name }}</h1>
        <div class="space-x-4">
            <a href="{{ route('admin.groups.members.index', $group) }}" class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                Manage Members
            </a>
            <a href="{{ route('admin.groups.edit', $group) }}" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded">
                Edit Group
            </a>
            <a href="{{ route('admin.groups.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Back to Groups
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Group Info -->
        <div class="md:col-span-2 bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Group Information</h2>
            <div class="space-y-3">
                <div class="py-2 border-b">
                    <p class="text-xs text-gray-500 uppercase">Group ID</p>
                    <p class="text-lg font-semibold text-gray-900">#{{ $group->id }}</p>
                </div>
                <div class="py-2 border-b">
                    <p class="text-xs text-gray-500 uppercase">Name</p>
                    <p class="text-lg font-semibold text-gray-900">{{ $group->name }}</p>
                </div>
                <div class="py-2 border-b">
                    <p class="text-xs text-gray-500 uppercase">Description</p>
                    <p class="text-sm text-gray-700">{{ $group->description ?? 'No description provided' }}</p>
                </div>
                <div class="py-2 border-b">
                    <p class="text-xs text-gray-500 uppercase">Administrator</p>
                    <p class="text-sm font-semibold text-gray-900">
                        {{ $group->admin?->name ?? 'Not Assigned' }}
                        @if($group->admin)
                            <span class="text-xs text-gray-500">({{ $group->admin->email }})</span>
                        @endif
                    </p>
                </div>
                <div class="py-2 border-b">
                    <p class="text-xs text-gray-500 uppercase">Status</p>
                    <span class="inline-block px-3 py-1 rounded-full text-xs font-bold {{ $group->status === 'active' ? 'bg-green-100 text-green-800' : ($group->status === 'suspended' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800') }}">
                        {{ ucfirst($group->status) }}
                    </span>
                </div>
                <div class="py-2">
                    <p class="text-xs text-gray-500 uppercase">Created</p>
                    <p class="text-sm text-gray-700">{{ $group->created_at->format('M d, Y H:i A') }}</p>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Quick Stats</h3>
            <div class="space-y-4">
                <div class="text-center py-3 bg-blue-50 rounded-lg">
                    <p class="text-2xl font-bold text-blue-600">{{ $members->total() }}</p>
                    <p class="text-sm text-gray-600">Total Members</p>
                </div>
                <div class="text-center py-3 bg-green-50 rounded-lg">
                    <p class="text-2xl font-bold text-green-600">0</p>
                    <p class="text-sm text-gray-600">Total Loans</p>
                </div>
                <div class="text-center py-3 bg-yellow-50 rounded-lg">
                    <p class="text-2xl font-bold text-yellow-600">0</p>
                    <p class="text-sm text-gray-600">Active Savings</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Members Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-bold text-gray-900">Group Members ({{ $members->total() }})</h2>
        </div>
        <table class="w-full">
            <thead class="bg-blue-600 text-white">
                <tr>
                    <th class="px-6 py-4 text-left text-sm font-semibold">ID</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold">Member Name</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold">Email</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold">Role</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold">Joined</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($members as $member)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 text-sm font-semibold text-gray-900">#{{ $member->id }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $member->user->name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $member->user->email }}</td>
                        <td class="px-6 py-4 text-sm">
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800">
                                {{ ucfirst($member->role) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $member->created_at->format('M d, Y') }}</td>
                        <td class="px-6 py-4 text-sm">
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800">
                                Active
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                            No members found
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $members->links() }}
    </div>
</div>
@endsection
