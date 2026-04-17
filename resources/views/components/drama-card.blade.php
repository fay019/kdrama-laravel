@props(['item', 'variant' => 'default', 'badge' => null, 'userStatus' => null, 'showActions' => false, 'actionButtons' => null])

@php
$variants = [
    'featured' => 'h-64 sm:h-72',
    'default' => 'h-52 sm:h-60',
    'compact' => 'h-40 sm:h-44',
];
$sizeClass = $variants[$variant] ?? $variants['default'];

// Handle array or object
$title = is_array($item) ? ($item['name'] ?? ($item['title'] ?? 'Unknown')) : ($item->name ?? $item->title ?? 'Unknown');

// Check if item has poster_path (drama) or profile_path (actor)
$hasPosterPath = is_array($item) ? !empty($item['poster_path']) : !empty($item->poster_path ?? null);
$hasProfilePath = is_array($item) ? !empty($item['profile_path']) : !empty($item->profile_path ?? null);
$isActor = !$hasPosterPath && $hasProfilePath;

// For actors, use profile_path; for dramas, use poster_path
$posterPath = $hasPosterPath
    ? (is_array($item) ? $item['poster_path'] : $item->poster_path)
    : (is_array($item) ? ($item['profile_path'] ?? null) : ($item->profile_path ?? null));

$rating = is_array($item) ? (isset($item['vote_average']) ? number_format($item['vote_average'], 1) : '0.0') : (isset($item->vote_average) ? number_format($item->vote_average, 1) : '0.0');
$date = is_array($item) ? ($item['first_air_date'] ?? null) : ($item->first_air_date ?? null);
$itemId = is_array($item) ? ($item['tmdb_id'] ?? ($item['id'] ?? null)) : ($item->tmdb_id ?? ($item->id ?? null));
$mediaType = is_array($item) ? ($item['media_type'] ?? null) : ($item->media_type ?? null);

// User status
$userStatusData = null;
$isWatched = false;
$isWatching = false;
$isInWatchlist = false;
$ratingValue = null;

if ($userStatus) {
    if (is_array($userStatus) && isset($userStatus[$itemId])) {
        $userStatusData = $userStatus[$itemId];
    } elseif (is_object($userStatus)) {
        $userStatusData = $userStatus;
    }

    if ($userStatusData) {
        $isWatched = is_array($userStatusData) ? ($userStatusData['is_watched'] ?? false) : ($userStatusData->is_watched ?? false);
        $isWatching = is_array($userStatusData) ? ($userStatusData['is_watching'] ?? false) : ($userStatusData->is_watching ?? false);
        $isInWatchlist = is_array($userStatusData) ? ($userStatusData['is_in_watchlist'] ?? false) : ($userStatusData->is_in_watchlist ?? false);
        $ratingValue = is_array($userStatusData) ? ($userStatusData['rating'] ?? null) : ($userStatusData->rating ?? null);
    }
}
@endphp

