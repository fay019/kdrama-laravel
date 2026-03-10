<!-- Admin Sidebar Navigation -->
<div class="hidden md:flex md:flex-col w-64 bg-slate-800 border-r border-slate-700 h-screen sticky top-0 overflow-hidden">
    <!-- Logo/Branding -->
    <div class="p-6 border-b border-slate-700 flex-shrink-0">
        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 text-white font-bold text-lg hover:text-red-400 transition">
            <span class="text-2xl">⚙️</span>
            <span>Admin Panel</span>
        </a>
    </div>

    <!-- Navigation Menu -->
    <nav class="flex-1 p-4 space-y-2 overflow-y-auto">
        <!-- Site Section - Collapsible -->
        <button onclick="toggleSection('site')" class="w-full flex items-center justify-between px-4 py-2 text-xs font-bold text-slate-400 uppercase tracking-wider hover:text-slate-300 transition group">
            <span>🌐 {{ __('admin.section_site') }}</span>
            <span class="site-toggle text-sm transform transition-transform duration-200">▼</span>
        </button>

        <div id="site-items" class="site-items space-y-1 transition-all duration-200">
            <a href="{{ route('home') }}"
               class="block px-4 py-3 rounded-lg text-slate-300 hover:bg-slate-700 transition">
                <span class="flex items-center gap-2">
                    <span>🏠</span> {{ __('admin.nav_home') }}
                </span>
            </a>

            <a href="{{ route('kdrams.catalog') }}"
               class="block px-4 py-3 rounded-lg text-slate-300 hover:bg-slate-700 transition">
                <span class="flex items-center gap-2">
                    <span>📺</span> {{ __('admin.nav_kdrams') }}
                </span>
            </a>

            <a href="{{ route('contact.show') }}"
               class="block px-4 py-3 rounded-lg text-slate-300 hover:bg-slate-700 transition">
                <span class="flex items-center gap-2">
                    <span>📧</span> {{ __('admin.nav_contact') }}
                </span>
            </a>

            @auth
                <a href="{{ route('dashboard') }}"
                   class="block px-4 py-3 rounded-lg text-slate-300 hover:bg-slate-700 transition">
                    <span class="flex items-center gap-2">
                        <span>🎬</span> {{ __('admin.nav_dashboard') }}
                    </span>
                </a>

                <a href="{{ route('watchlist.index') }}"
                   class="block px-4 py-3 rounded-lg text-slate-300 hover:bg-slate-700 transition">
                    <span class="flex items-center gap-2">
                        <span>📋</span> {{ __('admin.nav_watchlist') }}
                    </span>
                </a>
            @endauth
        </div>

        <!-- Divider -->
        <div class="my-4 border-t border-slate-700"></div>

        <!-- Admin Section - Collapsible -->
        <button onclick="toggleSection('admin')" class="w-full flex items-center justify-between px-4 py-2 text-xs font-bold text-slate-400 uppercase tracking-wider hover:text-slate-300 transition group">
            <span>⚙️ {{ __('admin.section_admin') }}</span>
            <span class="admin-toggle text-sm transform transition-transform duration-200">▼</span>
        </button>

        <div id="admin-items" class="admin-items space-y-1 transition-all duration-200">
            <!-- Dashboard -->
            <a href="{{ route('admin.dashboard') }}"
               class="block px-4 py-3 rounded-lg {{ request()->routeIs('admin.dashboard') ? 'bg-red-600 text-white' : 'text-slate-300 hover:bg-slate-700' }} transition">
                <span class="flex items-center gap-2">
                    <span>📊</span> {{ __('admin.nav_admin_dashboard') }}
                </span>
            </a>

            <!-- Users Management -->
            <a href="{{ route('admin.users.index') }}"
               class="block px-4 py-3 rounded-lg {{ request()->routeIs('admin.users.*') ? 'bg-red-600 text-white' : 'text-slate-300 hover:bg-slate-700' }} transition">
                <span class="flex items-center gap-2">
                    <span>👥</span> {{ __('admin.nav_manage_users') }}
                </span>
            </a>

            <!-- Settings -->
            <a href="{{ route('admin.settings.index') }}"
               class="block px-4 py-3 rounded-lg {{ request()->routeIs('admin.settings.*') ? 'bg-red-600 text-white' : 'text-slate-300 hover:bg-slate-700' }} transition">
                <span class="flex items-center gap-2">
                    <span>⚙️</span> {{ __('admin.nav_settings') }}
                </span>
            </a>

            <!-- Author & SEO -->
            <a href="{{ route('admin.author.edit') }}"
               class="block px-4 py-3 rounded-lg {{ request()->routeIs('admin.author.*') ? 'bg-red-600 text-white' : 'text-slate-300 hover:bg-slate-700' }} transition">
                <span class="flex items-center gap-2">
                    <span>📝</span> {{ __('admin.nav_author_seo') }}
                </span>
            </a>

            <!-- Messages -->
            <a href="{{ route('admin.contact.index') }}"
               class="block px-4 py-3 rounded-lg {{ request()->routeIs('admin.contact.*') ? 'bg-red-600 text-white' : 'text-slate-300 hover:bg-slate-700' }} transition">
                <span class="flex items-center gap-2">
                    <span>📧</span> {{ __('admin.nav_messages') }}
                </span>
            </a>

            <!-- Icon Picker -->
            <a href="{{ route('admin.icons.search') }}"
               class="block px-4 py-3 rounded-lg {{ request()->routeIs('admin.icons.*') ? 'bg-red-600 text-white' : 'text-slate-300 hover:bg-slate-700' }} transition">
                <span class="flex items-center gap-2">
                    <span>🎨</span> {{ __('admin.nav_icon_picker') }}
                </span>
            </a>

            <!-- Telescope (Debug Tool) -->
            <a href="/telescope"
               class="block px-4 py-3 rounded-lg {{ request()->url() == url('/telescope') ? 'bg-red-600 text-white' : 'text-slate-300 hover:bg-slate-700' }} transition">
                <span class="flex items-center gap-2">
                    <span>🔍</span> {{ __('admin.nav_telescope') }}
                </span>
            </a>
        </div>

        <!-- Divider -->
        <div class="my-4 border-t border-slate-700"></div>

        <!-- Export Section - Collapsible -->
        <button onclick="toggleSection('exports')" class="w-full flex items-center justify-between px-4 py-2 text-xs font-bold text-slate-400 uppercase tracking-wider hover:text-slate-300 transition group">
            <span>📥 {{ __('admin.section_exports') }}</span>
            <span class="exports-toggle text-sm transform transition-transform duration-200">▼</span>
        </button>

        <div id="exports-items" class="exports-items space-y-1 transition-all duration-200">
            <!-- Cache Management -->
            <a href="{{ route('admin.exports.cache') }}"
               class="block px-4 py-3 rounded-lg {{ request()->routeIs('admin.exports.cache') ? 'bg-red-600 text-white' : 'text-slate-300 hover:bg-slate-700' }} transition">
                <span class="flex items-center gap-2">
                    <span>📦</span> {{ __('admin.nav_cache_pdfs') }}
                </span>
            </a>

            <!-- Export Stats -->
            <a href="{{ route('admin.exports.stats') }}"
               class="block px-4 py-3 rounded-lg {{ request()->routeIs('admin.exports.stats') ? 'bg-red-600 text-white' : 'text-slate-300 hover:bg-slate-700' }} transition">
                <span class="flex items-center gap-2">
                    <span>📊</span> {{ __('admin.nav_export_stats') }}
                </span>
            </a>
        </div>
    </nav>

    <!-- User Info Footer -->
    <div class="p-4 border-t border-slate-700 space-y-3 flex-shrink-0 mt-auto">
        <!-- User Info -->
        <div class="text-sm">
            <p class="text-slate-400 text-xs">{{ __('admin.logged_in_as') }}</p>
            <p class="text-white font-semibold truncate">{{ auth()->user()->name }}</p>
            <p class="text-slate-500 text-xs truncate">{{ auth()->user()->email }}</p>
        </div>

        <!-- Language Switcher -->
        <div class="flex gap-1">
            <form action="{{ route('language.switch', 'fr') }}" method="POST" class="flex-1">
                @csrf
                <button type="submit" class="w-full px-2 py-1 text-xs rounded {{ app()->getLocale() === 'fr' ? 'bg-red-600 text-white' : 'text-slate-400 hover:bg-slate-700 hover:text-white' }} transition">
                    🇫🇷 FR
                </button>
            </form>
            <form action="{{ route('language.switch', 'en') }}" method="POST" class="flex-1">
                @csrf
                <button type="submit" class="w-full px-2 py-1 text-xs rounded {{ app()->getLocale() === 'en' ? 'bg-red-600 text-white' : 'text-slate-400 hover:bg-slate-700 hover:text-white' }} transition">
                    🇬🇧 EN
                </button>
            </form>
            <form action="{{ route('language.switch', 'de') }}" method="POST" class="flex-1">
                @csrf
                <button type="submit" class="w-full px-2 py-1 text-xs rounded {{ app()->getLocale() === 'de' ? 'bg-red-600 text-white' : 'text-slate-400 hover:bg-slate-700 hover:text-white' }} transition">
                    🇩🇪 DE
                </button>
            </form>
        </div>

        <!-- Profile & Logout -->
        <div class="space-y-2">
            <a href="{{ route('profile.edit') }}" class="block px-3 py-2 text-slate-400 hover:bg-slate-700 hover:text-white text-sm transition rounded">
                ⚙️ {{ __('admin.profile_settings') }}
            </a>
            <form method="POST" action="{{ route('logout') }}" class="inline w-full">
                @csrf
                <button type="submit" class="w-full px-3 py-2 text-slate-400 hover:bg-red-600 hover:text-white text-sm transition rounded font-semibold text-left">
                    🚪 {{ __('admin.logout') }}
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Mobile Menu Toggle (shown on mobile) -->
<div class="md:hidden fixed bottom-4 right-4 z-40">
    <button onclick="toggleMobileAdminMenu()" class="bg-red-600 hover:bg-red-700 text-white rounded-full p-3 shadow-lg transition">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
        </svg>
    </button>
