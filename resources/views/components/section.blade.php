@props(['title', 'icon' => null, 'subtitle' => null])

<div class="space-y-6">
    <!-- Section Header -->
    <div class="space-y-1">
        <div class="flex items-center gap-3">
            @if($icon)
                <span class="text-2xl sm:text-3xl">{{ $icon }}</span>
            @endif
            <h2 class="text-xl sm:text-2xl lg:text-3xl font-bold text-slate-100 dark:text-white tracking-tight">
                {!! $title !!}
            </h2>
        </div>
        @if($subtitle)
            <p class="text-slate-400 dark:text-slate-300 text-xs sm:text-sm ml-0 sm:ml-10">
                {{ $subtitle }}
            </p>
        @endif
    </div>

    <!-- Slot Content -->
    <div>
        {{ $slot }}
    </div>
</div>
