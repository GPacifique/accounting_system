@extends('layouts.app')

@section('title', 'Manage User Roles - Admin Dashboard')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-gradient-to-r from-indigo-600 to-indigo-800 text-white shadow">
        <div class="max-w-7xl mx-auto py-8 px-4">
            <h1 class="text-4xl font-bold">Manage User Roles</h1>
            <p class="text-indigo-100 mt-2">Assign and manage roles for system users</p>
        </div>
    </div>

    <div class="max-w-7xl mx-auto py-12 px-4">
        <!-- Alerts -->
        @if($message = session('success'))
            <div class="bg-green-50 border-l-4 border-green-500 rounded-lg p-4 mb-6">
                <p class="text-green-800 font-medium">{{ $message }}</p>
            </div>
        @endif

        <!-- Users Table -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <table class="w-full">
                <thead class="bg-blue-600 text-white">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">User Name</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">System Admin</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Assigned Roles</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($users as $user)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $user->name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-700">{{ $user->email }}</td>
                            <td class="px-6 py-4 text-sm">
                                @if($user->is_admin)
                                    <span class="inline-block bg-red-100 text-red-800 px-3 py-1 rounded-full text-xs font-medium">
                                        Yes
                                    </span>
                                @else
                                    <span class="inline-block bg-gray-100 text-gray-800 px-3 py-1 rounded-full text-xs font-medium">
                                        No
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm">
                                @if($user->userRoles()->exists())
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($user->userRoles as $userRole)
                                            <span class="inline-block bg-indigo-100 text-indigo-800 px-2 py-1 rounded text-xs">
                                                {{ $userRole->role->display_name }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-gray-500 text-xs">No roles assigned</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <a href="{{ route('admin.user-roles.edit', $user) }}" class="inline-block bg-indigo-500 hover:bg-indigo-600 text-white px-3 py-1 rounded transition text-xs">
                                    Manage Roles
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                No users found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $users->links() }}
        </div>
    </div>
</div>
@endsection
