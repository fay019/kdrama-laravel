@extends('layouts.app')

@section('title', __('home.page_title'))

@section('content')
<!-- Hero Section -->
<div class="relative overflow-hidden bg-gradient-to-b from-slate-900 via-slate-900 to-black dark:from-black dark:via-slate-950 dark:to-black">
    <!-- Animated Background Elements -->
    <div class="absolute inset-0 overflow-hidden">
        <div class="absolute top-0 left-1/2 w-64 h-64 bg-red-600 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-pulse" style="animation: blob 7s infinite; transform: translateX(-50%);"></div>
        <div class="absolute top-1/3 right-0 w-64 h-64 bg-purple-600 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-pulse" style="animation: blob 7s infinite 2s;"></div>
        <div class="absolute bottom-0 left-1/4 w-64 h-64 bg-pink-600 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-pulse" style="animation: blob 7s infinite 4s;"></div>
    </div>

    <!-- Content -->
    <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center py-16 sm:py-24">
        <div class="space-y-5 sm:space-y-6">
            <!-- Main Title -->
            <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black text-white leading-tight tracking-tight">
                {!! str_replace('{k-dramas}', '<span class="bg-gradient-to-r from-red-500 via-pink-500 to-purple-600 dark:from-red-400 dark:to-pink-500 bg-clip-text text-transparent">K-Dramas</span>', __('home.hero_title')) !!}
            </h1>

            <!-- Subtitle -->
            <p class="text-sm sm:text-lg text-slate-300 dark:text-slate-200 max-w-2xl mx-auto leading-relaxed font-medium">
                {{ __('home.hero_subtitle') }}
            </p>

            <!-- CTA Button -->
            <div class="flex justify-center pt-2">
                <a
                    href="{{ route('kdrams.catalog') }}"
                    class="group relative inline-flex items-center gap-2 px-6 sm:px-8 py-3 sm:py-4 bg-gradient-to-r from-red-600 to-red-700 dark:from-red-700 dark:to-red-800 hover:from-red-700 hover:to-red-800 dark:hover:from-red-800 dark:hover:to-red-900 text-white font-bold text-base sm:text-lg rounded-lg shadow-xl hover:shadow-red-600/50 dark:hover:shadow-red-700/30 transition-all duration-300 transform hover:scale-105"
                >
                    <span>{{ __('home.hero_cta') }}</span>
                    <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                    </svg>
                </a>
            </div>
        </div>
    </div>

    <style>
        @keyframes blob {
            0%, 100% { transform: translateX(-50%) translateY(0px); }
            33% { transform: translateX(-50%) translateY(-30px); }
            66% { transform: translateX(-50%) translateY(30px); }
        }
    </style>
</div>

<!-- Featured Section - Large Cards -->
@if(isset($featured) && count($featured) > 0)
    <section class="py-16 sm:py-24 px-4 sm:px-6 lg:px-8 bg-gradient-to-b from-black dark:from-slate-950 to-slate-900 dark:to-black">
        <div class="max-w-7xl mx-auto">
            <x-section :title="isset($isAdminList) && $isAdminList ? __('home.featured_title_admin') : __('home.featured_title')" icon="{{ isset($isAdminList) && $isAdminList ? '👤' : '🌟' }}">
                <!-- Featured Grid: 2/3/4 columns -->
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4 lg:gap-5">
                    @foreach($featured as $item)
                        <x-drama-card :item="$item" variant="featured" />
                    @endforeach
                </div>
            </x-section>
        </div>
    </section>
@else
    <!-- Empty State -->
    <section class="py-16 sm:py-24 px-4 sm:px-6 lg:px-8 bg-gradient-to-b from-black dark:from-slate-950 to-slate-900 dark:to-black">
        <div class="max-w-3xl mx-auto">
            <div class="rounded-xl border-2 border-dashed border-slate-700 dark:border-slate-700 bg-slate-900/50 dark:bg-slate-950/50 backdrop-blur-sm py-12 sm:py-16 px-6 text-center space-y-4">
                <div class="text-4xl sm:text-5xl opacity-40">🔑</div>
                <div>
                    <h3 class="text-lg sm:text-xl font-bold text-slate-100 dark:text-white mb-1">
                        {{ __('home.no_content') }}
                    </h3>
                    <p class="text-sm sm:text-base text-slate-400 dark:text-slate-300">
                        {{ __('home.configure_apis') }}
                    </p>
                </div>
                @auth
                    @if(auth()->user()->is_admin)
                        <a
                            href="{{ route('admin.settings.index') }}"
                            class="inline-flex items-center gap-2 px-5 py-2 bg-red-600 dark:bg-red-700 hover:bg-red-700 dark:hover:bg-red-800 text-white font-semibold text-sm rounded-lg transition-colors duration-200 mt-3"
                        >
                            <span>⚙️</span>
                            {{ __('home.setup_apis_btn') }}
                        </a>
                    @endif
                @endauth
            </div>
        </div>
    </section>
