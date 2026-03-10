<?php

namespace Tests\Feature;

use App\Services\TmdbService;
use App\Services\StreamingAvailabilityService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ApiServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    /**
     * Teste que TmdbService gère correctement les réponses réussies.
     */
    public function test_tmdb_service_returns_correct_data_on_success()
    {
        Http::fake([
            'api.themoviedb.org/3/discover/tv*' => Http::response([
                'results' => [
                    ['id' => 123, 'name' => 'Squid Game']
                ]
            ], 200),
        ]);

        $service = new TmdbService();
        $result = $service->discoverAsianContent();

        $this->assertNotNull($result);
        $this->assertArrayHasKey('results', $result);
        $this->assertEquals(123, $result['results'][0]['id']);
        $this->assertEquals('Squid Game', $result['results'][0]['name']);
    }

    /**
     * Teste que TmdbService gère les erreurs API (ex: 500).
     */
    public function test_tmdb_service_returns_null_on_error()
    {
        Http::fake([
            'api.themoviedb.org/3/*' => Http::response(['status_message' => 'Internal Server Error'], 500),
        ]);

        $service = new TmdbService();
        $result = $service->getContentDetails(123);

        $this->assertNull($result);
    }

    /**
     * Teste que StreamingAvailabilityService parse correctement les données de streaming.
     */
    public function test_streaming_availability_service_parses_correctly()
    {
        Http::fake([
            'streaming-availability.p.rapidapi.com/get*' => Http::response([
                'streamingOptions' => [
                    'fr' => [
                        'netflix' => [
                            [
                                'type' => 'subscription',
                                'link' => 'https://netflix.com/title/123'
                            ]
                        ]
                    ]
                ]
            ], 200),
        ]);

        $service = new StreamingAvailabilityService();
        $result = $service->getAvailability(123, 'tv', 'fr');

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals('netflix', $result[0]['service']);
        $this->assertEquals('https://netflix.com/title/123', $result[0]['link']);
    }

    /**
     * Teste que StreamingAvailabilityService gère les régions manquantes.
     */
    public function test_streaming_availability_service_returns_empty_array_if_no_region_data()
    {
        Http::fake([
            'streaming-availability.p.rapidapi.com/get*' => Http::response([
                'streamingOptions' => [
                    'us' => [
                        'hulu' => []
                    ]
                ]
            ], 200),
        ]);

        $service = new StreamingAvailabilityService();
        $result = $service->getAvailability(123, 'tv', 'fr'); // On demande 'fr' mais 'us' est retourné

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * Teste la gestion des timeouts.
     */
    public function test_api_services_handle_timeouts()
    {
        Http::fake([
            '*' => function () {
                throw new \Illuminate\Http\Client\ConnectionException("Connection timed out", 0);
            },
        ]);

        $tmdb = new TmdbService();
        $this->assertNull($tmdb->searchContent('test'));

        $streaming = new StreamingAvailabilityService();
        $this->assertNull($streaming->getAvailability(123));
    }
}
