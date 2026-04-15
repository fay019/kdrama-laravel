@extends('layouts.app')

@section('title', $kdrama['name'] ?? __('show.page_title'))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    @if(session('success'))
        <div class="mb-6 p-4 bg-green-500/20 border border-green-500/50 rounded-xl text-green-400 text-sm flex items-center gap-3 animate-fade-in">
            <span>✅</span>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 p-4 bg-red-500/20 border border-red-500/50 rounded-xl text-red-400 text-sm flex items-center gap-3 animate-fade-in">
            <span>❌</span>
            {{ session('error') }}
        </div>
    @endif
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <!-- Affiche -->
        <div>
            @if($kdrama['poster_path'] ?? false)
                <img
                    src="https://image.tmdb.org/t/p/w500{{ $kdrama['poster_path'] }}"
                    alt="{{ $kdrama['name'] }}"
                    class="w-full rounded-lg shadow-lg"
                >
            @else
                <div class="w-full bg-slate-800 rounded-lg h-96 flex items-center justify-center">
                    <span class="text-slate-500">{{ __('show.no_image') }}</span>
                </div>
            @endif

            <!-- Action Buttons -->
            <div class="mt-6 space-y-3">
                @auth
                    @php
                        $isInWatchlist = $userStatus && $userStatus->is_in_watchlist;
                        $isWatching = $userStatus && $userStatus->is_watching;
                        $isWatched = $userStatus && $userStatus->is_watched;
                    @endphp
                    <button id="watchlistBtn" data-content-id="{{ $kdrama['tmdb_id'] ?? $kdrama['id'] }}"
                            class="w-full {{ $isInWatchlist ? 'bg-slate-700' : 'bg-red-500' }} hover:opacity-90 text-white font-bold py-3 rounded-lg transition watchlist-btn"
                            data-remove-text="{{ __('show.remove_watchlist') }}"
                            data-add-text="{{ __('show.add_watchlist') }}">
                        {{ $isInWatchlist ? __('show.remove_watchlist') : __('show.add_watchlist') }}
                    </button>
                    <button id="watchingBtn" data-content-id="{{ $kdrama['tmdb_id'] ?? $kdrama['id'] }}"
                            class="w-full {{ $isWatching ? 'bg-slate-700' : 'bg-amber-500' }} hover:opacity-90 text-white font-bold py-3 rounded-lg transition watching-btn"
                            data-unwatching-text="{{ __('show.mark_unwatching') }}"
                            data-watching-text="{{ __('show.mark_watching') }}">
                        {{ $isWatching ? __('show.mark_unwatching') : __('show.mark_watching') }}
                    </button>
                    <button id="watchedBtn" data-content-id="{{ $kdrama['tmdb_id'] ?? $kdrama['id'] }}"
                            class="w-full {{ $isWatched ? 'bg-slate-700' : 'bg-green-600' }} hover:opacity-90 text-white font-bold py-3 rounded-lg transition watched-btn"
                            data-unwatch-text="{{ __('show.mark_unwatched') }}"
                            data-watch-text="{{ __('show.mark_watched') }}">
                        {{ $isWatched ? __('show.mark_unwatched') : __('show.mark_watched') }}
                    </button>
                @else
                    <a href="{{ route('login') }}" class="block w-full bg-red-500 hover:bg-red-600 text-white font-bold py-3 rounded-lg transition text-center">
                        {{ __('show.login_to_add') }}
                    </a>
                @endauth
            </div>
        </div>

        <!-- Détails -->
        <div class="md:col-span-2">
            <div class="flex justify-between items-start mb-2">
                <h1 id="drama-title" class="text-4xl font-bold">{{ $kdrama['name'] ?? 'N/A' }}</h1>
                <div class="flex gap-2">
                    <button onclick="switchLang('fr')" class="bg-slate-700 hover:bg-slate-600 px-3 py-1 rounded text-xs transition">{{ __('show.lang_fr') }}</button>
                    <button onclick="switchLang('en')" class="bg-slate-700 hover:bg-slate-600 px-3 py-1 rounded text-xs transition">{{ __('show.lang_en') }}</button>
                    <button onclick="switchLang('de')" class="bg-slate-700 hover:bg-slate-600 px-3 py-1 rounded text-xs transition">{{ __('show.lang_de') }}</button>
                </div>
            </div>

            @if(!empty($kdrama['tagline']))
                <p id="drama-tagline" class="text-red-500 italic mb-4">{{ $kdrama['tagline'] }}</p>
            @endif

            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-4 text-slate-400">
                    @if($kdrama['first_air_date'] ?? false)
                        @php
                            $date = $kdrama['first_air_date'];
                            if (is_string($date)) {
                                $date = \Carbon\Carbon::parse($date);
                            }
                        @endphp
                        <span>{{ $date->format('Y') }}</span>
                    @endif
                    <span>⭐ {{ $kdrama['vote_average'] ?? 'N/A' }}/10</span>
                    @if($kdrama['number_of_seasons'] ?? false)
                        <span>{{ $kdrama['number_of_seasons'] }} {{ \Illuminate\Support\Str::plural(__('show.season'), $kdrama['number_of_seasons']) }}</span>
                    @endif
                </div>

                <!-- Rating Block - Top Right -->
                @auth
                    <div id="ratingBlock" class="flex gap-0.5" style="{{ !$isWatched ? 'display:none;' : '' }}">
                        @php
                            $currentRating = $userStatus?->rating ?? null;
                        @endphp
                        <button data-rating="1" class="rating-btn px-2 py-2 rounded font-bold text-sm transition flex items-center justify-center {{ $currentRating === 1 ? 'bg-red-600 hover:bg-red-700' : 'bg-slate-700 hover:bg-slate-600' }}" title="{{ __('show.rating_bad_title') }}">
                            👎
                        </button>
                        <button data-rating="2" class="rating-btn px-2 py-2 rounded font-bold text-sm transition flex items-center justify-center {{ $currentRating === 2 ? 'bg-green-600 hover:bg-green-700' : 'bg-slate-700 hover:bg-slate-600' }}" title="{{ __('show.rating_good_title') }}">
                            👍
                        </button>
                        <button data-rating="3" class="rating-btn px-2 py-2 rounded font-bold text-sm transition flex items-center justify-center {{ $currentRating === 3 ? 'bg-purple-600 hover:bg-purple-700' : 'bg-slate-700 hover:bg-slate-600' }}" title="{{ __('show.rating_excellent_title') }}">
                            👍👍
                        </button>
                    </div>
                @endauth
            </div>

            <!-- Production & Diffusion - OPTION 1 (Avant le synopsis) -->
            <div class="mb-8">
                <x-production-info :kdrama="$kdrama" />
            </div>

            <!-- Synopsis -->
            <div class="mb-8">
                <h2 class="text-2xl font-bold mb-4">{{ __('show.synopsis') }}</h2>
                <p id="drama-overview" class="text-slate-300 leading-relaxed">
                    {{ $kdrama['overview'] ?? __('show.no_image') }}
                </p>
            </div>

            <!-- Genres -->
            @if(isset($kdrama['genres']) && count($kdrama['genres']) > 0)
                <div class="mb-8">
                    <h2 class="text-2xl font-bold mb-4">{{ __('show.genres') }}</h2>
                    <div class="flex flex-wrap gap-2">
                        @foreach($kdrama['genres'] as $genre)
                            <span class="bg-slate-800 px-4 py-2 rounded-full text-sm">{{ $genre['name'] }}</span>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Production & Diffusion - OPTION 2 (Après les genres) - Décommentez pour tester -->
            {{-- <div class="mb-8">
                <x-production-info :kdrama="$kdrama" />
            </div> --}}

            <!-- Où regarder -->
            <div class="mb-8">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-2xl font-bold flex items-center gap-2">
                        <span class="text-red-500">📺</span> {{ __('show.where_to_watch') }}
                    </h2>
                    @php
                        $lastChecked = null;
                        if ($availability && count($availability) > 0) {
                            // Si on a des données, on peut essayer de trouver une date de mise à jour (via un record DB si on l'avait passé)
                        }
                        // On récupère le record streaming s'il existe pour afficher la vraie date de vérification
                        $streamingRecord = \App\Models\StreamingAvailability::where('tmdb_id', $kdrama['tmdb_id'] ?? $kdrama['id'])->where('region', 'fr')->first();
                    @endphp
    @if(auth()->check() && auth()->user()->is_admin)
                        <form method="POST" action="{{ route('kdrams.refresh-streaming', $kdrama['tmdb_id'] ?? $kdrama['id']) }}">
                            @csrf
                            <button type="submit" class="text-xs text-slate-500 hover:text-red-400 transition-colors flex items-center gap-1 group bg-slate-800/50 px-2 py-1 rounded border border-slate-700 hover:border-red-500/50">
                                <span class="group-hover:rotate-180 transition-transform duration-500 inline-block">🔄</span>
                                {{ __('show.admin_refresh_streaming') }}
                            </button>
                        </form>
                    @endif
                </div>

                @if(count($availability) > 0)
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($availability as $service)
                            <a href="{{ $service['link'] ?? '#' }}" target="_blank" class="flex items-center gap-4 bg-slate-800 hover:bg-slate-700 p-4 rounded-xl border border-slate-700/50 transition-all hover:scale-[1.02] group">
                                <div class="flex-shrink-0 w-12 h-12 flex items-center justify-center bg-slate-900 rounded-lg overflow-hidden p-1 shadow-inner">
                                    @if($service['logo'] ?? false)
                                        <img src="{{ $service['logo'] }}" alt="{{ $service['service'] }}" class="max-w-full max-h-full object-contain">
                                    @else
                                        <span class="text-2xl">📽️</span>
                                    @endif
                                </div>
                                <div class="flex-grow">
                                    <div class="font-bold text-white group-hover:text-red-400 transition-colors uppercase text-xs tracking-widest">
                                        {{ $service['service'] }}
                                    </div>
                                    <div class="text-slate-400 text-[10px] font-medium">
                                        {{ strtoupper($service['type'] ?? 'subscription') }}
                                        @if(!empty($service['price']))
                                            • <span class="text-red-400">{{ $service['price'] }} {{ $service['currency'] ?? '' }}</span>
                                        @endif
                                    </div>
                                    <div class="mt-1 text-[10px] text-red-500 font-bold flex items-center gap-1">
                                        {{ __('show.watch_now_link') }} <span class="text-xs">→</span>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @elseif(count($streamingLinks) > 0)
                    <!-- Fallback: Search links on production platforms -->
                    <div class="space-y-3">
                        <p class="text-slate-400 text-sm mb-4">
                            {{ __('show.streaming_not_available') }}
                        </p>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @foreach($streamingLinks as $link)
                                <a href="{{ $link['url'] }}" target="_blank" rel="noopener noreferrer" class="flex items-center gap-3 px-4 py-3 rounded-lg border-2 border-transparent hover:border-red-500 transition group bg-gradient-to-r {{ $link['color'] }} hover:shadow-lg">
                                    <span class="text-2xl">{{ $link['icon'] }}</span>
                                    <div class="flex-grow">
                                        <div class="font-bold text-sm text-white group-hover:text-white transition">{{ $link['name'] }}</div>
                                        <div class="text-[10px] text-slate-200">{{ __('show.streaming_search_platform') }}</div>
                                    </div>
                                    <span class="text-white group-hover:text-yellow-300 transition">→</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="bg-slate-800/30 border border-dashed border-slate-700 rounded-xl p-6 text-center">
                        <p class="text-slate-500 text-sm italic">
                            {{ __('show.streaming_unavailable') }}
                        </p>
                        <p class="text-[10px] text-slate-600 mt-1">
                            @if($streamingRecord && $streamingRecord->last_updated_at)
                                ({{ __('show.streaming_last_checked') }} {{ $streamingRecord->last_updated_at->format('d/m/Y H:i') }})
                            @else
                                ({{ __('show.streaming_never_checked') }})
                            @endif
                        </p>
                    </div>
                @endif
            </div>

            <!-- Infos supplémentaires -->
            <div class="grid grid-cols-2 gap-4 mb-8">
                @if($kdrama['status'] ?? false)
                    <div class="bg-slate-800 p-4 rounded-lg">
                        <div class="text-slate-400 text-sm">{{ __('show.status') }}</div>
                        <div class="font-bold">{{ $kdrama['status'] }}</div>
                    </div>
                @endif

                @if($kdrama['original_language'] ?? false)
                    <div class="bg-slate-800 p-4 rounded-lg">
                        <div class="text-slate-400 text-sm">{{ __('show.air_date') }}</div>
                        <div class="font-bold">{{ strtoupper($kdrama['original_language']) }}</div>
                    </div>
                @endif

                @if($kdrama['number_of_episodes'] ?? false)
                    <div class="bg-slate-800 p-4 rounded-lg">
                        <div class="text-slate-400 text-sm">{{ __('show.episodes') }}</div>
                        <div class="font-bold">{{ $kdrama['number_of_episodes'] }}</div>
                    </div>
                @endif

                @if($kdrama['last_air_date'] ?? false)
                    <div class="bg-slate-800 p-4 rounded-lg">
                        <div class="text-slate-400 text-sm">{{ __('show.air_date') }}</div>
                        <div class="font-bold">
                            @php
                                $lastDate = $kdrama['last_air_date'];
                                if (is_string($lastDate)) {
                                    $lastDate = \Carbon\Carbon::parse($lastDate);
                                }
                            @endphp
                            {{ $lastDate->format('d/m/Y') }}
                        </div>
                    </div>
                @endif
            </div>

            <!-- Acteurs -->
            @if(isset($kdrama['credits']['cast']) && count($kdrama['credits']['cast']) > 0)
                <div class="mb-8">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold">{{ __('show.cast') }}</h2>
                        @if(count($kdrama['credits']['cast']) > 12)
                            <button id="toggleCastBtn" onclick="toggleCast()" class="text-red-500 hover:text-red-400 text-sm font-bold transition">
                                {{ __('show.show_full_cast') }} ({{ count($kdrama['credits']['cast']) }})
                            </button>
                        @endif
                    </div>

                    <div id="cast-grid" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                        @foreach($kdrama['credits']['cast'] as $index => $actor)
                            <div onclick="openActorModal({{ $actor['id'] }})" class="cast-item cursor-pointer hover:bg-slate-700/50 transition bg-slate-800/50 rounded-lg p-3 flex flex-col items-center text-center {{ $index >= 12 && (!isset($highlight_actor) || $actor['id'] != $highlight_actor) ? 'hidden' : '' }} {{ isset($highlight_actor) && $actor['id'] == $highlight_actor ? 'ring-2 ring-red-500 border-red-500 bg-red-500/10' : '' }}">
                                @if($actor['profile_path'])
                                    <img
                                        src="https://image.tmdb.org/t/p/w185{{ $actor['profile_path'] }}"
                                        alt="{{ $actor['name'] }}"
                                        class="w-20 h-20 rounded-full object-cover mb-3 border-2 {{ isset($highlight_actor) && $actor['id'] == $highlight_actor ? 'border-red-500' : 'border-slate-700' }}"
                                    >
                                @else
                                    <div class="w-20 h-20 rounded-full bg-slate-700 flex items-center justify-center mb-3 border-2 border-slate-600">
                                        <span class="text-2xl">👤</span>
                                    </div>
                                @endif
                                <div class="font-bold text-sm">
                                    {{ $actor['latin_name'] ?? $actor['name'] }}
                                    @php $primary = $actor['latin_name'] ?? $actor['name']; @endphp
                                    @if(!empty($actor['original_name']) && $actor['original_name'] !== $primary)
                                        <span class="text-slate-400 text-[10px] block">{{ $actor['original_name'] }}</span>
                                    @elseif(!empty($actor['name']) && $actor['name'] !== $primary)
                                        <span class="text-slate-400 text-[10px] block">{{ $actor['name'] }}</span>
                                    @endif
                                </div>
                                <div class="text-slate-400 text-xs mt-1">{{ __('show.character') }}: {{ $actor['character'] }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Recommandations -->
    @php
        $similarResults = isset($kdrama['similar']['results']) ? $kdrama['similar']['results'] : (isset($kdrama['similar']) ? $kdrama['similar'] : []);
        $similarContent = array_slice($similarResults, 0, 10);
        $hasSimilar = !empty($similarContent);
    @endphp
    @if($hasSimilar)
        <div class="mt-16">
            <h2 class="text-3xl font-bold mb-8">{{ __('show.similar') }}</h2>
            <div class="recommendations-grid">
                @foreach($similarContent as $similar)
                    <div class="recommendations-card fade-in">
                        <a href="{{ route('kdrams.show', ['id' => $similar['id'] ?? $similar['tmdb_id'] ?? null]) }}" class="block group">
                            @php
                                $posterPath = $similar['poster_path'] ?? null;
                                $name = $similar['name'] ?? ($similar['title'] ?? '');
                                $voteAverage = $similar['vote_average'] ?? 0;
                            @endphp
                            <div class="recommendations-image">
                                @if($posterPath)
                                    <img
                                        src="https://image.tmdb.org/t/p/w300{{ $posterPath }}"
                                        alt="{{ $name }}"
                                        class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-110"
                                    >
                                @else
                                    <div class="w-full h-full bg-gradient-to-br from-slate-700 to-slate-900 flex items-center justify-center">
                                        <span class="text-xl">🎬</span>
                                    </div>
                                @endif
                                <div class="absolute top-1 right-1">
                                    <span class="badge shadow-lg text-[9px]">⭐ {{ number_format($voteAverage, 1) }}</span>
                                </div>
                            </div>
                            <div class="p-2">
                                <h3 class="font-bold text-slate-100 group-hover:text-red-400 transition line-clamp-2 text-xs sm:text-sm">
                                    {{ $name }}
                                </h3>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

<!-- Modal Acteur -->
<div id="actorModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <!-- Overlay -->
        <div class="fixed inset-0 transition-opacity bg-slate-900 bg-opacity-90" onclick="closeActorModal()" aria-hidden="true"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <!-- Modal Content -->
        <div class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-slate-900 border border-slate-700 rounded-2xl shadow-2xl sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
            <div class="absolute top-4 right-4 z-10">
                <button onclick="closeActorModal()" class="text-slate-400 hover:text-white p-2 bg-slate-800 rounded-full transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <div id="modalContent" class="p-6 md:p-8">
                <!-- Loader -->
                <div id="modalLoader" class="flex flex-col items-center justify-center py-20">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-red-500 mb-4"></div>
                    <p class="text-slate-400">{{ __('common.loading') }}</p>
                </div>
                <!-- Body (chargé en AJAX) -->
                <div id="modalBody" class="hidden"></div>
            </div>
        </div>
    </div>
</div>

<script>
    const translations = @json($kdrama['translations'] ?? []);
    const errorMessages = {
        modifying: "{{ __('errors.error_modifying') }}",
        rating: "{{ __('errors.error_rating') }}",
        serverConnection: "{{ __('errors.error_modifying') }}"
    };

    function switchLang(lang) {
        if (!translations[lang]) return;

        const title = document.getElementById('drama-title');
        const overview = document.getElementById('drama-overview');
        const tagline = document.getElementById('drama-tagline');

        if (title) title.textContent = translations[lang].name || 'N/A';
        if (overview) overview.textContent = translations[lang].overview || 'Pas de description disponible';
        if (tagline) {
            tagline.textContent = translations[lang].tagline || '';
            tagline.style.display = translations[lang].tagline ? 'block' : 'none';
        }

        // Optionnel : Mettre en avant le bouton actif
        document.querySelectorAll('button[onclick^="switchLang"]').forEach(btn => {
            btn.classList.toggle('bg-red-500', btn.getAttribute('onclick').includes(`'${lang}'`));
            btn.classList.toggle('bg-slate-700', !btn.getAttribute('onclick').includes(`'${lang}'`));
        });
    }

    function toggleCast() {
        const castItemsHidden = document.querySelectorAll('.cast-item.hidden');
        const btn = document.getElementById('toggleCastBtn');

        if (castItemsHidden.length > 0) {
            // Afficher tout
            document.querySelectorAll('.cast-item').forEach(item => item.classList.remove('hidden'));
            btn.textContent = "{{ __('show.collapse_cast') }}";
        } else {
            // Réduire
            const highlightActorId = {{ $highlight_actor ?? 'null' }};
            document.querySelectorAll('.cast-item').forEach((item, index) => {
                // On cache si index >= 12 ET que ce n'est pas l'acteur à mettre en avant
                if (index >= 12) {
                    // Note: On ne peut pas facilement vérifier l'ID ici sans data-attribute,
                    // mais le PHP a déjà géré la classe hidden initiale.
                    // On va simplifier : si on réduit, on remet l'état initial.
                    // Mais pour faire simple en JS, on va juste cacher tout ce qui est > 12
                    // SAUF ceux qui ont la bordure rouge (highlight).
                    if (!item.classList.contains('ring-2')) {
                        item.classList.add('hidden');
                    }
                }
            });
            btn.textContent = "{{ __('show.show_full_cast') }}";
        }
    }

    // Modal Acteur
    async function openActorModal(actorId) {
        const modal = document.getElementById('actorModal');
        const loader = document.getElementById('modalLoader');
        const body = document.getElementById('modalBody');

        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden'; // Empêcher le scroll du body

        loader.classList.remove('hidden');
        body.classList.add('hidden');
        body.innerHTML = '';

        try {
            const response = await fetch(`/api/actor/${actorId}`);
            if (!response.ok) {
                const errorData = await response.json().catch(() => ({}));
                throw new Error(errorData.error || `Erreur HTTP: ${response.status}`);
            }

            const html = await response.text();
            body.innerHTML = html;
            loader.classList.add('hidden');
            body.classList.remove('hidden');
        } catch (error) {
            console.error("Détails de l'erreur actorDetails:", error);
            body.innerHTML = `<div class="text-center py-10 text-red-500">
                <p class="font-bold">{{ __('errors.error_loading_actor_data') }}</p>
                <p class="text-xs mt-2 opacity-70">Détail: ${error.message}</p>
            </div>`;
            loader.classList.add('hidden');
            body.classList.remove('hidden');
        }
    }

    function closeActorModal() {
        const modal = document.getElementById('actorModal');
        modal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    // Initialiser le bouton FR au chargement
    document.addEventListener('DOMContentLoaded', () => switchLang('fr'));

document.addEventListener('DOMContentLoaded', function() {
    const contentId = {{ $kdrama['tmdb_id'] ?? $kdrama['id'] }};
    const watchlistBtn = document.getElementById('watchlistBtn');
    const watchingBtn = document.getElementById('watchingBtn');
    const watchedBtn = document.getElementById('watchedBtn');

    if (!watchlistBtn || !watchingBtn || !watchedBtn) return;

    let inWatchlist = @auth {{ $isInWatchlist ? 'true' : 'false' }} @else false @endauth;
    let inWatching = @auth {{ $isWatching ? 'true' : 'false' }} @else false @endauth;
    let inWatched = @auth {{ $isWatched ? 'true' : 'false' }} @else false @endauth;

    // Event listeners
    watchlistBtn.addEventListener('click', function(e) {
        e.preventDefault();
        toggleWatchlist();
    });

    watchingBtn.addEventListener('click', function(e) {
        e.preventDefault();
        toggleWatching();
    });

    watchedBtn.addEventListener('click', function(e) {
        e.preventDefault();
        toggleWatched();
    });

    async function checkStatus() {
        try {
            const response = await fetch(`/api/watchlist/status/${contentId}`);
            const data = await response.json();

            inWatchlist = data.inWatchlist;
            inWatching = data.inWatching;
            inWatched = data.inWatched;
            updateButtonStates();
        } catch (error) {
            console.error('Erreur:', error);
        }
    }

    async function toggleWatchlist() {
        const wasInWatchlist = inWatchlist;
        const wasInWatched = inWatched;

        // Update immediately
        inWatchlist = !inWatchlist;
        updateButtonStates();

        try {
            watchlistBtn.disabled = true;

            const response = await fetch(`/api/watchlist/toggle/${contentId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json',
                },
            });

            const data = await response.json();
            console.log('Toggle Watchlist Response:', data);

            if (response.ok && data.status === 'success') {
                // Use server response to update states
                inWatchlist = data.inWatchlist;
                inWatching = false; // Reset watching when toggling watchlist
                inWatched = data.inWatched;

                // Reset rating when removing "watched" status (switch from watched to watchlist)
                if (wasInWatched && !inWatched) {
                    currentRating = null;
                    updateRatingButtonStates();
                }

                updateButtonStates();
                showToast(data.message, 'success');
            } else {
                // Revert on error
                inWatchlist = wasInWatchlist;
                inWatched = wasInWatched;
                updateButtonStates();
                const errorMsg = data.message || errorMessages.modifying;
                showToast(errorMsg, 'error');
            }
        } catch (error) {
            console.error('Erreur:', error);
            // Revert on error
            inWatchlist = wasInWatchlist;
            inWatched = wasInWatched;
            updateButtonStates();
            showToast(errorMessages.serverConnection, 'error');
        } finally {
            watchlistBtn.disabled = false;
        }
    }

    async function toggleWatched() {
        const wasInWatched = inWatched;
        const wasInWatchlist = inWatchlist;

        // Update immediately
        inWatched = !inWatched;
        updateButtonStates();

        try {
            watchedBtn.disabled = true;

            const response = await fetch(`/api/watched/toggle/${contentId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json',
                },
            });

            const data = await response.json();
            console.log('Toggle Watched Response:', data);

            if (response.ok && data.status === 'success') {
                // Use server response to update states
                inWatched = data.inWatched;
                inWatching = false; // Reset watching when toggling watched
                inWatchlist = data.inWatchlist;

                // Reset rating when removing "watched" status
                if (!inWatched && wasInWatched) {
                    currentRating = null;
                    updateRatingButtonStates();
                }

                updateButtonStates();
                showToast(data.message, 'success');
            } else {
                // Revert on error
                inWatched = wasInWatched;
                inWatchlist = wasInWatchlist;
                updateButtonStates();
                const errorMsg = data.message || errorMessages.modifying;
                showToast(errorMsg, 'error');
            }
        } catch (error) {
            console.error('Erreur:', error);
            // Revert on error
            inWatched = wasInWatched;
            inWatchlist = wasInWatchlist;
            updateButtonStates();
            showToast('Erreur de connexion au serveur', 'error');
        } finally {
            watchedBtn.disabled = false;
        }
    }

    async function toggleWatching() {
        const wasInWatching = inWatching;
        const wasInWatchlist = inWatchlist;
        const wasInWatched = inWatched;

        // Update immediately
        inWatching = !inWatching;
        updateButtonStates();

        try {
            watchingBtn.disabled = true;

            const response = await fetch(`/api/watching/toggle/${contentId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json',
                },
            });

            const data = await response.json();
            console.log('Toggle Watching Response:', data);

            if (response.ok && data.status === 'success') {
                // Use server response to update states (exclusive: only one can be true)
                inWatching = data.inWatching;
                inWatchlist = data.inWatchlist || false; // Force false if watching is true
                inWatched = data.inWatched || false; // Force false if watching is true

                updateButtonStates();
                showToast(data.message, 'success');
            } else {
                // Revert on error
                inWatching = wasInWatching;
                inWatchlist = wasInWatchlist;
                inWatched = wasInWatched;
                updateButtonStates();
                const errorMsg = data.message || errorMessages.modifying;
                showToast(errorMsg, 'error');
            }
        } catch (error) {
            console.error('Erreur:', error);
            // Revert on error
            inWatching = wasInWatching;
            inWatchlist = wasInWatchlist;
            inWatched = wasInWatched;
            updateButtonStates();
            showToast('Erreur de connexion au serveur', 'error');
        } finally {
            watchingBtn.disabled = false;
        }
    }

    function updateButtonStates() {
        const removeText = watchlistBtn.getAttribute('data-remove-text') || '❌ Retirer de ma watchlist';
        const addText = watchlistBtn.getAttribute('data-add-text') || '📺 Ajouter à ma watchlist';
        const unwatchingText = watchingBtn.getAttribute('data-unwatching-text') || '⏸️ Arrêter de regarder';
        const watchingText = watchingBtn.getAttribute('data-watching-text') || '🎬 En train de voir';
        const unwatchText = watchedBtn.getAttribute('data-unwatch-text') || '🔄 Marquer comme non-vu';
        const watchText = watchedBtn.getAttribute('data-watch-text') || '✅ Marquer comme regardé';

        if (inWatchlist) {
            watchlistBtn.textContent = removeText;
            watchlistBtn.classList.remove('bg-red-500', 'hover:bg-red-600');
            watchlistBtn.classList.add('bg-slate-700');
        } else {
            watchlistBtn.textContent = addText;
            watchlistBtn.classList.remove('bg-slate-700');
            watchlistBtn.classList.add('bg-red-500', 'hover:bg-red-600');
        }

        if (inWatching) {
            watchingBtn.textContent = unwatchingText;
            watchingBtn.classList.remove('bg-amber-500', 'hover:bg-amber-600');
            watchingBtn.classList.add('bg-slate-700');
        } else {
            watchingBtn.textContent = watchingText;
            watchingBtn.classList.remove('bg-slate-700');
            watchingBtn.classList.add('bg-amber-500', 'hover:bg-amber-600');
        }

        if (inWatched) {
            watchedBtn.textContent = unwatchText;
            watchedBtn.classList.remove('bg-green-600', 'hover:bg-green-700');
            watchedBtn.classList.add('bg-slate-700');

            // Show rating block
            const ratingBlock = document.getElementById('ratingBlock');
            if (ratingBlock) {
                ratingBlock.style.display = '';
            }
        } else {
            watchedBtn.textContent = watchText;
            watchedBtn.classList.remove('bg-slate-700');
            watchedBtn.classList.add('bg-green-600', 'hover:bg-green-700');

            // Hide rating block
            const ratingBlock = document.getElementById('ratingBlock');
            if (ratingBlock) {
                ratingBlock.style.display = 'none';
            }
        }
    }

    // Rating buttons
    const ratingButtons = document.querySelectorAll('.rating-btn');
    let currentRating = @auth {{ $userStatus?->rating ?? 'null' }} @else null @endauth;

    ratingButtons.forEach(btn => {
        btn.addEventListener('click', async (e) => {
            e.preventDefault();
            const rating = parseInt(btn.dataset.rating);

            // Toggle off if clicking the same rating
            const newRating = currentRating === rating ? null : rating;

            try {
                const response = await fetch(`/api/rating/${contentId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ rating: newRating })
                });

                const data = await response.json();

                if (response.ok && data.status === 'success') {
                    currentRating = data.rating;
                    updateRatingButtonStates();
                    showToast(data.message, 'success');
                } else {
                    showToast(data.error || errorMessages.rating, 'error');
                }
            } catch (error) {
                console.error('Erreur:', error);
                showToast('Erreur de connexion au serveur', 'error');
            }
        });
    });

    function updateRatingButtonStates() {
        ratingButtons.forEach(btn => {
            const rating = parseInt(btn.dataset.rating);
            btn.classList.remove('bg-red-600', 'hover:bg-red-700', 'bg-green-600', 'hover:bg-green-700', 'bg-purple-600', 'hover:bg-purple-700');
            btn.classList.add('bg-slate-700', 'hover:bg-slate-600');

            if (currentRating === rating) {
                btn.classList.remove('bg-slate-700', 'hover:bg-slate-600');
                if (rating === 1) {
                    btn.classList.add('bg-red-600', 'hover:bg-red-700');
                } else if (rating === 2) {
                    btn.classList.add('bg-green-600', 'hover:bg-green-700');
                } else if (rating === 3) {
                    btn.classList.add('bg-purple-600', 'hover:bg-purple-700');
                }
            }
        });
    }

    // Initialize rating button states
    updateRatingButtonStates();

    function showToast(message, type = 'success') {
        // Remove existing toast
        const existingToast = document.querySelector('.toast');
        if (existingToast) {
            existingToast.remove();
        }

        const toast = document.createElement('div');
        toast.className = `toast fixed top-4 right-4 px-6 py-3 rounded-lg text-white font-semibold shadow-lg z-50 animate-in fade-in slide-in-from-top`;

        if (type === 'success') {
            toast.classList.add('bg-green-600');
        } else {
            toast.classList.add('bg-red-600');
        }

        toast.textContent = message;
        document.body.appendChild(toast);

        // Auto-remove after 3 seconds
        setTimeout(() => {
            toast.style.animation = 'fade-out 0.3s ease-out forwards';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
});
</script>
@endsection
