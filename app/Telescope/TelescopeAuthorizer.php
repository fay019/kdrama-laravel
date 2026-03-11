<?php

namespace App\Telescope;

use Illuminate\Http\Request;

class TelescopeAuthorizer
{
    /**
     * Authorize access to Telescope.
     */
    public function __invoke(Request $request): bool
    {
        return $request->user() && $request->user()->is_admin === true;
    }
}