@extends('layouts.app')

@section('title', __('catalog.page_title'))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <!-- Header -->
    @php
        $hasFilters = !empty($filters['search']) || !empty($filters['actor']) || !empty($filters['min_rating']) || !empty($filters['from_year']) || !empty($filters['to_year']) || !empty($filters['hide_watched']) || !empty($filters['hide_watchlist']) || !empty($filters['exact_name']) || !empty($filters['has_photo']);
    @endphp
    <div class="mb-8" x-data="{ showFilters: {{ $hasFilters ? 'true' : 'false' }}, isLoading: false }">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-6 mb-8">
            <div>
                <div class="flex items-center justify-between mb-2">
                    <h1 class="text-4xl font-bold">{{ __('catalog.title') }}</h1>

                    <!-- Tabs -->
                    <div class="flex bg-slate-800/50 p-1 rounded-lg border border-slate-700">
                        <button type="button"
                                onclick="switchView('dramas')"
                                class="px-4 py-2 rounded-md text-sm font-medium transition-all {{ ($filters['view'] ?? 'dramas') === 'dramas' ? 'bg-red-500 text-white shadow-lg' : 'text-slate-400 hover:text-slate-200' }}">
                            {{ __('catalog.tabs_dramas') }}
                        </button>
                        <button type="button"
                                onclick="switchView('actors')"
                                class="px-4 py-2 rounded-md text-sm font-medium transition-all {{ ($filters['view'] ?? 'dramas') === 'actors' ? 'bg-red-500 text-white shadow-lg' : 'text-slate-400 hover:text-slate-200' }}">
                            {{ __('catalog.tabs_actors') }}
                        </button>
                    </div>
                </div>
                <p class="text-slate-400">{{ __('catalog.subtitle') }}</p>
            </div>
        </div>

        <!-- Mobile backdrop pour le filtre overlay -->
        <div class="fixed inset-0 bg-black/50 z-30 lg:hidden transition-opacity"
             :class="{ 'opacity-0 pointer-events-none': !showFilters, 'opacity-100': showFilters }"
             @click="showFilters = false"></div>

        <!-- Layout: Sidebar (desktop) + Content (responsive) -->
        <div class="flex gap-6 lg:gap-8">
            <!-- Sidebar Filtres: Modal sur mobile, Static sur desktop -->
            <aside class="fixed top-0 right-0 h-screen w-64 sm:w-72 z-40 lg:static lg:h-auto flex-shrink-0 transition-transform duration-300 ease-out overflow-y-auto"
                   :class="{ 'translate-x-full lg:translate-x-0': !showFilters, 'translate-x-0': showFilters }">
                <!-- Close button (Mobile only) -->
                <div class="lg:hidden sticky top-0 flex items-center justify-between p-3 bg-slate-900/95 backdrop-blur-sm border-b border-slate-700 z-10">
                    <h3 class="text-sm font-bold text-white">{{ __('catalog.show_filters') }}</h3>
                    <button @click="showFilters = false" class="p-1 text-slate-400 hover:text-white hover:bg-slate-800 rounded-lg transition-colors duration-200">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form method="GET" action="{{ route('kdrams.catalog') }}"
                      x-data="{
                          currentView: '{{ $view = $filters['view'] ?? 'dramas' }}'
                      }"
                      x-init="window.__alpine_data = $data"
                      @view-switched.window="currentView = $event.detail.view"
                      class="card-dark p-3 border-slate-700/50 shadow-2xl sticky top-20">
                    <!-- Page number input (hidden) -->
                    <input type="hidden" name="page" id="page-input" value="{{ $current_page }}">
                    <input type="hidden" name="view" id="view-input" x-model="currentView">
                    <input type="hidden" name="actor" value="{{ $filters['actor'] ?? '' }}">
                    <input type="hidden" name="actor_id" value="{{ $filters['actor_id'] ?? '' }}">

            <!-- Section 1: Basic Filters -->
            <div>
                <h3 class="text-sm font-bold text-slate-200 mb-2 flex items-center gap-2">
                    {{ __('catalog.filters_basic') }}
                </h3>
                <div class="grid grid-cols-1 gap-4 mb-5">
                    <!-- Recherche (Drames) -->
                    <div class="filter-form-group" x-show="currentView === 'dramas'">
                        <label class="filter-form-label">{{ __('catalog.filter_search') }}</label>
                        <input type="text" name="search_drama" placeholder="{{ __('catalog.filter_search_placeholder') }}" value="{{ ($filters['view'] ?? 'dramas') === 'dramas' ? ($filters['search'] ?? '') : '' }}"
                               @keydown.enter.prevent="triggerLiveFilter($el)"
                               class="w-full">
                        <p class="filter-form-hint">{{ __('catalog.filter_search_hint') }}</p>
                    </div>

                    <!-- Recherche (Acteurs) -->
                    <div class="filter-form-group" x-show="currentView === 'actors'">
                        <label class="filter-form-label">{{ __('catalog.filter_actor_search') ?? 'Rechercher un acteur' }}</label>
                        <input type="text" name="search_actor" placeholder="{{ __('catalog.filter_actor_placeholder') }}" value="{{ ($filters['view'] ?? '') === 'actors' ? ($filters['search'] ?? '') : '' }}"
                               @keydown.enter.prevent="triggerLiveFilter($el)"
                               class="w-full" :disabled="currentView !== 'actors'">
                        <p class="filter-form-hint">{{ __('catalog.filter_actor_hint') }}</p>
                    </div>

                    <!-- Tri -->
                    <div class="filter-form-group" x-show="currentView === 'dramas'">
                        <label class="filter-form-label">{{ __('catalog.filter_sort') }}</label>
                        <select name="sort" class="w-full">
                            <option value="popularity.desc" {{ $filters['sort'] === 'popularity.desc' ? 'selected' : '' }}>{{ __('catalog.filter_sort_popularity') }}</option>
                            <option value="vote_average.desc" {{ $filters['sort'] === 'vote_average.desc' ? 'selected' : '' }}>{{ __('catalog.filter_sort_rating') }}</option>
                            <option value="first_air_date.desc" {{ $filters['sort'] === 'first_air_date.desc' ? 'selected' : '' }}>{{ __('catalog.filter_sort_recent') }}</option>
                        </select>
                    </div>

                    <!-- Note minimale -->
                    <div class="filter-form-group" x-show="currentView === 'dramas'">
                        <label class="filter-form-label">{{ __('catalog.filter_min_rating') }}</label>
                        <div class="relative">
                            <input type="number" name="min_rating" min="0" max="10" step="0.5" value="{{ $filters['min_rating'] }}"
                                   class="w-full pr-8">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-500 text-xs">{{ __('catalog.filter_rating_unit') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 2: Advanced Filters -->
            <div class="border-t border-slate-700/50 pt-4" x-show="currentView === 'dramas'">
                <h3 class="text-sm font-bold text-slate-200 mb-2 flex items-center gap-2">
                    {{ __('catalog.filters_advanced') }}
                </h3>
                <div class="grid grid-cols-1 gap-4">
                    <!-- Année De -->
                    <div class="filter-form-group">
                        <label class="filter-form-label">{{ __('catalog.filter_from_year') }}</label>
                        <select name="from_year" class="w-full">
                            <option value="">{{ __('catalog.filter_all_years') }}</option>
                            @php
                                $currentYear = date('Y');
                                $startYear = 1970;
                            @endphp
                            @for($year = $currentYear; $year >= $startYear; $year--)
                                <option value="{{ $year }}" {{ $filters['from_year'] == $year ? 'selected' : '' }}>{{ $year }}</option>
                            @endfor
                        </select>
                    </div>

                    <!-- Année À -->
                    <div class="filter-form-group">
                        <label class="filter-form-label">{{ __('catalog.filter_to_year') }}</label>
                        <select name="to_year" class="w-full">
                            <option value="">{{ __('catalog.filter_all_years') }}</option>
                            @for($year = $currentYear; $year >= $startYear; $year--)
                                <option value="{{ $year }}" {{ $filters['to_year'] == $year ? 'selected' : '' }}>{{ $year }}</option>
                            @endfor
                        </select>
                    </div>

                    @auth
                        <!-- Masquer Regardés -->
                        <div class="filter-form-group">
                            <label class="filter-form-label">{{ __('catalog.filter_watched') }}</label>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="hide_watched" value="1" {{ $filters['hide_watched'] ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-700 rounded-full peer peer-checked:bg-green-500 peer-checked:after:translate-x-5 after:content-[''] after:absolute after:top-1/2 after:-translate-y-1/2 after:left-1 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all"></div>
                                <span class="ml-3 text-xs font-medium text-slate-400">{{ __('catalog.filter_toggle_active') }}</span>
                            </label>
                        </div>

                        <!-- Masquer En train de regarder -->
                        <div class="filter-form-group">
                            <label class="filter-form-label">{{ __('catalog.filter_watching') }}</label>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="hide_watching" value="1" {{ $filters['hide_watching'] ?? false ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-700 rounded-full peer peer-checked:bg-amber-500 peer-checked:after:translate-x-5 after:content-[''] after:absolute after:top-1/2 after:-translate-y-1/2 after:left-1 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all"></div>
                                <span class="ml-3 text-xs font-medium text-slate-400">{{ __('catalog.filter_toggle_active') }}</span>
                            </label>
                        </div>

                        <!-- Masquer Watchlist -->
                        <div class="filter-form-group">
                            <label class="filter-form-label">{{ __('catalog.filter_watchlist') }}</label>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="hide_watchlist" value="1" {{ $filters['hide_watchlist'] ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-700 rounded-full peer peer-checked:bg-red-500 peer-checked:after:translate-x-5 after:content-[''] after:absolute after:top-1/2 after:-translate-y-1/2 after:left-1 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all"></div>
                                <span class="ml-3 text-xs font-medium text-slate-400">{{ __('catalog.filter_toggle_active') }}</span>
                            </label>
                        </div>
                    @endauth
                </div>
            </div>

            <!-- Options Recherche Acteur -->
            <div class="border-t border-slate-700/50 pt-4" x-show="currentView === 'actors'">
                <h3 class="text-sm font-bold text-slate-200 mb-2 flex items-center gap-2">
                    {{ __('catalog.filters_advanced') }}
                </h3>
                <div class="grid grid-cols-1 gap-4">
                    <div class="filter-form-group">
                        <label class="filter-form-label">{{ __('catalog.filter_exact_name') }}</label>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="exact_name" value="1" {{ $filters['exact_name'] ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-700 rounded-full peer peer-checked:bg-blue-500 peer-checked:after:translate-x-5 after:content-[''] after:absolute after:top-1/2 after:-translate-y-1/2 after:left-1 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all"></div>
                            <span class="ml-3 text-xs font-medium text-slate-400">{{ __('catalog.filter_toggle_active') }}</span>
                        </label>
                    </div>

                    <div class="filter-form-group">
                        <label class="filter-form-label">{{ __('catalog.filter_has_photo') }}</label>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="has_photo" value="1" {{ $filters['has_photo'] ? 'checked' : '' }} class="sr-only peer">
                            <div class="w-11 h-6 bg-slate-700 rounded-full peer peer-checked:bg-purple-500 peer-checked:after:translate-x-5 after:content-[''] after:absolute after:top-1/2 after:-translate-y-1/2 after:left-1 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all"></div>
                            <span class="ml-3 text-xs font-medium text-slate-400">{{ __('catalog.filter_toggle_active') }}</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-700/50">
                <button type="button" onclick="resetFiltersAndReload()" class="btn-outline text-center py-2 px-6 text-sm w-full">
                    🔄 {{ __('catalog.filter_clear') }}
                </button>
            </div>
        </form>
            </aside>

            <!-- Main Content (Results Grid) -->
            <div class="flex-1" id="main-content-area">
                <div class="mb-6 flex items-center justify-between min-h-[40px]">
                    <div id="results-stats-container" class="flex items-center gap-4 {{ count($kdrams) === 0 ? 'hidden' : '' }}">
                        <p class="text-slate-400 text-sm">
                            {{ __('catalog.page_label') }}
                            <span id="current-page-display" class="text-red-400 font-bold">{{ $current_page }}</span> /
                            <span id="total-pages-display" class="text-red-400 font-bold">{{ $total_pages }}</span> —
                            {{ __('catalog.showing_label') }}
                            <span id="current-count" class="text-red-400 font-bold">{{ count($kdrams) }}</span>
                            {{ __('catalog.results_out_of') }}
                            <span id="total-results-display" class="text-red-400 font-bold">{{ $total_results }}</span>
                            {{ __('catalog.total_label') }}
                        </p>
                    </div>

                    <!-- Search Indicator -->
                    <div id="search-spinner"
                         x-show="isLoading"
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 transform scale-90"
                         x-transition:enter-end="opacity-100 transform scale-100"
                         x-transition:leave="transition ease-in duration-200"
                         x-transition:leave-start="opacity-100 transform scale-100"
                         x-transition:leave-end="opacity-0 transform scale-90"
                         class="flex items-center gap-3 bg-red-500/10 px-3 py-1.5 rounded-full border border-red-500/20">
                        <svg class="animate-spin h-4 w-4 text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="text-xs font-bold text-red-400 uppercase tracking-widest">{{ __('catalog.loading') }}</span>
                    </div>
                </div>

                <div id="kdrama-grid-container">
                    <div id="kdrama-grid" class="content-grid mb-12 {{ count($kdrams) === 0 ? 'hidden' : '' }}">
                        @php
                            $viewName = ($filters['view'] ?? 'dramas') === 'actors' ? 'kdrams._actor_card' : 'kdrams._card';
                        @endphp
                        @foreach($kdrams as $item)
                            @include($viewName, [
                                'kdrama' => $item,
                                'actor' => $item,
                                'filters' => $filters,
                                'userStatus' => $userStatus ?? []
                            ])
                        @endforeach
                    </div>

                    <!-- Pagination Simple -->
                    <div id="pagination-container" class="flex justify-center items-center gap-4 mt-16 {{ count($kdrams) === 0 ? 'hidden' : '' }}"></div>

                    <!-- No Results Container (Always in DOM, hidden when there are results) -->
                    <div id="no-results-container" class="card-dark py-24 text-center {{ count($kdrams) === 0 ? '' : 'hidden' }}">
                        <div class="text-6xl mb-6">🔍</div>
                        <h2 class="text-2xl font-bold text-slate-200 mb-4">{{ __('catalog.no_results') }}</h2>
                        <p class="text-slate-400 mb-8" id="no-results-message">
                            @if($filters['view'] === 'actors')
                                @if(!empty($filters['search']))
                                    Aucun acteur trouvé pour: <strong>"{{ $filters['search'] }}"</strong>
                                    @if($filters['exact_name'] || $filters['has_photo'])
                                        <br><span class="text-sm italic text-slate-500 mt-2 block">
                                        Filtres actifs:
                                        @if($filters['exact_name'])Nom exact @endif
                                        @if($filters['has_photo'])Avec photo @endif
                                        </span>
                                    @endif
                                @else
                                    {{ __('catalog.no_results_message') }}
                                @endif
                            @elseif(!empty($filters['search']))
                                {{ __('catalog.no_results_message') }}: <strong>"{{ $filters['search'] }}"</strong>
                            @elseif(!empty($filters['actor']))
                                {{ __('catalog.no_results_message') }}: <strong>"{{ $filters['actor'] }}"</strong><br>
                                <span class="text-sm italic text-slate-500">Note: {{ __('catalog.filter_actor_hint') }}</span>
                            @else
                                {{ __('catalog.no_results_message') }}
                            @endif
                        </p>

                        @php
                            $hasActiveFilters = !empty($filters['search']) || !empty($filters['actor']) || !empty($filters['min_rating']) || !empty($filters['from_year']) || !empty($filters['to_year']) || $filters['hide_watched'] || $filters['hide_watching'] || $filters['hide_watchlist'] || $filters['exact_name'] || $filters['has_photo'];
                        @endphp
                        @if(!$hasActiveFilters)
                            @auth
                                @if(auth()->user()->is_admin)
                                    <p class="text-slate-500 text-sm mb-4">{{ __('catalog.hide_filters') }}</p>
                                    <a href="{{ route('admin.settings.index') }}?view={{ $filters['view'] ?? 'dramas' }}" class="btn-primary inline-block">
                                        ⚙️ {{ __('common.configure') }}
                                    </a>
                                @endif
                            @endauth
                        @else
                            <a href="{{ route('kdrams.catalog') }}?view={{ $filters['view'] ?? 'dramas' }}" class="btn-primary inline-block">
                                🔄 {{ __('catalog.filter_clear') }}
                            </a>
                        @endif
                    </div>
                </div>

                <button @click="showFilters = true" v-show="!showFilters"
                        class="lg:hidden fixed bottom-6 right-6 z-20 w-14 h-14 rounded-full bg-slate-800/90 backdrop-blur-sm border border-slate-700 hover:border-slate-600 hover:bg-slate-700/90 shadow-lg flex items-center justify-center text-xl transition-all duration-200 hover:scale-110">
                    🔍
                </button>
            </div>
            <!-- Close main content div -->
        </div>
        <!-- Close flex layout div -->
    </div>
</div>

<!-- Modal Détails Acteur -->
<div id="actorModal" class="fixed inset-0 z-[60] hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <!-- Backdrop -->
        <div class="fixed inset-0 transition-opacity bg-slate-950/80 backdrop-blur-sm" aria-hidden="true"></div>

        <!-- Centering trick -->
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <!-- Modal panel -->
        <div class="inline-block w-full max-w-4xl overflow-hidden text-left align-bottom transition-all transform bg-slate-900 border border-slate-700 rounded-2xl shadow-2xl sm:my-8 sm:align-middle">
            <!-- Close Button -->
            <div class="absolute top-4 right-4 z-10">
                <button onclick="closeActorModal()" class="text-slate-400 hover:text-white p-2 bg-slate-800 rounded-full transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div id="modalContent" class="p-6 md:p-8">
                <!-- Loader -->
                <div id="actorModalLoader" class="flex flex-col items-center justify-center py-20">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-red-500 mb-4"></div>
                    <p class="text-slate-400 font-medium">Chargement...</p>
                </div>

                <!-- Content container -->
                <div id="modalBody">
                    <!-- Injection via JS -->
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

<script>
document.addEventListener('DOMContentLoaded', function() {
    const grid = document.getElementById('kdrama-grid');
    const searchSpinner = document.getElementById('search-spinner');
    const loadMoreBtn = document.getElementById('load-more');
    const spinner = document.getElementById('loading-spinner');

    // Définir grid si non présent (par exemple si la grille était masquée)
    window.getGrid = function() {
        return document.getElementById('kdrama-grid');
    };
    const btnText = loadMoreBtn?.querySelector('span');


    // Basculer entre la vue drames et acteurs
    window.switchView = function(view) {
        const viewInput = document.getElementById('view-input');
        const pageInput = document.getElementById('page-input');

        if (viewInput) viewInput.value = view;
        if (pageInput) pageInput.value = 1;

        // Mettre à jour les boutons visuellement immédiatement
        const buttons = document.querySelectorAll('[onclick^="switchView"]');
        buttons.forEach(btn => {
            if (btn.getAttribute('onclick').includes(view)) {
                btn.classList.add('bg-red-500', 'text-white', 'shadow-lg');
                btn.classList.remove('text-slate-400', 'hover:text-slate-200');
            } else {
                btn.classList.remove('bg-red-500', 'text-white', 'shadow-lg');
                btn.classList.add('text-slate-400', 'hover:text-slate-200');
            }
        });

        // Déclencher l'événement pour Alpine.js
        window.dispatchEvent(new CustomEvent('view-switched', { detail: { view: view } }));

        applyLiveFilter();
    };

    // Filtrer par acteur spécifique
    window.filterByActor = function(actorId, actorName) {
        // Navigate to dramas view filtered by actor ID
        window.location.href = '{{ route("kdrams.catalog") }}?view=dramas&actor_id=' + actorId;
    };

    // Fonction pour réinitialiser les filtres
    window.resetFiltersAndReload = function() {
        const filterForm = document.querySelector('form[method="GET"]');
        // Get current view before resetting form
        const currentView = document.getElementById('view-input').value || 'dramas';

        if (filterForm) {
            filterForm.reset();
        }

        // Keep the current view when resetting filters
        window.location.href = '{{ route("kdrams.catalog") }}?view=' + currentView;
    };

    // Fonction pour aller à une page via l'input
    window.goToPageInput = function() {
        const input = document.getElementById('goto-page-input');
        if (!input) return;
        const page = parseInt(input.value);
        if (page >= 1 && page <= parseInt(input.max)) {
            goToPage(page);
        }
    };

    // Mettre à jour l'input page hidden quand on navigue
    window.addEventListener('popstate', function() {
        const pageInput = document.getElementById('page-input');
        const params = new URLSearchParams(window.location.search);
        const page = params.get('page') || '1';
        if (pageInput) {
            pageInput.value = page;
        }
    });

    // Générer la pagination initiale au chargement de la page
    const initialCurrentPage = {{ $current_page }};
    const initialTotalPages = {{ $total_pages }};

    // Live Filtering en AJAX
    const filterForm = document.querySelector('form[method="GET"]');
    let filterTimeout;

    // Make triggerLiveFilter globally accessible for Alpine.js
    window.triggerLiveFilter = function(inputElement) {
        // Si c'est un filtre de recherche, reset la page à 1
        const searchFilters = ['search_drama', 'search_actor', 'actor', 'from_year', 'to_year', 'min_rating', 'exact_name', 'has_photo'];
        if (inputElement && (searchFilters.includes(inputElement.name) || inputElement.id === 'view-input')) {
            const pageInput = document.getElementById('page-input');
            if (pageInput) {
                pageInput.value = 1;
            }

            // Clear actor_id when using search filters to avoid conflicts
            if (inputElement.name === 'search_drama' && inputElement.value) {
                const actorIdInput = filterForm.querySelector('input[name="actor_id"]');
                if (actorIdInput) {
                    actorIdInput.value = '';
                }
            }
        }

        clearTimeout(filterTimeout);
        filterTimeout = setTimeout(applyLiveFilter, 300);
    };

    if (filterForm) {
        const allInputs = filterForm.querySelectorAll('input[type="text"], input[type="number"], select, input[type="checkbox"]');

        allInputs.forEach(input => {
            input.addEventListener('input', (e) => window.triggerLiveFilter(e.target));
            input.addEventListener('change', (e) => window.triggerLiveFilter(e.target));
        });

        function applyLiveFilter() {
            const formData = new FormData(filterForm);

            // S'assurer que le champ de recherche inactif (Drames vs Acteurs) n'est pas envoyé s'il est vide
            // ou s'il appartient à l'autre vue pour éviter les conflits
            const view = formData.get('view') || 'dramas';
            const params = new URLSearchParams();

            for (const [key, value] of formData.entries()) {
                if (value === '') continue;

                // Si on est en vue acteurs, on ne garde que view, page, search_actor (renommé en search), exact_name et has_photo
                if (view === 'actors') {
                    if (key === 'search_actor') {
                        params.append('search', value);
                    } else if (['view', 'page', 'exact_name', 'has_photo'].includes(key)) {
                        params.append(key, value);
                    }
                } else {
                    // En vue drames
                    if (key === 'search_drama') {
                        params.append('search', value);
                    } else if (key !== 'search_actor') {
                        params.append(key, value);
                    }
                }
            }

            const url = `{{ route('kdrams.catalog') }}?${params.toString()}`;

            // Update loading state in Alpine if possible
            if (window.__alpine_data) {
                window.__alpine_data.isLoading = true;
            }

            // Show indicator
            const currentGrid = getGrid();
            if (currentGrid) currentGrid.style.opacity = '0.5';

            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                // Update loading state in Alpine
                if (window.__alpine_data) {
                    window.__alpine_data.isLoading = false;
                }

                // Hide indicator
                const updatedGrid = getGrid();
                if (updatedGrid) updatedGrid.style.opacity = '1';

                const noResultsContainer = document.getElementById('no-results-container');
                const statsContainer = document.getElementById('results-stats-container');
                const paginationContainer = document.getElementById('pagination-container');

                if (data.html && data.html.trim() !== '') {
                    // Masquer le message "aucun résultat" si présent
                    if (noResultsContainer) noResultsContainer.classList.add('hidden');

                    // Remplacer le contenu de la grille et l'afficher
                    if (updatedGrid) {
                        updatedGrid.innerHTML = data.html;
                        updatedGrid.classList.remove('hidden');
                    }

                    // Afficher les stats et la pagination
                    if (statsContainer) statsContainer.classList.remove('hidden');
                    if (paginationContainer) paginationContainer.classList.remove('hidden');

                    // Mettre à jour le compteur de résultats actuels
                    const currentCountDisplay = document.getElementById('current-count');
                    if (currentCountDisplay) {
                        currentCountDisplay.textContent = data.current_count;
                    }

                    // Mettre à jour le compteur de pages
                    const currentPageDisplay = document.getElementById('current-page-display');
                    if (currentPageDisplay) {
                        currentPageDisplay.textContent = data.current_page;
                    }

                    const totalPagesDisplay = document.getElementById('total-pages-display');
                    if (totalPagesDisplay) {
                        totalPagesDisplay.textContent = data.total_pages;
                    }

                    const totalResultsDisplay = document.getElementById('total-results-display');
                    if (totalResultsDisplay) {
                        totalResultsDisplay.textContent = data.total_results;
                    }

                    // Générer la pagination
                    generatePagination(data.current_page, data.total_pages, url);
                } else {
                    // Aucun résultat
                    const finalGrid = getGrid();
                    if (finalGrid) {
                        finalGrid.innerHTML = '';
                        finalGrid.classList.add('hidden');
                    }
                    if (statsContainer) statsContainer.classList.add('hidden');
                    if (paginationContainer) paginationContainer.classList.add('hidden');

                    // Afficher le message "aucun résultat"
                    if (noResultsContainer) {
                        noResultsContainer.classList.remove('hidden');

                        // Mettre à jour le message si possible
                        const noResultsMessage = document.getElementById('no-results-message');
                        if (noResultsMessage) {
                            const search = params.get('search');
                            const view = params.get('view') || 'dramas';
                            const exactName = params.get('exact_name') === '1';
                            const hasPhoto = params.get('has_photo') === '1';

                            let message = `{{ __('catalog.no_results_message') }}`;

                            if (view === 'actors') {
                                if (search) {
                                    message = `Aucun acteur trouvé pour: <strong>"${search}"</strong>`;
                                    if (exactName || hasPhoto) {
                                        let filters = [];
                                        if (exactName) filters.push('Nom exact');
                                        if (hasPhoto) filters.push('Avec photo');
                                        message += `<br><span class="text-sm italic text-slate-500 mt-2 block">Filtres actifs: ${filters.join(', ')}</span>`;
                                    }
                                }
                            } else {
                                if (search) {
                                    message = `{{ __('catalog.no_results_message') }}: <strong>"${search}"</strong>`;
                                }
                            }

                            noResultsMessage.innerHTML = message;
                        }
                    }
                }

                // Mettre à jour l'URL sans recharger
                window.history.replaceState({}, '', url);
            })
            .catch(error => {
                console.error('Erreur filtrage:', error);

                // Update loading state in Alpine
                if (window.__alpine_data) {
                    window.__alpine_data.isLoading = false;
                }

                // Hide indicator
                const errorGrid = getGrid();
                if (errorGrid) errorGrid.style.opacity = '1';
            });
        }

    // Modal Acteur
    const actorModal = document.getElementById('actorModal');

    window.openActorModal = function(actorId) {
        if (!actorModal) return;

        // Afficher la modale et le loader
        actorModal.classList.remove('hidden');
        document.body.style.overflow = 'hidden'; // Empêcher le scroll

        const loader = document.getElementById('actorModalLoader');
        const modalBody = document.getElementById('modalBody');

        if (loader) loader.classList.remove('hidden');
        if (modalBody) modalBody.innerHTML = '';

        // Fetch les détails de l'acteur
        fetch(`/kdrams/actor/${actorId}`)
            .then(response => response.text())
            .then(html => {
                if (loader) loader.classList.add('hidden');
                if (modalBody) modalBody.innerHTML = html;
            })
            .catch(error => {
                console.error('Erreur chargement acteur:', error);
                if (loader) loader.classList.add('hidden');
                if (modalBody) modalBody.innerHTML = '<div class="text-center py-10 text-red-500">Erreur lors du chargement des détails de l\'acteur.</div>';
            });
    };

    window.closeActorModal = function() {
        if (!actorModal) return;
        actorModal.classList.add('hidden');
        document.body.style.overflow = ''; // Réactiver le scroll
    };

    // Fermer au clic sur l'overlay
    if (actorModal) {
        actorModal.addEventListener('click', function(e) {
            if (e.target === actorModal) {
                closeActorModal();
            }
        });
    }

        function generatePagination(currentPage, totalPages, baseUrl) {
            const paginationContainer = document.getElementById('pagination-container');
            if (!paginationContainer) return;

            paginationContainer.innerHTML = '';

            if (totalPages <= 1) return;

            paginationContainer.className = 'flex flex-col gap-4 mt-16';

            // ===== VERSION DESKTOP (boutons numérotés) =====
            const desktopContainer = document.createElement('div');
            desktopContainer.className = 'hidden md:flex gap-2 flex-wrap justify-center items-center';

            // Bouton Précédent (Desktop)
            if (currentPage > 1) {
                const prevBtn = document.createElement('button');
                prevBtn.textContent = '← Précédent';
                prevBtn.className = 'btn-outline px-3 py-2 text-sm';
                prevBtn.onclick = () => goToPage(currentPage - 1);
                desktopContainer.appendChild(prevBtn);
            }

            // Calculer la plage de pages à afficher (max 10 boutons)
            let startPage = Math.max(1, currentPage - 5);
            let endPage = Math.min(totalPages, startPage + 9);
            if (endPage - startPage < 9) {
                startPage = Math.max(1, endPage - 9);
            }

            // Boutons numérotés
            if (startPage > 1) {
                const firstBtn = document.createElement('button');
                firstBtn.textContent = '1';
                firstBtn.className = 'btn-outline px-2 py-1 text-sm';
                firstBtn.onclick = () => goToPage(1);
                desktopContainer.appendChild(firstBtn);

                if (startPage > 2) {
                    const dots = document.createElement('span');
                    dots.textContent = '...';
                    dots.className = 'px-2 text-slate-400';
                    desktopContainer.appendChild(dots);
                }
            }

            for (let i = startPage; i <= endPage; i++) {
                const pageBtn = document.createElement('button');
                pageBtn.textContent = i;
                pageBtn.className = i === currentPage
                    ? 'btn-primary px-3 py-2 text-sm'
                    : 'btn-outline px-3 py-2 text-sm';
                pageBtn.onclick = () => goToPage(i);
                desktopContainer.appendChild(pageBtn);
            }

            if (endPage < totalPages) {
                if (endPage < totalPages - 1) {
                    const dots = document.createElement('span');
                    dots.textContent = '...';
                    dots.className = 'px-2 text-slate-400';
                    desktopContainer.appendChild(dots);
                }

                const lastBtn = document.createElement('button');
                lastBtn.textContent = totalPages;
                lastBtn.className = 'btn-outline px-2 py-1 text-sm';
                lastBtn.onclick = () => goToPage(totalPages);
                desktopContainer.appendChild(lastBtn);
            }

            // Bouton Suivant (Desktop)
            if (currentPage < totalPages) {
                const nextBtn = document.createElement('button');
                nextBtn.textContent = 'Suivant →';
                nextBtn.className = 'btn-outline px-3 py-2 text-sm';
                nextBtn.onclick = () => goToPage(currentPage + 1);
                desktopContainer.appendChild(nextBtn);
            }

            paginationContainer.appendChild(desktopContainer);

            // ===== VERSION MOBILE (Previous/Next seulement) =====
            const mobileContainer = document.createElement('div');
            mobileContainer.className = 'flex md:hidden gap-2 justify-between w-full';

            // Bouton Précédent (Mobile)
            if (currentPage > 1) {
                const prevBtn = document.createElement('button');
                prevBtn.textContent = '← Préc.';
                prevBtn.className = 'btn-outline px-3 py-2 text-xs flex-1';
                prevBtn.onclick = () => goToPage(currentPage - 1);
                mobileContainer.appendChild(prevBtn);
            } else {
                const spacer = document.createElement('div');
                spacer.className = 'flex-1';
                mobileContainer.appendChild(spacer);
            }

            // Affichage page (Mobile)
            const pageInfo = document.createElement('div');
            pageInfo.className = 'flex items-center justify-center px-2 text-xs text-slate-400';
            pageInfo.textContent = `${currentPage}/${totalPages}`;
            mobileContainer.appendChild(pageInfo);

            // Bouton Suivant (Mobile)
            if (currentPage < totalPages) {
                const nextBtn = document.createElement('button');
                nextBtn.textContent = 'Suiv. →';
                nextBtn.className = 'btn-outline px-3 py-2 text-xs flex-1';
                nextBtn.onclick = () => goToPage(currentPage + 1);
                mobileContainer.appendChild(nextBtn);
            } else {
                const spacer = document.createElement('div');
                spacer.className = 'flex-1';
                mobileContainer.appendChild(spacer);
            }

            paginationContainer.appendChild(mobileContainer);

            // Input "Aller à la page" - mettre à jour l'existant
            const existingInputContainer = paginationContainer.querySelector('.goto-page-container');
            if (existingInputContainer) {
                // Mettre à jour les valeurs existantes
                const input = existingInputContainer.querySelector('input');
                const infoSpan = existingInputContainer.querySelector('.pagination-info');
                if (input) {
                    input.max = totalPages;
                    input.value = currentPage;
                }
                if (infoSpan) {
                    infoSpan.textContent = `${currentPage}/${totalPages}`;
                }
            } else {
                // Créer le conteneur s'il n'existe pas
                const inputContainer = document.createElement('div');
                inputContainer.className = 'flex flex-col md:flex-row gap-2 items-center justify-center goto-page-container';

                const label = document.createElement('label');
                label.textContent = 'Aller à la page:';
                label.className = 'text-xs md:text-sm text-slate-300';
                inputContainer.appendChild(label);

                const input = document.createElement('input');
                input.type = 'number';
                input.min = '1';
                input.max = totalPages;
                input.value = currentPage;
                input.className = 'w-14 md:w-16 px-2 py-1 bg-slate-800 border border-slate-600 text-white rounded text-xs md:text-sm';
                input.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        const page = parseInt(input.value);
                        if (page >= 1 && page <= totalPages) {
                            goToPage(page);
                        }
                    }
                });
                inputContainer.appendChild(input);

                const goBtn = document.createElement('button');
                goBtn.textContent = 'Go';
                goBtn.className = 'btn-primary px-3 md:px-4 py-1 text-xs md:text-sm';
                goBtn.type = 'button';
                goBtn.onclick = () => {
                    const page = parseInt(input.value);
                    if (page >= 1 && page <= totalPages) {
                        goToPage(page);
                    }
                };
                inputContainer.appendChild(goBtn);

                const infoSpan = document.createElement('span');
                infoSpan.textContent = `${currentPage}/${totalPages}`;
                infoSpan.className = 'text-xs text-slate-400 ml-2 pagination-info';
                inputContainer.appendChild(infoSpan);

                paginationContainer.appendChild(inputContainer);
            }
        }

        window.goToPage = function(page) {
            // Update the hidden page input in the form
            const pageInput = document.getElementById('page-input');
            if (pageInput) {
                pageInput.value = page;
            }

            // Call applyLiveFilter which will use the updated page value
            applyLiveFilter();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        };

        // Générer la pagination initiale si on a des résultats
        if (initialTotalPages > 1) {
            generatePagination(initialCurrentPage, initialTotalPages, '');
        }
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
                if (btnText) btnText.textContent = '{{ __('errors.error_retry') }}';
            });
        });
    }
});
</script>
