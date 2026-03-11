<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-100 leading-tight">
            👤 Dashboard - {{ auth()->user()->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
                <div class="card p-5 hover:border-red-500/50 transition">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-slate-400 text-sm font-medium">{{ __('dashboard.stat_to_watch') }}</p>
                            <p class="text-3xl font-bold text-red-400 mt-1">{{ $stats['total_watchlist'] }}</p>
                        </div>
                        <div class="text-5xl opacity-15">📺</div>
                    </div>
                </div>
                <div class="card p-5 hover:border-amber-500/50 transition">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-slate-400 text-sm font-medium">{{ __('dashboard.stat_watching') }}</p>
                            <p class="text-3xl font-bold text-amber-400 mt-1">{{ $stats['total_watching'] }}</p>
                        </div>
                        <div class="text-5xl opacity-15">🎬</div>
                    </div>
                </div>
                <div class="card p-5 hover:border-green-500/50 transition">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-slate-400 text-sm font-medium">{{ __('dashboard.stat_watched') }}</p>
                            <p class="text-3xl font-bold text-green-400 mt-1">{{ $stats['total_watched'] }}</p>
                        </div>
                        <div class="text-5xl opacity-15">✅</div>
                    </div>
                </div>
                <div class="card p-5 hover:border-purple-500/50 transition">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-slate-400 text-sm font-medium">{{ __('dashboard.stat_rated') }}</p>
                            <p class="text-3xl font-bold text-purple-400 mt-1">{{ $stats['total_rated'] }}</p>
                        </div>
                        <div class="text-5xl opacity-15">⭐</div>
                    </div>
                </div>
                <div class="card p-5 hover:border-yellow-500/50 transition">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-slate-400 text-sm font-medium">{{ __('dashboard.stat_average') }}</p>
                            <p class="text-3xl font-bold text-yellow-400 mt-1">{{ $stats['avg_rating'] }}{{ __('dashboard.stat_average_suffix') }}</p>
                        </div>
                        <div class="text-5xl opacity-15">📊</div>
                    </div>
                </div>
            </div>

            <!-- Watchlist Section -->
            <div class="mb-8">
                <h3 class="font-semibold text-xl text-slate-100 mb-4 flex items-center gap-2">
                    {{ __('dashboard.section_to_watch') }} ({{ $watchlist->count() }})
                </h3>
                @if($watchlist->isEmpty())
                    <div class="card p-8 text-center">
                        <p class="text-slate-400 mb-4">{{ __('dashboard.empty_watchlist') }}</p>
                        <a href="{{ route('kdrams.catalog') }}" class="btn-primary inline-block">
                            {{ __('dashboard.see_catalog') }}
                        </a>
                    </div>
                @else
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
                        @foreach($watchlist as $item)
                            <div class="group relative rounded-lg overflow-hidden shadow-lg hover:shadow-xl transition">
                                <!-- Poster Image -->
                                @if($item->kdrama->poster_path)
                                    <img src="https://image.tmdb.org/t/p/w342{{ $item->kdrama->poster_path }}"
                                         alt="{{ $item->kdrama->name }}"
                                         class="w-full h-auto object-cover group-hover:brightness-50 transition duration-300">
                                @else
                                    <div class="w-full aspect-[2/3] bg-slate-700 flex items-center justify-center">
                                        <span class="text-4xl">📺</span>
                                    </div>
                                @endif

                                <!-- Hover Info -->
                                <div class="absolute inset-0 bg-black/80 opacity-0 group-hover:opacity-100 transition duration-300 flex flex-col justify-between p-3">
                                    <div>
                                        <h4 class="text-white font-bold text-sm line-clamp-2 mb-2">
                                            {{ $item->kdrama->name ?? $item->kdrama->en_name }}
                                        </h4>
                                        @if($item->kdrama->first_air_date)
                                            <p class="text-slate-300 text-xs mb-2">
                                                {{ $item->kdrama->first_air_date->format('Y') }}
                                            </p>
                                        @endif

                                        @if($item->rating)
                                            <div class="flex items-center gap-1 text-xs">
                                                @if($item->rating == 1)
                                                    <span class="text-sm">👎</span>
                                                    <span class="text-red-400">{{ __('dashboard.rating_bad') }}</span>
                                                @elseif($item->rating == 2)
                                                    <span class="text-sm">👍</span>
                                                    <span class="text-green-400">{{ __('dashboard.rating_good') }}</span>
                                                @elseif($item->rating == 3)
                                                    <span class="text-sm">👍👍</span>
                                                    <span class="text-purple-400">{{ __('dashboard.rating_very_good') }}</span>
                                                @endif
                                            </div>
                                        @endif
                                    </div>

                                    <div class="flex gap-2">
                                        <a href="{{ route('kdrams.show', $item->tmdb_id) }}"
                                           class="flex-1 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold py-2 px-2 rounded text-center transition">
                                            {{ __('dashboard.btn_view') }}
                                        </a>
                                        <form action="{{ route('api.watchlist.toggle', $item->tmdb_id) }}" method="POST" class="flex-1">
                                            @csrf
                                            <button type="submit" class="w-full bg-slate-700 hover:bg-red-600 text-white text-xs font-semibold py-2 px-2 rounded transition">
                                                ✕
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Watching Section -->
            <div class="mb-8">
                <h3 class="font-semibold text-xl text-slate-100 mb-4 flex items-center gap-2">
                    {{ __('dashboard.section_watching') }} ({{ $watching->count() }})
                </h3>
                @if($watching->isEmpty())
                    <div class="card p-8 text-center">
                        <p class="text-slate-400 mb-4">{{ __('dashboard.empty_watchlist') }}</p>
                        <a href="{{ route('kdrams.catalog') }}" class="btn-primary inline-block">
                            {{ __('dashboard.see_catalog') }}
                        </a>
                    </div>
                @else
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
                        @foreach($watching as $item)
                            <div class="group relative rounded-lg overflow-hidden shadow-lg hover:shadow-xl transition ring-2 ring-amber-500/50">
                                <!-- Poster Image -->
                                @if($item->kdrama->poster_path)
                                    <img src="https://image.tmdb.org/t/p/w342{{ $item->kdrama->poster_path }}"
                                         alt="{{ $item->kdrama->name }}"
                                         class="w-full h-auto object-cover group-hover:brightness-50 transition duration-300">
                                @else
                                    <div class="w-full aspect-[2/3] bg-slate-700 flex items-center justify-center">
                                        <span class="text-4xl">📺</span>
                                    </div>
                                @endif

                                <!-- Playing Badge -->
                                <div class="absolute top-2 right-2 bg-amber-500 text-white rounded-full w-7 h-7 flex items-center justify-center text-sm font-bold">
                                    🎬
                                </div>

                                <!-- Hover Info -->
                                <div class="absolute inset-0 bg-black/80 opacity-0 group-hover:opacity-100 transition duration-300 flex flex-col justify-between p-3">
                                    <div>
                                        <h4 class="text-white font-bold text-sm line-clamp-2 mb-2">
                                            {{ $item->kdrama->name ?? $item->kdrama->en_name }}
                                        </h4>
                                        @if($item->kdrama->first_air_date)
                                            <p class="text-slate-300 text-xs mb-2">
                                                {{ $item->kdrama->first_air_date->format('Y') }}
                                            </p>
                                        @endif
                                    </div>

                                    <div class="flex gap-2">
                                        <a href="{{ route('kdrams.show', $item->tmdb_id) }}"
                                           class="flex-1 bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold py-2 px-2 rounded text-center transition">
                                            {{ __('dashboard.btn_view') }}
                                        </a>
                                        <form method="DELETE" class="flex-1" onsubmit="removeWatching(event, this)">
                                            @csrf
                                            <button type="submit" class="w-full bg-slate-700 hover:bg-red-600 text-white text-xs font-semibold py-2 px-2 rounded transition">
                                                ✕
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Watched Section -->
            <div class="mb-8">
                <h3 class="font-semibold text-xl text-slate-100 mb-4 flex items-center gap-2">
                    {{ __('dashboard.section_watched') }} ({{ $watched->count() }})
                </h3>
                @if($watched->isEmpty())
                    <div class="card p-8 text-center">
                        <p class="text-slate-400 mb-4">{{ __('dashboard.empty_watched') }}</p>
                        <a href="{{ route('kdrams.catalog') }}" class="btn-primary inline-block">
                            {{ __('dashboard.btn_discover') }}
                        </a>
                    </div>
                @else
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4">
                        @foreach($watched as $item)
                            <div class="group relative rounded-lg overflow-hidden shadow-lg hover:shadow-xl transition ring-2 ring-green-500/50">
                                <!-- Poster Image -->
                                @if($item->kdrama->poster_path)
                                    <img src="https://image.tmdb.org/t/p/w342{{ $item->kdrama->poster_path }}"
                                         alt="{{ $item->kdrama->name }}"
                                         class="w-full h-auto object-cover group-hover:brightness-50 transition duration-300">
                                @else
                                    <div class="w-full aspect-[2/3] bg-slate-700 flex items-center justify-center">
                                        <span class="text-4xl">📺</span>
                                    </div>
                                @endif

                                <!-- Checkmark Badge -->
                                <div class="absolute top-2 right-2 bg-green-500 text-white rounded-full w-7 h-7 flex items-center justify-center text-sm font-bold">
                                    ✓
                                </div>

                                <!-- Hover Info -->
                                <div class="absolute inset-0 bg-black/80 opacity-0 group-hover:opacity-100 transition duration-300 flex flex-col justify-between p-3">
                                    <div>
                                        <h4 class="text-white font-bold text-sm line-clamp-2 mb-2">
                                            {{ $item->kdrama->name ?? $item->kdrama->en_name }}
                                        </h4>
                                        @if($item->kdrama->first_air_date)
                                            <p class="text-slate-300 text-xs mb-2">
                                                {{ $item->kdrama->first_air_date->format('Y') }}
                                            </p>
                                        @endif

                                        @if($item->rating)
                                            <div class="flex items-center gap-1 text-xs">
                                                @if($item->rating == 1)
                                                    <span class="text-sm">👎</span>
                                                    <span class="text-red-400">{{ __('dashboard.rating_bad') }}</span>
                                                @elseif($item->rating == 2)
                                                    <span class="text-sm">👍</span>
                                                    <span class="text-green-400">{{ __('dashboard.rating_good') }}</span>
                                                @elseif($item->rating == 3)
                                                    <span class="text-sm">👍👍</span>
                                                    <span class="text-purple-400">{{ __('dashboard.rating_very_good') }}</span>
                                                @endif
                                            </div>
                                        @endif
                                    </div>

                                    <div class="flex gap-2">
                                        <a href="{{ route('kdrams.show', $item->tmdb_id) }}"
                                           class="flex-1 bg-green-600 hover:bg-green-700 text-white text-xs font-semibold py-2 px-2 rounded text-center transition">
                                            {{ __('dashboard.btn_view') }}
                                        </a>
                                        <button type="button" class="remove-watched flex-1 bg-slate-700 hover:bg-red-600 text-white text-xs font-semibold py-2 px-2 rounded transition"
                                                data-tmdb-id="{{ $item->tmdb_id }}">
                                            ✕
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Ratings -->
            @if(!$rated->isEmpty())
            <div class="mt-8 card p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="font-semibold text-xl text-slate-100 flex items-center gap-2">
                        {{ __('dashboard.section_ratings') }} ({{ $stats['total_rated'] }})
                    </h3>
                    <a href="{{ route('kdrams.catalog') }}" class="text-sm text-slate-400 hover:text-slate-300 transition">
                        {{ __('dashboard.see_catalog') }}
                    </a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($rated as $item)
                        <div class="border border-slate-600 bg-slate-700/30 rounded-lg p-4 hover:border-purple-500/50 transition">
                            <!-- Titre du kdrama -->
                            <a href="{{ route('kdrams.show', $item->tmdb_id) }}"
                               class="text-slate-100 hover:text-purple-400 font-semibold text-sm block mb-3 line-clamp-2 transition">
                                {{ $item->kdrama->name ?? $item->kdrama->en_name ?? 'K-Drama #'.$item->tmdb_id }}
                            </a>

                            <!-- Rating stars avec couleur -->
                            <div class="flex items-center gap-2 mb-3">
                                @if($item->rating == 1)
                                    <span class="text-lg">👎</span>
                                    <span class="text-red-400 font-semibold text-sm">{{ __('dashboard.rating_bad') }}</span>
                                @elseif($item->rating == 2)
                                    <span class="text-lg">👍</span>
                                    <span class="text-green-400 font-semibold text-sm">{{ __('dashboard.rating_good') }}</span>
                                @elseif($item->rating == 3)
                                    <span class="text-lg">👍👍</span>
                                    <span class="text-purple-400 font-semibold text-sm">{{ __('dashboard.rating_very_good') }}</span>
                                @endif
                            </div>

                            <!-- Date -->
                            <p class="text-slate-500 text-xs">
                                {{ $item->updated_at->diffForHumans() }}
                            </p>
                        </div>
                    @endforeach
                </div>
            </div>
            @else
            <div class="mt-8 card p-8 text-center">
                <p class="text-slate-400 mb-4">{{ __('dashboard.empty_ratings') }}</p>
                <a href="{{ route('kdrams.catalog') }}" class="btn-primary inline-block">
                    {{ __('dashboard.discover_and_rate') }}
                </a>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const removeWatchedButtons = document.querySelectorAll('.remove-watched');

    removeWatchedButtons.forEach(button => {
        button.addEventListener('click', async function(e) {
            e.preventDefault();
            const tmdbId = this.dataset.tmdbId;
            const row = this.closest('.flex');

            try {
                this.disabled = true;
                this.classList.add('opacity-50');

                const response = await fetch(`/api/watched/toggle/${tmdbId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                    },
                });

                if (response.ok) {
                    // Animate removal
                    row.style.transition = 'opacity 0.3s ease';
                    row.style.opacity = '0';
                    showToast('❌ Supprimé des regardés', 'success');
                    setTimeout(() => {
                        row.remove();
                    }, 300);
                } else {
                    showToast('Erreur lors de la suppression', 'error');
                    this.disabled = false;
                    this.classList.remove('opacity-50');
                }
            } catch (error) {
                console.error('Erreur:', error);
                showToast('Erreur lors de la suppression', 'error');
                this.disabled = false;
                this.classList.remove('opacity-50');
            }
        });
    });

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
