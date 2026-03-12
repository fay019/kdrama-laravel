@extends('layouts.app')

@section('title', __('home.page_title'))

@section('content')
<!-- Hero Section -->
<div class="hero-banner">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h1 class="text-3xl sm:text-5xl font-bold mb-3">
                {!! str_replace('{k-dramas}', '<span class="gradient-text">K-Dramas</span>', __('home.hero_title')) !!}
            </h1>
            <p class="text-sm sm:text-lg text-slate-50 mb-6 max-w-2xl mx-auto leading-relaxed">
                {{ __('home.hero_subtitle') }}
            </p>
            <a href="{{ route('kdrams.catalog') }}" class="btn-primary text-sm sm:text-base py-2 px-6">
                {{ __('home.hero_cta') }}
            </a>
        </div>
    </div>
</div>

<!-- Featured Section -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <h2 class="section-title">
        <span class="section-title-icon">
            @if(isset($isAdminList) && $isAdminList)
                👤
            @else
                🌟
            @endif
        </span>
        <span class="section-title-text">
            @if(isset($isAdminList) && $isAdminList)
                {{ __('home.featured_title_admin') }}
            @else
                {{ __('home.featured_title') }}
            @endif
        </span>
    </h2>

    @if(isset($featured) && count($featured) > 0)
        <div class="content-grid">
            @foreach($featured as $item)
                <a href="{{ route('kdrams.show', $item['id']) }}" class="content-card group fade-in">
                    <div class="content-image">
                        @if(isset($item['poster_path']))
                            <img
                                src="https://image.tmdb.org/t/p/w500{{ $item['poster_path'] }}"
                                alt="{{ $item['name'] ?? $item['title'] }}"
                            >
                        @else
                            <div class="w-full h-full bg-gradient-to-br from-slate-700 to-slate-900 flex items-center justify-center">
                                <span class="text-slate-400 text-center">
                                    <div class="text-3xl mb-2">🎬</div>
                                    {{ __('home.no_image') }}
                                </span>
                            </div>
                        @endif

                        <!-- Overlay with rating -->
                        <div class="absolute top-2 right-2">
                            <span class="badge">⭐ {{ number_format($item['vote_average'] ?? 0, 1) }}/10</span>
                        </div>
                    </div>

                    <div class="p-4">
                        <h3 class="font-bold text-lg text-slate-100 group-hover:text-red-400 transition line-clamp-2">
                            {{ $item['name'] ?? $item['title'] }}
                        </h3>
                    </div>
                </a>
            @endforeach
        </div>
    @else
        <div class="card-dark py-16 text-center">
            <p class="text-slate-300 text-lg">
                <span class="text-3xl mb-4 block">🔑</span>
                {{ __('home.no_content') }} <br>
                {{ __('home.configure_apis') }}
            </p>
            @auth
                @if(auth()->user()->is_admin)
                    <a href="{{ route('admin.settings.index') }}" class="btn-primary mt-6 inline-block">
                        {{ __('home.setup_apis_btn') }}
                    </a>
                @endif
            @endauth
        </div>
    @endif
</div>

<!-- Newest Releases Section -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 border-t border-slate-700">
    <h2 class="section-title">
        <span class="section-title-icon">📺</span>
        <span class="section-title-text">{{ __('home.newest_releases_title') }}</span>
    </h2>

    @if(isset($newest) && count($newest) > 0)
        <div class="content-grid">
            @foreach($newest as $item)
                <a href="{{ route('kdrams.show', $item['id']) }}" class="content-card group fade-in">
                    <div class="content-image">
                        @if($item['poster_path'] ?? false)
                            <img
                                src="https://image.tmdb.org/t/p/w500{{ $item['poster_path'] }}"
                                alt="{{ $item['name'] ?? $item['title'] }}"
                            >
                        @else
                            <div class="w-full h-full bg-gradient-to-br from-slate-700 to-slate-900 flex items-center justify-center">
                                <span class="text-slate-400 text-center">
                                    <div class="text-3xl mb-2">🎬</div>
                                    {{ __('home.no_image') }}
                                </span>
                            </div>
                        @endif

                        <!-- Overlay with rating -->
                        <div class="absolute top-2 right-2">
                            <span class="badge">⭐ {{ number_format($item['vote_average'] ?? 0, 1) }}/10</span>
                        </div>
                    </div>

                    <div class="p-4">
                        <h3 class="font-bold text-lg text-slate-100 group-hover:text-red-400 transition line-clamp-2">
                            {{ $item['name'] ?? $item['title'] }}
                        </h3>
                        @if($item['first_air_date'] ?? false)
                            <p class="text-sm text-slate-400 mt-1">
                                📅 {{ \Carbon\Carbon::parse($item['first_air_date'])->format(__('home.date_format')) }}
                            </p>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>

<!-- Upcoming Releases Section -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 border-t border-slate-700">
    <h2 class="section-title">
        <span class="section-title-icon">📅</span>
        <span class="section-title-text">{{ __('home.upcoming_releases_title') }}</span>
    </h2>

    @if(isset($upcoming) && count($upcoming) > 0)
        <div class="content-grid">
            @foreach($upcoming as $item)
                <a href="{{ route('kdrams.show', $item['id']) }}" class="content-card group fade-in">
                    <div class="content-image">
                        @if($item['poster_path'] ?? false)
                            <img
                                src="https://image.tmdb.org/t/p/w500{{ $item['poster_path'] }}"
                                alt="{{ $item['name'] ?? $item['title'] }}"
                            >
                        @else
                            <div class="w-full h-full bg-gradient-to-br from-slate-700 to-slate-900 flex items-center justify-center">
                                <span class="text-slate-400 text-center">
                                    <div class="text-3xl mb-2">🎬</div>
                                    {{ __('home.no_image') }}
                                </span>
                            </div>
                        @endif

                        <!-- Overlay with rating -->
                        <div class="absolute top-2 right-2">
                            <span class="badge">⭐ {{ number_format($item['vote_average'] ?? 0, 1) }}/10</span>
                        </div>
                    </div>

                    <div class="p-4">
                        <h3 class="font-bold text-lg text-slate-100 group-hover:text-red-400 transition line-clamp-2">
                            {{ $item['name'] ?? $item['title'] }}
                        </h3>
                        @if($item['first_air_date'] ?? false)
                            <p class="text-sm text-slate-400 mt-1">
                                📅 {{ \Carbon\Carbon::parse($item['first_air_date'])->format(__('home.date_format')) }}
                            </p>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>

@endsection
