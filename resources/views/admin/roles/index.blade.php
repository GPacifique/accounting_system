@extends('layouts.app')

@section('title', 'Manage Roles - Admin Dashboard')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-gradient-to-r from-indigo-600 to-indigo-800 text-white shadow">
        <div class="max-w-7xl mx-auto py-8 px-4 flex justify-between items-center">
            <div>
                <h1 class="text-4xl font-bold">Manage Roles</h1>
                <p class="text-indigo-100 mt-2">Create, edit, and manage system roles</p>
            </div>
            <a href="{{ route('admin.roles.create') }}" class="bg-indigo-500 hover:bg-indigo-400 px-6 py-3 rounded-lg font-semibold transition">
                + Create Role
            </a>
        </div>
    </div>

    <div class="max-w-7xl mx-auto py-12 px-4">
        <!-- Alerts -->
        @if($message = session('success'))
            <div class="bg-green-50 border-l-4 border-green-500 rounded-lg p-4 mb-6">
                <div class="flex items-center">
                    <svg class="h-5 w-5 text-green-600 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/>
                    </svg>
                    <p class="text-green-800 font-medium">{{ $message }}</p>
                </div>
            </div>
        @endif

        @if($message = session('error'))
            <div class="bg-red-50 border-l-4 border-red-500 rounded-lg p-4 mb-6">
                <div class="flex items-center">
                    <svg class="h-5 w-5 text-red-600 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"/>
                    </svg>
                    <p class="text-red-800 font-medium">{{ $message }}</p>
                </div>
            </div>
        @endif

        <!-- Roles Table -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <table class="w-full">
                <thead class="bg-blue-600 text-white">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Role Name</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Display Name</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Permissions</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($roles as $role)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $role->name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-700">{{ $role->display_name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-700">
                                <span class="inline-block bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-xs font-medium">
                                    {{ $role->permissions()->count() }} permissions
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <span class="inline-block {{ $role->is_system ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800' }} px-3 py-1 rounded-full text-xs font-medium">
                                    {{ $role->is_system ? 'System' : 'Custom' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm space-x-2">
                                <a href="{{ route('admin.roles.assignments', $role) }}" class="inline-block bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded transition text-xs">
                                    View Users
                                </a>
                                @if(!$role->is_system)
                                    <a href="{{ route('admin.roles.edit', $role) }}" class="inline-block bg-indigo-500 hover:bg-indigo-600 text-white px-3 py-1 rounded transition text-xs">
                                        Edit
                                    </a>
                                    <form method="POST" action="{{ route('admin.roles.destroy', $role) }}" class="inline-block" onsubmit="return confirm('Are you sure?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded transition text-xs">
                                            Delete
                                        </button>
                                    </form>
                                @else
                                    <a href="{{ route('admin.roles.edit', $role) }}" class="inline-block bg-gray-500 hover:bg-gray-600 text-white px-3 py-1 rounded transition text-xs opacity-75 cursor-not-allowed" disabled title="System roles cannot be edited">
                                        Edit
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                No roles found. <a href="{{ route('admin.roles.create') }}" class="text-indigo-600 hover:text-indigo-800 font-semibold">Create one</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $roles->links() }}
        </div>
    </div>
</div>
@endsection
