<?php

namespace App\Helpers;

class ApiKeyHelper
{
    /**
     * Mask API key showing only last 4 characters
     *
     * @param  string  $apiKey  The API key to mask
     * @return string Masked API key (e.g., "••••••••••••••xyz1")
     */
    public static function maskApiKey($apiKey)
    {
        if (empty($apiKey)) {
            return '';
        }

        $length = strlen($apiKey);
        if ($length <= 4) {
            return str_repeat('•', $length);
        }

        $lastFour = substr($apiKey, -4);
        $masked = str_repeat('•', $length - 4).$lastFour;

        return $masked;
    }
}
