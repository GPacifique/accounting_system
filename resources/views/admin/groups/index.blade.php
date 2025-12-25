@extends('layouts.admin')

@section('title', 'Groups Management')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-4xl font-bold text-gray-900">Groups Management</h1>
        <div class="flex gap-3">
            <a href="{{ route('admin.groups.create') }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded flex items-center gap-2">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.586L7.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 10.586V7z" clip-rule="evenodd"></path>
                </svg>
                ➕ Create New Group
            </a>
            <a href="{{ route('admin.dashboard') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Groups Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full">
            <thead class="bg-blue-600 text-white">
                <tr>
                    <th class="px-6 py-4 text-left text-sm font-semibold">ID</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold">Group Name</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold">Admin</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold">Members</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold">Status</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold">Created</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($groups as $group)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 text-sm font-semibold text-gray-900">#{{ $group->id }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900 font-semibold">{{ $group->name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $group->creator?->name ?? 'Not Assigned' }}</td>
                        <td class="px-6 py-4 text-sm">
                            <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full font-semibold">
                                {{ $group->members_count ?? 0 }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <span class="px-3 py-1 rounded-full text-xs font-bold {{ $group->status === 'active' ? 'bg-green-100 text-green-800' : ($group->status === 'suspended' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800') }}">
                                {{ ucfirst($group->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $group->created_at->format('M d, Y') }}</td>
                        <td class="px-6 py-4 text-sm">
                            <div class="flex space-x-2">
                                <a href="{{ route('admin.groups.show', $group) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-3 rounded text-xs">
                                    View
                                </a>
                                <a href="{{ route('admin.groups.edit', $group) }}" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-1 px-3 rounded text-xs">
                                    Edit
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                            No groups found
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $groups->links() }}
    </div>
</div>
@endsection
