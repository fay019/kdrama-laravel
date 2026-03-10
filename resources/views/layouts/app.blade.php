<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', config('app.name', 'Laravel'))</title>

        <!-- Meta Tags -->
        @php
            $metadata = \App\Models\SiteMetadata::first();
        @endphp
        @if($metadata)
            <meta name="description" content="{{ $metadata->meta_description ?? config('app.name') }}">
            @if($metadata->meta_keywords)
                <meta name="keywords" content="{{ $metadata->meta_keywords }}">
            @endif

            <!-- Open Graph Tags -->
            <meta property="og:type" content="{{ $metadata->og_type ?? 'website' }}">
            <meta property="og:title" content="{{ $metadata->og_title ?? config('app.name') }}">
            @if($metadata->og_description)
                <meta property="og:description" content="{{ $metadata->og_description }}">
            @endif
            @if($metadata->og_image)
                <meta property="og:image" content="{{ asset('storage/' . $metadata->og_image) }}">
                <meta property="og:image:type" content="image/png">
            @endif
        @endif

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- i18n for JavaScript -->
        @php
            $i18n = [
                'watchlist_error_modify' => __('watchlist.js.error_modify'),
                'watchlist_error_delete' => __('watchlist.js.error_delete'),
                'watchlist_error_rating' => __('watchlist.js.error_rating'),
                'watchlist_error_connection' => __('watchlist.js.error_connection'),
                'watchlist_confirm_delete' => __('watchlist.js.confirm_delete'),
                'watchlist_confirm_delete_short' => __('watchlist.js.confirm_delete_short'),
                'watchlist_action_done' => __('watchlist.js.action_done'),
                'watchlist_badge_watched' => __('watchlist.js.badge_watched'),
                'watchlist_badge_to_watch' => __('watchlist.js.badge_to_watch'),
            ];
        @endphp
        <script>
            window.i18n = @json($i18n);
        </script>
    </head>
    <body class="font-sans antialiased overflow-x-hidden">
        <div class="min-h-screen w-full bg-slate-800">
            <!-- Navigation (hidden on admin pages) -->
            @if(!request()->routeIs('admin.*'))
                @include('layouts.navigation')
            @endif

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-slate-800 shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                @yield('content', $slot ?? '')
            </main>

            <!-- Footer (hidden on admin pages) -->
            @if(!request()->routeIs('admin.*'))
                <x-footer />
            @endif
        </div>
    </body>
</html>
