<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard') - ItSinda</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-gradient-to-r from-gray-900 to-gray-800 text-white shadow-lg">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between h-16">
                <!-- Logo -->
                <div class="flex items-center space-x-4">
                    <a href="{{ route('admin.dashboard') }}" class="text-2xl font-bold">
                        ItSinda Admin
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="flex items-center space-x-6">
                    <a href="{{ route('admin.dashboard') }}" class="hover:text-blue-300 transition {{ request()->routeIs('admin.dashboard') ? 'text-blue-300 border-b-2 border-blue-300' : '' }}">
                        Dashboard
                    </a>
                    <a href="{{ route('admin.users.index') }}" class="hover:text-blue-300 transition {{ request()->routeIs('admin.users.*') ? 'text-blue-300 border-b-2 border-blue-300' : '' }}">
                        Users
                    </a>
                    <a href="{{ route('admin.groups.index') }}" class="hover:text-blue-300 transition {{ request()->routeIs('admin.groups.*') ? 'text-blue-300 border-b-2 border-blue-300' : '' }}">
                        Groups
                    </a>
                    <a href="{{ route('admin.loans.index') }}" class="hover:text-blue-300 transition {{ request()->routeIs('admin.loans.*') ? 'text-blue-300 border-b-2 border-blue-300' : '' }}">
                        Loans
                    </a>
                    <a href="{{ route('admin.savings.index') }}" class="hover:text-blue-300 transition {{ request()->routeIs('admin.savings.*') ? 'text-blue-300 border-b-2 border-blue-300' : '' }}">
                        Savings
                    </a>
                    <a href="{{ route('admin.transactions.index') }}" class="hover:text-blue-300 transition {{ request()->routeIs('admin.transactions.*') ? 'text-blue-300 border-b-2 border-blue-300' : '' }}">
                        Transactions
                    </a>
                    <a href="{{ route('admin.reports') }}" class="hover:text-blue-300 transition {{ request()->routeIs('admin.reports') ? 'text-blue-300 border-b-2 border-blue-300' : '' }}">
                        Reports
                    </a>
                </div>

                <!-- User Menu -->
                <div class="flex items-center space-x-4">
                    <div class="relative group">
                        <button class="flex items-center space-x-2 hover:text-blue-300 transition">
                            <span>{{ auth()->user()->name }}</span>
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                        <div class="absolute right-0 w-56 bg-white text-gray-900 rounded-lg shadow-lg hidden group-hover:block z-50">
                            <!-- Dashboard Links -->
                            <div class="border-b px-4 py-2 text-xs font-semibold text-gray-600 uppercase">Switch Dashboard</div>
                            @if(auth()->user()->is_admin)
                                <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 hover:bg-blue-50 text-blue-600 font-medium border-b">
                                    <span class="inline-block w-2 h-2 bg-blue-600 rounded-full mr-2"></span>System Admin
                                </a>
                            @endif
                            @if(auth()->user()->groupAdminGroups()->exists())
                                <a href="{{ route('group-admin.dashboard') }}" class="block px-4 py-2 hover:bg-green-50 text-green-600 font-medium border-b">
                                    <span class="inline-block w-2 h-2 bg-green-600 rounded-full mr-2"></span>Group Admin
                                </a>
                            @endif
                            @if(auth()->user()->isMemberOfGroup())
                                <a href="{{ route('member.dashboard') }}" class="block px-4 py-2 hover:bg-purple-50 text-purple-600 font-medium border-b">
                                    <span class="inline-block w-2 h-2 bg-purple-600 rounded-full mr-2"></span>Member
                                </a>
                            @endif

                            <!-- Other Options -->
                            <a href="{{ route('admin.settings') }}" class="block px-4 py-2 hover:bg-gray-100 border-b">Settings</a>
                            <a href="{{ route('profile.edit') }}" class="block px-4 py-2 hover:bg-gray-100 border-b">Profile</a>

                            <!-- Logout -->
                            <form method="POST" action="{{ route('logout') }}" class="block">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 hover:bg-red-50 text-red-600 font-medium">Logout</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main>
        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg m-4">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg m-4">
                {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white text-center py-6 mt-12">
        <p>&copy; {{ date('Y') }} ItSinda. All rights reserved. | Admin Dashboard v1.0</p>
    </footer>
</body>
</html>