<div class="group relative overflow-hidden rounded-xl {{ $sizeClass }} cursor-pointer transition-transform duration-300 hover:scale-105"
     @if($isActor) onclick="openActorModal({{ $itemId }})" @endif>
    @if(!$isActor)
        <a href="{{ route('kdrams.show', $itemId) }}" class="absolute inset-0 z-40"></a>
    @endif

    <!-- Background Image -->
    @if($posterPath)
        <img
            src="https://image.tmdb.org/t/p/w342{{ $posterPath }}"
            srcset="https://image.tmdb.org/t/p/w342{{ $posterPath }} 1x, https://image.tmdb.org/t/p/w500{{ $posterPath }} 2x"
            alt="{{ $title }}"
            class="absolute inset-0 w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
            loading="lazy"
            decoding="async"
        >
    @else
        <div class="absolute inset-0 w-full h-full bg-gradient-to-br from-slate-700 via-slate-800 to-slate-900 dark:from-slate-900 dark:to-black flex items-center justify-center">
            <div class="text-center">
                <div class="text-4xl mb-2 opacity-60">🎬</div>
                <p class="text-slate-400 dark:text-slate-500 text-xs opacity-75">{{ __('home.no_image') }}</p>
            </div>
        </div>
    @endif

    <!-- Overlay - Gradient Dark -->
    <div class="absolute inset-0 bg-gradient-to-t from-black via-black/40 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>

    <!-- Media Type Badge (Top Left) -->
    @if(!$isActor && $mediaType)
        <div class="absolute top-2 left-2 z-10">
            @if($mediaType === 'tv')
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-blue-600/90 text-white shadow-lg p-1" title="Série">
                    <img src="{{ asset('images/pdf-icons/tv.svg') }}" alt="TV" class="w-full h-full filter invert brightness-200">
                </span>
            @elseif($mediaType === 'movie')
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-purple-600/90 text-white shadow-lg p-1" title="Film">
                    <img src="{{ asset('images/pdf-icons/clapperboard.svg') }}" alt="Film" class="w-full h-full filter invert brightness-200">
                </span>
            @endif
        </div>
    @endif

    <!-- Status Badges (Top Right) -->
    @if($userStatusData)
        <div class="absolute top-2 right-2 flex flex-col gap-1 items-end z-10">
            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-yellow-500/90 dark:bg-yellow-600 text-black dark:text-white font-bold text-xs shadow-lg">
                <span>⭐</span>
                <span>{{ $rating }}/10</span>
            </span>

            @if($isWatched)
                <span class="inline-flex items-center px-2 py-1 rounded-lg bg-green-500/90 text-white text-[10px] font-bold shadow-lg">
                    {{ __('watchlist.status_watched') }}
                </span>
                @if($ratingValue)
                    <span class="text-base">
                        @if($ratingValue == 1) 👎
                        @elseif($ratingValue == 2) 👍
                        @else 👍👍
                        @endif
                    </span>
                @endif
            @elseif($isWatching)
                <span class="inline-flex items-center px-2 py-1 rounded-lg bg-amber-500/90 text-white text-[10px] font-bold shadow-lg">
                    {{ __('watchlist.status_watching') }}
                </span>
            @elseif($isInWatchlist)
                <span class="inline-flex items-center px-2 py-1 rounded-lg bg-red-500/90 text-white text-[10px] font-bold shadow-lg">
                    {{ __('watchlist.status_to_watch') }}
                </span>
            @endif
        </div>
    @elseif($rating > 0 && ($variant === 'featured' || $variant === 'default'))
        <div class="absolute top-2 right-2 z-10">
            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-yellow-500/90 dark:bg-yellow-600 text-black dark:text-white font-bold text-xs shadow-lg">
                <span>⭐</span>
                <span>{{ $rating }}/10</span>
            </span>
        </div>
    @endif

    <!-- Content Overlay (Bottom) -->
    <div class="absolute inset-0 flex flex-col justify-between p-4 transform translate-y-2 group-hover:translate-y-0 transition-transform duration-300 opacity-0 group-hover:opacity-100 z-20 pointer-events-none">
        <div></div>
        <div class="space-y-2">
            @if($badge)
                <span class="inline-flex items-center px-2 py-1 rounded-lg bg-red-600 dark:bg-red-700 text-white text-xs font-bold uppercase tracking-wider">
                    {{ $badge }}
                </span>
            @endif

            <h3 class="text-white font-semibold leading-tight line-clamp-2 text-xs sm:text-sm">
                {{ $title }}
            </h3>

            @if($date)
                <p class="text-slate-200 dark:text-slate-300 text-xs">
                    📅 {{ \Carbon\Carbon::parse($date)->format(__('home.date_format')) }}
                </p>
            @endif
        </div>
    </div>

    <!-- Action Buttons (Hover) -->
    @if($showActions && $actionButtons)
        <div class="absolute inset-0 flex gap-4 items-center justify-center px-4 py-3 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity duration-300 z-20">
            {!! $actionButtons !!}
        </div>
    @endif
</div>
