<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CheckSetup
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip check for setup routes
        if ($request->routeIs('setup.*')) {
            return $next($request);
        }

        // Check if setup is needed
        if ($this->isSetupNeeded()) {
            return redirect()->route('setup.index');
        }

        return $next($request);
    }

    private function isSetupNeeded(): bool
    {
        // Use a marker file to determine if setup is complete
        if (file_exists(storage_path('.setup-complete'))) {
            return false;
        }

        try {
            // Fallback check: database connection and users
            return User::count() === 0;
        } catch (\Exception $e) {
            return true;
        }
    }
}
