<?php

namespace App\Helpers;

use App\Models\Setting;

class ApiConfig
{
    public static function get($key, $default = null)
    {
        $priority = Setting::get('api_source_priority', 'env_first');

        if ($priority === 'env_first') {
            return env(strtoupper($key)) ?? Setting::get($key, $default);
        } else {
            return Setting::get($key) ?? env(strtoupper($key), $default);
        }
    }
}
