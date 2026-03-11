<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class TelescopeAuthorize
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Only admins can access Telescope
        if (! auth()->user()?->is_admin) {
            abort(403, 'Unauthorized access to Telescope');
        }

        return $next($request);
    }
}