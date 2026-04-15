<x-app-layout>
    <div class="flex min-h-screen bg-slate-900">
        <!-- Sidebar -->
        <x-admin-sidebar />

        <!-- Main Content -->
        <div class="flex-1">
            <!-- Header -->
            <div class="w-full bg-gradient-to-r from-slate-800 to-slate-900 border-b border-slate-700 sticky top-0 z-10 overflow-hidden">
                <div class="px-3 sm:px-6 lg:px-8 py-4">
                    <h1 class="text-2xl sm:text-3xl font-bold text-white flex items-center gap-2 sm:gap-3">
                        <span class="text-3xl sm:text-4xl">📊</span>
                        <span>{{ __('admin.dashboard_title') }}</span>
                    </h1>
                    <p class="text-slate-400 mt-1">{{ __('admin.dashboard_welcome', ['name' => auth()->user()->name]) }}</p>
                </div>
            </div>

            <!-- Page Content -->
            <div class="w-full py-6 px-3 sm:py-8 sm:px-6 lg:px-8">
        <div class="w-full max-w-6xl mx-auto">
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <!-- Total Users -->
                <div class="bg-gradient-to-br from-blue-600 to-blue-700 rounded-2xl p-6 border border-blue-500/20 hover:border-blue-500/40 transition shadow-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-100 text-sm font-semibold mb-1">{{ __('admin.dashboard_stats_users') }}</p>
                            <p class="text-4xl font-bold text-white">{{ $totalUsers }}</p>
                            <a href="{{ route('admin.users.index') }}" class="text-blue-200 hover:text-white text-sm font-semibold mt-3 inline-block transition">
                                {{ __('admin.dashboard_stats_users_view') }} →
                            </a>
                        </div>
                        <div class="text-6xl opacity-20">👥</div>
                    </div>
                </div>

                <!-- Total Contents -->
                <div class="bg-gradient-to-br from-purple-600 to-purple-700 rounded-2xl p-6 border border-purple-500/20 hover:border-purple-500/40 transition shadow-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-purple-100 text-sm font-semibold mb-1">{{ __('admin.dashboard_stats_contents') }}</p>
                            <p class="text-4xl font-bold text-white">{{ $totalContents }}</p>
                            <p class="text-purple-200 text-xs mt-3">{{ __('admin.dashboard_stats_contents_desc') }}</p>
                        </div>
                        <div class="text-6xl opacity-20">📺</div>
                    </div>
                </div>

                <!-- Data Freshness -->
                <div class="bg-gradient-to-br from-green-600 to-green-700 rounded-2xl p-6 border border-green-500/20 hover:border-green-500/40 transition shadow-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-100 text-sm font-semibold mb-1">{{ __('admin.dashboard_rapidapi_cache') }}</p>
                            <p class="text-2xl font-bold text-white">{{ $cacheDuration }}h</p>
                            <p class="text-green-200 text-xs mt-3">{{ __('admin.dashboard_cache_duration') }}</p>
                        </div>
                        <div class="text-6xl opacity-20">🔄</div>
                    </div>
                </div>
            </div>

            <!-- Main Features Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                <!-- User Management -->
                <div class="bg-slate-800 rounded-2xl p-8 border border-slate-700 hover:border-slate-600 transition shadow-lg">
                    <div class="flex items-center gap-3 mb-6">
                        <span class="text-3xl sm:text-4xl">👥</span>
                        <h3 class="text-2xl font-bold text-white">{{ __('admin.dashboard_user_management') }}</h3>
                    </div>
                    <p class="text-slate-400 mb-6">{{ __('admin.dashboard_user_management_desc') }}</p>
                    <a href="{{ route('admin.users.index') }}" class="inline-block px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-bold rounded-lg transition">
                        {{ __('admin.dashboard_manage_users_btn') }}
                    </a>
                </div>

                <!-- Settings & Configuration -->
                <div class="bg-slate-800 rounded-2xl p-8 border border-slate-700 hover:border-slate-600 transition shadow-lg">
                    <div class="flex items-center gap-3 mb-6">
                        <span class="text-3xl sm:text-4xl">⚙️</span>
                        <h3 class="text-2xl font-bold text-white">{{ __('admin.dashboard_settings_title') }}</h3>
                    </div>
                    <p class="text-slate-400 mb-6">{{ __('admin.dashboard_settings_desc') }}</p>
                    <a href="{{ route('admin.settings.index') }}" class="inline-block px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-bold rounded-lg transition">
                        {{ __('admin.dashboard_edit_settings_btn') }}
                    </a>
                </div>

                <!-- Content Management -->
                <div class="bg-slate-800 rounded-2xl p-8 border border-slate-700 hover:border-slate-600 transition shadow-lg">
                    <div class="flex items-center gap-3 mb-6">
                        <span class="text-3xl sm:text-4xl">📝</span>
                        <h3 class="text-2xl font-bold text-white">{{ __('admin.dashboard_author_title') }}</h3>
                    </div>
                    <p class="text-slate-400 mb-6">{{ __('admin.dashboard_author_desc') }}</p>
                    <a href="{{ route('admin.author.edit') }}" class="inline-block px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-bold rounded-lg transition">
                        {{ __('admin.dashboard_edit_author_btn') }}
                    </a>
                </div>

                <!-- Messages -->
                <div class="bg-slate-800 rounded-2xl p-8 border border-slate-700 hover:border-slate-600 transition shadow-lg">
                    <div class="flex items-center gap-3 mb-6">
                        <span class="text-3xl sm:text-4xl">📧</span>
                        <h3 class="text-2xl font-bold text-white">{{ __('admin.dashboard_messages_title') }}</h3>
                    </div>
                    <p class="text-slate-400 mb-6">{{ __('admin.dashboard_messages_desc') }}</p>
                    <a href="{{ route('admin.contact.index') }}" class="inline-block px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-bold rounded-lg transition">
                        {{ __('admin.dashboard_view_messages_btn') }}
                    </a>
                </div>

                <!-- Export Stats -->
                <div class="bg-slate-800 rounded-2xl p-8 border border-slate-700 hover:border-slate-600 transition shadow-lg">
                    <div class="flex items-center gap-3 mb-6">
                        <span class="text-3xl sm:text-4xl">📊</span>
                        <h3 class="text-2xl font-bold text-white">{{ __('admin.dashboard_export_stats_title') }}</h3>
                    </div>
                    <p class="text-slate-400 mb-6">{{ __('admin.dashboard_export_stats_desc') }}</p>
                    <a href="{{ route('admin.exports.stats') }}" class="inline-block px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-bold rounded-lg transition">
                        {{ __('admin.dashboard_view_stats_btn') }}
                    </a>
                </div>

                <!-- Cache Management -->
                <div class="bg-slate-800 rounded-2xl p-8 border border-slate-700 hover:border-slate-600 transition shadow-lg">
                    <div class="flex items-center gap-3 mb-6">
                        <span class="text-3xl sm:text-4xl">📦</span>
                        <h3 class="text-2xl font-bold text-white">{{ __('admin.dashboard_cache_management_title') }}</h3>
                    </div>
                    <p class="text-slate-400 mb-6">{{ __('admin.dashboard_cache_management_desc') }}</p>
                    <a href="{{ route('admin.exports.cache') }}" class="inline-block px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-bold rounded-lg transition">
                        {{ __('admin.dashboard_manage_cache_btn') }}
                    </a>
                </div>
            </div>

            <!-- Export Watchlist Section -->
            <div class="bg-gradient-to-r from-amber-600 to-amber-700 rounded-2xl p-8 border border-amber-500/20 shadow-lg">
                <div class="flex items-center gap-3 mb-4">
                    <span class="text-4xl">📥</span>
                    <h3 class="text-2xl font-bold text-white">{{ __('admin.dashboard_export_watchlist_title') }}</h3>
                </div>
                <p class="text-amber-100 mb-6">{{ __('admin.dashboard_export_watchlist_desc') }}</p>
                <button onclick="openAdminExportModal({{ auth()->id() }}, '{{ auth()->user()->name }}')" class="px-8 py-4 bg-white hover:bg-amber-50 text-amber-700 font-bold rounded-lg transition shadow-md">
                    {{ __('admin.dashboard_export_watchlist_btn') }}
                </button>
            </div>
        </div>
    </div>
</x-app-layout>

<!-- Admin Export Modal -->
@include('admin.exports._admin-export-modal')
