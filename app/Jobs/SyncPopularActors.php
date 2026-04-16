<?php

namespace App\Jobs;

use App\Models\Actor;
use App\Models\JobHistory;
use App\Services\TmdbService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SyncPopularActors implements ShouldQueue
{
    use Queueable;

    public function __construct()
    {
        //
    }

    public function handle(TmdbService $tmdbService): void
    {
        // Increase timeout for long-running job (scanning 100 pages of TMDB)
        set_time_limit(300);

        $jobStartTime = now();
        Log::channel('jobs')->info('Starting Korean drama actors sync job');

        try {
            $allActors = [];
            $processedDramas = 0;
            $maxPages = 100; // Scan 100 pages of Korean dramas (~2000 dramas)

            // Discover Korean dramas and extract all actors
            for ($p = 1; $p <= $maxPages; $p++) {
                $response = \Illuminate\Support\Facades\Http::timeout(15)->get('https://api.themoviedb.org/3/discover/tv', [
                    'api_key' => config('services.tmdb.api_key'),
                    'language' => 'en-US',
                    'with_origin_country' => 'KR',
                    'include_adult' => false,
                    'page' => $p,
                    'sort_by' => 'popularity.desc',
                ]);

                if ($response->failed()) {
                    Log::channel('jobs')->warning("Failed to fetch page {$p} of Korean dramas");
                    break;
                }

                $data = $response->json();
                if (empty($data['results'])) {
                    break;
                }

                // For each Korean drama, get the cast
                foreach ($data['results'] as $drama) {
                    // Get drama credits (cast)
                    $creditsResponse = \Illuminate\Support\Facades\Http::timeout(15)->get(
                        "https://api.themoviedb.org/3/tv/{$drama['id']}/credits",
                        [
                            'api_key' => config('services.tmdb.api_key'),
                            'language' => 'en-US',
                        ]
                    );

                    if ($creditsResponse->successful()) {
                        $credits = $creditsResponse->json();
                        if (!empty($credits['cast'])) {
                            // Extract actors from this drama
                            foreach ($credits['cast'] as $actor) {
                                if (!isset($allActors[$actor['id']])) {
                                    $allActors[$actor['id']] = [
                                        'id' => $actor['id'],
                                        'name' => $actor['name'],
                                        'profile_path' => $actor['profile_path'] ?? null,
                                        'popularity' => $actor['popularity'] ?? 0,
                                        'drama_count' => 0,
                                    ];
                                }
                                $allActors[$actor['id']]['drama_count']++;
                            }
                        }
                    }

                    $processedDramas++;
                }

                Log::channel('jobs')->info("Processed {$processedDramas} dramas, found ".count($allActors)." unique actors");
            }

            Log::channel('jobs')->info("Total unique actors extracted: ".count($allActors));

            // Sync all actors to database in efficient batches using upsert
            $synced = 0;
            $batchSize = 100; // Small batches for memory efficiency (5568 actors ÷ 100 = ~56 queries)
            $now = now();

            $actorsBatch = array_chunk($allActors, $batchSize, true);
            foreach ($actorsBatch as $batchIndex => $batch) {
                try {
                    // Prepare upsert data
                    $upsertData = [];
                    foreach ($batch as $actorData) {
                        $upsertData[] = [
                            'tmdb_id' => $actorData['id'],
                            'name' => $actorData['name'],
                            'profile_path' => $actorData['profile_path'],
                            'popularity' => $actorData['popularity'],
                            'tv_credits_count' => $actorData['drama_count'],
                            'last_synced_at' => $now,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }

                    // Use upsert for better performance
                    Actor::upsert($upsertData, ['tmdb_id'], ['name', 'profile_path', 'popularity', 'tv_credits_count', 'last_synced_at', 'updated_at']);

                    $synced += count($batch);
                    Log::channel('jobs')->info("Synced {$synced} actors so far (batch ".($batchIndex + 1)." of ".count($actorsBatch).")");
                } catch (\Exception $e) {
                    Log::channel('jobs')->error("Batch ".($batchIndex + 1)." failed: ".$e->getMessage()." in file ".$e->getFile()." line ".$e->getLine());
                    throw $e;
                }
            }

            Log::channel('jobs')->info("Synced {$synced} actors from Korean dramas to database", ['count' => $synced]);

            Log::channel('jobs')->info('Actor sync completed successfully');

            // Record job completion in history
            JobHistory::create([
                'job_class' => self::class,
                'queue' => 'default',
                'payload' => json_encode(['displayName' => 'App\Jobs\SyncPopularActors']),
                'attempts' => 1,
                'output' => "Processed {$processedDramas} dramas, synced {$synced} actors",
                'status' => 'completed',
                'exception' => null,
                'duration_seconds' => now()->diffInSeconds($jobStartTime),
                'metadata' => [
                    'dramas_processed' => $processedDramas,
                    'actors_synced' => $synced,
                    'total_unique_actors_found' => count($allActors),
                ],
                'started_at' => $jobStartTime,
                'completed_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::channel('jobs')->error('Actor sync failed: '.$e->getMessage());

            // Record job failure in history
            JobHistory::create([
                'job_class' => self::class,
                'queue' => 'default',
                'payload' => json_encode(['displayName' => 'App\Jobs\SyncPopularActors']),
                'attempts' => 1,
                'output' => null,
                'status' => 'failed',
                'exception' => $e->getMessage(),
                'duration_seconds' => now()->diffInSeconds($jobStartTime),
                'metadata' => null,
                'started_at' => $jobStartTime,
                'completed_at' => now(),
            ]);

            throw $e;
        }
    }
}
