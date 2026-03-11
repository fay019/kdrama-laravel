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
        $user = auth()->user();

        if (! $user?->is_admin) {
            dd([
                'user' => $user?->email,
                'is_admin' => $user?->is_admin,
                'blocked' => true
            ]);
            abort(403, 'Unauthorized access to Telescope');
        }

        return $next($request);
    }
}