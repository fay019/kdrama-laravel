@props(['actors' => []])

<div class="flex overflow-x-auto gap-3 sm:gap-4 pb-2 scrollbar-hide" style="scroll-behavior: smooth; scrollbar-width: none;">
    <style>
        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }
    </style>

    @forelse($actors as $actor)
        <div class="relative group flex-shrink-0">
            <!-- Avatar -->
            @if(!empty($actor['profile_path']))
                <img
                    src="https://image.tmdb.org/t/p/w200{{ $actor['profile_path'] }}"
                    alt="{{ $actor['name'] }}"
                    class="w-12 sm:w-14 h-12 sm:h-14 rounded-full object-cover border border-slate-700 shadow-sm group-hover:scale-110 transition-all duration-200 group-hover:z-10 cursor-pointer"
                    loading="lazy"
                    decoding="async"
                    onclick="openActorModal({{ $actor['tmdb_id'] }})"
                >
            @else
                <div class="w-12 sm:w-14 h-12 sm:h-14 rounded-full bg-gradient-to-br from-slate-700 via-slate-800 to-slate-900 border border-slate-700 shadow-sm flex items-center justify-center group-hover:scale-110 transition-all duration-200 group-hover:z-10 cursor-pointer"
                     onclick="openActorModal({{ $actor['tmdb_id'] }})">
                    <span class="text-lg opacity-60">👤</span>
                </div>
            @endif

            <!-- Tooltip -->
            <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-2 py-1 bg-black rounded text-white text-xs font-medium whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none z-20">
                {{ $actor['name'] }}
                <div class="absolute top-full left-1/2 transform -translate-x-1/2 w-1 h-1 bg-black"></div>
            </div>
        </div>
    @empty
        <div class="text-slate-400 text-sm py-4">
            {{ __('home.no_actors') }}
        </div>
    @endforelse
</div>
