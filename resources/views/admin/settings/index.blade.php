@extends('layouts.admin')

@section('title', 'Admin Settings')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-3xl">
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-4xl font-bold text-gray-900">Admin Settings</h1>
        <a href="{{ route('admin.dashboard') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
            Back to Dashboard
        </a>
    </div>

    <!-- System Settings -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-xl font-bold text-gray-900 mb-4">System Configuration</h2>
        <div class="space-y-4">
            <div class="p-4 bg-blue-50 rounded-lg border-l-4 border-blue-500">
                <h3 class="font-bold text-gray-900 mb-2">Current Admin User</h3>
                <p class="text-gray-700">{{ auth()->user()->name }} ({{ auth()->user()->email }})</p>
            </div>

            <div class="p-4 bg-green-50 rounded-lg border-l-4 border-green-500">
                <h3 class="font-bold text-gray-900 mb-2">System Version</h3>
                <p class="text-gray-700">ItSinda v1.0</p>
            </div>

            <div class="p-4 bg-yellow-50 rounded-lg border-l-4 border-yellow-500">
                <h3 class="font-bold text-gray-900 mb-2">Last Backup</h3>
                <p class="text-gray-700">Not yet configured</p>
            </div>
        </div>
    </div>

    <!-- Admin Actions -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-xl font-bold text-gray-900 mb-4">Admin Actions</h2>
        <div class="space-y-3">
            <a href="#" class="block px-4 py-3 bg-blue-50 hover:bg-blue-100 rounded-lg transition border-l-4 border-blue-500">
                <h3 class="font-bold text-gray-900">System Backup</h3>
                <p class="text-sm text-gray-600 mt-1">Create a backup of the entire database</p>
            </a>

            <a href="#" class="block px-4 py-3 bg-green-50 hover:bg-green-100 rounded-lg transition border-l-4 border-green-500">
                <h3 class="font-bold text-gray-900">Clear Cache</h3>
                <p class="text-sm text-gray-600 mt-1">Clear all system caches to free up memory</p>
            </a>

            <a href="#" class="block px-4 py-3 bg-purple-50 hover:bg-purple-100 rounded-lg transition border-l-4 border-purple-500">
                <h3 class="font-bold text-gray-900">System Logs</h3>
                <p class="text-sm text-gray-600 mt-1">View detailed system logs and error messages</p>
            </a>

            <a href="#" class="block px-4 py-3 bg-orange-50 hover:bg-orange-100 rounded-lg transition border-l-4 border-orange-500">
                <h3 class="font-bold text-gray-900">Email Configuration</h3>
                <p class="text-sm text-gray-600 mt-1">Configure email settings for notifications</p>
            </a>

            <a href="#" class="block px-4 py-3 bg-red-50 hover:bg-red-100 rounded-lg transition border-l-4 border-red-500">
                <h3 class="font-bold text-gray-900">Data Management</h3>
                <p class="text-sm text-gray-600 mt-1">Manage and export system data (USE WITH CAUTION)</p>
            </a>
        </div>
    </div>

    <!-- Security Information -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-xl font-bold text-gray-900 mb-4">Security Information</h2>
        <div class="space-y-3 text-sm text-gray-700">
            <p class="flex items-start">
                <span class="text-green-500 font-bold mr-3">✓</span>
                Password hashing is enabled and configured
            </p>
            <p class="flex items-start">
                <span class="text-green-500 font-bold mr-3">✓</span>
                User authentication is secured with Laravel's built-in auth
            </p>
            <p class="flex items-start">
                <span class="text-green-500 font-bold mr-3">✓</span>
                CSRF protection is enabled on all forms
            </p>
            <p class="flex items-start">
                <span class="text-green-500 font-bold mr-3">✓</span>
                Rate limiting is configured for login attempts
            </p>
            <p class="flex items-start">
                <span class="text-yellow-500 font-bold mr-3">!</span>
                Remember to regularly update all dependencies and libraries
            </p>
        </div>
    </div>

    <!-- Quick Links -->
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-bold text-gray-900 mb-4">Quick Links</h2>
        <div class="grid grid-cols-2 gap-4">
            <a href="{{ route('admin.dashboard') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-center font-semibold text-gray-900 transition">
                Dashboard
            </a>
            <a href="{{ route('admin.users.index') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-center font-semibold text-gray-900 transition">
                Users
            </a>
            <a href="{{ route('admin.groups.index') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-center font-semibold text-gray-900 transition">
                Groups
            </a>
            <a href="{{ route('admin.reports') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-center font-semibold text-gray-900 transition">
                Reports
            </a>
        </div>
    </div>
</div>
@endsection
