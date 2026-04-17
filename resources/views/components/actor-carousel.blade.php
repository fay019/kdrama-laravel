@props(['actors' => []])

@if(count($actors) > 0)
<div class="relative w-full overflow-hidden bg-gradient-to-r from-black via-slate-900 to-black py-8">
    <style>
        @keyframes scroll {
            0% {
                transform: translateX(0);
            }
            100% {
                transform: translateX(-100%);
            }
        }

        .actor-carousel {
            display: flex;
            animation: scroll 30s linear infinite;
            gap: 1rem;
        }

        .actor-carousel:hover {
            animation-play-state: paused;
        }

        .actor-carousel-item {
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
        }

        .actor-carousel-item img {
            width: 80px;
            height: 80px;
        }

        @media (min-width: 640px) {
            .actor-carousel-item img {
                width: 100px;
                height: 100px;
            }
        }

        @media (min-width: 1024px) {
            .actor-carousel-item img {
                width: 120px;
                height: 120px;
            }
        }
    </style>

    <!-- Gradient overlays for smooth edges -->
    <div class="absolute left-0 top-0 bottom-0 w-20 bg-gradient-to-r from-black to-transparent z-10 pointer-events-none"></div>
    <div class="absolute right-0 top-0 bottom-0 w-20 bg-gradient-to-l from-black to-transparent z-10 pointer-events-none"></div>

    <!-- Carousel Container -->
    <div class="flex gap-4 px-4">
        <div class="actor-carousel">
            @foreach(array_merge($actors, $actors) as $actor)
                <div class="actor-carousel-item group cursor-pointer" onclick="openActorModal({{ $actor['tmdb_id'] }})">
                    <!-- Avatar -->
                    @if(!empty($actor['profile_path']))
                        <img
                            src="https://image.tmdb.org/t/p/w200{{ $actor['profile_path'] }}"
                            alt="{{ $actor['name'] }}"
                            class="rounded-full object-cover border-2 border-slate-600 shadow-lg group-hover:border-red-500 group-hover:scale-110 transition-all duration-300"
                            loading="lazy"
                            decoding="async"
                        >
                    @else
                        <div class="w-20 sm:w-24 lg:w-28 h-20 sm:h-24 lg:h-28 rounded-full bg-gradient-to-br from-slate-700 via-slate-800 to-slate-900 border-2 border-slate-600 shadow-lg flex items-center justify-center group-hover:border-red-500 group-hover:scale-110 transition-all duration-300">
                            <span class="text-2xl sm:text-3xl opacity-60">👤</span>
                        </div>
                    @endif

                    <!-- Name -->
                    <p class="text-xs sm:text-sm font-semibold text-slate-300 group-hover:text-white text-center line-clamp-2 transition-colors max-w-16 sm:max-w-20 lg:max-w-28">
                        {{ $actor['name'] }}
                    </p>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endif
