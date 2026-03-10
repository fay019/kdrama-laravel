<?php

namespace Tests\Feature;

use App\Services\TmdbService;
use App\Services\StreamingAvailabilityService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ApiCacheTest extends TestCase
{
    public function test_tmdb_service_caches_responses()
    {
        Cache::flush();
        Http::fake([
            'api.themoviedb.org/*' => Http::response(['results' => [['id' => 1, 'name' => 'Test TV']]], 200),
        ]);

        $service = new TmdbService();

        // Premier appel - devrait appeler l'API
        $service->discoverAsianContent();
        Http::assertSentCount(1);

        // Deuxième appel - devrait utiliser le cache
        $service->discoverAsianContent();
        Http::assertSentCount(1);
    }

    public function test_streaming_availability_service_caches_responses()
    {
        Cache::flush();
        Http::fake([
            'streaming-availability.p.rapidapi.com/*' => Http::response([
                'streamingOptions' => [
                    'fr' => [
                        'netflix' => [
                            'type' => 'subscription',
                            'link' => 'https://netflix.com'
                        ]
                    ]
                ]
            ], 200),
        ]);

        $service = new StreamingAvailabilityService();

        // Premier appel
        $service->getAvailability(123);
        Http::assertSentCount(1);

        // Deuxième appel - devrait utiliser le cache
        $service->getAvailability(123);
        Http::assertSentCount(1);
    }
}
