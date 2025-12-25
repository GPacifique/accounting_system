@extends('layouts.admin')

@section('title', 'Role Assignments - ' . $group->name)

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Manage Roles - {{ $group->name }}</h1>
            <p class="text-gray-500 mt-1">Assign and manage member roles for this group</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.groups.permissions', $group) }}" class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                View Permissions
            </a>
            <a href="{{ route('admin.groups.members.index', $group) }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Back to Members
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
            <div class="text-red-700 font-bold mb-2">Validation Errors:</div>
            <ul class="text-red-600 list-disc ml-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Role Assignment Form -->
    <form action="{{ route('admin.groups.role-assignments.update', $group) }}" method="POST" class="bg-white rounded-lg shadow-lg p-8">
        @csrf
        @method('PUT')

        <!-- Instructions -->
        <div class="mb-8 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <p class="text-blue-800">
                <strong>Instructions:</strong> Select the role and status for each group member. Changes will be applied immediately upon submission.
            </p>
        </div>

        <!-- Members Table -->
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-blue-600 text-white border-b">
                    <tr>
                        <th class="px-6 py-4 text-left text-sm font-semibold">Member Name</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold">Email</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold">Current Role</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold">New Role</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse ($members as $member)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                {{ $member->user->name ?? 'Unknown' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ $member->user->email ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <span class="px-3 py-1 rounded-full text-xs font-bold
                                    @if ($member->role === 'admin')
                                        bg-purple-100 text-purple-800
                                    @elseif ($member->role === 'treasurer')
                                        bg-blue-100 text-blue-800
                                    @else
                                        bg-gray-100 text-gray-800
                                    @endif
                                ">
                                    {{ ucfirst($member->role) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <select name="members[{{ $loop->index }}][id]" value="{{ $member->id }}" class="hidden">
                                    <option value="{{ $member->id }}">{{ $member->id }}</option>
                                </select>
                                <input type="hidden" name="members[{{ $loop->index }}][id]" value="{{ $member->id }}">
                                <select name="members[{{ $loop->index }}][role]" class="px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    @foreach ($availableRoles as $role)
                                        <option value="{{ $role }}" @selected($member->role === $role)>
                                            {{ ucfirst($role) }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <select name="members[{{ $loop->index }}][status]" class="px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                    <option value="active" @selected($member->status === 'active')>Active</option>
                                    <option value="inactive" @selected($member->status === 'inactive')>Inactive</option>
                                    <option value="suspended" @selected($member->status === 'suspended')>Suspended</option>
                                </select>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                No members found in this group.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if ($members->hasPages())
            <div class="mt-6">
                {{ $members->links() }}
            </div>
        @endif

        <!-- Action Buttons -->
        <div class="mt-8 flex gap-4 justify-end">
            <a href="{{ route('admin.groups.members.index', $group) }}" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 font-semibold hover:bg-gray-50">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2 bg-indigo-500 hover:bg-indigo-700 text-white font-bold rounded-lg transition">
                Save Changes
            </button>
        </div>
    </form>

    <!-- Role Description Reference -->
    <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Admin Role -->
        <div class="bg-purple-50 border border-purple-200 rounded-lg p-6">
            <div class="flex items-center mb-4">
                <span class="inline-block px-3 py-1 rounded-full text-xs font-bold bg-purple-100 text-purple-800">Admin</span>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-3">Administrator</h3>
            <ul class="text-sm text-gray-700 space-y-2">
                <li class="flex items-start">
                    <svg class="w-4 h-4 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                    </svg>
                    Manage all group members
                </li>
                <li class="flex items-start">
                    <svg class="w-4 h-4 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                    </svg>
                    Approve loans and savings
                </li>
                <li class="flex items-start">
                    <svg class="w-4 h-4 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                    </svg>
                    Edit group settings
                </li>
                <li class="flex items-start">
                    <svg class="w-4 h-4 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                    </svg>
                    Manage member roles
                </li>
            </ul>
        </div>

        <!-- Treasurer Role -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
            <div class="flex items-center mb-4">
                <span class="inline-block px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800">Treasurer</span>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-3">Treasurer</h3>
            <ul class="text-sm text-gray-700 space-y-2">
                <li class="flex items-start">
                    <svg class="w-4 h-4 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                    </svg>
                    Manage financial records
                </li>
                <li class="flex items-start">
                    <svg class="w-4 h-4 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                    </svg>
                    Approve loans and savings
                </li>
                <li class="flex items-start">
                    <svg class="w-4 h-4 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                    </svg>
                    View financial reports
                </li>
                <li class="flex items-start">
                    <svg class="w-4 h-4 text-gray-300 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="text-gray-500">Cannot manage members</span>
                </li>
            </ul>
        </div>

        <!-- Member Role -->
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-6">
            <div class="flex items-center mb-4">
                <span class="inline-block px-3 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-800">Member</span>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-3">Regular Member</h3>
            <ul class="text-sm text-gray-700 space-y-2">
                <li class="flex items-start">
                    <svg class="w-4 h-4 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                    </svg>
                    View group information
                </li>
                <li class="flex items-start">
                    <svg class="w-4 h-4 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                    </svg>
                    Submit loan/savings requests
                </li>
                <li class="flex items-start">
                    <svg class="w-4 h-4 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                    </svg>
                    View personal records
                </li>
                <li class="flex items-start">
                    <svg class="w-4 h-4 text-gray-300 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="text-gray-500">Cannot approve transactions</span>
                </li>
            </ul>
        </div>
    </div>
</div>
@endsection
