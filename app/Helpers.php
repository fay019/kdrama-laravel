<?php

use App\Helpers\ApiKeyHelper;

if (! function_exists('maskApiKey')) {
    /**
     * Mask API key showing only last 4 characters
     *
     * @param  string  $apiKey  The API key to mask
     * @return string Masked API key
     */
    function maskApiKey($apiKey)
    {
        return ApiKeyHelper::maskApiKey($apiKey);
    }
}
