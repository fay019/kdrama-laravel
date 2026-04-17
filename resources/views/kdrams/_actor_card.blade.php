@props(['actor'])

<div class="group cursor-pointer flex flex-col items-center gap-4 p-4 rounded-2xl bg-slate-900/50 dark:bg-slate-950/50 border border-slate-800 hover:border-red-500/50 transition-all duration-300 transform hover:scale-105" onclick="openActorModal({{ $actor['id'] }})">
    <!-- Circular Photo Container -->
    <div class="relative flex-shrink-0 w-40 h-40 rounded-full overflow-hidden ring-2 ring-slate-700 group-hover:ring-red-500/50 transition-all duration-300 shadow-lg">
        @if(!empty($actor['profile_path']))
            <img src="https://image.tmdb.org/t/p/w300{{ $actor['profile_path'] }}"
                 srcset="https://image.tmdb.org/t/p/w300{{ $actor['profile_path'] }} 1x, https://image.tmdb.org/t/p/w342{{ $actor['profile_path'] }} 2x"
                 alt="{{ $actor['name'] }}"
                 class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                 loading="lazy"
                 decoding="async">
        @else
            <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-slate-800 to-slate-900 dark:from-slate-900 dark:to-black">
                <svg class="w-24 h-24 text-slate-600 dark:text-slate-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                </svg>
            </div>
        @endif
    </div>

    <!-- Text Below Photo -->
    <div class="text-center w-full px-2 space-y-2">
        <h3 class="text-white font-bold text-sm sm:text-base leading-tight group-hover:text-red-400 transition-colors line-clamp-2">
            {{ $actor['name'] }}
        </h3>

        @if(!empty($actor['known_for']))
            <p class="text-slate-400 dark:text-slate-500 text-xs line-clamp-2">
                <span class="text-slate-500">{{ __('catalog.known_for') }}:</span>
                @foreach(array_slice($actor['known_for'], 0, 2) as $work)
                    <span class="text-slate-300">{{ $work['name'] ?? $work['title'] }}</span>{{ !$loop->last ? '<span class="text-slate-600">,</span>' : '' }}
                @endforeach
            </p>
        @endif
    </div>
</div>
