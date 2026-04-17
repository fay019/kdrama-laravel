@extends('layouts.app')

@section('title', __('watchlist.page_title'))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <!-- SECTION 1: STATS CARDS -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2 sm:gap-4 mb-12">
        <div class="rounded-lg border border-slate-800 bg-slate-900/50 p-3 sm:p-5 hover:border-red-500/50 transition">
            <p class="text-slate-400 text-xs sm:text-sm font-medium">{{ __('dashboard.stat_to_watch') }}</p>
            <p class="text-xl sm:text-3xl font-bold text-red-400 mt-1">{{ count($toWatch) }}</p>
        </div>
        <div class="rounded-lg border border-slate-800 bg-slate-900/50 p-3 sm:p-5 hover:border-amber-500/50 transition">
            <p class="text-slate-400 text-xs sm:text-sm font-medium">{{ __('dashboard.stat_watching') }}</p>
            <p class="text-xl sm:text-3xl font-bold text-amber-400 mt-1">{{ count($watching) }}</p>
        </div>
        <div class="rounded-lg border border-slate-800 bg-slate-900/50 p-3 sm:p-5 hover:border-green-500/50 transition">
            <p class="text-slate-400 text-xs sm:text-sm font-medium">{{ __('dashboard.stat_watched') }}</p>
            <p class="text-xl sm:text-3xl font-bold text-green-400 mt-1">{{ count($watched) }}</p>
        </div>
        <div class="rounded-lg border border-slate-800 bg-slate-900/50 p-3 sm:p-5 hover:border-purple-500/50 transition">
            <p class="text-slate-400 text-xs sm:text-sm font-medium">{{ __('dashboard.stat_rated') }}</p>
            <p class="text-xl sm:text-3xl font-bold text-purple-400 mt-1">{{ $items->filter(fn($i) => $i->rating !== null)->count() }}</p>
        </div>
        @php
            $ratedItemsFiltered = $items->filter(fn($i) => $i->rating !== null);
            $avgRating = $ratedItemsFiltered->count() > 0
                ? number_format($ratedItemsFiltered->sum('rating') / $ratedItemsFiltered->count(), 1)
                : '0.0';
        @endphp
        <div class="rounded-lg border border-slate-800 bg-slate-900/50 p-3 sm:p-5 hover:border-yellow-500/50 transition">
            <p class="text-slate-400 text-xs sm:text-sm font-medium">{{ __('dashboard.stat_average') }}</p>
            <p class="text-xl sm:text-3xl font-bold text-yellow-400 mt-1">{{ $avgRating }}<span class="text-sm">/3</span></p>
        </div>
    </div>

    <!-- SECTION 2: TITLE & EXPORT -->
    <div class="mb-8 flex justify-between items-start">
        <div>
            <h1 class="text-4xl font-bold mb-2">{{ __('watchlist.title') }}</h1>
            <p class="text-slate-400">{{ count($items) }} {{ count($items) != 1 ? __('watchlist.js.drama_plural') : __('watchlist.js.drama_singular') }}</p>
        </div>
        @if(count($items) > 0)
            <button onclick="openExportModal()" class="flex items-center gap-1 px-2 sm:px-6 py-1 sm:py-3 text-xs sm:text-base bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-bold rounded-lg transition shadow-lg">
                {{ __('watchlist.export_button') }}
            </button>
        @endif
    </div>

    @if(count($items) > 0)
        <!-- SECTION 3: FILTER TABS -->
        <div class="mb-8 flex gap-2 flex-wrap">
            <button class="filter-tab active px-4 py-2 text-sm font-semibold rounded-lg transition" data-filter="all" data-count="{{ count($items) }}">
                {{ __('watchlist.filter_all') }} (<span class="tab-count">{{ count($items) }}</span>)
            </button>
            <button class="filter-tab px-4 py-2 text-sm font-semibold rounded-lg transition" data-filter="towatch" data-count="{{ count($toWatch) }}">
                {{ __('watchlist.filter_to_watch') }} (<span class="tab-count">{{ count($toWatch) }}</span>)
            </button>
            <button class="filter-tab px-4 py-2 text-sm font-semibold rounded-lg transition" data-filter="watching" data-count="{{ count($watching) }}">
                {{ __('watchlist.filter_watching') }} (<span class="tab-count">{{ count($watching) }}</span>)
            </button>
            <button class="filter-tab px-4 py-2 text-sm font-semibold rounded-lg transition" data-filter="watched" data-count="{{ count($watched) }}">
                {{ __('watchlist.filter_watched') }} (<span class="tab-count">{{ count($watched) }}</span>)
            </button>
        </div>

        <!-- SECTION 4: UNIFIED GRID -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4 mb-12" id="watchlistGrid">
            @foreach($items as $item)
                <div class="group relative overflow-hidden rounded-xl h-52 sm:h-60 cursor-pointer transition-transform duration-300 hover:scale-105 watchlist-item"
                     data-watched="{{ $item->is_watched ? 'true' : 'false' }}"
                     data-watching="{{ $item->is_watching ? 'true' : 'false' }}"
                     data-in-watchlist="{{ $item->is_in_watchlist ? 'true' : 'false' }}"
                     data-content-id="{{ $item->tmdb_id }}"
                     data-title="{{ $item->kdrama->name ?? 'Unknown' }}">

                    <!-- Status Badge -->
                    <div class="absolute top-2 left-2 text-white px-3 py-1 rounded-full text-xs font-semibold z-10 status-badge pointer-events-none">
                        @if($item->is_watched)
                            <span class="bg-green-600">{{ __('watchlist.status_watched') }}</span>
                        @elseif($item->is_watching)
                            <span class="bg-amber-500">{{ __('watchlist.status_watching') }}</span>
                        @else
                            <span class="bg-red-600">{{ __('watchlist.status_to_watch') }}</span>
                        @endif
                    </div>

                    <!-- Poster Link -->
                    <a href="{{ route('kdrams.show', $item->tmdb_id) }}" class="absolute inset-0 z-5 block"></a>
                    <div class="absolute inset-0 z-0">
                        @if($item->kdrama && $item->kdrama->poster_path)
                            <img src="https://image.tmdb.org/t/p/w500{{ $item->kdrama->poster_path }}"
                                 alt="{{ $item->kdrama->name }}"
                                 class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                                 loading="lazy"
                                 decoding="async">
                        @else
                            <div class="w-full h-full bg-gradient-to-br from-slate-700 via-slate-800 to-slate-900 flex items-center justify-center">
                                <div class="text-4xl">🎬</div>
                            </div>
                        @endif

                        <!-- Overlay -->
                        <div class="absolute inset-0 bg-gradient-to-t from-black via-black/40 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                    </div>

                    <!-- Content Overlay (Title & Date) -->
                    <div class="absolute inset-0 flex flex-col justify-between p-4 transform translate-y-2 group-hover:translate-y-0 transition-transform duration-300 opacity-0 group-hover:opacity-100 z-30 pointer-events-none">
                        <div></div>
                        <div class="space-y-2">
                            <h3 class="text-white font-semibold leading-tight line-clamp-2 text-xs sm:text-sm">
                                {{ $item->kdrama->name ?? $item->kdrama->en_name ?? 'Unknown' }}
                            </h3>
                            @if($item->kdrama && $item->kdrama->first_air_date)
                                <p class="text-slate-200 text-xs">
                                    📅 {{ \Carbon\Carbon::parse($item->kdrama->first_air_date)->format(__('home.date_format')) }}
                                </p>
                            @endif
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex gap-2 items-center justify-center px-3 py-3 z-20 backdrop-blur-sm pointer-events-none group-hover:pointer-events-auto">
                        <button type="button" class="px-2.5 py-2 rounded-lg text-white text-base transition toggle-watchlist-btn {{ $item->is_in_watchlist ? 'bg-red-600 cursor-default' : 'bg-white/20 hover:bg-red-500/80 cursor-pointer' }}" data-content-id="{{ $item->tmdb_id }}" title="{{ __('watchlist.title_watchlist_toggle') }}" {{ $item->is_in_watchlist ? 'disabled' : '' }}>
                            📺
                        </button>
                        <button type="button" class="px-2.5 py-2 rounded-lg text-white text-base transition toggle-watching-btn {{ $item->is_watching ? 'bg-amber-600 cursor-default' : 'bg-white/20 hover:bg-amber-500/80 cursor-pointer' }}" data-content-id="{{ $item->tmdb_id }}" title="{{ __('watchlist.title_watching_toggle') }}" {{ $item->is_watching ? 'disabled' : '' }}>
                            🎬
                        </button>
                        <button type="button" class="px-2.5 py-2 rounded-lg text-white text-base transition toggle-watched-btn {{ $item->is_watched ? 'bg-green-600 cursor-default' : 'bg-white/20 hover:bg-green-500/80 cursor-pointer' }}" data-content-id="{{ $item->tmdb_id }}" title="{{ __('watchlist.title_watched_toggle') }}" {{ $item->is_watched ? 'disabled' : '' }}>
                            ✅
                        </button>
                        <button type="button" class="px-2.5 py-2 rounded-lg bg-white/20 hover:bg-red-600/80 text-white text-base transition delete-btn cursor-pointer" data-content-id="{{ $item->tmdb_id }}" title="{{ __('watchlist.title_delete') }}">
                            🗑️
                        </button>
                    </div>

                    <!-- Rating (for watched items) -->
                    @if($item->is_watched)
                        <div class="absolute bottom-2 right-2 z-10 rating-display-container" data-current-rating="{{ $item->rating }}" data-content-id="{{ $item->tmdb_id }}">
                            @if($item->rating)
                                <span class="rating-emoji text-lg bg-black/30 backdrop-blur-sm px-1.5 py-0.5 rounded-lg inline-block">
                                    {{ $item->rating === 1 ? '👎' : ($item->rating === 2 ? '👍' : '👍👍') }}
                                </span>
                            @endif
                            <div class="rating-menu hidden absolute bottom-8 right-0 bg-slate-900/90 backdrop-blur-md border border-slate-700 rounded-lg p-1.5 flex gap-1 shadow-lg" style="z-index: 20;">
                                <button type="button" class="watchlist-rating-btn px-2 py-1.5 rounded text-xs font-bold bg-slate-700 hover:bg-red-600 transition" data-rating="1" title="{{ __('watchlist.title_rating_bad') }}">👎</button>
                                <button type="button" class="watchlist-rating-btn px-2 py-1.5 rounded text-xs font-bold bg-slate-700 hover:bg-green-600 transition" data-rating="2" title="{{ __('watchlist.title_rating_good') }}">👍</button>
                                <button type="button" class="watchlist-rating-btn px-2 py-1.5 rounded text-xs font-bold bg-slate-700 hover:bg-purple-600 transition" data-rating="3" title="{{ __('watchlist.title_rating_very_good') }}">👍👍</button>
                                <button type="button" class="watchlist-rating-btn px-2 py-1.5 rounded text-xs font-bold bg-slate-700 hover:bg-red-500 transition" data-rating="null" title="{{ __('watchlist.title_rating_remove') }}">✕</button>
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        <!-- SECTION 5: RATINGS SECTION -->
        @php
            $ratedItems = $items->filter(fn($i) => $i->rating !== null);
        @endphp
        @if(count($ratedItems) > 0)
            <div class="mt-12 border border-slate-700 bg-slate-900/30 rounded-lg p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="font-semibold text-xl text-slate-100 flex items-center gap-2">
                        ⭐ {{ __('dashboard.section_ratings') }} ({{ count($ratedItems) }})
                    </h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($ratedItems as $item)
                        <div class="border border-slate-600 bg-slate-700/30 rounded-lg p-4 hover:border-purple-500/50 transition">
                            <a href="{{ route('kdrams.show', $item->tmdb_id) }}"
                               class="text-slate-100 hover:text-purple-400 font-semibold text-sm block mb-3 line-clamp-2 transition">
                                {{ $item->kdrama->name ?? $item->kdrama->en_name ?? 'K-Drama #'.$item->tmdb_id }}
                            </a>

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

                            <p class="text-slate-500 text-xs">
                                {{ $item->updated_at->diffForHumans() }}
                            </p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @else
        <!-- EMPTY STATE -->
        <div class="rounded-xl border-2 border-dashed border-slate-600/50 bg-slate-900/30 backdrop-blur-sm py-24 px-6 text-center">
            <div class="text-7xl mb-6">🍿</div>
            <h2 class="text-3xl font-bold text-white mb-4">{{ __('watchlist.empty_title') }}</h2>
            <p class="text-slate-400 mb-10 max-w-md mx-auto text-base">{{ __('watchlist.empty_description') }}</p>
            <a href="{{ route('kdrams.catalog') }}" class="inline-flex items-center gap-2 px-8 py-3 bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white font-bold rounded-lg shadow-lg hover:shadow-red-600/40 transition-all duration-300 transform hover:scale-105">
                {{ __('watchlist.empty_cta') }}
            </a>
        </div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterTabs = document.querySelectorAll('.filter-tab');
    const watchlistItems = document.querySelectorAll('.watchlist-item');

    filterTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const filter = this.dataset.filter;
            filterTabs.forEach(t => {
                t.classList.remove('active', 'bg-red-600', 'text-white');
                t.classList.add('bg-slate-700', 'text-slate-300');
            });
            this.classList.remove('bg-slate-700', 'text-slate-300');
            this.classList.add('active', 'bg-red-600', 'text-white');

            watchlistItems.forEach(item => {
                const isWatched = item.dataset.watched === 'true';
                const isWatching = item.dataset.watching === 'true';
                const isInWatchlist = item.dataset.inWatchlist === 'true';

                let show = false;
                if (filter === 'all') show = true;
                else if (filter === 'towatch' && isInWatchlist && !isWatched && !isWatching) show = true;
                else if (filter === 'watching' && isWatching) show = true;
                else if (filter === 'watched' && isWatched) show = true;

                item.style.display = show ? '' : 'none';
            });
        });
    });

    document.querySelectorAll('.toggle-watchlist-btn').forEach(btn => {
        btn.addEventListener('click', async function(e) {
            e.preventDefault();
            e.stopPropagation();
            if (this.disabled) return;
            const contentId = this.dataset.contentId;
            const item = this.closest('.watchlist-item');

            try {
                const response = await fetch(`/api/watchlist/toggle/${contentId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/json',
                    },
                });

                if (response.ok) {
                    const data = await response.json();
                    item.dataset.inWatchlist = data.inWatchlist ? 'true' : 'false';
                    item.dataset.watching = 'false';
                    item.dataset.watched = data.inWatched ? 'true' : 'false';
                    updateBadge(item);
                    updateButtonStyles(item);
                    showToast(data.message, 'success');
                }
            } catch (error) {
                console.error('Error:', error);
                showToast(window.i18n?.watchlist_error_modify || 'Error', 'error');
            }
        });
    });

    document.querySelectorAll('.toggle-watching-btn').forEach(btn => {
        btn.addEventListener('click', async function(e) {
            e.preventDefault();
            e.stopPropagation();
            if (this.disabled) return;
            const contentId = this.dataset.contentId;
            const item = this.closest('.watchlist-item');

            try {
                const response = await fetch(`/api/watching/toggle/${contentId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/json',
                    },
                });

                if (response.ok) {
                    const data = await response.json();
                    item.dataset.watching = data.inWatching ? 'true' : 'false';
                    item.dataset.inWatchlist = data.inWatchlist || false ? 'true' : 'false';
                    item.dataset.watched = data.inWatched || false ? 'true' : 'false';
                    updateBadge(item);
                    updateButtonStyles(item);
                    showToast(data.message, 'success');
                }
            } catch (error) {
                console.error('Error:', error);
                showToast(window.i18n?.watchlist_error_modify || 'Error', 'error');
            }
        });
    });

    document.querySelectorAll('.toggle-watched-btn').forEach(btn => {
        btn.addEventListener('click', async function(e) {
            e.preventDefault();
            e.stopPropagation();
            if (this.disabled) return;
            const contentId = this.dataset.contentId;
            const item = this.closest('.watchlist-item');

            try {
                const response = await fetch(`/api/watched/toggle/${contentId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/json',
                    },
                });

                if (response.ok) {
                    const data = await response.json();
                    item.dataset.watched = data.inWatched ? 'true' : 'false';
                    item.dataset.watching = 'false';
                    item.dataset.inWatchlist = data.inWatchlist ? 'true' : 'false';
                    updateBadge(item);
                    updateButtonStyles(item);
                    showToast(data.message, 'success');
                }
            } catch (error) {
                console.error('Error:', error);
                showToast(window.i18n?.watchlist_error_modify || 'Error', 'error');
            }
        });
    });

    document.querySelectorAll('.watchlist-item').forEach(item => {
        item.addEventListener('click', function(e) {
            // Check if click is on a button or its parent
            if (e.target.closest('.toggle-watchlist-btn') ||
                e.target.closest('.toggle-watching-btn') ||
                e.target.closest('.toggle-watched-btn') ||
                e.target.closest('.delete-btn') ||
                e.target.closest('.watchlist-rating-btn')) {
                return;
            }
            // Redirect to drama page
            const contentId = this.dataset.contentId;
            if (contentId) {
                window.location.href = `/kdrams/${contentId}`;
            }
        });
    });

    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', async function(e) {
            e.preventDefault();
            e.stopPropagation();
            const contentId = this.dataset.contentId;
            const item = this.closest('.watchlist-item');
            const title = item.dataset.title;

            showConfirmModal((window.i18n?.watchlist_confirm_delete_item || 'Delete "{title}"?').replace('{title}', title), async () => {
                try {
                    const response = await fetch(`/api/watchlist/${contentId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Content-Type': 'application/json',
                        },
                    });

                    if (response.ok) {
                        item.style.opacity = '0';
                        setTimeout(() => {
                            item.remove();
                            if (document.querySelectorAll('.watchlist-item').length === 0) {
                                location.reload();
                            }
                            showToast(window.i18n?.watchlist_action_done || 'Done', 'success');
                        }, 300);
                    }
                } catch (error) {
                    console.error('Error:', error);
                    showToast(window.i18n?.watchlist_error_delete || 'Error', 'error');
                }
            }, null, title);
        });
    });

    document.querySelectorAll('.rating-display-container').forEach(container => {
        const menu = container.querySelector('.rating-menu');
        container.addEventListener('mouseenter', () => menu.classList.remove('hidden'));
        container.addEventListener('mouseleave', () => menu.classList.add('hidden'));

        menu.querySelectorAll('.watchlist-rating-btn').forEach(btn => {
            btn.addEventListener('click', async function(e) {
                e.preventDefault();
                e.stopPropagation();
                const contentId = container.dataset.contentId;
                const rating = this.dataset.rating === 'null' ? null : parseInt(this.dataset.rating);

                try {
                    const response = await fetch(`/api/rating/${contentId}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'X-Requested-With': 'XMLHttpRequest',
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ rating: rating })
                    });

                    if (response.ok) {
                        const data = await response.json();
                        container.dataset.currentRating = data.rating || '';
                        updateRatingDisplay(container);
                        menu.classList.add('hidden');
                        showToast(data.message, 'success');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    showToast(window.i18n?.watchlist_error_connection || 'Error', 'error');
                }
            });
        });
    });

    function updateRatingDisplay(container) {
        const currentRating = parseInt(container.dataset.currentRating) || null;
        const emojiSpan = container.querySelector('.rating-emoji');

        if (currentRating) {
            const emoji = currentRating === 1 ? '👎' : (currentRating === 2 ? '👍' : '👍👍');
            if (emojiSpan) {
                emojiSpan.textContent = emoji;
            } else {
                const span = document.createElement('span');
                span.className = 'rating-emoji text-lg bg-black/30 backdrop-blur-sm px-1.5 py-0.5 rounded-lg inline-block';
                span.textContent = emoji;
                container.insertBefore(span, container.querySelector('.rating-menu'));
            }
        } else {
            if (emojiSpan) emojiSpan.remove();
        }
    }

    function updateBadge(item) {
        const badge = item.querySelector('.status-badge');
        const isWatched = item.dataset.watched === 'true';
        const isWatching = item.dataset.watching === 'true';

        let html = '';
        if (isWatched) {
            html = '<span class="bg-green-600">' + (window.i18n?.watchlist_badge_watched || 'Watched') + '</span>';
        } else if (isWatching) {
            html = '<span class="bg-amber-500">' + (window.i18n?.watchlist_badge_watching || 'Watching') + '</span>';
        } else {
            html = '<span class="bg-red-600">' + (window.i18n?.watchlist_badge_to_watch || 'To Watch') + '</span>';
        }
        badge.innerHTML = html;
    }

    function updateButtonStyles(item) {
        const isInWatchlist = item.dataset.inWatchlist === 'true';
        const isWatching = item.dataset.watching === 'true';
        const isWatched = item.dataset.watched === 'true';

        // Update watchlist button
        const watchlistBtn = item.querySelector('.toggle-watchlist-btn');
        if (watchlistBtn) {
            watchlistBtn.disabled = isInWatchlist;
            watchlistBtn.className = 'px-2.5 py-2 rounded-lg text-white text-base transition toggle-watchlist-btn ' +
                (isInWatchlist ? 'bg-red-600 cursor-default' : 'bg-white/20 hover:bg-red-500/80 cursor-pointer');
        }

        // Update watching button
        const watchingBtn = item.querySelector('.toggle-watching-btn');
        if (watchingBtn) {
            watchingBtn.disabled = isWatching;
            watchingBtn.className = 'px-2.5 py-2 rounded-lg text-white text-base transition toggle-watching-btn ' +
                (isWatching ? 'bg-amber-600 cursor-default' : 'bg-white/20 hover:bg-amber-500/80 cursor-pointer');
        }

        // Update watched button
        const watchedBtn = item.querySelector('.toggle-watched-btn');
        if (watchedBtn) {
            watchedBtn.disabled = isWatched;
            watchedBtn.className = 'px-2.5 py-2 rounded-lg text-white text-base transition toggle-watched-btn ' +
                (isWatched ? 'bg-green-600 cursor-default' : 'bg-white/20 hover:bg-green-500/80 cursor-pointer');
        }
    }

    function showToast(message, type = 'success') {
        const existing = document.querySelector('.toast');
        if (existing) existing.remove();

        const toast = document.createElement('div');
        toast.className = `toast fixed top-4 right-4 px-6 py-3 rounded-lg text-white font-semibold shadow-lg z-50`;
        toast.classList.add(type === 'success' ? 'bg-green-600' : 'bg-red-600');
        toast.textContent = message;
        document.body.appendChild(toast);

        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    window.showConfirmModal = function(message, onConfirm, onCancel = null, title = null) {
        const modal = document.createElement('div');
        modal.className = 'fixed inset-0 z-50 flex items-center justify-center';
        modal.id = 'confirmModal';
        const titleHTML = title ? `<p class="text-slate-400 text-sm mb-4 truncate">📺 ${title}</p>` : '';
        modal.innerHTML = `
            <div class="fixed inset-0 bg-black/50" onclick="closeConfirmModal()"></div>
            <div class="relative bg-slate-900 border border-slate-700 rounded-xl shadow-2xl max-w-sm w-full mx-4 p-6">
                <h3 class="text-xl font-bold text-white mb-2 flex items-center gap-2">
                    <span>⚠️</span> ${window.i18n?.watchlist_confirm_title || 'Confirm'}
                </h3>
                ${titleHTML}
                <p class="text-slate-300 mb-6">${message}</p>
                <div class="flex gap-3">
                    <button class="flex-1 px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white font-semibold rounded-lg transition" onclick="closeConfirmModal()">
                        ${window.i18n?.watchlist_confirm_cancel || 'Cancel'}
                    </button>
                    <button class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition" id="confirmBtn">
                        ${window.i18n?.watchlist_confirm_delete_btn || 'Delete'}
                    </button>
                </div>
            </div>
        `;
        document.body.appendChild(modal);

        document.getElementById('confirmBtn').addEventListener('click', () => {
            closeConfirmModal();
            onConfirm();
        });

        const handleEscape = (e) => {
            if (e.key === 'Escape') {
                closeConfirmModal();
                if (onCancel) onCancel();
                document.removeEventListener('keydown', handleEscape);
            }
        };
        document.addEventListener('keydown', handleEscape);
    };

    window.closeConfirmModal = function() {
        const modal = document.getElementById('confirmModal');
        if (modal) modal.remove();
    };
});
</script>

@include('watchlist._export-modal')

@endsection
