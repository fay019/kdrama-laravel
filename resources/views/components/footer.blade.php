@php
    $metadata = \App\Models\SiteMetadata::first();
    $socialLinks = \App\Models\SocialLink::where('is_visible', true)->orderBy('order')->get();
@endphp

<footer class="bg-slate-900 border-t border-slate-700 mt-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- About Section -->
            <div>
                <h3 class="text-lg font-semibold text-white mb-4">{{ $metadata?->site_name ?? 'Moussouni' }}</h3>
                <p class="text-slate-400 text-sm mb-4">
                    {{ $metadata?->site_tagline ?? 'Discover & Rate Korean Dramas' }}
                </p>
                @if($metadata?->author_name)
                    <p class="text-slate-500 text-xs">
                        {{ __('common.footer_created_by') }} {{ $metadata->author_name }}
                    </p>
                @endif
            </div>

            <!-- Quick Links -->
            <div>
                <h3 class="text-lg font-semibold text-white mb-4">{{ __('common.footer_quick_links') }}</h3>
                <ul class="space-y-2 text-sm">
                    <li>
                        <a href="{{ route('home') }}" class="text-slate-400 hover:text-white transition">{{ __('common.footer_home') }}</a>
                    </li>
                    <li>
                        <a href="{{ route('kdrams.catalog') }}" class="text-slate-400 hover:text-white transition">{{ __('common.footer_catalog') }}</a>
                    </li>
                    @auth
                        <li>
                            <a href="{{ route('watchlist.index') }}" class="text-slate-400 hover:text-white transition">{{ __('common.footer_watchlist') }}</a>
                        </li>
                        <li>
                            <a href="{{ route('dashboard') }}" class="text-slate-400 hover:text-white transition">{{ __('common.footer_dashboard') }}</a>
                        </li>
                    @endauth
                </ul>
            </div>

            <!-- Social Links -->
            <div>
                <h3 class="text-lg font-semibold text-white mb-4">{{ __('common.footer_connect') }}</h3>
                @if($socialLinks->count() > 0)
                    <div class="flex gap-4">
                        @foreach($socialLinks as $link)
                            @php
                                $svgContent = null;
                                $iconName = $link->icon;

                                // Check if it's a Simple Icon (si-prefix)
                                if (str_starts_with($iconName, 'si-')) {
                                    // Remove si- prefix to get the actual icon name
                                    $actualIconName = substr($iconName, 3);
                                    $iconPath = base_path("vendor/codeat3/blade-simple-icons/resources/svg/{$actualIconName}.svg");
                                    if (file_exists($iconPath)) {
                                        $svgContent = file_get_contents($iconPath);
                                        // Use fill instead of stroke for Simple Icons
                                        $svgContent = str_replace('<svg', '<svg class="w-6 h-6 fill-current"', $svgContent);
                                    }
                                } else {
                                    // Tabler Icon from Composer package
                                    $iconPath = base_path("vendor/secondnetwork/blade-tabler-icons/resources/svg/{$iconName}.svg");
                                    if (file_exists($iconPath)) {
                                        $svgContent = file_get_contents($iconPath);
                                        $svgContent = str_replace(['<svg', '</svg>'], ['<svg class="w-6 h-6 stroke-current"', '</svg>'], $svgContent);
                                    }
                                }
                            @endphp
                            <a href="{{ $link->url }}" target="_blank" rel="noopener noreferrer"
                               class="inline-flex items-center justify-center w-6 h-6 text-slate-400 hover:text-white transition"
                               title="{{ $link->platform }}">
                                @if($svgContent)
                                    {!! $svgContent !!}
                                @else
                                    <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10"></circle>
                                    </svg>
                                @endif
                            </a>
                        @endforeach
                    </div>
                @else
                    <p class="text-slate-500 text-sm">{{ __('common.footer_coming_soon') }}</p>
                @endif
            </div>
        </div>

        <!-- Divider -->
        <div class="border-t border-slate-700 mt-8 pt-8">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <div>
                    <p class="text-slate-500 text-sm">
                        {{ $metadata?->site_copyright ?? '© 2026 Moussouni. All rights reserved.' }}
                    </p>
                    @if($metadata?->site_footer_text)
                        <p class="text-slate-500 text-sm">
                            {{ $metadata->site_footer_text }}
                        </p>
                    @endif
                </div>

                <!-- Language Switcher -->
                <div class="flex gap-2">
                    <form action="{{ route('language.switch', 'fr') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="px-2 py-1 text-xs rounded {{ app()->getLocale() === 'fr' ? 'bg-red-600 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }} transition">
                            🇫🇷 FR
                        </button>
                    </form>
                    <form action="{{ route('language.switch', 'en') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="px-2 py-1 text-xs rounded {{ app()->getLocale() === 'en' ? 'bg-red-600 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }} transition">
                            🇬🇧 EN
                        </button>
                    </form>
                    <form action="{{ route('language.switch', 'de') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="px-2 py-1 text-xs rounded {{ app()->getLocale() === 'de' ? 'bg-red-600 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }} transition">
                            🇩🇪 DE
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</footer>
