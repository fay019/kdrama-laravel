<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', config('app.name', 'Laravel'))</title>

        <!-- Meta Tags -->
        @php
            try {
                $metadata = \App\Models\SiteMetadata::first();
            } catch (\Exception $e) {
                $metadata = null;
            }
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

            <!-- Favicon -->
            @if($metadata->favicon_path)
                <link rel="icon" type="image/png" href="{{ asset('storage/' . $metadata->favicon_path) }}">
            @endif
        @endif

        <!-- Google AdSense -->
        @php
            // Fetches the AdSense client ID from the database, with a fallback to the .env file.
            try {
                $adsenseClient = \App\Models\Setting::get('adsense_client_id', env('ADSENSE_CLIENT_ID'));
            } catch (\Exception $e) {
                $adsenseClient = env('ADSENSE_CLIENT_ID');
            }
        @endphp
        @if($adsenseClient)
            <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client={{ $adsenseClient }}"
                    crossorigin="anonymous"></script>
        @endif

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Castoro+Titling&display=swap" rel="stylesheet">

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
                'watchlist_confirm_delete_item' => __('watchlist.js.confirm_delete_item'),
                'watchlist_confirm_title' => __('watchlist.js.confirm_title'),
                'watchlist_confirm_cancel' => __('watchlist.js.confirm_cancel'),
                'watchlist_confirm_delete_btn' => __('watchlist.js.confirm_delete_btn'),
                'watchlist_action_done' => __('watchlist.js.action_done'),
                'watchlist_badge_watched' => __('watchlist.js.badge_watched'),
                'watchlist_badge_watching' => __('watchlist.js.badge_watching'),
                'watchlist_badge_to_watch' => __('watchlist.js.badge_to_watch'),
                'btn_list' => __('watchlist.btn_list'),
                'btn_watching' => __('watchlist.btn_watching'),
                'btn_watched' => __('watchlist.btn_watched'),
                'removed_from_watchlist_suffix' => __('watchlist.removed_from_watchlist_suffix'),
                'removed_from_watching_suffix' => __('watchlist.removed_from_watching_suffix'),
                'removed_from_watched_suffix' => __('watchlist.removed_from_watched_suffix'),
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
