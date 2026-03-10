<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class TmdbService
{
    private $apiKey;
    private $baseUrl = 'https://api.themoviedb.org/3';

    public function __construct()
    {
        $this->apiKey = config('services.tmdb.api_key');
    }

    public function discoverAsianContent(array $filters = [])
    {
        $cacheKey = 'tmdb_discover_v2_' . md5(json_encode($filters));

        return Cache::remember($cacheKey, now()->addDay(), function () use ($filters) {
            $languages = ['fr-FR', 'en-US'];
            $allResults = [];
            $totalPages = 0;
            $totalResults = 0;

            foreach ($languages as $lang) {
                $params = [
                    'api_key' => $this->apiKey,
                    'language' => $lang,
                    'with_origin_country' => implode('|', $filters['origins'] ?? ['KR']),
                    'with_genres' => $filters['genres'] ?? '18',
                    'sort_by' => $filters['sort'] ?? 'popularity.desc',
                    'page' => $filters['page'] ?? 1,
                    'vote_average.gte' => $filters['min_rating'] ?? 0,
                ];

                if (!empty($filters['from_year'])) {
                    $params['first_air_date.gte'] = "{$filters['from_year']}-01-01";
                }

                if (!empty($filters['to_year'])) {
                    $params['first_air_date.lte'] = "{$filters['to_year']}-12-31";
                }

                if (!empty($filters['with_cast'])) {
                    $params['with_cast'] = $filters['with_cast'];
                }

                try {
                    $response = Http::timeout(15)->get("{$this->baseUrl}/discover/tv", $params);
                    if ($response->successful()) {
                        $data = $response->json();
                        if (isset($data['results'])) {
                            foreach ($data['results'] as $item) {
                                $id = $item['id'];
                                if (!isset($allResults[$id])) {
                                    $allResults[$id] = $item;
                                } else {
                                    if ($lang === 'fr-FR' && !empty($item['name'])) {
                                        $allResults[$id]['name'] = $item['name'];
                                    }
                                    if ($lang === 'en-US' && !empty($item['name'])) {
                                        $allResults[$id]['en_name'] = $item['name'];
                                    }
                                }
                            }
                        }
                        $totalPages = max($totalPages, $data['total_pages'] ?? 0);
                        $totalResults = max($totalResults, $data['total_results'] ?? 0);
                    }
                } catch (\Exception $e) {
                    \Log::error("TMDB API Discover Error ($lang): " . $e->getMessage());
                }
            }

            return [
                'results' => array_values($allResults),
                'total_pages' => (int) $totalPages,
                'total_results' => (int) $totalResults,
                'page' => (int) ($filters['page'] ?? 1)
            ];
        });
    }

    public function getContentDetails($tmdbId, $type = 'tv')
    {
        $cacheKey = "tmdb_details_multi_lang_{$type}_{$tmdbId}";

        return Cache::remember($cacheKey, now()->addWeek(), function () use ($tmdbId, $type) {
            try {
                $languages = ['fr-FR', 'en-US', 'de-DE'];
                $data = [];

                foreach ($languages as $lang) {
                    $response = Http::timeout(15)->get("{$this->baseUrl}/{$type}/{$tmdbId}", [
                        'api_key' => $this->apiKey,
                        'language' => $lang,
                        'append_to_response' => 'credits,keywords,external_ids,similar,production_companies,networks'
                    ]);

                    if ($response->successful()) {
                        $data[$lang] = $response->json();
                    }
                }

                if (empty($data)) {
                    return null;
                }

                // Utiliser FR comme base
                $base = $data['fr-FR'] ?? reset($data);
                $base['translations'] = [];

                foreach ($data as $lang => $content) {
                    $langCode = explode('-', $lang)[0]; // fr, en, de
                    $base['translations'][$langCode] = [
                        'name' => $content['name'] ?? null,
                        'overview' => $content['overview'] ?? null,
                        'tagline' => $content['tagline'] ?? null,
                    ];
                }

                // Enrichir les noms des acteurs (latin_name) si nécessaire
                if (isset($base['credits']['cast'])) {
                    foreach ($base['credits']['cast'] as &$actor) {
                        $latin = $this->getPersonLatinName($actor['id']);
                        if ($latin) {
                            $actor['latin_name'] = $latin;
                        }
                    }
                }

                return $base;
            } catch (\Exception $e) {
                \Log::error("TMDB API Multi-lang Error: " . $e->getMessage());
                return null;
            }
        });
    }

    private function getPersonLatinName($personId)
    {
        $cacheKey = "tmdb_person_latin_name_{$personId}";

        return Cache::remember($cacheKey, now()->addWeek(), function () use ($personId) {
            try {
                $resp = Http::timeout(10)->get("{$this->baseUrl}/person/{$personId}", [
                    'api_key' => $this->apiKey,
                    'language' => 'en-US'
                ]);
                if ($resp->failed()) {
                    return null;
                }
                $data = $resp->json();
                // Le champ 'name' en en-US est généralement en alphabet latin
                return $data['name'] ?? null;
            } catch (\Exception $e) {
                \Log::warning("TMDB person fetch failed for {$personId}: " . $e->getMessage());
                return null;
            }
        });
    }

    public function searchContent($query, $page = 1)
    {
        $limit = 20;
        $offset = ($page - 1) * $limit;
        $cacheKey = "tmdb_search_tv_compact_v8_" . md5($query);

        $allKrData = Cache::remember($cacheKey, now()->addHour(), function () use ($query) {
            try {
                $languages = ['fr-FR', 'en-US'];

                // Phase 1: Déterminer le total de pages via le premier appel FR
                $firstResp = Http::timeout(15)->get("{$this->baseUrl}/search/tv", [
                    'api_key' => $this->apiKey,
                    'language' => 'fr-FR',
                    'query' => $query,
                    'page' => 1,
                    'include_adult' => false
                ]);

                if (!$firstResp->successful()) return [];

                $data = $firstResp->json();
                $apiTotalPages = (int) ($data['total_pages'] ?? 0);
                if ($apiTotalPages === 0) return [];

                // Phase 2: Tout scanner (max 15 pages) pour avoir une base stable
                $maxApiPagesToScan = min($apiTotalPages, 15);
                $uniqueKrIds = [];
                $idToData = [];

                for ($p = 1; $p <= $maxApiPagesToScan; $p++) {
                    foreach ($languages as $lang) {
                        $resp = Http::timeout(15)->get("{$this->baseUrl}/search/tv", [
                            'api_key' => $this->apiKey,
                            'language' => $lang,
                            'query' => $query,
                            'page' => $p,
                            'include_adult' => false
                        ]);

                        if ($resp->successful()) {
                            $d = $resp->json();
                            foreach (($d['results'] ?? []) as $item) {
                                if (isset($item['origin_country']) && in_array('KR', $item['origin_country'])) {
                                    $id = $item['id'];
                                    if (!isset($uniqueKrIds[$id])) {
                                        $uniqueKrIds[$id] = true;
                                        $idToData[$id] = $item;
                                        if ($lang === 'en-US') {
                                            $idToData[$id]['en_name'] = $item['name'];
                                        }
                                    } else {
                                        if ($lang === 'fr-FR') {
                                            $idToData[$id]['name'] = $item['name'];
                                        }
                                        if ($lang === 'en-US') {
                                            $idToData[$id]['en_name'] = $item['name'];
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                return array_values($idToData);
            } catch (\Exception $e) {
                \Log::error("TMDB searchContent Scan failed: " . $e->getMessage());
                return [];
            }
        });

        $totalFound = count($allKrData);
        $paginatedResults = array_slice($allKrData, $offset, $limit);
        $results = [];

        foreach ($paginatedResults as $show) {
            $id = $show['id'];
            // Enrichissement FR/EN si manquant
            if (empty($show['name']) || (isset($show['en_name']) && $show['name'] === $show['en_name'])) {
                $details = $this->getShowSimpleDetails($id, 'fr-FR');
                if ($details && !empty($details['name'])) {
                    $show['name'] = $details['name'];
                }
            }
            if (empty($show['en_name'])) {
                $details = $this->getShowSimpleDetails($id, 'en-US');
                if ($details && !empty($details['name'])) {
                    $show['en_name'] = $details['name'];
                }
            }
            $results[] = $show;
        }

        $finalTotalPages = (int) ceil($totalFound / $limit);

        return [
            'results' => $results,
            'total_pages' => max(1, $finalTotalPages),
            'total_results' => $totalFound,
            'page' => (int) $page
        ];
    }

    private function getShowSimpleDetails($id, $lang)
    {
        $cacheKey = "tmdb_show_simple_{$id}_{$lang}";
        return Cache::remember($cacheKey, now()->addWeek(), function () use ($id, $lang) {
            try {
                $response = Http::timeout(10)->get("{$this->baseUrl}/tv/{$id}", [
                    'api_key' => $this->apiKey,
                    'language' => $lang
                ]);
                return $response->successful() ? $response->json() : null;
            } catch (\Exception $e) {
                return null;
            }
        });
    }

    public function getPersonTvCredits($personId, $page = 1)
    {
        $cacheKey = "tmdb_person_tv_credits_v3_{$personId}_p{$page}";

        return Cache::remember($cacheKey, now()->addDay(), function () use ($personId, $page) {
            try {
                $languages = ['fr-FR', 'en-US'];
                $allCredits = [];

                foreach ($languages as $lang) {
                    $response = Http::timeout(15)->get("{$this->baseUrl}/person/{$personId}/tv_credits", [
                        'api_key' => $this->apiKey,
                        'language' => $lang
                    ]);

                    if ($response->successful()) {
                        $data = $response->json();
                        if (isset($data['cast'])) {
                            foreach ($data['cast'] as $item) {
                                // Filtrer pour ne garder que les contenus coréens (KR)
                                if (isset($item['origin_country']) && in_array('KR', $item['origin_country'])) {
                                    $id = $item['id'];
                                    if (!isset($allCredits[$id])) {
                                        $allCredits[$id] = $item;
                                        if ($lang === 'fr-FR') {
                                            $allCredits[$id]['name'] = $item['name'];
                                        } else {
                                            $allCredits[$id]['en_name'] = $item['name'];
                                        }
                                    } else {
                                        if ($lang === 'fr-FR' && !empty($item['name'])) {
                                            $allCredits[$id]['name'] = $item['name'];
                                        }
                                        if ($lang === 'en-US' && !empty($item['name'])) {
                                            $allCredits[$id]['en_name'] = $item['name'];
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                if (!empty($allCredits)) {
                    // Enrichissement si manquant
                    foreach ($allCredits as $id => &$show) {
                        if (empty($show['name']) || (isset($show['en_name']) && $show['name'] === $show['en_name'])) {
                            $details = $this->getShowSimpleDetails($id, 'fr-FR');
                            if ($details && !empty($details['name'])) {
                                $show['name'] = $details['name'];
                            }
                        }
                        if (empty($show['en_name'])) {
                            $details = $this->getShowSimpleDetails($id, 'en-US');
                            if ($details && !empty($details['name'])) {
                                $show['en_name'] = $details['name'];
                            }
                        }
                    }

                    $results = array_values($allCredits);

                    // Trier par popularité par défaut
                    usort($results, function($a, $b) {
                        return ($b['popularity'] ?? 0) <=> ($a['popularity'] ?? 0);
                    });

                    $total_results = count($results);
                    $per_page = 20;
                    $total_pages = ceil($total_results / $per_page);

                    // Paginer manuellement
                    $offset = ($page - 1) * $per_page;
                    $paginated_results = array_slice($results, $offset, $per_page);

                    return [
                        'results' => $paginated_results,
                        'total_results' => $total_results,
                        'total_pages' => (int) $total_pages,
                        'page' => (int) $page
                    ];
                }

                return ['results' => [], 'total_pages' => 0];
            } catch (\Exception $e) {
                \Log::error("TMDB API Error (tv_credits multi-lang): " . $e->getMessage());
                return null;
            }
        });
    }

    public function getPersonDetails($personId)
    {
        $cacheKey = "tmdb_person_details_v1_{$personId}";

        return Cache::remember($cacheKey, now()->addWeek(), function () use ($personId) {
            try {
                $languages = ['fr-FR', 'en-US'];
                $data = [];

                foreach ($languages as $lang) {
                    $response = Http::timeout(15)->get("{$this->baseUrl}/person/{$personId}", [
                        'api_key' => $this->apiKey,
                        'language' => $lang,
                        'append_to_response' => 'external_ids,combined_credits'
                    ]);

                    if ($response->successful()) {
                        $data[$lang] = $response->json();
                    }
                }

                if (empty($data)) {
                    return null;
                }

                // Base FR
                $base = $data['fr-FR'] ?? reset($data);

                // Si la bio FR est vide, prendre l'anglaise
                if (empty($base['biography']) && isset($data['en-US']['biography'])) {
                    $base['biography'] = $data['en-US']['biography'];
                }

                // Garder le nom latin (EN)
                if (isset($data['en-US']['name'])) {
                    $base['latin_name'] = $data['en-US']['name'];
                }

                return $base;
            } catch (\Exception $e) {
                \Log::error("TMDB API Person Details Error: " . $e->getMessage());
                return null;
            }
        });
    }

    public function searchPerson($query)
    {
        $cacheKey = "tmdb_search_person_v2_" . md5($query);

        return Cache::remember($cacheKey, now()->addDay(), function () use ($query) {
            try {
                // On cherche en anglais pour avoir plus de chance de trouver le nom latin (ex: "Yoona")
                // car en FR, TMDB garde parfois uniquement le nom coréen pour certains acteurs.
                $response = Http::timeout(15)->get("{$this->baseUrl}/search/person", [
                    'api_key' => $this->apiKey,
                    'language' => 'en-US',
                    'query' => $query,
                    'include_adult' => false
                ]);

                if ($response->failed()) {
                    return null;
                }

                return $response->json();
            } catch (\Exception $e) {
                \Log::error("TMDB API Error (searchPerson): " . $e->getMessage());
                return null;
            }
        });
    }
}
