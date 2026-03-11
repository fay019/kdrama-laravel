<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

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

            <!-- Favicon -->
            @if($metadata->favicon_path)
                <link rel="icon" type="image/png" href="{{ asset('storage/' . $metadata->favicon_path) }}">
            @endif
        @endif

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-white antialiased">
        <div class="min-h-screen flex flex-col">
            <!-- Navigation -->
            @include('layouts.navigation')

            <!-- Main Content -->
            <div class="flex-1 flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-slate-800">
                <div>
                    <a href="/">
                        <x-application-logo class="w-20 h-20 fill-current text-white" />
                    </a>
                </div>

                <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-slate-800 shadow-md overflow-hidden sm:rounded-lg">
                    {{ $slot }}
                </div>
            </div>

            <!-- Footer -->
            <x-footer />
        </div>
    </body>
</html>
