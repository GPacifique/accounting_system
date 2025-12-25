@extends('layouts.admin')

@section('title', 'Group Members - ' . $group->name)

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Members of {{ $group->name }}</h1>
            <p class="text-gray-500 mt-1">Manage group members, roles, and permissions</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.groups.permissions', $group) }}" class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                Permission Matrix
            </a>
            <a href="{{ route('admin.groups.role-assignments.index', $group) }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                Bulk Assign Roles
            </a>
            <a href="{{ route('admin.groups.show', $group) }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Back to Group
            </a>
            <a href="{{ route('admin.groups.members.create', $group) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                + Add Member
            </a>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if ($message = Session::get('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            {{ $message }}
        </div>
    @endif

    @if ($message = Session::get('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            {{ $message }}
        </div>
    @endif

    <!-- Members Table -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-100 border-b">
                    <tr>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Member Name</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Email</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Role</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Status</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Joined</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Savings</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse ($members as $member)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <div class="font-medium">{{ $member->user->name }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ $member->user->email }}
                            </td>
                            <td class="px-6 py-4 text-sm">
                                @php
                                    $roleColors = [
                                        'admin' => 'bg-purple-100 text-purple-800',
                                        'treasurer' => 'bg-blue-100 text-blue-800',
                                        'member' => 'bg-gray-100 text-gray-800'
                                    ];
                                @endphp
                                <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $roleColors[$member->role] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ ucfirst($member->role) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                @php
                                    $statusColors = [
                                        'active' => 'bg-green-100 text-green-800',
                                        'inactive' => 'bg-yellow-100 text-yellow-800',
                                        'suspended' => 'bg-red-100 text-red-800'
                                    ];
                                @endphp
                                <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $statusColors[$member->status] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ ucfirst($member->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ $member->joined_at ? $member->joined_at->format('M d, Y') : 'N/A' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 font-medium">
                                GHS {{ number_format($member->current_savings, 2) }}
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <div class="flex gap-2">
                                    <a href="{{ route('admin.groups.members.edit', [$group, $member]) }}" class="text-blue-600 hover:text-blue-900 font-semibold">
                                        Edit
                                    </a>
                                    <form action="{{ route('admin.groups.members.destroy', [$group, $member]) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure? This will remove the member from the group.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900 font-semibold">
                                            Remove
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                <p class="text-sm">No members in this group yet.</p>
                                <a href="{{ route('admin.groups.members.create', $group) }}" class="text-blue-600 hover:text-blue-900 font-semibold mt-2 inline-block">
                                    Add the first member
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $members->links() }}
    </div>
</div>
@endsection
