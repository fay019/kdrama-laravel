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
        $cacheKey = 'tmdb_discover_v2_'.md5(json_encode($filters));

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
                    'include_adult' => false,
                ];

                if (! empty($filters['from_year'])) {
                    $params['first_air_date.gte'] = "{$filters['from_year']}-01-01";
                }

                if (! empty($filters['to_year'])) {
                    $params['first_air_date.lte'] = "{$filters['to_year']}-12-31";
                }

                if (! empty($filters['with_cast'])) {
                    $params['with_cast'] = $filters['with_cast'];
                }

                try {
                    $response = Http::timeout(15)->get("{$this->baseUrl}/discover/tv", $params);
                    if ($response->successful()) {
                        $data = $response->json();
                        if (isset($data['results'])) {
                            foreach ($data['results'] as $item) {
                                $id = $item['id'];
                                if (! isset($allResults[$id])) {
                                    $allResults[$id] = $item;
                                } else {
                                    if ($lang === 'fr-FR' && ! empty($item['name'])) {
                                        $allResults[$id]['name'] = $item['name'];
                                    }
                                    if ($lang === 'en-US' && ! empty($item['name'])) {
                                        $allResults[$id]['en_name'] = $item['name'];
                                    }
                                }
                            }
                        }
                        $totalPages = max($totalPages, $data['total_pages'] ?? 0);
                        $totalResults = max($totalResults, $data['total_results'] ?? 0);
                    }
                } catch (\Exception $e) {
                    \Log::error("TMDB API Discover Error ($lang): ".$e->getMessage());
                }
            }

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
        $cacheKey = "tmdb_details_multi_lang_{$type}_{$tmdbId}";

        return Cache::remember($cacheKey, now()->addWeek(), function () use ($tmdbId, $type) {
            try {
                $languages = ['fr-FR', 'en-US', 'de-DE'];
                $data = [];

                foreach ($languages as $lang) {
                    $response = Http::timeout(15)->get("{$this->baseUrl}/{$type}/{$tmdbId}", [
                        'api_key' => $this->apiKey,
                        'language' => $lang,
                        'append_to_response' => 'credits,keywords,external_ids,similar,production_companies,networks',
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
        $cacheKey = 'tmdb_search_tv_v13_'.md5($query);

        $allKrData = Cache::remember($cacheKey, now()->addHours(12), function () use ($query) {
            try {
                $languages = ['fr-FR', 'en-US'];
                $idToData = [];

                // Optimization: Scan max 5 pages instead of 20 (80% of results found in first 5)
                for ($p = 1; $p <= 5; $p++) {
                    $foundInThisPage = false;
                    foreach ($languages as $lang) {
                        $response = Http::timeout(10)->get("{$this->baseUrl}/search/tv", [
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
                                    if (isset($item['origin_country']) && in_array('KR', $item['origin_country'])) {
                                        $id = $item['id'];
                                        if (! isset($idToData[$id])) {
                                            $idToData[$id] = $item;
                                            if ($lang === 'en-US') {
                                                $idToData[$id]['en_name'] = $item['name'];
                                            }
                                        } else {
                                            if ($lang === 'fr-FR' && ! empty($item['name'])) {
                                                $idToData[$id]['name'] = $item['name'];
                                            }
                                            if ($lang === 'en-US' && ! empty($item['name'])) {
                                                $idToData[$id]['en_name'] = $item['name'];
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
        $cacheKey = "tmdb_show_simple_{$id}_{$lang}";

        return Cache::remember($cacheKey, now()->addWeek(), function () use ($id, $lang) {
            try {
                $response = Http::timeout(10)->get("{$this->baseUrl}/tv/{$id}", [
                    'api_key' => $this->apiKey,
                    'language' => $lang,
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
                        'language' => $lang,
                    ]);

                    if ($response->successful()) {
                        $data = $response->json();
                        if (isset($data['cast'])) {
                            foreach ($data['cast'] as $item) {
                                // Filtrer pour ne garder que les contenus coréens (KR)
                                if (isset($item['origin_country']) && in_array('KR', $item['origin_country'])) {
                                    $id = $item['id'];
                                    if (! isset($allCredits[$id])) {
                                        $allCredits[$id] = $item;
                                        if ($lang === 'fr-FR') {
                                            $allCredits[$id]['name'] = $item['name'];
                                        } else {
                                            $allCredits[$id]['en_name'] = $item['name'];
                                        }
                                    } else {
                                        if ($lang === 'fr-FR' && ! empty($item['name'])) {
                                            $allCredits[$id]['name'] = $item['name'];
                                        }
                                        if ($lang === 'en-US' && ! empty($item['name'])) {
                                            $allCredits[$id]['en_name'] = $item['name'];
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
                            if ($details && ! empty($details['name'])) {
                                $show['name'] = $details['name'];
                            }
                        }
                        if (empty($show['en_name'])) {
                            $details = $this->getShowSimpleDetails($id, 'en-US');
                            if ($details && ! empty($details['name'])) {
                                $show['en_name'] = $details['name'];
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
        $cacheKey = "tmdb_person_details_v1_{$personId}";

        return Cache::remember($cacheKey, now()->addWeek(), function () use ($personId) {
            try {
                $languages = ['fr-FR', 'en-US'];
                $data = [];

                foreach ($languages as $lang) {
                    $response = Http::timeout(15)->get("{$this->baseUrl}/person/{$personId}", [
                        'api_key' => $this->apiKey,
                        'language' => $lang,
                        'append_to_response' => 'external_ids,combined_credits',
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
                \Log::error('TMDB API Person Details Error: '.$e->getMessage());

                return null;
            }
        });
    }

    public function searchPerson($query, $page = 1, $exactMatch = false, $hasPhoto = false)
    {
        $page = (int) $page;
        $cacheKey = 'tmdb_search_person_v12_'.md5($query).'_p'.$page.'_e'.($exactMatch ? '1' : '0').'_hp'.($hasPhoto ? '1' : '0');

        return Cache::remember($cacheKey, now()->addDay(), function () use ($query, $page, $exactMatch, $hasPhoto) {
            try {
                $allResults = [];
                $targetCount = 20;

                // On normalise la requête pour la recherche TMDB
                $searchQuery = $query;

                // Cas spécial pour "IU" qui est souvent cherchée en minuscule "ui"
                // On va chercher à la fois le terme original et "IU" si c'est "ui"
                $isUiQuery = mb_strtolower($query) === 'ui';

                $isShortQuery = mb_strlen($searchQuery) <= 3;
                $maxPagesToScan = ($isShortQuery || $exactMatch) ? 100 : 50;

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

    public function getPopularActors($page = 1, $hasPhoto = false)
    {
        $page = (int) $page;
        $cacheKey = "tmdb_popular_actors_v11_p{$page}_hp".($hasPhoto ? '1' : '0');

        return Cache::remember($cacheKey, now()->addHours(6), function () use ($page, $hasPhoto) {
            try {
                $allResults = [];
                $targetCount = 20;

                // Optimization: Scan max 10 pages instead of 100 (most popular Korean actors in first 10)
                for ($p = 1; $p <= 10; $p++) {
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

                                if (! collect($allResults)->contains('id', $person['id'])) {
                                    $allResults[] = $person;
                                }
                            }
                        }
                    } else {
                        break;
                    }

                    // Early stopping: if we have enough buffer for requested page, stop scanning
                    if (count($allResults) >= ($page * $targetCount) + 40) {
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
            foreach ($person['known_for'] as $work) {
                // Vérifie le pays d'origine (Corée du Sud)
                if (isset($work['origin_country']) && in_array('KR', $work['origin_country'])) {
                    return true;
                }
                // Vérifie la langue originale (Coréen)
                if (isset($work['original_language']) && $work['original_language'] === 'ko') {
                    return true;
                }
            }
        }

        // 2. Si non trouvé dans 'known_for', TMDB ne nous donne pas beaucoup plus d'infos dans l'objet simple.
        // Mais on peut vérifier si le nom contient des caractères coréens (optionnel, peut-être trop risqué)
        // Pour l'instant on reste sur les métadonnées des œuvres connues.

        return false;
    }
}
