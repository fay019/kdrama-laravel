<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Locale priority: user preference → session → default (fr)
        $locale = 'fr';

        // 1. Check user preference (if authenticated)
        if (auth()->check() && auth()->user()->preferred_language) {
            $locale = auth()->user()->preferred_language;
        }
        // 2. Check session
        elseif (session()->has('locale')) {
            $locale = session('locale');
        }

        // Validate locale is supported
        $supportedLocales = ['fr', 'en', 'de'];
        if (!in_array($locale, $supportedLocales)) {
            $locale = 'fr';
        }

        // Set application locale
        App::setLocale($locale);

        return $next($request);
    }
}
