@props(['actor'])

<div class="content-card group cursor-pointer flex flex-col items-center gap-3" onclick="openActorModal({{ $actor['id'] }})">
    <!-- Circular Photo -->
    <div class="flex-shrink-0" style="width: 160px; height: 160px; border-radius: 50%;">
        @if(!empty($actor['profile_path']))
            <img src="https://image.tmdb.org/t/p/w300{{ $actor['profile_path'] }}"
                 alt="{{ $actor['name'] }}"
                 class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                 style="width: 160px; height: 160px; border-radius: 50%; display: block;">
        @else
            <div class="flex items-center justify-center text-slate-600 bg-slate-900" style="width: 160px; height: 160px; border-radius: 50%;">
                <svg class="w-16 h-16" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                </svg>
            </div>
        @endif
    </div>

    <!-- Text Below Photo -->
    <div class="text-center w-full px-2">
        <h3 class="text-white font-bold text-sm sm:text-base leading-tight group-hover:text-red-400 transition-colors line-clamp-2">
            {{ $actor['name'] }}
        </h3>

        @if(!empty($actor['known_for']))
            <p class="text-slate-400 text-xs mt-2 line-clamp-2">
                {{ __('catalog.known_for') }}:
                @foreach(array_slice($actor['known_for'], 0, 2) as $work)
                    {{ $work['name'] ?? $work['title'] }}{{ !$loop->last ? ',' : '' }}
                @endforeach
            </p>
        @endif
    </div>
</div>
