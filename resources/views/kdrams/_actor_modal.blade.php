<div class="flex flex-col items-center">
    <!-- Circular Photo - Centered at top -->
    <div class="relative flex-shrink-0 w-48 h-48 rounded-full overflow-hidden ring-4 ring-red-500/30 shadow-xl mb-6">
        @if($actor['profile_path'])
            <img src="https://image.tmdb.org/t/p/w342{{ $actor['profile_path'] }}"
                 srcset="https://image.tmdb.org/t/p/w342{{ $actor['profile_path'] }} 1x, https://image.tmdb.org/t/p/w500{{ $actor['profile_path'] }} 2x"
                 alt="{{ $actor['name'] }}"
                 class="w-full h-full object-cover"
                 loading="lazy"
                 decoding="async">
        @else
            <div class="w-full h-full bg-gradient-to-br from-slate-800 to-slate-900 flex items-center justify-center">
                <svg class="w-24 h-24 text-slate-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                </svg>
            </div>
        @endif
    </div>

    <!-- Actor Name & Info - Centered -->
    <div class="text-center w-full px-4 mb-6">
        <h2 class="text-3xl font-bold text-white mb-1">{{ $actor['latin_name'] ?? $actor['name'] }}</h2>
        @if(($actor['original_name'] ?? $actor['name']) !== ($actor['latin_name'] ?? $actor['name']))
            <p class="text-slate-400 text-base mb-4">{{ $actor['original_name'] ?? $actor['name'] }}</p>
        @endif

        <!-- Birth & Location Info -->
        <div class="flex flex-col sm:flex-row gap-3 justify-center mb-4 text-sm text-slate-300">
            @if($actor['birthday'])
                <div class="flex items-center justify-center gap-1">
                    <span>📅</span>
                    <span>
                        {{ \Carbon\Carbon::parse($actor['birthday'])->format('d/m/Y') }}
                        @if(!$actor['deathday'])
                            <span class="text-slate-500">({{ \Carbon\Carbon::parse($actor['birthday'])->age }} {{ trans_choice('common.years', \Carbon\Carbon::parse($actor['birthday'])->age) }})</span>
                        @endif
                    </span>
                </div>
            @endif

            @if($actor['place_of_birth'] ?? null)
                <div class="flex items-center justify-center gap-1">
                    <span>📍</span>
                    <span>{{ $actor['place_of_birth'] }}</span>
                </div>
            @endif
        </div>
    </div>

    <!-- Social Media Links - Using system icons -->
    <div class="w-full px-4 mb-8">
        <div class="flex flex-wrap gap-2 justify-center">
            @php
                $socialIcons = [
                    'instagram' => ['icon' => 'si-instagram', 'url' => 'https://instagram.com/', 'bg' => 'bg-gradient-to-r from-purple-600 to-pink-600'],
                    'facebook' => ['icon' => 'si-facebook', 'url' => 'https://facebook.com/', 'bg' => 'bg-blue-600'],
                    'twitter' => ['icon' => 'si-x', 'url' => 'https://twitter.com/', 'bg' => 'bg-slate-700'],
                    'tiktok' => ['icon' => 'si-tiktok', 'url' => 'https://tiktok.com/@', 'bg' => 'bg-black'],
                    'youtube' => ['icon' => 'si-youtube', 'url' => 'https://youtube.com/', 'bg' => 'bg-red-600'],
                    'imdb' => ['icon' => 'si-imdb', 'url' => 'https://imdb.com/name/', 'bg' => 'bg-yellow-700'],
                    'wikidata' => ['icon' => 'si-wikidata', 'url' => 'https://wikidata.org/wiki/', 'bg' => 'bg-cyan-600'],
                ];
            @endphp

            @foreach($socialIcons as $platform => $config)
                @php
                    $idField = $platform . '_id';
                    $externalIds = $actor['external_ids'] ?? [];
                    $hasId = isset($externalIds[$idField]) && $externalIds[$idField];
                @endphp

                @if($hasId)
                    @php
                        // Load SVG from vendor
                        $iconName = substr($config['icon'], 3); // Remove 'si-' prefix
                        $svgPath = base_path("vendor/codeat3/blade-simple-icons/resources/svg/{$iconName}.svg");
                        $svgContent = file_exists($svgPath) ? file_get_contents($svgPath) : null;

                        // Determine URL based on platform
                        $url = $config['url'];
                        if ($platform === 'tiktok') {
                            $url .= $externalIds[$idField];
                        } else if ($platform === 'twitter') {
                            $url .= $externalIds[$idField];
                        } else if ($platform === 'wikidata') {
                            $url .= $externalIds[$idField];
                        } else {
                            $url .= $externalIds[$idField];
                        }
                    @endphp

                    @if($svgContent)
                        <a href="{{ $url }}" target="_blank" class="p-1.5 {{ $config['bg'] }} rounded-full hover:shadow-lg hover:scale-110 transition text-white" title="{{ ucfirst($platform) }}">
                            {!! str_replace('<svg', '<svg class="w-4 h-4 fill-current"', $svgContent) !!}
                        </a>
                    @endif
                @endif
            @endforeach
        </div>
    </div>

    <!-- Biography -->
    <div class="w-full px-4">
        <h3 class="text-xl font-bold mb-3 text-red-500">{{ __('show.actor_biography') }}</h3>
        <div class="text-slate-300 text-sm leading-relaxed max-h-48 overflow-y-auto pr-2 custom-scrollbar">
            @if($actor['biography'])
                {{ $actor['biography'] }}
            @else
                <p class="italic text-slate-500">{{ __('show.actor_no_biography') }}</p>
            @endif
        </div>
    </div>

    <!-- Recent Projects -->
    @if(isset($actor['combined_credits']['cast']) && count($actor['combined_credits']['cast']) > 0)
        <div class="w-full px-4 mt-6">
            <h3 class="text-lg font-bold mb-3">{{ __('show.actor_recent_projects') }}</h3>
            <div class="grid grid-cols-4 sm:grid-cols-6 gap-2">
                @php
                    $projects = collect($actor['combined_credits']['cast'])
                        ->sortByDesc('popularity')
                        ->take(12);
                @endphp
                @foreach($projects as $project)
                    <div class="flex flex-col gap-1">
                        <div class="aspect-[2/3] bg-slate-800 rounded-lg overflow-hidden border border-slate-700 hover:border-red-500/50 transition">
                            @if($project['poster_path'])
                                <img src="https://image.tmdb.org/t/p/w154{{ $project['poster_path'] }}"
                                     alt="{{ $project['name'] ?? $project['title'] }}"
                                     class="w-full h-full object-cover"
                                     loading="lazy"
                                     decoding="async">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-[9px] text-slate-600 text-center p-1 bg-slate-700">
                                    {{ substr($project['name'] ?? $project['title'], 0, 15) }}
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- View All Dramas Button -->
    <div class="w-full px-4 mt-8 flex justify-center">
        <button onclick="closeActorModal(); filterByActor({{ $actor['id'] }}, '{{ addslashes($actor['latin_name'] ?? $actor['name']) }}')" class="btn-primary py-2 px-6 text-sm rounded-lg hover:shadow-lg transition">
            {{ __('show.actor_view_all_dramas') }}
        </button>
    </div>
</div>
