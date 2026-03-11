<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPasswordMustChange
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip password change routes (both standard Breeze and custom mandatory change)
        if ($request->routeIs('password.change', 'password.update', 'change-password.update')) {
            return $next($request);
        }

        // Check if user must change password
        if (auth()->check() && auth()->user()->password_must_change) {
            return redirect()->route('password.change')
                ->with('warning', __('auth.password_must_change_notification'));
        }

        return $next($request);
    }
}