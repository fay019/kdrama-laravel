<div class="flex flex-col md:flex-row gap-6">
    <!-- Photo & Infos de base -->
    <div class="w-full md:w-1/3 flex flex-col items-center">
        @if($actor['profile_path'])
            <img src="https://image.tmdb.org/t/p/w500{{ $actor['profile_path'] }}"
                 alt="{{ $actor['name'] }}"
                 class="w-48 h-64 md:w-full md:h-auto rounded-xl object-cover shadow-lg border-2 border-slate-700">
        @else
            <div class="w-48 h-64 md:w-full md:h-auto rounded-xl bg-slate-800 flex items-center justify-center border-2 border-slate-700">
                <span class="text-6xl">👤</span>
            </div>
        @endif

        <div class="mt-4 w-full space-y-3">
            <div class="bg-slate-800/50 p-3 rounded-lg border border-slate-700">
                <p class="text-slate-400 text-xs uppercase tracking-wider font-bold">Nom Réel</p>
                <p class="text-white font-medium">{{ $actor['latin_name'] ?? $actor['name'] }}</p>
                @if(($actor['original_name'] ?? $actor['name']) !== ($actor['latin_name'] ?? $actor['name']))
                    <p class="text-slate-400 text-sm">{{ $actor['original_name'] ?? $actor['name'] }}</p>
                @endif
            </div>

            @if($actor['birthday'])
                <div class="bg-slate-800/50 p-3 rounded-lg border border-slate-700">
                    <p class="text-slate-400 text-xs uppercase tracking-wider font-bold">Date de naissance</p>
                    <p class="text-white font-medium">
                        {{ \Carbon\Carbon::parse($actor['birthday'])->format('d/m/Y') }}
                        @if(!$actor['deathday'])
                            <span class="text-slate-500 text-sm">({{ \Carbon\Carbon::parse($actor['birthday'])->age }} ans)</span>
                        @endif
                    </p>
                </div>
            @endif

            @if($actor['place_of_birth'])
                <div class="bg-slate-800/50 p-3 rounded-lg border border-slate-700">
                    <p class="text-slate-400 text-xs uppercase tracking-wider font-bold">Lieu de naissance</p>
                    <p class="text-white font-medium text-sm">{{ $actor['place_of_birth'] }}</p>
                </div>
            @endif

            @if(isset($actor['external_ids']['instagram_id']) && $actor['external_ids']['instagram_id'])
                <a href="https://instagram.com/{{ $actor['external_ids']['instagram_id'] }}" target="_blank" class="flex items-center justify-center gap-2 w-full py-2 bg-gradient-to-r from-purple-600 to-pink-600 rounded-lg font-bold hover:opacity-90 transition">
                    <span>📸</span> Instagram
                </a>
            @endif
        </div>
    </div>

    <!-- Biographie & Travaux -->
    <div class="w-full md:w-2/3">
        <h3 class="text-2xl font-bold mb-4 text-red-500">Biographie</h3>
        <div class="text-slate-300 text-sm leading-relaxed max-h-64 overflow-y-auto pr-2 custom-scrollbar">
            @if($actor['biography'])
                {{ $actor['biography'] }}
            @else
                <p class="italic text-slate-500">Aucune biographie disponible pour le moment.</p>
            @endif
        </div>

        @if(isset($actor['combined_credits']['cast']) && count($actor['combined_credits']['cast']) > 0)
            <h3 class="text-xl font-bold mt-8 mb-4">Derniers Projets</h3>
            <div class="grid grid-cols-3 sm:grid-cols-4 gap-3">
                @php
                    $projects = collect($actor['combined_credits']['cast'])
                        ->sortByDesc('popularity')
                        ->take(8);
                @endphp
                @foreach($projects as $project)
                    <div class="flex flex-col gap-1">
                        <div class="aspect-[2/3] bg-slate-800 rounded overflow-hidden">
                            @if($project['poster_path'])
                                <img src="https://image.tmdb.org/t/p/w185{{ $project['poster_path'] }}"
                                     alt="{{ $project['name'] ?? $project['title'] }}"
                                     class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-xs text-slate-600 text-center p-1">
                                    {{ $project['name'] ?? $project['title'] }}
                                </div>
                            @endif
                        </div>
                        <p class="text-[10px] text-slate-300 truncate font-medium">
                            {{ $project['name'] ?? $project['title'] }}
                        </p>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="mt-8 flex justify-end">
            <a href="{{ route('kdrams.catalog', ['actor_id' => $actor['id'], 'actor' => $actor['latin_name'] ?? $actor['name']]) }}" class="btn-primary py-2 px-6 text-sm">
                🔍 Voir tous ses K-Dramas
            </a>
        </div>
    </div>
</div>
