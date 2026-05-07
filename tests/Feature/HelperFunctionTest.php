<?php

namespace Tests\Feature;

use Tests\TestCase;

class HelperFunctionTest extends TestCase
{
    /**
     * Test that the maskApiKey helper function is globally available and works correctly.
     */
    public function test_mask_api_key_helper_is_available(): void
    {
        $this->assertTrue(function_exists('maskApiKey'));

        $apiKey = '1234567890abcdef';
        $masked = maskApiKey($apiKey);

        $this->assertEquals('************cdef', $masked);
        $this->assertStringEndsWith('cdef', $masked);
        $this->assertEquals(strlen($apiKey), strlen($masked));
    }

    /**
     * Test maskApiKey with short key.
     */
    public function test_mask_api_key_with_short_key(): void
    {
        $apiKey = '123';
        $masked = maskApiKey($apiKey);

        $this->assertEquals('***', $masked);
    }

    /**
     * Test maskApiKey with empty string.
     */
    public function test_mask_api_key_with_empty_string(): void
    {
        $this->assertEquals('', maskApiKey(''));
    }
}
