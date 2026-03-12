<nav x-data="{ open: false }" class="bg-slate-950 border-b border-slate-700 relative z-40">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    @php
                        $metadata = \App\Models\SiteMetadata::first();
                    @endphp
                    <a href="{{ route('home') }}" class="hover:opacity-80 transition-opacity duration-200">
                        @if($metadata && $metadata->favicon_path)
                            <img src="{{ asset('storage/' . $metadata->favicon_path) }}" alt="Logo" class="h-14 sm:h-14 lg:h-16 w-14 sm:w-14 lg:w-16 rounded-full object-cover shadow-lg" />
                        @else
                            <x-application-logo class="block h-14 sm:h-14 lg:h-16 w-auto fill-current text-white shadow-lg rounded-full" />
                        @endif
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('kdrams.catalog')" :active="request()->routeIs('kdrams.*')">
                        {{ __('common.nav_kdrams') }}
                    </x-nav-link>
                    <x-nav-link :href="route('contact.show')" :active="request()->routeIs('contact.*')">
                        {{ __('common.nav_contact') }}
                    </x-nav-link>
                    @auth
                        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                            {{ __('common.nav_dashboard') }}
                        </x-nav-link>
                        <x-nav-link :href="route('watchlist.index')" :active="request()->routeIs('watchlist.*')">
                            {{ __('common.nav_watchlist') }}
                        </x-nav-link>
                        @if(auth()->user()->is_admin)
                            <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.*')">
                                {{ __('common.nav_admin') }}
                            </x-nav-link>
                        @endif
                    @endauth
                </div>
            </div>

            <!-- Settings Dropdown -->
            @auth
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-slate-800 hover:text-slate-200 focus:outline-none transition ease-in-out duration-150">
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
            @else
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <a href="{{ route('login') }}" class="text-slate-300 hover:text-white mx-2">{{ __('auth.log_in') }}</a>
                <a href="{{ route('register') }}" class="text-slate-300 hover:text-white mx-2">{{ __('auth.register') }}</a>
            </div>
            @endauth

            <!-- Hamburger Menu -->
            <div class="flex items-center sm:hidden">
                <button @click="open = !open" class="relative inline-flex items-center justify-center p-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 focus:outline-none transition-all duration-200">
                    <svg class="h-6 w-6 transition-transform duration-300" :class="open ? 'rotate-90' : ''" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': !open}" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': !open, 'inline-flex': open}" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Menu Backdrop -->
    <div class="fixed inset-0 bg-black/40 z-20 sm:hidden transition-opacity duration-300"
         :class="open ? 'opacity-100 pointer-events-auto' : 'opacity-0 pointer-events-none'"
         @click="open = false"></div>

    <!-- Responsive Drawer Navigation Menu -->
    <div class="fixed left-0 top-16 h-screen w-64 z-30 sm:hidden transition-transform duration-300 ease-out"
         :class="open ? 'translate-x-0' : '-translate-x-full'"
         style="background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);">
        <div class="overflow-y-auto h-full flex flex-col">
            <!-- User Menu (Top) -->
            @auth
            <div class="py-3 px-3 border-b border-slate-700/50">
                <div class="px-2 py-3 mb-2 bg-slate-800/50 rounded-lg">
                    <div class="font-medium text-sm text-white truncate">👤 {{ Auth::user()->name }}</div>
                    <div class="text-xs text-slate-400 truncate">{{ Auth::user()->email }}</div>
                </div>

                <div class="space-y-1">
                    <a href="{{ route('profile.edit') }}" class="block px-4 py-2 rounded-lg text-sm text-slate-200 hover:text-white hover:bg-slate-800 transition-colors duration-200">
                        ⚡ {{ __('common.nav_profile') }}
                    </a>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-left px-4 py-2 rounded-lg text-sm text-slate-200 hover:text-white hover:bg-red-500/20 transition-colors duration-200">
                            🚪 {{ __('common.nav_logout') }}
                        </button>
                    </form>
                </div>
            </div>
            @endauth

            <!-- Navigation Links -->
            <div class="py-4 space-y-1 px-3">
                <a href="{{ route('kdrams.catalog') }}" class="block px-4 py-3 rounded-lg text-slate-200 hover:text-white hover:bg-slate-800 transition-colors duration-200 {{ request()->routeIs('kdrams.*') ? 'bg-red-500/20 text-red-400' : '' }}">
                    🎬 {{ __('common.nav_kdrams') }}
                </a>
                <a href="{{ route('contact.show') }}" class="block px-4 py-3 rounded-lg text-slate-200 hover:text-white hover:bg-slate-800 transition-colors duration-200 {{ request()->routeIs('contact.*') ? 'bg-red-500/20 text-red-400' : '' }}">
                    ✉️ {{ __('common.nav_contact') }}
                </a>
                @auth
                    <a href="{{ route('dashboard') }}" class="block px-4 py-3 rounded-lg text-slate-200 hover:text-white hover:bg-slate-800 transition-colors duration-200 {{ request()->routeIs('dashboard') ? 'bg-red-500/20 text-red-400' : '' }}">
                        📊 {{ __('common.nav_dashboard') }}
                    </a>
                    <a href="{{ route('watchlist.index') }}" class="block px-4 py-3 rounded-lg text-slate-200 hover:text-white hover:bg-slate-800 transition-colors duration-200 {{ request()->routeIs('watchlist.*') ? 'bg-red-500/20 text-red-400' : '' }}">
                        ❤️ {{ __('common.nav_watchlist') }}
                    </a>
                    @if(auth()->user()->is_admin)
                        <a href="{{ route('admin.dashboard') }}" class="block px-4 py-3 rounded-lg text-slate-200 hover:text-white hover:bg-slate-800 transition-colors duration-200 {{ request()->routeIs('admin.*') ? 'bg-red-500/20 text-red-400' : '' }}">
                            ⚙️ {{ __('common.nav_admin') }}
                        </a>
                    @endif
                @endauth

                <!-- Login/Register (In main nav) -->
                @guest
                    <div class="border-t border-slate-700 mt-3 pt-3 space-y-1">
                        <a href="{{ route('login') }}" class="block px-4 py-3 rounded-lg text-slate-200 hover:text-white hover:bg-slate-800 transition-colors duration-200">
                            🔑 {{ __('auth.log_in') }}
                        </a>
                        <a href="{{ route('register') }}" class="block px-4 py-3 rounded-lg text-slate-200 hover:text-white hover:bg-slate-800 transition-colors duration-200">
                            ✍️ {{ __('auth.register') }}
                        </a>
                    </div>
                @endguest
            </div>
        </div>
    </div>
</nav>
