@extends('layouts.app')

@section('title', __('catalog.page_title'))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <!-- Header -->
    @php
        $hasFilters = !empty($filters['search']) || !empty($filters['actor']) || !empty($filters['min_rating']) || !empty($filters['from_year']) || !empty($filters['to_year']) || !empty($filters['hide_watched']) || !empty($filters['hide_watchlist']);
    @endphp
    <div class="mb-8" x-data="{ showFilters: {{ $hasFilters ? 'true' : 'false' }} }">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-6 mb-8">
            <div>
                <h1 class="text-4xl font-bold mb-2">{{ __('catalog.title') }}</h1>
                <p class="text-slate-400">{{ __('catalog.subtitle') }}</p>
            </div>
        </div>

        <!-- FAB Filtre (Mobile uniquement) -->
        <button @click="showFilters = !showFilters"
                class="fixed bottom-6 right-6 lg:hidden z-20 w-14 h-14 rounded-full bg-slate-800/90 backdrop-blur-sm border border-slate-700 hover:border-slate-600 hover:bg-slate-700/90 shadow-lg flex items-center justify-center text-xl transition-all duration-200"
                :class="showFilters ? 'scale-110' : 'scale-100'">
            <span x-text="showFilters ? '✕' : '🔍'"></span>
        </button>

        <!-- Mobile backdrop pour le filtre overlay -->
        <div class="fixed inset-0 bg-black/50 z-30 lg:hidden transition-opacity"
             :class="{ 'opacity-0 pointer-events-none': !showFilters, 'opacity-100': showFilters }"
             @click="showFilters = false"></div>

        <!-- Layout: Sidebar (desktop) + Content (responsive) -->
        <div class="flex gap-6 lg:gap-8">
            <!-- Sidebar Filtres: Modal sur mobile, Static sur desktop -->
            <aside class="fixed top-0 right-0 h-screen w-64 sm:w-72 z-40 lg:static lg:h-auto flex-shrink-0 transition-transform duration-300 ease-out overflow-y-auto"
                   :class="{ 'translate-x-full lg:translate-x-0': !showFilters, 'translate-x-0': showFilters }">
                <form method="GET" action="{{ route('kdrams.catalog') }}"
                      class="card-dark p-3 border-slate-700/50 shadow-2xl sticky top-20">
                    <!-- Page number input (hidden) -->
                    <input type="hidden" name="page" id="page-input" value="{{ $current_page }}">

            <!-- Section 1: Basic Filters -->
            <div>
                <h3 class="text-sm font-bold text-slate-200 mb-2 flex items-center gap-2">
                    <span class="text-red-500">🔍</span> Filtres essentiels
                </h3>
                <div class="grid grid-cols-1 gap-4 mb-5">
                    <!-- Recherche -->
                    <div class="filter-form-group">
                        <label class="filter-form-label">{{ __('catalog.filter_search') }}</label>
                        <input type="text" name="search" placeholder="{{ __('catalog.filter_search_placeholder') }}" value="{{ $filters['search'] }}"
                               class="w-full">
                        <p class="filter-form-hint">{{ __('catalog.filter_search_hint') }}</p>
                    </div>

                    <!-- Acteur -->
                    <div class="filter-form-group">
                        <label class="filter-form-label">{{ __('catalog.filter_actor') }}</label>
                        <input type="hidden" name="actor_id" value="{{ $filters['actor_id'] ?? '' }}">
                        <input type="text" name="actor" placeholder="{{ __('catalog.filter_actor_placeholder') }}" value="{{ $filters['actor'] }}"
                               class="w-full">
                        <p class="filter-form-hint">{{ __('catalog.filter_actor_hint') }}</p>
                    </div>

                    <!-- Tri -->
                    <div class="filter-form-group">
                        <label class="filter-form-label">{{ __('catalog.filter_sort') }}</label>
                        <select name="sort" class="w-full">
                            <option value="popularity.desc" {{ $filters['sort'] === 'popularity.desc' ? 'selected' : '' }}>{{ __('catalog.filter_sort_popularity') }}</option>
                            <option value="vote_average.desc" {{ $filters['sort'] === 'vote_average.desc' ? 'selected' : '' }}>{{ __('catalog.filter_sort_rating') }}</option>
                            <option value="first_air_date.desc" {{ $filters['sort'] === 'first_air_date.desc' ? 'selected' : '' }}>{{ __('catalog.filter_sort_recent') }}</option>
                        </select>
                    </div>

                    <!-- Note minimale -->
                    <div class="filter-form-group">
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
            <div class="border-t border-slate-700/50 pt-4">
                <h3 class="text-sm font-bold text-slate-200 mb-2 flex items-center gap-2">
                    <span class="text-amber-500">⚙️</span> Filtres avancés
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
                                <span class="ml-3 text-xs font-medium text-slate-400">Actif</span>
                            </label>
                        </div>

                        <!-- Masquer En train de regarder -->
                        <div class="filter-form-group">
                            <label class="filter-form-label">{{ __('catalog.filter_watching') }}</label>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="hide_watching" value="1" {{ $filters['hide_watching'] ?? false ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-700 rounded-full peer peer-checked:bg-amber-500 peer-checked:after:translate-x-5 after:content-[''] after:absolute after:top-1/2 after:-translate-y-1/2 after:left-1 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all"></div>
                                <span class="ml-3 text-xs font-medium text-slate-400">Actif</span>
                            </label>
                        </div>

                        <!-- Masquer Watchlist -->
                        <div class="filter-form-group">
                            <label class="filter-form-label">{{ __('catalog.filter_watchlist') }}</label>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="hide_watchlist" value="1" {{ $filters['hide_watchlist'] ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-11 h-6 bg-slate-700 rounded-full peer peer-checked:bg-red-500 peer-checked:after:translate-x-5 after:content-[''] after:absolute after:top-1/2 after:-translate-y-1/2 after:left-1 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all"></div>
                                <span class="ml-3 text-xs font-medium text-slate-400">Actif</span>
                            </label>
                        </div>
                    @endauth
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
            <div class="flex-1">
                @if(count($kdrams) > 0)
                    <div class="mb-6 flex items-center justify-between">
                        <p class="text-slate-400 text-sm">
                            Page <span id="current-page-display" class="text-red-400 font-bold">{{ $current_page }}</span> / <span id="total-pages-display" class="text-red-400 font-bold">{{ $total_pages }}</span>
                            — Affichage de <span id="current-count" class="text-red-400 font-bold">{{ count($kdrams) }}</span> résultats sur <span id="total-results-display" class="text-red-400 font-bold">{{ $total_results }}</span> total
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

                    <!-- Pagination Simple -->
                    <div id="pagination-container" class="flex justify-center items-center gap-4 mt-16"></div>
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
            <!-- Close main content div -->
        </div>
        <!-- Close flex layout div -->
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

    // Fonction pour réinitialiser les filtres
    window.resetFiltersAndReload = function() {
        const filterForm = document.querySelector('form[method="GET"]');
        if (filterForm) {
            filterForm.reset();
        }
        window.location.href = '{{ route("kdrams.catalog") }}';
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

    if (filterForm) {
        const allInputs = filterForm.querySelectorAll('input[type="text"], input[type="number"], select, input[type="checkbox"]');

        allInputs.forEach(input => {
            input.addEventListener('input', (e) => triggerLiveFilter(e.target));
            input.addEventListener('change', (e) => triggerLiveFilter(e.target));
        });

        function triggerLiveFilter(inputElement) {
            // Si c'est un filtre de recherche, reset la page à 1
            const searchFilters = ['search', 'actor', 'from_year', 'to_year', 'min_rating'];
            if (inputElement && searchFilters.includes(inputElement.name)) {
                const pageInput = document.getElementById('page-input');
                if (pageInput) {
                    pageInput.value = 1;
                }
            }

            clearTimeout(filterTimeout);
            filterTimeout = setTimeout(applyLiveFilter, 300);
        }

        function applyLiveFilter() {
            const formData = new FormData(filterForm);
            const params = new URLSearchParams(formData);

            // Nettoyer les paramètres vides
            for (const [key, value] of params.entries()) {
                if (value === '') {
                    params.delete(key);
                }
            }

            const url = `{{ route('kdrams.catalog') }}?${params.toString()}`;

            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.html) {
                    // Remplacer le contenu de la grille
                    grid.innerHTML = data.html;

                    // Mettre à jour le compteur de résultats actuels
                    const countSpan = document.getElementById('current-count');
                    if (countSpan) {
                        countSpan.textContent = grid.querySelectorAll('.content-card').length;
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

                    // Mettre à jour l'URL sans recharger
                    window.history.replaceState({}, '', url);
                }
            })
            .catch(error => console.error('Erreur filtrage:', error));
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
                if (btnText) btnText.textContent = '❌ Erreur, réessayez';
            });
        });
    }
});
</script>
