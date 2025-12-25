@extends('layouts.app')

@section('title', 'Manage Permissions - Admin Dashboard')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-gradient-to-r from-indigo-600 to-indigo-800 text-white shadow">
        <div class="max-w-7xl mx-auto py-8 px-4 flex justify-between items-center">
            <div>
                <h1 class="text-4xl font-bold">Manage Permissions</h1>
                <p class="text-indigo-100 mt-2">Create, edit, and manage system permissions</p>
            </div>
            <a href="{{ route('admin.permissions.create') }}" class="bg-indigo-500 hover:bg-indigo-400 px-6 py-3 rounded-lg font-semibold transition">
                + Create Permission
            </a>
        </div>
    </div>

    <div class="max-w-7xl mx-auto py-12 px-4">
        <!-- Alerts -->
        @if($message = session('success'))
            <div class="bg-green-50 border-l-4 border-green-500 rounded-lg p-4 mb-6">
                <p class="text-green-800 font-medium">{{ $message }}</p>
            </div>
        @endif

        @if($message = session('error'))
            <div class="bg-red-50 border-l-4 border-red-500 rounded-lg p-4 mb-6">
                <p class="text-red-800 font-medium">{{ $message }}</p>
            </div>
        @endif

        <!-- Permissions by Category -->
        <div class="space-y-6">
            @foreach($grouped as $category => $permissionList)
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="bg-gray-100 px-6 py-4 border-b">
                        <h2 class="text-lg font-semibold text-gray-800 capitalize">{{ str_replace('_', ' ', $category) }} Management</h2>
                        <p class="text-sm text-gray-600">{{ $permissionList->count() }} permissions</p>
                    </div>

                    <table class="w-full">
                        <thead class="bg-blue-600 text-white">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Permission Name</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Display Name</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Used By</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($permissionList as $permission)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $permission->name }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-700">{{ $permission->display_name }}</td>
                                    <td class="px-6 py-4 text-sm">
                                        <span class="inline-block bg-indigo-100 text-indigo-800 px-3 py-1 rounded-full text-xs font-medium">
                                            {{ $permission->roles()->count() }} roles
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm space-x-2">
                                        <a href="{{ route('admin.permissions.edit', $permission) }}" class="inline-block bg-indigo-500 hover:bg-indigo-600 text-white px-3 py-1 rounded transition text-xs">
                                            Edit
                                        </a>
                                        <form method="POST" action="{{ route('admin.permissions.destroy', $permission) }}" class="inline-block" onsubmit="return confirm('Are you sure?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded transition text-xs disabled:opacity-50 disabled:cursor-not-allowed"
                                                @if($permission->roles()->exists()) disabled title="Permission is assigned to roles" @endif>
                                                Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endforeach
        </div>

        @if($permissions->isEmpty())
            <div class="bg-white rounded-lg shadow-md p-8 text-center">
                <p class="text-gray-500 mb-4">No permissions found.</p>
                <a href="{{ route('admin.permissions.create') }}" class="text-indigo-600 hover:text-indigo-800 font-semibold">Create one</a>
            </div>
        @endif
    </div>
</div>
@endsection
