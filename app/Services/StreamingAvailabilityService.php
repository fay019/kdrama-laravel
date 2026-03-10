<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Models\StreamingAvailability;
use App\Models\Setting;

class StreamingAvailabilityService
{
    private $apiKey;
    private $host = 'streaming-availability.p.rapidapi.com';

    public function __construct()
    {
        $this->apiKey = config('services.rapidapi.key');
    }

    /**
     * Récupère la disponibilité en streaming pour un contenu spécifique.
     * Utilise le cache en base de données (24h) avant d'appeler RapidAPI.
     */
    public function getAvailability($tmdbId, $type = 'tv', $region = 'fr', $title = null, $forceRefresh = false)
    {
        // Normalisation du type pour l'API TMDB (tv/ ou movie/)
        // L'API Streaming Availability semble utiliser tv/ pour les séries
        $apiType = ($type === 'series' || $type === 'tv') ? 'tv' : $type;

        if (!$this->apiKey) {
            \Log::warning("RapidAPI key not configured");
            return null;
        }

        // 1. Chercher d'abord en base de données (Notre "Cache" persistant)
        $record = StreamingAvailability::where('tmdb_id', $tmdbId)
            ->where('type', $type)
            ->where('region', $region)
            ->first();

        // Récupérer le cache duration depuis les settings (défaut: 24 heures)
        $cacheHours = (int) Setting::get('rapidapi_cache_hours', 24);
        $cacheExpiry = now()->subHours($cacheHours);

        // Si on a un record et qu'il date de moins de X heures, on l'utilise (sauf si forceRefresh)
        if ($record && $record->last_updated_at && $record->last_updated_at->gt($cacheExpiry) && !$forceRefresh) {
            return $this->enrichWithSearchLinks($record->data, $title);
        }

        // 2. Si pas en BD ou expiré, appeler l'API
        try {
            // Tentative 1: Utilisation du titre (plus fiable pour cette version de l'API)
            if (!empty($title)) {
                $response = Http::withHeaders([
                    'x-rapidapi-key' => $this->apiKey,
                    'x-rapidapi-host' => $this->host
                ])->timeout(15)->get("https://{$this->host}/shows/search/title", [
                    'title' => $title,
                    'country' => $region,
                    'output_language' => 'en'
                ]);

                if ($response->successful()) {
                    $results = $response->json();
                    if (!empty($results)) {
                        // On cherche le résultat qui correspond au TMDB ID (tv/ID, series/ID ou juste ID)
                        $foundShow = null;

                        foreach ($results as $show) {
                            $showTmdbId = $show['tmdbId'] ?? null;
                            if (!$showTmdbId) continue;

                            if ($showTmdbId === "{$apiType}/{$tmdbId}" ||
                                $showTmdbId === "series/{$tmdbId}" ||
                                $showTmdbId === (string)$tmdbId) {
                                $foundShow = $show;
                                break;
                            }
                        }

                        // Si on ne trouve pas exactement le TMDB ID, ON NE PREND PAS le premier résultat
                        // car cela cause des faux positifs (ex: "La mission de Miss Hong" -> "Miss Detective")
                        if ($foundShow) {
                            $parsedData = $this->parseResponse($foundShow, $region, $title);
                            return $this->saveAndReturn($tmdbId, $type, $region, $parsedData);
                        } else {
                            \Log::info("StreamingAvailabilityService: No exact TMDB match found for ID {$tmdbId} (type {$apiType}) in search results for '$title'");
                        }
                    }
                }
            }

            // Tentative 2: Fallback sur /shows/get (si le titre a échoué ou n'est pas fourni)
            $response = Http::withHeaders([
                'x-rapidapi-key' => $this->apiKey,
                'x-rapidapi-host' => $this->host
            ])->timeout(15)->get("https://{$this->host}/shows/get", [
                'tmdb_id' => "tv/{$tmdbId}", // Essai avec tv/ d'abord
                'country' => $region,
                'output_language' => 'en'
            ]);

            if (!$response->successful()) {
                // Essai avec series/ si tv/ a échoué
                $response = Http::withHeaders([
                    'x-rapidapi-key' => $this->apiKey,
                    'x-rapidapi-host' => $this->host
                ])->timeout(15)->get("https://{$this->host}/shows/get", [
                    'tmdb_id' => "series/{$tmdbId}",
                    'country' => $region,
                    'output_language' => 'en'
                ]);
            }

            if ($response->successful()) {
                $data = $response->json();
                $parsedData = $this->parseResponse($data, $region, $title);
                return $this->saveAndReturn($tmdbId, $type, $region, $parsedData);
            }

            // Si tout échoue
            \Log::warning("RapidAPI failed for TMDB {$tmdbId}: " . $response->status());

            // Fallback : si l'API échoue mais qu'on a de vieilles données en BD, on les renvoie
            if ($record) {
                return $this->enrichWithSearchLinks($record->data, $title);
            }
            return null;

        } catch (\Exception $e) {
            \Log::error("RapidAPI exception for TMDB {$tmdbId}: " . $e->getMessage());
            // Secours : renvoyer les vieilles données si disponibles
            return $record ? $this->enrichWithSearchLinks($record->data, $title) : null;
        }
    }

