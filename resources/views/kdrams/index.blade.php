@extends('layouts.app')

@section('title', __('catalog.page_title'))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <!-- Header -->
    @php
        $hasFilters = !empty($filters['search']) || !empty($filters['actor']) || !empty($filters['min_rating']) || !empty($filters['from_year']) || !empty($filters['to_year']) || !empty($filters['hide_watched']) || !empty($filters['hide_watchlist']);
    @endphp
    <div class="mb-12" x-data="{ showFilters: {{ $hasFilters ? 'true' : 'false' }} }">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-6 mb-8">
            <div>
                <h1 class="text-4xl font-bold mb-2">{{ __('catalog.title') }}</h1>
                <p class="text-slate-400">{{ __('catalog.subtitle') }}</p>
            </div>

            <!-- Bouton toggle filtres (Mobile uniquement) -->
            <button @click="showFilters = !showFilters"
                    class="sm:hidden btn-outline flex items-center justify-center gap-2 py-3 px-6 w-full"
                    :class="showFilters ? 'bg-slate-800' : ''">
                <span x-text="showFilters ? '{{ __('catalog.hide_filters') }}' : '{{ __('catalog.show_filters') }}'"></span>
            </button>
        </div>

        <!-- Filtres -->
        <form method="GET" action="{{ route('kdrams.catalog') }}"
              class="card-dark p-6 sm:p-8 border-slate-700/50 shadow-2xl"
              :class="showFilters ? 'block' : 'hidden sm:block'">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-6">
                <!-- Recherche -->
                <div class="space-y-2">
                    <label class="input-label flex items-center gap-2">
                        <span class="text-red-400">🔍</span> {{ __('catalog.filter_search') }}
                    </label>
                    <input type="text" name="search" placeholder="{{ __('catalog.filter_search_placeholder') }}" value="{{ $filters['search'] }}"
                           class="w-full bg-slate-800/50 border-slate-700 focus:ring-red-500/50">
                    <p class="text-[10px] text-slate-500 italic">{{ __('catalog.filter_search_hint') }}</p>
                </div>

                <!-- Acteur -->
                <div class="space-y-2">
                    <label class="input-label flex items-center gap-2">
                        <span class="text-red-400">🎭</span> {{ __('catalog.filter_actor') }}
                    </label>
                    <input type="hidden" name="actor_id" value="{{ $filters['actor_id'] ?? '' }}">
                    <input type="text" name="actor" placeholder="{{ __('catalog.filter_actor_placeholder') }}" value="{{ $filters['actor'] }}"
                           class="w-full bg-slate-800/50 border-slate-700 focus:ring-red-500/50">
                    <p class="text-[10px] text-slate-500 italic">{{ __('catalog.filter_actor_hint') }}</p>
                </div>

                <!-- Tri -->
                <div class="space-y-2">
                    <label class="input-label flex items-center gap-2">
                        <span class="text-red-400">📊</span> {{ __('catalog.filter_sort') }}
                    </label>
                    <select name="sort" class="w-full bg-slate-800/50 border-slate-700 focus:ring-red-500/50">
                        <option value="popularity.desc" {{ $filters['sort'] === 'popularity.desc' ? 'selected' : '' }}>{{ __('catalog.filter_sort_popularity') }}</option>
                        <option value="vote_average.desc" {{ $filters['sort'] === 'vote_average.desc' ? 'selected' : '' }}>{{ __('catalog.filter_sort_rating') }}</option>
                        <option value="first_air_date.desc" {{ $filters['sort'] === 'first_air_date.desc' ? 'selected' : '' }}>{{ __('catalog.filter_sort_recent') }}</option>
                    </select>
                </div>

                <!-- Note minimale -->
                <div class="space-y-2">
                    <label class="input-label flex items-center gap-2">
                        <span class="text-red-400">⭐</span> {{ __('catalog.filter_min_rating') }}
                    </label>
                    <div class="relative">
                        <input type="number" name="min_rating" min="0" max="10" step="0.5" value="{{ $filters['min_rating'] }}"
                               class="w-full bg-slate-800/50 border-slate-700 focus:ring-red-500/50 pl-3 pr-8">
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 text-xs">{{ __('catalog.filter_rating_unit') }}</span>
                    </div>
                </div>

                <!-- Année De -->
                <div class="space-y-2">
                    <label class="input-label flex items-center gap-2">
                        <span class="text-red-400">📅</span> {{ __('catalog.filter_from_year') }}
                    </label>
                    <select name="from_year" class="w-full bg-slate-800/50 border-slate-700 focus:ring-red-500/50">
                        <option value="">{{ __('catalog.filter_all_years') }}</option>
                        @php
                            $currentYear = date('Y');
                            $startYear = 1970; // Les K-Dramas avant 70 sont très rares sur TMDB
                        @endphp
                        @for($year = $currentYear; $year >= $startYear; $year--)
                            <option value="{{ $year }}" {{ $filters['from_year'] == $year ? 'selected' : '' }}>
                                {{ $year }}
                            </option>
                        @endfor
                    </select>
                </div>

                <!-- Année À -->
                <div class="space-y-2">
                    <label class="input-label flex items-center gap-2">
                        <span class="text-red-400">📅</span> {{ __('catalog.filter_to_year') }}
                    </label>
                    <select name="to_year" class="w-full bg-slate-800/50 border-slate-700 focus:ring-red-500/50">
                        <option value="">{{ __('catalog.filter_all_years') }}</option>
                        @for($year = $currentYear; $year >= $startYear; $year--)
                            <option value="{{ $year }}" {{ $filters['to_year'] == $year ? 'selected' : '' }}>
                                {{ $year }}
                            </option>
                        @endfor
                    </select>
                </div>

                @auth
                    <!-- Masquer Déjà vus -->
                    <div class="space-y-2">
                        <label class="input-label flex items-center gap-2">
                            <span class="text-red-400">🚫</span> {{ __('catalog.filter_watched') }}
                        </label>
                        <div class="flex items-center h-10">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="hide_watched" value="1" {{ $filters['hide_watched'] ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-red-500"></div>
                                <span class="ml-3 text-xs font-medium text-slate-400">{{ __('catalog.filter_clear') }}</span>
                            </label>
                        </div>
                    </div>

                    <!-- Masquer Watchlist -->
                    <div class="space-y-2">
                        <label class="input-label flex items-center gap-2">
                            <span class="text-red-400">📌</span> {{ __('catalog.filter_watchlist') }}
                        </label>
                        <div class="flex items-center h-10">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="hide_watchlist" value="1" {{ $filters['hide_watchlist'] ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-red-500"></div>
                                <span class="ml-3 text-xs font-medium text-slate-400">{{ __('catalog.filter_clear') }}</span>
                            </label>
                        </div>
                    </div>
                @endauth
            </div>

            <div class="mt-8 pt-6 border-t border-slate-700/50 flex flex-col sm:flex-row gap-4 justify-between items-center">
                <div class="text-slate-500 text-xs italic order-2 sm:order-1">
                    * {{ __('catalog.filter_search_btn') }}
                </div>
                <div class="flex gap-3 w-full sm:w-auto order-1 sm:order-2">
                    <a href="{{ route('kdrams.catalog') }}" class="btn-outline flex-1 sm:flex-initial text-center py-2 px-6 text-sm">
                        {{ __('catalog.filter_clear') }}
                    </a>
                    <button type="submit" class="btn-primary flex-1 sm:flex-initial py-2 px-10">
                        🚀 {{ __('catalog.filter_search_btn') }}
                    </button>
                </div>
            </div>
        </form>
    </div>

    @if(count($kdrams) > 0)
        <div class="mb-6 flex items-center justify-between">
            <p class="text-slate-400 text-sm">
                {{ __('catalog.show_filters') }} <span id="current-count" class="text-red-400 font-bold">{{ count($kdrams) }}</span>
                {{ __('catalog.hide_filters') }} <span class="text-red-400 font-bold">{{ $total_results }}</span> {{ __('catalog.results_count') }}
            </p>
        </div>

        <div id="kdrama-grid" class="content-grid mb-12">
            @foreach($kdrams as $kdrama)
                @include('kdrams._card', [
                    'kdrama' => $kdrama,
                    'filters' => $filters,
                    'userStatus' => $userStatus ?? []
                ])
            @endforeach
        </div>

        <!-- Pagination -->
        <div id="pagination-container" class="flex justify-center gap-4 items-center mt-16">
            @if($current_page < $total_pages)
                <button id="load-more"
                        data-page="{{ $current_page + 1 }}"
                        data-url="{{ route('kdrams.catalog', array_merge($filters, ['page' => '__PAGE__'])) }}"
                        class="btn-primary py-3 px-10 text-lg"
                        data-load-more-text="✨ {{ __('catalog.show_filters') }}"
                        data-loading-text="⏳ {{ __('catalog.hide_filters') }}"
                        data-error-text="❌ {{ __('catalog.no_results') }}">
                    <span>✨ {{ __('catalog.show_filters') }}</span>
                    <span id="loading-spinner" class="hidden animate-spin ml-2">⏳</span>
                </button>
            @endif
        </div>
    @else
        <div class="card-dark py-24 text-center">
            <div class="text-6xl mb-6">🔍</div>
            <h2 class="text-2xl font-bold text-slate-200 mb-4">{{ __('catalog.no_results') }}</h2>
            <p class="text-slate-400 mb-8">
                @if(!empty($filters['search']))
                    {{ __('catalog.no_results_message') }}: "{{ $filters['search'] }}".
                @elseif(!empty($filters['actor']))
                    {{ __('catalog.no_results_message') }}: "{{ $filters['actor'] }}".<br>
                    <span class="text-sm italic text-slate-500">Note: {{ __('catalog.filter_actor_hint') }}</span>
                @else
                    {{ __('catalog.no_results_message') }}
                @endif
            </p>

            @if(empty($filters['search']) && empty($filters['actor']))
                @auth
                    @if(auth()->user()->is_admin)
                        <p class="text-slate-500 text-sm mb-4">{{ __('catalog.hide_filters') }}</p>
                        <a href="{{ route('admin.settings.index') }}" class="btn-primary inline-block">
                            ⚙️ {{ __('common.configure') }}
                        </a>
                    @endif
                @endauth
            @else
                <a href="{{ route('kdrams.catalog') }}" class="btn-primary inline-block">
                    🔄 {{ __('catalog.filter_clear') }}
                </a>
            @endif
        </div>
    @endif
</div>
@endsection

<script>
document.addEventListener('DOMContentLoaded', function() {
    const loadMoreBtn = document.getElementById('load-more');
    const grid = document.getElementById('kdrama-grid');
    const spinner = document.getElementById('loading-spinner');
    const btnText = loadMoreBtn?.querySelector('span');

    // Vider actor_id si le nom de l'acteur est modifié manuellement
    const actorInput = document.querySelector('input[name="actor"]');
    const actorIdInput = document.querySelector('input[name="actor_id"]');
    if (actorInput && actorIdInput) {
        actorInput.addEventListener('input', function() {
            actorIdInput.value = '';
        });
    }

    if (loadMoreBtn) {
        // Nettoyer l'URL des paramètres vides
        const currentUrl = new URL(window.location.href);
        const params = new URLSearchParams(currentUrl.search);
        let hasChanges = false;

        for (const [key, value] of params.entries()) {
            if (value === '') {
                params.delete(key);
                hasChanges = true;
            }
        }

        if (hasChanges) {
            window.history.replaceState({}, '', `${currentUrl.pathname}?${params.toString()}`);
        }

        loadMoreBtn.addEventListener('click', function() {
            const page = this.getAttribute('data-page');
            const baseUrl = this.getAttribute('data-url');
            const url = baseUrl.replace('__PAGE__', page);

            // UI Feedback
            this.disabled = true;
            spinner.classList.remove('hidden');
            if (btnText) btnText.textContent = 'Chargement...';

            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.html) {
                    // Ajouter les nouveaux éléments
                    grid.insertAdjacentHTML('beforeend', data.html);

                    // Mettre à jour le compteur
                    const countSpan = document.getElementById('current-count');
                    if (countSpan && grid) {
                        const actualCount = grid.querySelectorAll('.content-card').length;
                        countSpan.textContent = actualCount;
                    }

                    // Mettre à jour le bouton
                    if (data.has_more) {
                        this.setAttribute('data-page', data.next_page);
                        this.disabled = false;
                        spinner.classList.add('hidden');
                        if (btnText) btnText.textContent = '✨ Voir plus de K-Dramas';
                    } else {
                        document.getElementById('pagination-container').remove();
                    }
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
                this.disabled = false;
                spinner.classList.add('hidden');
                if (btnText) btnText.textContent = '❌ Erreur, réessayez';
            });
        });
    }
});
</script>