@endif

<!-- Top Actors Carousel -->
@if(isset($topActors) && count($topActors) > 0)
    <section class="border-t border-slate-800 dark:border-slate-800">
        <x-actor-carousel :actors="$topActors" />
    </section>
@endif

<!-- Newest Releases Section - Medium Cards -->
@if(isset($newest) && count($newest) > 0)
    <section class="py-16 sm:py-24 px-4 sm:px-6 lg:px-8 bg-gradient-to-b from-slate-900 dark:from-black to-slate-950 dark:to-slate-950 border-t border-slate-800 dark:border-slate-800">
        <div class="max-w-7xl mx-auto">
            <x-section title="{{ __('home.newest_releases_title') }}" icon="📺">
                <!-- Newest Grid: 2/3/4 columns -->
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4 lg:gap-5">
                    @foreach($newest as $item)
                        <x-drama-card :item="$item" variant="default" badge="NEW" />
                    @endforeach
                </div>
            </x-section>
        </div>
    </section>
@endif

<!-- Upcoming Releases Section - Compact Cards -->
@if(isset($upcoming) && count($upcoming) > 0)
    <section class="py-16 sm:py-24 px-4 sm:px-6 lg:px-8 bg-gradient-to-b from-slate-950 dark:from-slate-950 to-black dark:to-black border-t border-slate-800 dark:border-slate-800">
        <div class="max-w-7xl mx-auto">
            <x-section title="{{ __('home.upcoming_releases_title') }}" icon="📅">
                <!-- Upcoming Grid: 2/3/4 columns -->
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4 lg:gap-5">
                    @foreach($upcoming as $item)
                        <x-drama-card :item="$item" variant="compact" badge="SOON" />
                    @endforeach
                </div>
            </x-section>
        </div>
    </section>
@endif

<!-- CTA Section -->
<section class="py-14 sm:py-20 px-4 sm:px-6 lg:px-8 bg-gradient-to-r from-red-600/10 dark:from-red-900/10 via-purple-600/10 dark:via-purple-900/10 to-pink-600/10 dark:to-pink-900/10 border-t border-slate-800 dark:border-slate-700">
    <div class="max-w-3xl mx-auto text-center space-y-4">
        <h2 class="text-2xl sm:text-4xl font-bold text-slate-100 dark:text-white tracking-tight">
            Ready to explore K-Dramas?
        </h2>
        <p class="text-slate-400 dark:text-slate-300 text-sm sm:text-base">
            Browse our complete collection and start building your watchlist.
        </p>
        <a
            href="{{ route('kdrams.catalog') }}"
            class="inline-flex items-center gap-2 px-6 sm:px-8 py-3 sm:py-4 bg-gradient-to-r from-red-600 to-pink-600 dark:from-red-700 dark:to-pink-700 hover:from-red-700 hover:to-pink-700 dark:hover:from-red-800 dark:hover:to-pink-800 text-white font-bold text-sm sm:text-base rounded-lg shadow-xl hover:shadow-red-600/50 dark:hover:shadow-red-700/30 transition-all duration-300 transform hover:scale-105"
        >
            <span>Explore Now</span>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
            </svg>
        </a>
    </div>
</section>

<!-- Actor Modal -->
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
                    <p class="text-slate-400 font-medium">{{ __('common.loading') }}</p>
                </div>

                <!-- Content container -->
                <div id="modalBody">
                    <!-- Injection via JS -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
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
                if (modalBody) modalBody.innerHTML = '<div class="text-center py-10 text-red-500">{{ __('show.actor_error_loading') }}</div>';
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

    // Clavier: Échap pour fermer
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && !actorModal.classList.contains('hidden')) {
            closeActorModal();
        }
    });

    // Filtrer les drames par acteur
    window.filterByActor = function(actorId, actorName) {
        // Navigate to dramas view filtered by actor ID
        window.location.href = '{{ route("kdrams.catalog") }}?view=dramas&actor_id=' + actorId;
    };
</script>

@endsection
