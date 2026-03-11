@php
    $actorId = null;
    if (isset($filters) && is_array($filters)) {
        $actorId = $filters['with_cast'] ?? ($filters['actor_id'] ?? null);
    }

    // Sécurité supplémentaire : si $kdrama n'est pas un tableau ou un objet avec ID, on arrête là
    if (!is_array($kdrama) && !is_object($kdrama)) {
        return;
    }

    // Récupération de l'ID de manière sécurisée (tableau ou objet)
    // On privilégie TOUJOURS le tmdb_id pour les routes si on l'a, sinon id
    $kdramaId = is_array($kdrama) ? ($kdrama['tmdb_id'] ?? ($kdrama['id'] ?? null)) : ($kdrama->tmdb_id ?? ($kdrama->id ?? null));

    if (!$kdramaId) {
        return;
    }
@endphp
<a href="{{ route('kdrams.show', ['id' => $kdramaId, 'actor_id' => $actorId]) }}" class="content-card group fade-in">
    <div class="content-image">
        @php
            $posterPath = is_array($kdrama) ? ($kdrama['poster_path'] ?? null) : ($kdrama->poster_path ?? null);
            $name = is_array($kdrama) ? ($kdrama['name'] ?? ($kdrama['title'] ?? '')) : ($kdrama->name ?? '');
            $voteAverage = is_array($kdrama) ? ($kdrama['vote_average'] ?? 0) : ($kdrama->vote_average ?? 0);
        @endphp

        @if($posterPath)
            <img
                src="https://image.tmdb.org/t/p/w500{{ $posterPath }}"
                alt="{{ $name }}"
            >
        @else
            <div class="w-full h-full bg-gradient-to-br from-slate-700 to-slate-900 flex items-center justify-center">
                <span class="text-slate-400 text-center">
                    <div class="text-3xl mb-2">🎬</div>
                    Pas d'image
                </span>
            </div>
        @endif
        <div class="absolute top-2 right-2 flex flex-col gap-1 items-end">
            <span class="badge shadow-lg">⭐ {{ number_format($voteAverage, 1) }}/10</span>

            @php
                $userStatusForItem = null;
                if (isset($userStatus)) {
                    if (is_array($userStatus) && isset($userStatus[$kdramaId])) {
                        $userStatusForItem = $userStatus[$kdramaId];
                    } elseif (is_object($userStatus) && isset($userStatus->tmdb_id) && $userStatus->tmdb_id == $kdramaId) {
                        $userStatusForItem = $userStatus;
                    }
                }
            @endphp

            @if($userStatusForItem)
                @php
                    $isWatched = is_array($userStatusForItem) ? $userStatusForItem['is_watched'] : $userStatusForItem->is_watched;
                    $isInWatchlist = is_array($userStatusForItem) ? $userStatusForItem['is_in_watchlist'] : $userStatusForItem->is_in_watchlist;
                    $isWatching = is_array($userStatusForItem) ? $userStatusForItem['is_watching'] : $userStatusForItem->is_watching;
                    $rating = is_array($userStatusForItem) ? ($userStatusForItem['rating'] ?? null) : ($userStatusForItem->rating ?? null);
                @endphp
                @if($isWatched)
                    <span class="bg-green-500/90 text-white text-[10px] font-bold px-2 py-1 rounded shadow-lg flex items-center gap-1">
                        ✅ VU
                    </span>
                    @if($rating)
                        <span class="badge-rating shadow-lg text-base">
                            @if($rating == 1) 👎
                            @elseif($rating == 2) 👍
                            @else 👍👍
                            @endif
                        </span>
                    @endif
                @elseif($isWatching)
                    <span class="bg-amber-500/90 text-white text-[10px] font-bold px-2 py-1 rounded shadow-lg flex items-center gap-1">
                        🎬 EN COURS
                    </span>
                @elseif($isInWatchlist)
                    <span class="bg-red-500/90 text-white text-[10px] font-bold px-2 py-1 rounded shadow-lg flex items-center gap-1">
                        📺 À VOIR
                    </span>
                @endif
            @endif
        </div>
    </div>
    <div class="p-4">
        @php
            $frName = is_array($kdrama) ? ($kdrama['name'] ?? ($kdrama['title'] ?? null)) : ($kdrama->name ?? null);
            $enName = is_array($kdrama) ? ($kdrama['en_name'] ?? null) : ($kdrama->en_name ?? null);
            $originalName = is_array($kdrama) ? ($kdrama['original_name'] ?? null) : ($kdrama->original_name ?? null);

            // Déterminer le titre principal
            // On veut FR en priorité, puis EN, puis OR
            $displayTitle = $frName;
            if (empty($displayTitle)) {
                $displayTitle = $enName ?? $originalName;
            }
        @endphp

        <h3 class="font-bold text-slate-100 group-hover:text-red-400 transition line-clamp-2">
            {{ $displayTitle }}
        </h3>

        <div class="flex flex-col gap-0.5 mt-1">
            @if(!empty($enName) && $enName !== $displayTitle)
                <p class="text-xs text-slate-500 italic line-clamp-1">EN: {{ $enName }}</p>
            @endif

            @if(!empty($originalName) && $originalName !== $displayTitle && $originalName !== $enName)
                <p class="text-xs text-slate-500 line-clamp-1">OR: {{ $originalName }}</p>
            @endif
        </div>
    </div>
</a>
