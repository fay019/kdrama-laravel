<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

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
        $cacheKey = 'tmdb_discover_v4_'.md5(json_encode($filters)); // v4: Added adult content filter

        return Cache::remember($cacheKey, now()->addDay(), function () use ($filters) {
            $languages = ['fr-FR', 'en-US'];
            $allResults = [];
            $totalPages = 0;
            $totalResults = 0;

            // Discover both TV shows and movies
            $mediaTypes = ['tv', 'movie'];

            foreach ($mediaTypes as $mediaType) {
                foreach ($languages as $lang) {
                    $params = [
                        'api_key' => $this->apiKey,
                        'language' => $lang,
                        'with_origin_country' => implode('|', $filters['origins'] ?? ['KR']),
                        'with_genres' => $filters['genres'] ?? '18',
                        'sort_by' => $filters['sort'] ?? 'popularity.desc',
                        'page' => $filters['page'] ?? 1,
                        'vote_average.gte' => $filters['min_rating'] ?? 0,
                        'include_adult' => false,
                    ];

                    // Different date parameters for TV vs Movies
                    if ($mediaType === 'tv') {
                        if (! empty($filters['from_year'])) {
                            $params['first_air_date.gte'] = "{$filters['from_year']}-01-01";
                        }
                        if (! empty($filters['to_year'])) {
                            $params['first_air_date.lte'] = "{$filters['to_year']}-12-31";
                        }
                    } else {
                        if (! empty($filters['from_year'])) {
                            $params['primary_release_date.gte'] = "{$filters['from_year']}-01-01";
                        }
                        if (! empty($filters['to_year'])) {
                            $params['primary_release_date.lte'] = "{$filters['to_year']}-12-31";
                        }
                    }

                    if (! empty($filters['with_cast'])) {
                        $params['with_cast'] = $filters['with_cast'];
                    }

                    try {
                        $endpoint = "/discover/{$mediaType}";
                        $response = Http::timeout(15)->get("{$this->baseUrl}{$endpoint}", $params);

                        if ($response->successful()) {
                            $data = $response->json();
                            if (isset($data['results'])) {
                                foreach ($data['results'] as $item) {
                                    // Skip adult content
                                    if ($item['adult'] ?? false) {
                                        continue;
                                    }

                                    $id = $item['id'];
                                    // Add media_type to distinguish TV from Movies
                                    $item['media_type'] = $mediaType;

                                    if (! isset($allResults[$id])) {
                                        $allResults[$id] = $item;
                                    } else {
                                        if ($lang === 'fr-FR' && ! empty($item['name'] ?? $item['title'])) {
                                            $allResults[$id]['name'] = $item['name'] ?? $item['title'];
                                        }
                                        if ($lang === 'en-US' && ! empty($item['name'] ?? $item['title'])) {
                                            $allResults[$id]['en_name'] = $item['name'] ?? $item['title'];
                                        }
                                    }
                                }
                            }
                            $totalPages = max($totalPages, $data['total_pages'] ?? 0);
                            $totalResults = max($totalResults, $data['total_results'] ?? 0);
                        }
                    } catch (\Exception $e) {
                        \Log::error("TMDB API Discover Error ($mediaType/$lang): ".$e->getMessage());
                    }
                }
            }

            // Sort by popularity descending
            usort($allResults, function ($a, $b) {
                return ($b['popularity'] ?? 0) <=> ($a['popularity'] ?? 0);
            });

            return [
                'results' => array_values($allResults),
                'total_pages' => (int) $totalPages,
                'total_results' => (int) $totalResults,
                'page' => (int) ($filters['page'] ?? 1),
            ];
        });
    }

    public function getContentDetails($tmdbId, $type = 'tv')
    {
        $cacheKey = "tmdb_details_multi_lang_v2_{$type}_{$tmdbId}"; // v2: Added adult filter + title handling

        return Cache::remember($cacheKey, now()->addWeek(), function () use ($tmdbId, $type) {
            try {
                $languages = ['fr-FR', 'en-US', 'de-DE'];
                $data = [];

                foreach ($languages as $lang) {
                    $params = [
                        'api_key' => $this->apiKey,
                        'language' => $lang,
                        'append_to_response' => 'credits,keywords,external_ids,similar,production_companies,networks',
                    ];

                    // Add adult filter for movies
                    if ($type === 'movie') {
                        $params['include_adult'] = false;
                    }

                    $response = Http::timeout(15)->get("{$this->baseUrl}/{$type}/{$tmdbId}", $params);

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

                // Normalize base data: ensure 'name' field exists (movies use 'title')
                if (! isset($base['name']) && isset($base['title'])) {
                    $base['name'] = $base['title'];
                }

                foreach ($data as $lang => $content) {
                    $langCode = explode('-', $lang)[0]; // fr, en, de
                    // Handle both 'name' (TV) and 'title' (movies) fields
                    $contentName = $content['name'] ?? $content['title'] ?? null;
                    $base['translations'][$langCode] = [
                        'name' => $contentName,
                        'title' => $contentName,  // Add title for compatibility
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
                \Log::error('TMDB API Multi-lang Error: '.$e->getMessage());

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
                    'language' => 'en-US',
                ]);
                if ($resp->failed()) {
                    return null;
                }
                $data = $resp->json();

                // Le champ 'name' en en-US est généralement en alphabet latin
                return $data['name'] ?? null;
            } catch (\Exception $e) {
                \Log::warning("TMDB person fetch failed for {$personId}: ".$e->getMessage());

                return null;
            }
        });
    }

    public function searchContent($query, $page = 1)
    {
        $limit = 20;
        $offset = ($page - 1) * $limit;
        $cacheKey = 'tmdb_search_content_v15_'.md5($query); // v15: Added adult content filter

        $allKrData = Cache::remember($cacheKey, now()->addHours(12), function () use ($query) {
            try {
                $languages = ['fr-FR', 'en-US'];
                $idToData = [];
                $mediaTypes = ['tv', 'movie']; // Search both TV and Movies

                // Optimization: Scan max 5 pages instead of 20 (80% of results found in first 5)
                for ($p = 1; $p <= 5; $p++) {
                    $foundInThisPage = false;
                    foreach ($mediaTypes as $mediaType) {
                        foreach ($languages as $lang) {
                            $endpoint = "/search/{$mediaType}";
                            $response = Http::timeout(10)->get("{$this->baseUrl}{$endpoint}", [
                                'api_key' => $this->apiKey,
                                'language' => $lang,
                                'query' => $query,
                                'page' => $p,
                                'include_adult' => false,
                            ]);

                            if ($response->successful()) {
                                $data = $response->json();
                                if (! empty($data['results'])) {
                                    $foundInThisPage = true;
                                    foreach ($data['results'] as $item) {
                                        // Skip adult content
                                        if ($item['adult'] ?? false) {
                                            continue;
                                        }

                                        // Check if it's Korean content
                                        $isKorean = false;
                                        if (isset($item['origin_country']) && in_array('KR', $item['origin_country'])) {
                                            $isKorean = true;
                                        }

                                        if ($isKorean) {
                                            $id = $item['id'];
                                            // Add media_type to distinguish TV from Movies
                                            $item['media_type'] = $mediaType;

                                            if (! isset($idToData[$id])) {
                                                $idToData[$id] = $item;
                                                if ($lang === 'en-US') {
                                                    $idToData[$id]['en_name'] = $item['name'] ?? $item['title'];
                                                }
                                            } else {
                                                if ($lang === 'fr-FR' && ! empty($item['name'] ?? $item['title'])) {
                                                    $idToData[$id]['name'] = $item['name'] ?? $item['title'];
                                                }
                                                if ($lang === 'en-US' && ! empty($item['name'] ?? $item['title'])) {
                                                    $idToData[$id]['en_name'] = $item['name'] ?? $item['title'];
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    // Early stopping if no Korean content found
                    if (! $foundInThisPage && $p > 1) {
                        break;
                    }
                }

                // Sort by popularity descending
                usort($idToData, function ($a, $b) {
                    return ($b['popularity'] ?? 0) <=> ($a['popularity'] ?? 0);
                });

                return array_values($idToData);
            } catch (\Exception $e) {
                \Log::error('TMDB searchContent Scan failed: '.$e->getMessage());

                return [];
            }
        });

        $totalFound = count($allKrData);
        $paginatedResults = array_slice($allKrData, $offset, $limit);

        // Batch-load missing names instead of individual calls per result
        $idsNeedingEnrichment = [];
        foreach ($paginatedResults as &$show) {
            $id = $show['id'];
            if (empty($show['name']) || (isset($show['en_name']) && $show['name'] === $show['en_name'])) {
                $idsNeedingEnrichment[$id][] = 'fr';
            }
            if (empty($show['en_name'])) {
                $idsNeedingEnrichment[$id][] = 'en';
            }
        }

        // Batch-fetch details for missing names
        foreach ($idsNeedingEnrichment as $id => $languages) {
            foreach ($languages as $lang) {
                $langCode = $lang === 'en' ? 'en-US' : 'fr-FR';
                $details = $this->getShowSimpleDetails($id, $langCode);
                if ($details && ! empty($details['name'])) {
                    foreach ($paginatedResults as &$show) {
                        if ($show['id'] === $id) {
                            if ($lang === 'fr' && empty($show['name'])) {
                                $show['name'] = $details['name'];
                            } elseif ($lang === 'en' && empty($show['en_name'])) {
                                $show['en_name'] = $details['name'];
                            }
                        }
                    }
                }
            }
        }

        return [
            'results' => $paginatedResults,
            'total_pages' => (int) ceil($totalFound / $limit),
            'total_results' => $totalFound,
            'page' => (int) $page,
        ];
    }

    private function getShowSimpleDetails($id, $lang)
    {
        $cacheKey = "tmdb_show_simple_v2_{$id}_{$lang}"; // v2: Added adult filter + movie fallback

        return Cache::remember($cacheKey, now()->addWeek(), function () use ($id, $lang) {
            try {
                // Try TV first, then fallback to movie
                $response = Http::timeout(10)->get("{$this->baseUrl}/tv/{$id}", [
                    'api_key' => $this->apiKey,
                    'language' => $lang,
                    'include_adult' => false,
                ]);

                if ($response->successful()) {
                    return $response->json();
                }

                // Fallback to movie if TV not found
                $response = Http::timeout(10)->get("{$this->baseUrl}/movie/{$id}", [
                    'api_key' => $this->apiKey,
                    'language' => $lang,
                    'include_adult' => false,
                ]);

                return $response->successful() ? $response->json() : null;
            } catch (\Exception $e) {
                return null;
            }
        });
    }

    public function getPersonTvCredits($personId, $page = 1)
    {
        $cacheKey = "tmdb_person_credits_v9_{$personId}_p{$page}"; // Incremented version

        return Cache::remember($cacheKey, now()->addDay(), function () use ($personId, $page) {
            try {
                $languages = ['fr-FR', 'en-US'];
                $allCredits = [];
                $creditTypes = ['tv_credits', 'movie_credits']; // Get both TV and Movies

                foreach ($creditTypes as $creditType) {
                    foreach ($languages as $lang) {
                        $response = Http::timeout(15)->get("{$this->baseUrl}/person/{$personId}/{$creditType}", [
                            'api_key' => $this->apiKey,
                            'language' => $lang,
                            'page' => $page,
                            'include_adult' => false,
                        ]);

                        if ($response->successful()) {
                            $data = $response->json();
                            if (isset($data['cast'])) {
                                foreach ($data['cast'] as $item) {
                                    // Filter to exclude adult content only
                                    if (! ($item['adult'] ?? false)) {
                                        $id = $item['id'];
                                        // Add media_type to distinguish TV from Movies
                                        $mediaType = $creditType === 'tv_credits' ? 'tv' : 'movie';
                                        // TV uses 'name', Movies use 'title'
                                        $itemName = $item['name'] ?? $item['title'] ?? null;

                                        if (! isset($allCredits[$id])) {
                                            $allCredits[$id] = $item;
                                            $allCredits[$id]['media_type'] = $mediaType;
                                            if ($lang === 'fr-FR') {
                                                $allCredits[$id]['name'] = $itemName;
                                            } else {
                                                $allCredits[$id]['en_name'] = $itemName;
                                            }
                                        } else {
                                            if ($lang === 'fr-FR' && ! empty($itemName)) {
                                                $allCredits[$id]['name'] = $itemName;
                                            }
                                            if ($lang === 'en-US' && ! empty($itemName)) {
                                                $allCredits[$id]['en_name'] = $itemName;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                if (! empty($allCredits)) {
                    // Enrichissement si manquant
                    foreach ($allCredits as $id => &$show) {
                        if (empty($show['name']) || (isset($show['en_name']) && $show['name'] === $show['en_name'])) {
                            $details = $this->getShowSimpleDetails($id, 'fr-FR');
                            if ($details) {
                                $detailName = $details['name'] ?? $details['title'] ?? null;
                                if (! empty($detailName)) {
                                    $show['name'] = $detailName;
                                }
                            }
                        }
                        if (empty($show['en_name'])) {
                            $details = $this->getShowSimpleDetails($id, 'en-US');
                            if ($details) {
                                $detailName = $details['name'] ?? $details['title'] ?? null;
                                if (! empty($detailName)) {
                                    $show['en_name'] = $detailName;
                                }
                            }
                        }
                    }

                    $results = array_values($allCredits);

                    // Trier par popularité par défaut
                    usort($results, function ($a, $b) {
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
                        'page' => (int) $page,
                    ];
                }

                return ['results' => [], 'total_pages' => 0];
            } catch (\Exception $e) {
                \Log::error('TMDB API Error (tv_credits multi-lang): '.$e->getMessage());

                return null;
            }
        });
    }

    public function getPersonDetails($personId)
    {
        $cacheKey = "tmdb_person_details_v3_{$personId}";

        return Cache::remember($cacheKey, now()->addWeek(), function () use ($personId) {
            try {
                $languages = ['fr-FR', 'en-US'];
                $data = [];

                foreach ($languages as $lang) {
                    $response = Http::timeout(15)->get("{$this->baseUrl}/person/{$personId}", [
                        'api_key' => $this->apiKey,
                        'language' => $lang,
                        'append_to_response' => 'external_ids,combined_credits',
                        'include_adult' => false,
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

                // Keep only essential data in combined_credits to save DB space
                if (isset($base['combined_credits']['cast'])) {
                    // Sort by popularity descending so best-known shows appear first
                    usort($base['combined_credits']['cast'], function ($a, $b) {
                        return ($b['popularity'] ?? 0) <=> ($a['popularity'] ?? 0);
                    });

                    // Keep only id, name, poster_path
                    $base['combined_credits']['cast'] = array_map(function ($item) {
                        return [
                            'id' => $item['id'] ?? null,
                            'name' => $item['name'] ?? $item['title'] ?? null,
                            'poster_path' => $item['poster_path'] ?? null,
                        ];
                    }, $base['combined_credits']['cast']);
                }

                return $base;
            } catch (\Exception $e) {
                \Log::error('TMDB API Person Details Error: '.$e->getMessage());

                return null;
            }
        });
    }

    public function searchPerson($query, $page = 1, $exactMatch = false, $hasPhoto = false, $hasWorks = true)
    {
        $page = (int) $page;
        $cacheKey = 'tmdb_search_person_v14_'.md5($query).'_p'.$page.'_e'.($exactMatch ? '1' : '0').'_hp'.($hasPhoto ? '1' : '0').'_hw'.($hasWorks ? '1' : '0'); // v14: Filter adult content in known_for

        return Cache::remember($cacheKey, now()->addDay(), function () use ($query, $page, $exactMatch, $hasPhoto, $hasWorks) {
            try {
                $allResults = [];
                $targetCount = 20;

                // On normalise la requête pour la recherche TMDB
                $searchQuery = $query;

                // Cas spécial pour "IU" qui est souvent cherchée en minuscule "ui"
                // On va chercher à la fois le terme original et "IU" si c'est "ui"
                $isUiQuery = mb_strtolower($query) === 'ui';

                $isShortQuery = mb_strlen($searchQuery) <= 3;
                // Reduce pages scanned for faster live search (while DB is being populated)
                $maxPagesToScan = ($isShortQuery || $exactMatch) ? 30 : 15;

                $currentTmdbPage = 1;
                $queriesToTry = [$searchQuery];
                if ($isUiQuery && mb_strtolower($searchQuery) !== 'iu') {
                    $queriesToTry[] = 'IU';
                }

                foreach ($queriesToTry as $q) {
                    $currentTmdbPage = 1;
                    while ($currentTmdbPage <= $maxPagesToScan) {
                        $response = Http::timeout(15)->get("{$this->baseUrl}/search/person", [
                            'api_key' => $this->apiKey,
                            'language' => 'en-US',
                            'query' => $q,
                            'include_adult' => false,
                            'page' => $currentTmdbPage,
                        ]);

                        if ($response->failed()) {
                            break;
                        }

                        $data = $response->json();
                        if (empty($data['results'])) {
                            break;
                        }

                        foreach ($data['results'] as $person) {
                            if ($this->isPersonKoreanActor($person)) {
                                $isExact = mb_strtolower($person['name']) === mb_strtolower($q) ||
                                          ($isUiQuery && mb_strtolower($person['name']) === 'iu');

                                if ($exactMatch && ! $isExact) {
                                    continue;
                                }

                                if ($hasPhoto && empty($person['profile_path'])) {
                                    continue;
                                }

                                // Check if actor has TV credits when hasWorks filter is enabled
                                if ($hasWorks) {
                                    $credits = $this->getPersonTvCredits($person['id']);
                                    if (! $credits || empty($credits['results'])) {
                                        continue;
                                    }
                                }

                                if (! collect($allResults)->contains('id', $person['id'])) {
                                    // Priorité aux correspondances exactes
                                    if ($isExact) {
                                        array_unshift($allResults, $person);
                                    } else {
                                        $allResults[] = $person;
                                    }
                                }
                            }
                        }

                        if ($currentTmdbPage >= ($data['total_pages'] ?? 0)) {
                            break;
                        }

                        $currentTmdbPage++;

                        // On s'arrête si on a assez pour la page demandée + de la réserve
                        if (count($allResults) >= ($page * $targetCount) + 40) {
                            break;
                        }
                    }
                }

                $totalFound = count($allResults);
                $offset = ($page - 1) * $targetCount;
                $paginatedResults = array_slice($allResults, $offset, $targetCount);

                $totalPages = (int) ceil($totalFound / $targetCount);
                if (count($allResults) > ($page * $targetCount)) {
                    $totalPages = max($totalPages, $page + 1);
                }

                return [
                    'results' => $paginatedResults,
                    'total_results' => $totalFound,
                    'total_pages' => max(1, $totalPages),
                    'page' => (int) $page,
                ];
            } catch (\Exception $e) {
                \Log::error('TMDB API Error (searchPerson): '.$e->getMessage());

                return null;
            }
        });
    }

    public function getPopularActors($page = 1, $hasPhoto = false, $hasWorks = true)
    {
        $page = (int) $page;
        $cacheKey = "tmdb_popular_actors_v13_p{$page}_hp".($hasPhoto ? '1' : '0').'_hw'.($hasWorks ? '1' : '0'); // v13: Filter adult content in known_for

        return Cache::remember($cacheKey, now()->addHours(6), function () use ($page, $hasPhoto, $hasWorks) {
            try {
                $allResults = [];
                $targetCount = 20;

                // Optimized scanning: Balance speed vs result count
                // With filter: Need more pages since many actors get filtered out
                // Without filter: Can get good results with fewer pages
                $maxPages = $hasWorks ? 25 : 40;

                for ($p = 1; $p <= $maxPages; $p++) {
                    $response = Http::timeout(15)->get("{$this->baseUrl}/person/popular", [
                        'api_key' => $this->apiKey,
                        'language' => 'en-US',
                        'include_adult' => false,
                        'page' => $p,
                    ]);

                    if ($response->successful()) {
                        $pageData = $response->json();
                        foreach ($pageData['results'] as $person) {
                            if ($this->isPersonKoreanActor($person)) {
                                if ($hasPhoto && empty($person['profile_path'])) {
                                    continue;
                                }

                                // Check if actor has TV credits when hasWorks filter is enabled
                                if ($hasWorks) {
                                    $credits = $this->getPersonTvCredits($person['id']);
                                    if (! $credits || empty($credits['results'])) {
                                        continue;
                                    }
                                }

                                if (! collect($allResults)->contains('id', $person['id'])) {
                                    $allResults[] = $person;
                                }
                            }
                        }
                    } else {
                        break;
                    }

                    // Early stopping: if we have enough buffer for requested page, stop scanning
                    $requiredBuffer = $hasWorks ? 100 : 200;
                    if (count($allResults) >= ($page * $targetCount) + $requiredBuffer) {
                        break;
                    }
                }

                $totalFound = count($allResults);
                $offset = ($page - 1) * $targetCount;
                $paginatedResults = array_slice($allResults, $offset, $targetCount);

                $totalPages = (int) ceil($totalFound / $targetCount);

                // If we filled current page and have more results in reserve, next page exists
                if (count($allResults) > ($page * $targetCount)) {
                    $totalPages = max($totalPages, $page + 1);
                }

                return [
                    'results' => $paginatedResults,
                    'total_results' => $totalFound,
                    'total_pages' => max(1, $totalPages),
                    'page' => (int) $page,
                ];
            } catch (\Exception $e) {
                \Log::error('TMDB API Error (getPopularActors): '.$e->getMessage());

                return null;
            }
        });
    }

    /**
     * Vérifie si un acteur est coréen ou travaille principalement dans des dramas coréens.
     */
    public function isPersonKoreanActor(array $person): bool
    {
        // 1. Vérifier le pays d'origine dans 'known_for'
        if (isset($person['known_for']) && is_array($person['known_for'])) {
            $hasKoreanWork = false;

            foreach ($person['known_for'] as $work) {
                // Exclure si le contenu est adulte
                if ($work['adult'] ?? false) {
                    continue; // Skip adult content
                }

                // Vérifie le pays d'origine (Corée du Sud)
                if (isset($work['origin_country']) && in_array('KR', $work['origin_country'])) {
                    $hasKoreanWork = true;
                    break;
                }
                // Vérifie la langue originale (Coréen)
                if (isset($work['original_language']) && $work['original_language'] === 'ko') {
                    $hasKoreanWork = true;
                    break;
                }
            }

            if ($hasKoreanWork) {
                return true;
            }
        }

        // 2. Si non trouvé dans 'known_for', TMDB ne nous donne pas beaucoup plus d'infos dans l'objet simple.
        // Mais on peut vérifier si le nom contient des caractères coréens (optionnel, peut-être trop risqué)
        // Pour l'instant on reste sur les métadonnées des œuvres connues.

        return false;
    }
}