    /**
     * Enregistre les données en BD et les retourne.
     */
    private function saveAndReturn($tmdbId, $type, $region, $parsedData)
    {
        StreamingAvailability::updateOrCreate(
            [
                'tmdb_id' => $tmdbId,
                'type' => $type,
                'region' => $region,
            ],
            [
                'data' => $parsedData,
                'last_updated_at' => now(),
            ]
        );

        return $parsedData;
    }

    /**
     * Analyse la réponse de l'API pour extraire les options de streaming.
     */
    private function parseResponse($data, $region, $title = null)
    {
        $platforms = [];

        // Structure attendue dans la nouvelle version de l'API :
        // $data['streamingOptions'][$region] ou $data['result']['streamingOptions'][$region]
        $options = $data['streamingOptions'][$region]
                ?? $data['result']['streamingOptions'][$region]
                ?? null;

        if (!$options) {
            return $platforms;
        }

        foreach ($options as $serviceDetails) {
            // Le format des services peut varier selon les versions, on s'adapte
            $serviceName = $serviceDetails['service']['id'] ?? $serviceDetails['service'] ?? 'unknown';

            $platforms[] = [
                'service' => $serviceName,
                'type' => $serviceDetails['type'] ?? 'subscription',
                'link' => $serviceDetails['link'] ?? null,
                'price' => $serviceDetails['price']['amount'] ?? null,
                'currency' => $serviceDetails['price']['currency'] ?? null,
                'logo' => $this->getServiceLogo($serviceName)
            ];

            $lastIdx = count($platforms) - 1;

            // Normalisation du lien Netflix
            if (strtolower($serviceName) === 'netflix') {
                if (!empty($title)) {
                    // Lien vers la recherche Netflix pour plus de flexibilité (Titre Anglais recommandé)
                    $platforms[$lastIdx]['link'] = "https://www.netflix.com/search?q=" . urlencode($title);
                } elseif (!empty($platforms[$lastIdx]['link'])) {
                    // Fallback : Normalisation du lien existant (capture /title/ ou /watch/)
                    $link = $platforms[$lastIdx]['link'];
                    if (preg_match('/netflix\.com\/.*(?:title|watch)\/(\d+)/', $link, $matches)) {
                        $platforms[$lastIdx]['link'] = "https://www.netflix.com/title/" . $matches[1];
                    }
                }
            }

            // Normalisation du lien Disney+
            if (strtolower($serviceName) === 'disney' && !empty($platforms[$lastIdx]['link'])) {
                $link = $platforms[$lastIdx]['link'];
                if (preg_match('/disneyplus\.com\/.*\/([a-zA-Z0-9-]+)$/', $link, $matches)) {
                    // On conserve le format mais on pourrait le simplifier ici si besoin
                }
            }
        }

        return $platforms;
    }

    /**
     * Permet d'injecter ou corriger les liens de recherche à la volée sur des données existantes
     */
    private function enrichWithSearchLinks($data, $title)
    {
        if (empty($data) || !is_array($data) || empty($title)) {
            return $data;
        }

        foreach ($data as &$platform) {
            if (isset($platform['service']) && $platform['service'] === 'netflix') {
                $platform['link'] = "https://www.netflix.com/search?q=" . urlencode($title);
            }
        }

        return $data;
    }

    private function getServiceLogo($service)
    {
        $logos = [
            'netflix' => 'https://upload.wikimedia.org/wikipedia/commons/0/08/Netflix_2015_logo.svg',
            'disney' => 'https://upload.wikimedia.org/wikipedia/commons/3/3e/Disney%2B_logo.svg',
            'apple' => 'https://upload.wikimedia.org/wikipedia/commons/2/28/Apple_TV_Plus_Logo.svg',
            'prime' => 'https://upload.wikimedia.org/wikipedia/commons/1/11/Amazon_Prime_Video_logo.svg',
            'hulu' => 'https://upload.wikimedia.org/wikipedia/commons/e/e4/Hulu_Logo.svg',
            'hbo' => 'https://upload.wikimedia.org/wikipedia/commons/1/17/HBO_Max_Logo.svg',
            'paramount' => 'https://upload.wikimedia.org/wikipedia/commons/a/a5/Paramount_Plus_logo.svg',
            'viki' => 'https://upload.wikimedia.org/wikipedia/commons/2/2e/Rakuten_Viki_Logo.svg',
        ];

        return $logos[strtolower($service)] ?? null;
    }
}