</div>

<!-- Mobile Sidebar Overlay -->
<div id="mobileAdminMenu" class="hidden fixed inset-0 z-30">
    <div class="fixed inset-0 bg-black bg-opacity-50" onclick="toggleMobileAdminMenu()"></div>
    <div class="fixed left-0 top-0 bottom-0 w-64 bg-slate-800 border-r border-slate-700 overflow-y-auto">
        <!-- Logo -->
        <div class="p-6 border-b border-slate-700">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 text-white font-bold text-lg">
                <span class="text-2xl">⚙️</span>
                <span>Admin</span>
            </a>
        </div>

        <!-- Navigation (same as above with collapsible sections) -->
        <nav class="p-4 space-y-2">
            <!-- Site Section -->
            <button onclick="toggleSection('mobile-site')" class="w-full flex items-center justify-between px-4 py-2 text-xs font-bold text-slate-400 uppercase hover:text-slate-300 transition">
                <span>🌐 {{ __('admin.section_site') }}</span>
                <span class="mobile-site-toggle text-sm">▼</span>
            </button>
            <div id="mobile-site-items" class="mobile-site-items space-y-1">
                <a href="{{ route('home') }}" class="block px-4 py-3 rounded-lg text-slate-300 hover:bg-slate-700 transition" onclick="toggleMobileAdminMenu()">
                    <span class="flex items-center gap-2"><span>🏠</span> {{ __('admin.nav_home') }}</span>
                </a>
                <a href="{{ route('kdrams.catalog') }}" class="block px-4 py-3 rounded-lg text-slate-300 hover:bg-slate-700 transition" onclick="toggleMobileAdminMenu()">
                    <span class="flex items-center gap-2"><span>📺</span> {{ __('admin.nav_kdrams') }}</span>
                </a>
                <a href="{{ route('contact.show') }}" class="block px-4 py-3 rounded-lg text-slate-300 hover:bg-slate-700 transition" onclick="toggleMobileAdminMenu()">
                    <span class="flex items-center gap-2"><span>📧</span> {{ __('admin.nav_contact') }}</span>
                </a>
                @auth
                    <a href="{{ route('dashboard') }}" class="block px-4 py-3 rounded-lg text-slate-300 hover:bg-slate-700 transition" onclick="toggleMobileAdminMenu()">
                        <span class="flex items-center gap-2"><span>🎬</span> {{ __('admin.nav_dashboard') }}</span>
                    </a>
                    <a href="{{ route('watchlist.index') }}" class="block px-4 py-3 rounded-lg text-slate-300 hover:bg-slate-700 transition" onclick="toggleMobileAdminMenu()">
                        <span class="flex items-center gap-2"><span>📋</span> {{ __('admin.nav_watchlist') }}</span>
                    </a>
                @endauth
            </div>

            <div class="my-4 border-t border-slate-700"></div>

            <!-- Admin Section -->
            <button onclick="toggleSection('mobile-admin')" class="w-full flex items-center justify-between px-4 py-2 text-xs font-bold text-slate-400 uppercase hover:text-slate-300 transition">
                <span>⚙️ {{ __('admin.section_admin') }}</span>
                <span class="mobile-admin-toggle text-sm">▼</span>
            </button>
            <div id="mobile-admin-items" class="mobile-admin-items space-y-1">
                <a href="{{ route('admin.dashboard') }}" class="block px-4 py-3 rounded-lg text-slate-300 hover:bg-slate-700 transition" onclick="toggleMobileAdminMenu()">
                    <span class="flex items-center gap-2"><span>📊</span> {{ __('admin.nav_admin_dashboard') }}</span>
                </a>
                <a href="{{ route('admin.users.index') }}" class="block px-4 py-3 rounded-lg text-slate-300 hover:bg-slate-700 transition" onclick="toggleMobileAdminMenu()">
                    <span class="flex items-center gap-2"><span>👥</span> {{ __('admin.nav_manage_users') }}</span>
                </a>
                <a href="{{ route('admin.settings.index') }}" class="block px-4 py-3 rounded-lg text-slate-300 hover:bg-slate-700 transition" onclick="toggleMobileAdminMenu()">
                    <span class="flex items-center gap-2"><span>⚙️</span> {{ __('admin.nav_settings') }}</span>
                </a>
                <a href="{{ route('admin.author.edit') }}" class="block px-4 py-3 rounded-lg text-slate-300 hover:bg-slate-700 transition" onclick="toggleMobileAdminMenu()">
                    <span class="flex items-center gap-2"><span>📝</span> {{ __('admin.nav_author_seo') }}</span>
                </a>
                <a href="{{ route('admin.contact.index') }}" class="block px-4 py-3 rounded-lg text-slate-300 hover:bg-slate-700 transition" onclick="toggleMobileAdminMenu()">
                    <span class="flex items-center gap-2"><span>📧</span> {{ __('admin.nav_messages') }}</span>
                </a>
                <a href="{{ route('admin.icons.search') }}" class="block px-4 py-3 rounded-lg text-slate-300 hover:bg-slate-700 transition" onclick="toggleMobileAdminMenu()">
                    <span class="flex items-center gap-2"><span>🎨</span> {{ __('admin.nav_icon_picker') }}</span>
                </a>
                <a href="/telescope" class="block px-4 py-3 rounded-lg text-slate-300 hover:bg-slate-700 transition" onclick="toggleMobileAdminMenu()">
                    <span class="flex items-center gap-2"><span>🔍</span> {{ __('admin.nav_telescope') }}</span>
                </a>
            </div>

            <div class="my-4 border-t border-slate-700"></div>

            <!-- Exports Section -->
            <button onclick="toggleSection('mobile-exports')" class="w-full flex items-center justify-between px-4 py-2 text-xs font-bold text-slate-400 uppercase hover:text-slate-300 transition">
                <span>📥 {{ __('admin.section_exports') }}</span>
                <span class="mobile-exports-toggle text-sm">▼</span>
            </button>
            <div id="mobile-exports-items" class="mobile-exports-items space-y-1">
                <a href="{{ route('admin.exports.cache') }}" class="block px-4 py-3 rounded-lg text-slate-300 hover:bg-slate-700 transition" onclick="toggleMobileAdminMenu()">
                    <span class="flex items-center gap-2"><span>📦</span> {{ __('admin.nav_cache_pdfs') }}</span>
                </a>
                <a href="{{ route('admin.exports.stats') }}" class="block px-4 py-3 rounded-lg text-slate-300 hover:bg-slate-700 transition" onclick="toggleMobileAdminMenu()">
                    <span class="flex items-center gap-2"><span>📊</span> {{ __('admin.nav_export_stats') }}</span>
                </a>
            </div>
        </nav>

        <!-- User Info Footer (Mobile) -->
        <div class="p-4 border-t border-slate-700 space-y-3 mt-auto">
            <!-- User Info -->
            <div class="text-sm">
                <p class="text-slate-400 text-xs">{{ __('admin.logged_in_as') }}</p>
                <p class="text-white font-semibold truncate">{{ auth()->user()->name }}</p>
                <p class="text-slate-500 text-xs truncate">{{ auth()->user()->email }}</p>
            </div>

            <!-- Language Switcher (Mobile) -->
            <div class="flex gap-1">
                <form action="{{ route('language.switch', 'fr') }}" method="POST" class="flex-1">
                    @csrf
                    <button type="submit" class="w-full px-2 py-1 text-xs rounded {{ app()->getLocale() === 'fr' ? 'bg-red-600 text-white' : 'text-slate-400 hover:bg-slate-700 hover:text-white' }} transition" onclick="toggleMobileAdminMenu()">
                        🇫🇷 FR
                    </button>
                </form>
                <form action="{{ route('language.switch', 'en') }}" method="POST" class="flex-1">
                    @csrf
                    <button type="submit" class="w-full px-2 py-1 text-xs rounded {{ app()->getLocale() === 'en' ? 'bg-red-600 text-white' : 'text-slate-400 hover:bg-slate-700 hover:text-white' }} transition" onclick="toggleMobileAdminMenu()">
                        🇬🇧 EN
                    </button>
                </form>
                <form action="{{ route('language.switch', 'de') }}" method="POST" class="flex-1">
                    @csrf
                    <button type="submit" class="w-full px-2 py-1 text-xs rounded {{ app()->getLocale() === 'de' ? 'bg-red-600 text-white' : 'text-slate-400 hover:bg-slate-700 hover:text-white' }} transition" onclick="toggleMobileAdminMenu()">
                        🇩🇪 DE
                    </button>
                </form>
            </div>

            <!-- Profile & Logout -->
            <div class="space-y-2">
                <a href="{{ route('profile.edit') }}" class="block px-3 py-2 text-slate-400 hover:bg-slate-700 hover:text-white text-sm transition rounded" onclick="toggleMobileAdminMenu()">
                    ⚙️ {{ __('admin.profile_settings') }}
                </a>
                <form method="POST" action="{{ route('logout') }}" class="inline w-full">
                    @csrf
                    <button type="submit" class="w-full px-3 py-2 text-slate-400 hover:bg-red-600 hover:text-white text-sm transition rounded font-semibold text-left">
                        🚪 {{ __('admin.logout') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Initialize sidebar sections on page load
document.addEventListener('DOMContentLoaded', () => {
    initializeSections();
});

function initializeSections() {
    const sections = ['site', 'admin', 'exports', 'mobile-site', 'mobile-admin', 'mobile-exports'];

    sections.forEach(section => {
        const state = localStorage.getItem(`sidebar-${section}`);
        // Default: all sections open on first load
        const isOpen = state === null ? true : state === 'open';

        if (!isOpen) {
            collapseSection(section);
        } else {
            expandSection(section);
        }
    });
}

function toggleSection(section) {
    const items = document.getElementById(`${section}-items`);
    if (!items) return;

    const toggle = document.querySelector(`.${section}-toggle`);
    const isOpen = !items.classList.contains('hidden');

    if (isOpen) {
        collapseSection(section);
    } else {
        expandSection(section);
    }
}

function collapseSection(section) {
    const items = document.getElementById(`${section}-items`);
    const toggle = document.querySelector(`.${section}-toggle`);

    if (items) {
        items.style.maxHeight = '0';
        items.classList.add('hidden');
        items.style.opacity = '0';
    }

    if (toggle) {
        toggle.textContent = '▶';
    }

    localStorage.setItem(`sidebar-${section}`, 'closed');
}

function expandSection(section) {
    const items = document.getElementById(`${section}-items`);
    const toggle = document.querySelector(`.${section}-toggle`);

    if (items) {
        items.classList.remove('hidden');
        items.style.maxHeight = 'none';
        items.style.opacity = '1';
    }

    if (toggle) {
        toggle.textContent = '▼';
    }

    localStorage.setItem(`sidebar-${section}`, 'open');
}

function toggleMobileAdminMenu() {
    const menu = document.getElementById('mobileAdminMenu');
    menu.classList.toggle('hidden');
}
</script>

<style>
.site-items, .admin-items, .exports-items,
.mobile-site-items, .mobile-admin-items, .mobile-exports-items {
    max-height: none;
    opacity: 1;
    overflow: hidden;
    transition: max-height 0.3s ease, opacity 0.3s ease;
}

.site-items.hidden, .admin-items.hidden, .exports-items.hidden,
.mobile-site-items.hidden, .mobile-admin-items.hidden, .mobile-exports-items.hidden {
    max-height: 0;
    opacity: 0;
}
</style>
