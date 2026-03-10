@php
    $metadata = \App\Models\SiteMetadata::first();
    $socialLinks = \App\Models\SocialLink::where('is_visible', true)->orderBy('order')->get();
@endphp

<footer class="bg-slate-900 border-t border-slate-700 mt-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- About Section -->
            <div>
                <h3 class="text-lg font-semibold text-white mb-4">{{ $metadata->site_name ?? 'Moussouni' }}</h3>
                <p class="text-slate-400 text-sm mb-4">
                    {{ $metadata->site_tagline ?? 'Discover & Rate Korean Dramas' }}
                </p>
                @if($metadata->author_name)
                    <p class="text-slate-500 text-xs">
                        Created with ❤️ by {{ $metadata->author_name }}
                    </p>
                @endif
            </div>

            <!-- Quick Links -->
            <div>
                <h3 class="text-lg font-semibold text-white mb-4">Quick Links</h3>
                <ul class="space-y-2 text-sm">
                    <li>
                        <a href="{{ route('home') }}" class="text-slate-400 hover:text-white transition">Home</a>
                    </li>
                    <li>
                        <a href="{{ route('kdrams.catalog') }}" class="text-slate-400 hover:text-white transition">Catalog</a>
                    </li>
                    @auth
                        <li>
                            <a href="{{ route('watchlist.index') }}" class="text-slate-400 hover:text-white transition">Watchlist</a>
                        </li>
                        <li>
                            <a href="{{ route('dashboard') }}" class="text-slate-400 hover:text-white transition">Dashboard</a>
                        </li>
                    @endauth
                </ul>
            </div>

            <!-- Social Links -->
            <div>
                <h3 class="text-lg font-semibold text-white mb-4">Connect</h3>
                @if($socialLinks->count() > 0)
                    <div class="flex gap-4">
                        @foreach($socialLinks as $link)
                            @php
                                $iconPath = base_path("node_modules/@tabler/icons/icons/outline/{$link->icon}.svg");
                                $svgContent = file_exists($iconPath) ? file_get_contents($iconPath) : null;
                            @endphp
                            <a href="{{ $link->url }}" target="_blank" rel="noopener noreferrer"
                               class="inline-flex items-center justify-center w-6 h-6 text-slate-400 hover:text-white transition"
                               title="{{ $link->platform }}">
                                @if($svgContent)
                                    {!! str_replace(['<svg', '</svg>'], ['<svg class="w-6 h-6 stroke-current"', '</svg>'], $svgContent) !!}
                                @else
                                    <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10"></circle>
                                    </svg>
                                @endif
                            </a>
                        @endforeach
                    </div>
                @else
                    <p class="text-slate-500 text-sm">Coming soon...</p>
                @endif
            </div>
        </div>

        <!-- Divider -->
        <div class="border-t border-slate-700 mt-8 pt-8">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="text-slate-500 text-sm">
                    {{ $metadata->site_copyright ?? '© 2026 Moussouni. All rights reserved.' }}
                </p>
                @if($metadata->site_footer_text)
                    <p class="text-slate-500 text-sm">
                        {{ $metadata->site_footer_text }}
                    </p>
                @endif
            </div>
        </div>
    </div>
</footer>
