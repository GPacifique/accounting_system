@extends('layouts.app')

@section('title', 'Group Members - ' . $group->name)

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-gradient-to-r from-purple-600 to-purple-800 text-white shadow">
        <div class="max-w-6xl mx-auto py-8 px-4">
            <h1 class="text-3xl font-bold">Group Members</h1>
            <p class="text-purple-100 mt-2">{{ $group->name }}</p>
        </div>
    </div>

    <div class="max-w-6xl mx-auto py-12 px-4">
        <!-- Back Link -->
        <div class="mb-6">
            <a href="{{ route('group-admin.dashboard') }}" class="text-blue-600 hover:text-blue-800 font-semibold flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Dashboard
            </a>
        </div>

        <!-- Members Table -->
        <div class="bg-white rounded-lg shadow-sm">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-900">All Members</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-blue-600 text-white">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase">Role</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase">Joined</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($members as $member)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 whitespace-nowrap font-semibold text-gray-900">{{ $member->user->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $member->user->email }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $member->role === 'admin' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800' }}">
                                        {{ ucfirst($member->role) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $member->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                        {{ ucfirst($member->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $member->created_at->format('M d, Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                    No members found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($members->hasPages())
                <div class="px-6 py-4 border-t">
                    {{ $members->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
