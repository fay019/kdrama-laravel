@extends('layouts.app')

@section('title', __('watchlist.page_title'))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <div class="mb-8 flex justify-between items-start">
        <div>
            <h1 class="text-4xl font-bold mb-2">{{ __('watchlist.title') }}</h1>
            <p class="text-slate-400">{{ count($items) }} {{ count($items) != 1 ? __('watchlist.js.drama_plural') : __('watchlist.js.drama_singular') }}</p>
        </div>
        @if(count($items) > 0)
            <button onclick="openExportModal()" class="flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-bold rounded-lg transition shadow-lg">
                {{ __('watchlist.export_button') }}
            </button>
        @endif
    </div>

    @if(count($items) > 0)
        <!-- Filter Buttons -->
        <div class="mb-8 flex gap-3 flex-wrap">
            <button class="filter-btn active px-6 py-2 rounded-lg font-semibold transition" data-filter="all">
                {{ __('watchlist.filter_all') }} ({{ count($items) }})
            </button>
            <button class="filter-btn px-6 py-2 rounded-lg font-semibold transition" data-filter="towatch">
                {{ __('watchlist.filter_to_watch') }} ({{ count($toWatch) }})
            </button>
            <button class="filter-btn px-6 py-2 rounded-lg font-semibold transition" data-filter="watching">
                {{ __('watchlist.filter_watching') }} ({{ count($watching) }})
            </button>
            <button class="filter-btn px-6 py-2 rounded-lg font-semibold transition" data-filter="watched">
                {{ __('watchlist.filter_watched') }} ({{ count($watched) }})
            </button>
        </div>

        <div class="content-grid" id="watchlistGrid">
            @foreach($items as $item)
                <div class="content-card group fade-in watchlist-item" data-watched="{{ $item->is_watched ? 'true' : 'false' }}" data-watching="{{ $item->is_watching ? 'true' : 'false' }}" data-in-watchlist="{{ $item->is_in_watchlist ? 'true' : 'false' }}" data-content-id="{{ $item->tmdb_id }}">
                    <!-- Status Badge -->
                    @if($item->is_watched)
                        <div class="absolute top-2 left-2 bg-green-600 text-white px-3 py-1 rounded-full text-xs font-semibold z-10">
                            {{ __('watchlist.status_watched') }}
                        </div>
                    @elseif($item->is_watching)
                        <div class="absolute top-2 left-2 bg-amber-500 text-white px-3 py-1 rounded-full text-xs font-semibold z-10">
                            {{ __('watchlist.status_watching') }}
                        </div>
                    @else
                        <div class="absolute top-2 left-2 bg-red-600 text-white px-3 py-1 rounded-full text-xs font-semibold z-10">
                            {{ __('watchlist.status_to_watch') }}
                        </div>
                    @endif

                    <a href="{{ route('kdrams.show', $item->tmdb_id) }}" class="block">
                        <div class="content-image">
                            @if($item->kdrama && $item->kdrama->poster_path)
                                <img
                                    src="https://image.tmdb.org/t/p/w500{{ $item->kdrama->poster_path }}"
                                    alt="{{ $item->kdrama->name }}"
                                >
                            @else
                                <div class="w-full h-full bg-gradient-to-br from-slate-700 to-slate-900 flex items-center justify-center">
                                    <span class="text-slate-400 text-center">
                                        <div class="text-3xl mb-2">🎬</div>
                                        {{ __('watchlist.no_image') }}
                                    </span>
                                </div>
                            @endif

                            <!-- Action buttons overlay -->
                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition flex gap-2 items-center justify-center backdrop-blur-sm p-3">
                                <!-- Watchlist Toggle -->
                                <button type="button" class="toggle-watchlist-btn {{ $item->is_in_watchlist ? 'bg-red-600 hover:bg-red-700' : 'bg-slate-600 hover:bg-slate-700' }} text-white font-bold py-2 px-3 rounded-lg shadow-lg transform transition hover:scale-105 text-sm" data-content-id="{{ $item->tmdb_id }}" title="{{ __('watchlist.title_watchlist_toggle') }}">
                                    {{ __('watchlist.btn_list') }}
                                </button>

                                <!-- Watching Toggle -->
                                <button type="button" class="toggle-watching-btn {{ $item->is_watching ? 'bg-amber-500 hover:bg-amber-600' : 'bg-slate-600 hover:bg-slate-700' }} text-white font-bold py-2 px-3 rounded-lg shadow-lg transform transition hover:scale-105 text-sm" data-content-id="{{ $item->tmdb_id }}" title="{{ __('watchlist.title_watching_toggle') }}">
                                    {{ __('watchlist.btn_watching') }}
                                </button>

                                <!-- Watched Toggle -->
                                <button type="button" class="toggle-watched-btn {{ $item->is_watched ? 'bg-green-600 hover:bg-green-700' : 'bg-slate-600 hover:bg-slate-700' }} text-white font-bold py-2 px-3 rounded-lg shadow-lg transform transition hover:scale-105 text-sm" data-content-id="{{ $item->tmdb_id }}" title="{{ __('watchlist.title_watched_toggle') }}">
                                    {{ __('watchlist.btn_watched') }}
                                </button>

                                <!-- Delete Button -->
                                <button type="button" class="delete-btn bg-red-700 hover:bg-red-800 text-white font-bold py-2 px-3 rounded-lg shadow-lg transform transition hover:scale-105 text-sm" data-content-id="{{ $item->tmdb_id }}" title="{{ __('watchlist.title_delete') }}">
                                    🗑️
                                </button>
                            </div>

                            <!-- Rating display for watched items -->
                            @if($item->is_watched)
                                <div class="absolute bottom-1 right-1 rating-display-container" data-current-rating="{{ $item->rating }}" data-content-id="{{ $item->tmdb_id }}">
                                    <!-- Show selected rating emoji if rated, otherwise show hidden menu -->
                                    @if($item->rating)
                                        <span class="rating-emoji text-lg bg-black/30 backdrop-blur-sm px-1.5 py-0.5 rounded-lg inline-block">
                                            {{ $item->rating === 1 ? '👎' : ($item->rating === 2 ? '👍' : '👍👍') }}
                                        </span>
                                    @endif
                                    <!-- Hidden rating menu appears on hover -->
                                    <div class="rating-menu hidden absolute bottom-8 right-0 bg-slate-900/90 backdrop-blur-md border border-slate-700 rounded-lg p-1.5 flex gap-1 shadow-lg" style="z-index: 20;">
                                        <button type="button" class="watchlist-rating-btn px-2 py-1.5 rounded text-xs font-bold bg-slate-700 hover:bg-red-600 transition flex items-center justify-center" data-rating="1" title="{{ __('watchlist.title_rating_bad') }}">👎</button>
                                        <button type="button" class="watchlist-rating-btn px-2 py-1.5 rounded text-xs font-bold bg-slate-700 hover:bg-green-600 transition flex items-center justify-center" data-rating="2" title="{{ __('watchlist.title_rating_good') }}">👍</button>
                                        <button type="button" class="watchlist-rating-btn px-2 py-1.5 rounded text-xs font-bold bg-slate-700 hover:bg-purple-600 transition flex items-center justify-center" data-rating="3" title="{{ __('watchlist.title_rating_very_good') }}">👍👍</button>
                                        <button type="button" class="watchlist-rating-btn px-2 py-1.5 rounded text-xs font-bold bg-slate-700 hover:bg-red-500 transition flex items-center justify-center" data-rating="null" title="{{ __('watchlist.title_rating_remove') }}">✕</button>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </a>

                    <div class="p-4">
                        <a href="{{ route('kdrams.show', $item->tmdb_id) }}" class="block">
                            <h3 class="font-bold text-lg text-slate-100 group-hover:text-red-400 transition line-clamp-2">
                                {{ $item->kdrama->name ?? __('watchlist.unknown_drama') }}
                            </h3>
                        </a>
                        <p class="text-slate-400 text-sm mt-2">
                            @if($item->kdrama && $item->kdrama->first_air_date)
                                📅
                                @php
                                    $date = $item->kdrama->first_air_date;
                                    if (is_string($date)) {
                                        $date = \Carbon\Carbon::parse($date);
                                    }
                                @endphp
                                {{ $date->format('Y') }}
                            @endif
                        </p>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="card-dark py-24 text-center">
            <div class="text-6xl mb-6">🍿</div>
            <h2 class="text-2xl font-bold text-slate-200 mb-4">{{ __('watchlist.empty_title') }}</h2>
            <p class="text-slate-400 mb-8 max-w-md mx-auto">
                {{ __('watchlist.empty_description') }}
            </p>
            <a href="{{ route('kdrams.catalog') }}" class="btn-primary inline-block">
                {{ __('watchlist.empty_cta') }}
            </a>
        </div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterBtns = document.querySelectorAll('.filter-btn');
    const watchlistItems = document.querySelectorAll('.watchlist-item');
    const watchlistToggleBtns = document.querySelectorAll('.toggle-watchlist-btn');
    const watchingBtns = document.querySelectorAll('.toggle-watching-btn');
    const watchedBtns = document.querySelectorAll('.toggle-watched-btn');
    const deleteBtns = document.querySelectorAll('.delete-btn');
    let currentFilter = 'all';

    // Filter functionality
    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            currentFilter = this.dataset.filter;

            // Update active button
            filterBtns.forEach(b => b.classList.remove('active', 'bg-red-600', 'text-white'));
            filterBtns.forEach(b => b.classList.add('bg-slate-700', 'text-slate-300'));
            this.classList.remove('bg-slate-700', 'text-slate-300');
            this.classList.add('active', 'bg-red-600', 'text-white');

            // Filter items
            watchlistItems.forEach(item => {
                const isWatched = item.dataset.watched === 'true';
                const isWatching = item.dataset.watching === 'true';
                const isInWatchlist = item.dataset.inWatchlist === 'true';

                if (currentFilter === 'all') {
                    item.style.display = '';
                } else if (currentFilter === 'towatch' && isInWatchlist && !isWatched && !isWatching) {
                    item.style.display = '';
                } else if (currentFilter === 'watching' && isWatching) {
                    item.style.display = '';
                } else if (currentFilter === 'watched' && isWatched) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });

    // Toggle watchlist functionality
    watchlistToggleBtns.forEach(btn => {
        btn.addEventListener('click', async function(e) {
            e.preventDefault();
            const contentId = this.dataset.contentId;
            const item = this.closest('.watchlist-item');

            try {
                this.disabled = true;

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
                    item.dataset.watching = 'false'; // Reset watching when toggling watchlist
                    item.dataset.watched = data.inWatched ? 'true' : 'false';

                    // Update button colors based on server response
                    const watchlistBtn = this;
                    const watchingBtn = item.querySelector('.toggle-watching-btn');
                    const watchedBtn = item.querySelector('.toggle-watched-btn');

                    if (data.inWatchlist) {
                        watchlistBtn.classList.remove('bg-slate-600', 'hover:bg-slate-700');
                        watchlistBtn.classList.add('bg-red-600', 'hover:bg-red-700');
                        watchingBtn.classList.remove('bg-amber-500', 'hover:bg-amber-600');
                        watchingBtn.classList.add('bg-slate-600', 'hover:bg-slate-700');
                        watchedBtn.classList.remove('bg-green-600', 'hover:bg-green-700');
                        watchedBtn.classList.add('bg-slate-600', 'hover:bg-slate-700');
                    } else {
                        watchlistBtn.classList.remove('bg-red-600', 'hover:bg-red-700');
                        watchlistBtn.classList.add('bg-slate-600', 'hover:bg-slate-700');
                    }
                    showToast(data.message, 'success');

                    // Update badge
                    updateBadge(item);

                    // Re-apply filter if needed
                    if (currentFilter === 'towatch' && item.dataset.watched === 'false' && item.dataset.watching === 'false' && item.dataset.inWatchlist === 'false') {
                        // Was in towatch, now not in any category
                        item.style.display = 'none';
                    }
                } else {
                    showToast(window.i18n.watchlist_error_modify, 'error');
                }
            } catch (error) {
                console.error('Erreur:', error);
                showToast(window.i18n.watchlist_error_modify, 'error');
            } finally {
                this.disabled = false;
            }
        });
    });

    // Toggle watched functionality
    watchedBtns.forEach(btn => {
        btn.addEventListener('click', async function(e) {
            e.preventDefault();
            const contentId = this.dataset.contentId;
            const item = this.closest('.watchlist-item');

            try {
                this.disabled = true;

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
                    item.dataset.watching = 'false'; // Reset watching when toggling watched
                    item.dataset.inWatchlist = data.inWatchlist ? 'true' : 'false';

                    // Update watched, watching and watchlist button colors
                    const watchedBtn = this;
                    const watchingBtn = item.querySelector('.toggle-watching-btn');
                    const watchlistBtn = item.querySelector('.toggle-watchlist-btn');

                    if (data.inWatched) {
                        watchedBtn.classList.remove('bg-slate-600', 'hover:bg-slate-700');
                        watchedBtn.classList.add('bg-green-600', 'hover:bg-green-700');
                        watchingBtn.classList.remove('bg-amber-500', 'hover:bg-amber-600');
                        watchingBtn.classList.add('bg-slate-600', 'hover:bg-slate-700');
                        watchlistBtn.classList.remove('bg-red-600', 'hover:bg-red-700');
                        watchlistBtn.classList.add('bg-slate-600', 'hover:bg-slate-700');
                    } else {
                        watchedBtn.classList.remove('bg-green-600', 'hover:bg-green-700');
                        watchedBtn.classList.add('bg-slate-600', 'hover:bg-slate-700');
                    }
                    showToast(data.message, 'success');

                    // Update badge
                    updateBadge(item);

                    // Re-apply filter if needed
                    if (currentFilter === 'towatch' && item.dataset.watched === 'true') {
                        item.style.display = 'none';
                    } else if (currentFilter === 'watched' && item.dataset.watched === 'false') {
                        item.style.display = 'none';
                    }
                } else {
                    showToast(window.i18n.watchlist_error_modify, 'error');
                }
            } catch (error) {
                console.error('Erreur:', error);
                showToast(window.i18n.watchlist_error_modify, 'error');
            } finally {
                this.disabled = false;
            }
        });
    });

    // Toggle watching functionality
    watchingBtns.forEach(btn => {
        btn.addEventListener('click', async function(e) {
            e.preventDefault();
            const contentId = this.dataset.contentId;
            const item = this.closest('.watchlist-item');

            try {
                this.disabled = true;

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

                    // Update watching, watchlist and watched button colors
                    const watchingBtn = this;
                    const watchlistBtn = item.querySelector('.toggle-watchlist-btn');
                    const watchedBtn = item.querySelector('.toggle-watched-btn');

                    if (data.inWatching) {
                        watchingBtn.classList.remove('bg-slate-600', 'hover:bg-slate-700');
                        watchingBtn.classList.add('bg-amber-500', 'hover:bg-amber-600');
                        watchlistBtn.classList.remove('bg-red-600', 'hover:bg-red-700');
                        watchlistBtn.classList.add('bg-slate-600', 'hover:bg-slate-700');
                        watchedBtn.classList.remove('bg-green-600', 'hover:bg-green-700');
                        watchedBtn.classList.add('bg-slate-600', 'hover:bg-slate-700');
                    } else {
                        watchingBtn.classList.remove('bg-amber-500', 'hover:bg-amber-600');
                        watchingBtn.classList.add('bg-slate-600', 'hover:bg-slate-700');
                    }
                    showToast(data.message, 'success');

                    // Update badge
                    updateBadge(item);

                    // Re-apply filter if needed
                    if (currentFilter === 'watching' && item.dataset.watching === 'false') {
                        item.style.display = 'none';
                    } else if (currentFilter === 'towatch' && item.dataset.watching === 'true') {
                        item.style.display = 'none';
                    }
                } else {
                    showToast(window.i18n.watchlist_error_modify, 'error');
                }
            } catch (error) {
                console.error('Erreur:', error);
                showToast(window.i18n.watchlist_error_modify, 'error');
            } finally {
                this.disabled = false;
            }
        });
    });

    // Delete functionality
    deleteBtns.forEach(btn => {
        btn.addEventListener('click', async function(e) {
            e.preventDefault();

            const action = this.dataset.action;
            const confirmMsg = action === 'remove-all'
                ? window.i18n.watchlist_confirm_delete
                : window.i18n.watchlist_confirm_delete_short;

            if (!confirm(confirmMsg)) return;

            const contentId = this.dataset.contentId;
            const item = this.closest('.watchlist-item');

            try {
                this.disabled = true;

                const response = await fetch(`/api/watchlist/${contentId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                    },
                });

                if (response.ok) {
                    const data = await response.json();

                    // Animate removal
                    item.style.transition = 'opacity 0.3s ease';
                    item.style.opacity = '0';
                    setTimeout(() => {
                        item.remove();
                        showToast(data.message || window.i18n.watchlist_action_done, 'success');

                        // Check if grid is empty
                        if (document.querySelectorAll('.watchlist-item').length === 0) {
                            location.reload();
                        }
                    }, 300);
                } else {
                    showToast(window.i18n.watchlist_error_delete, 'error');
                    this.disabled = false;
                }
            } catch (error) {
                console.error('Erreur:', error);
                showToast(window.i18n.watchlist_error_delete, 'error');
                this.disabled = false;
            }
        });
    });

    // Rating functionality for watched items
    const ratingContainers = document.querySelectorAll('.rating-display-container');

    ratingContainers.forEach(container => {
        const menu = container.querySelector('.rating-menu');
        const ratingBtns = menu.querySelectorAll('.watchlist-rating-btn');

        // Show/hide menu on hover
        container.addEventListener('mouseenter', () => {
            menu.classList.remove('hidden');
        });

        container.addEventListener('mouseleave', () => {
            menu.classList.add('hidden');
        });

        ratingBtns.forEach(btn => {
            btn.addEventListener('click', async function(e) {
                e.preventDefault();
                const contentId = container.dataset.contentId;
                const ratingStr = this.dataset.rating;
                const rating = ratingStr === 'null' ? null : parseInt(ratingStr);

                try {
                    this.disabled = true;

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
                    } else {
                        const data = await response.json();
                        showToast(data.error || window.i18n.watchlist_error_rating, 'error');
                    }
                } catch (error) {
                    console.error('Erreur:', error);
                    showToast(window.i18n.watchlist_error_connection, 'error');
                } finally {
                    this.disabled = false;
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
                span.className = 'rating-emoji text-lg';
                span.textContent = emoji;
                container.insertBefore(span, container.querySelector('.rating-menu'));
            }
        } else {
            if (emojiSpan) {
                emojiSpan.remove();
            }
        }
    }

    // Initialize rating displays
    ratingContainers.forEach(container => updateRatingDisplay(container));

    function updateBadge(item) {
        const badgeContainer = item.querySelector('.absolute.top-2.left-2');
        const isWatched = item.dataset.watched === 'true';
        const isWatching = item.dataset.watching === 'true';

        // Remove all color classes
        badgeContainer.classList.remove('bg-green-600', 'bg-amber-500', 'bg-red-600');

        // Add correct color based on state (exclusive: only one can be true)
        if (isWatched) {
            badgeContainer.classList.add('bg-green-600');
            badgeContainer.textContent = window.i18n.watchlist_badge_watched;
        } else if (isWatching) {
            badgeContainer.classList.add('bg-amber-500');
            badgeContainer.textContent = window.i18n.watchlist_badge_watching;
        } else {
            badgeContainer.classList.add('bg-red-600');
            badgeContainer.textContent = window.i18n.watchlist_badge_to_watch;
        }
    }

    function showToast(message, type = 'success') {
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

        setTimeout(() => {
            toast.style.animation = 'fade-out 0.3s ease-out forwards';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
});
</script>

<!-- Export Modal -->
@include('watchlist._export-modal')

@endsection
