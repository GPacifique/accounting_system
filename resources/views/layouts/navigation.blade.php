<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center space-x-2">
                    <img src="{{ asset('images/isubyo-logo-modern.svg') }}" alt="isubyo Logo" class="h-9 w-auto">
                    <a href="{{ route('dashboard') }}" class="text-lg font-bold text-gray-800">
                        isubyo
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex items-center">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>

                    <!-- View Switcher for Group Admins -->
                    @php
                        $isGroupAdmin = auth()->user() && auth()->check() &&
                            \App\Models\GroupMember::where('user_id', auth()->id())
                                ->where('role', 'admin')
                                ->where('status', 'active')
                                ->exists();
                        $isMember = auth()->user() && auth()->check() &&
                            \App\Models\GroupMember::where('user_id', auth()->id())
                                ->where('status', 'active')
                                ->exists();
                    @endphp

                    @if($isGroupAdmin && $isMember)
                        <div class="flex items-center space-x-2 border-l border-gray-200 pl-8">
                            <span class="text-xs text-gray-600 font-medium">View:</span>
                            <a href="{{ route('member.dashboard') }}"
                               class="text-sm px-3 py-2 rounded-md transition {{ request()->routeIs('member.*') ? 'bg-green-100 text-green-800 font-medium' : 'text-gray-600 hover:text-gray-900' }}">
                                Member
                            </a>
                            <a href="{{ route('group-admin.dashboard') }}"
                               class="text-sm px-3 py-2 rounded-md transition {{ request()->routeIs('group-admin.*') ? 'bg-green-100 text-green-800 font-medium' : 'text-gray-600 hover:text-gray-900' }}">
                                Admin
                            </a>
                        </div>
                    @endif
                </div>

                <!-- Search Box -->
                <div class="hidden lg:flex lg:items-center lg:ms-auto lg:me-6">
                    @include('components.search-box')
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-green-600 bg-white hover:text-green-800 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Group Admin - Switch Groups (Only for Non-System Admins) -->
                        @php
                            $adminGroups = auth()->user() && auth()->check() && !auth()->user()->is_admin ?
                                \App\Models\GroupMember::where('user_id', auth()->id())
                                    ->where('role', 'admin')
                                    ->where('status', 'active')
                                    ->with('group')
                                    ->get() : collect();
                        @endphp

                        @if($adminGroups->count() > 0)
                            <div class="border-t border-gray-100"></div>
                            <div class="px-4 py-2 text-xs font-semibold text-gray-700 uppercase">Groups You Manage</div>
                            @foreach($adminGroups as $gm)
                                <x-dropdown-link :href="route('group-admin.dashboard')">
                                    🏢 {{ $gm->group->name }}
                                </x-dropdown-link>
                            @endforeach
                        @endif

                        <!-- System Admin Links (Restricted to System Admins Only) -->
                        @if(auth()->user()->is_admin)
                            <div class="border-t border-gray-100"></div>
                            <x-dropdown-link :href="route('admin.roles.index')">
                                {{ __('Manage Roles') }}
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('admin.permissions.index')">
                                {{ __('Manage Permissions') }}
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('admin.user-roles.index')">
                                {{ __('User Roles') }}
                            </x-dropdown-link>
                        @endif

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>

            <!-- View Switcher for Group Admins (Mobile) -->
            @php
                $isGroupAdmin = auth()->user() && auth()->check() &&
                    \App\Models\GroupMember::where('user_id', auth()->id())
                        ->where('role', 'admin')
                        ->where('status', 'active')
                        ->exists();
                $isMember = auth()->user() && auth()->check() &&
                    \App\Models\GroupMember::where('user_id', auth()->id())
                        ->where('status', 'active')
                        ->exists();
            @endphp

            @if($isGroupAdmin && $isMember)
                <div class="px-4 py-2 border-t border-gray-200 mt-2 pt-3">
                    <p class="text-xs font-semibold text-gray-600 uppercase mb-2">Switch View</p>
                    <x-responsive-nav-link :href="route('member.dashboard')" :active="request()->routeIs('member.*')">
                        📊 Member Dashboard
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('group-admin.dashboard')" :active="request()->routeIs('group-admin.*')">
                        ⚙️ Admin Dashboard
                    </x-responsive-nav-link>
                </div>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-green-700">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-green-600">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Admin Links -->
                @if(auth()->user()->is_admin)
                    <x-responsive-nav-link :href="route('admin.roles.index')">
                        {{ __('Manage Roles') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.permissions.index')">
                        {{ __('Manage Permissions') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.user-roles.index')">
                        {{ __('User Roles') }}
                    </x-responsive-nav-link>
                @endif

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
