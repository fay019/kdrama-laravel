<?php

namespace App\Http\Controllers;

use App\Helpers\StreamingLinkHelper;
use App\Models\Actor;
use App\Models\Kdrama;
use App\Models\User;
use App\Models\WatchlistItem;
use App\Services\StreamingAvailabilityService;
use App\Services\TmdbService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ContentController extends Controller
{
    private $tmdbService;

    private $streamingService;

    public function __construct(TmdbService $tmdbService, StreamingAvailabilityService $streamingService)
    {
        $this->tmdbService = $tmdbService;
        $this->streamingService = $streamingService;
    }

    public function index()
    {
        $featured = [];
        $isAdminList = false;
        $newest = [];

        // Check if first admin has rated dramas
        $firstAdmin = User::where('is_admin', true)->first();

        if ($firstAdmin) {
            // Get admin's rated dramas (with rating != null)
            $ratedDramas = WatchlistItem::where('user_id', $firstAdmin->id)
                ->whereNotNull('rating')
                ->with('kdrama')
                ->get()
                ->pluck('kdrama')
                ->filter();

            // If admin has rated dramas, use them
            if ($ratedDramas->isNotEmpty()) {
                // Start with rated dramas, max 12 total
                $itemsToDisplay = $ratedDramas->shuffle();

                // If less than 12, add watching items
                if ($itemsToDisplay->count() < 12) {
                    $watchingDramas = WatchlistItem::where('user_id', $firstAdmin->id)
                        ->where('is_watching', true)
                        ->with('kdrama')
                        ->get()
                        ->pluck('kdrama')
                        ->filter();

                    $itemsToDisplay = $itemsToDisplay->merge($watchingDramas)->unique('tmdb_id')->shuffle();
                }

                // If still less than 12, add to_watch items
                if ($itemsToDisplay->count() < 12) {
                    $toWatchDramas = WatchlistItem::where('user_id', $firstAdmin->id)
                        ->where('is_in_watchlist', true)
                        ->where('is_watching', false)
                        ->with('kdrama')
                        ->get()
                        ->pluck('kdrama')
                        ->filter();

                    $itemsToDisplay = $itemsToDisplay->merge($toWatchDramas)->unique('tmdb_id')->shuffle();
                }

                // Take max 12
                $featured = $itemsToDisplay->take(12)->map(function ($kdrama) {
                    return $this->formatKdramaForDisplay($kdrama);
                })->values()->toArray();

                $isAdminList = true;
            }
        }

        // If no admin dramas found, use API for featured
        if (empty($featured)) {
            $apiResult = $this->tmdbService->discoverAsianContent([
                'origins' => ['KR'],
                'sort' => 'popularity.desc',
                'page' => 1,
            ]);
            $featured = $apiResult['results'] ?? [];
            $isAdminList = false;
        }

        // Get releases sorted by date (newest first)
        // Fetch pages 1-2 to get enough results
        $allReleases = [];
        for ($page = 1; $page <= 2; $page++) {
            $pageResult = $this->tmdbService->discoverAsianContent([
                'origins' => ['KR'],
                'sort' => 'first_air_date.desc',
                'page' => $page,
            ]);
            $allReleases = array_merge($allReleases, $pageResult['results'] ?? []);
        }

        // Get today's date for comparison
        $today = Carbon::today();

        // Separate into past and upcoming, filter by poster_path
        $newest = [];
        $upcoming = [];

        foreach ($allReleases as $item) {
            if (empty($item['poster_path'])) {
                continue; // Skip items without images
            }

            $airDate = isset($item['first_air_date']) ? Carbon::parse($item['first_air_date']) : null;

            if ($airDate && $airDate->lte($today)) {
                // Past releases
                $newest[] = $item;
            } elseif ($airDate && $airDate->gt($today)) {
                // Upcoming releases
                $upcoming[] = $item;
            }
        }

        // Limit to 8 items each
        $newest = array_slice($newest, 0, 8);

        // Sort upcoming by date ascending (closest first)
        usort($upcoming, function ($a, $b) {
            $dateA = Carbon::parse($a['first_air_date']);
            $dateB = Carbon::parse($b['first_air_date']);

            return $dateA->getTimestamp() - $dateB->getTimestamp();
        });
        $upcoming = array_slice($upcoming, 0, 8);

        return view('index', [
            'featured' => $featured,
            'isAdminList' => $isAdminList,
            'newest' => $newest,
            'upcoming' => $upcoming,
        ]);
    }

    /**
     * Format kdrama for display with language fallback
     */
    private function formatKdramaForDisplay($kdrama)
    {
        $locale = app()->getLocale();
        $data = $kdrama->toArray();

        // Set 'name' to localized version based on current locale with fallback
        $data['name'] = $this->getLocalizedTitle($kdrama, $locale);

        return $data;
    }

    /**
     * Get localized title with fallback: current locale → EN → FR → original
     */
    private function getLocalizedTitle($kdrama, $locale)
    {
        // Prefer current locale
        if ($locale === 'de' && ! empty($kdrama->translations['de']['name'] ?? null)) {
            return $kdrama->translations['de']['name'];
        } elseif ($locale === 'en' && ! empty($kdrama->en_name)) {
            return $kdrama->en_name;
        } elseif ($locale === 'fr' && ! empty($kdrama->name)) {
            return $kdrama->name;
        }

        // Fallback: EN → FR → original
        if (! empty($kdrama->en_name)) {
            return $kdrama->en_name;
        }
        if (! empty($kdrama->name)) {
            return $kdrama->name;
        }

        return $kdrama->original_name ?? 'Unknown';
    }

    public function catalog(Request $request)
    {
        $view = $request->get('view', 'dramas');
        $filters = [
            'view' => $view,
            'origins' => ['KR'],
            'sort' => $request->get('sort', 'popularity.desc'),
            'page' => $request->get('page', 1),
            'min_rating' => $request->get('min_rating', 0),
            'from_year' => $request->get('from_year'),
            'to_year' => $request->get('to_year'),
            'search' => $request->get('search'),
            'exact_name' => $request->boolean('exact_name'),
            'has_photo' => $request->boolean('has_photo'),
            'has_works' => $request->boolean('has_works', true),
            'actor' => $request->get('actor'),
            'actor_id' => $request->get('actor_id'),
            'hide_watched' => $request->boolean('hide_watched'),
            'hide_watching' => $request->boolean('hide_watching'),
            'hide_watchlist' => $request->boolean('hide_watchlist'),
        ];

        if ($view === 'actors') {
            // Get actors from database (synced daily)
            $query = \App\Models\Actor::query();

            // Search by name if provided
            if (! empty($filters['search'])) {
                $searchTerm = "%{$filters['search']}%";
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('name', 'like', $searchTerm)
                      ->orWhere('known_for', 'like', $searchTerm);
                });
            }

            // Filter by TV credits if enabled (has_works)
            if ($filters['has_works']) {
                $query->where('tv_credits_count', '>=', 1);
            }

            // Filter by photo if requested
            if ($filters['has_photo']) {
                $query->whereNotNull('profile_path');
            }

            // Get total count before pagination
            $totalResults = $query->count();

            // Paginate results (20 per page)
            $targetCount = 20;
            $page = (int) $filters['page'];
            $actorData = $query->orderBy('popularity', 'desc')
                ->skip(($page - 1) * $targetCount)
                ->limit($targetCount)
                ->get();

            // Transform to match TMDB structure (id = tmdb_id for modal links)
            $results = $actorData->map(function ($actor) {
                return [
                    'id' => $actor->tmdb_id,  // For openActorModal() to work
                    'tmdb_id' => $actor->tmdb_id,
                    'name' => $actor->name,
                    'profile_path' => $actor->profile_path,
                    'popularity' => $actor->popularity,
                    'known_for' => $actor->known_for,
                    'tv_credits_count' => $actor->tv_credits_count,
                ];
            })->toArray();

            $totalPages = ceil($totalResults / $targetCount);

            $results = [
                'results' => $results,
                'total_results' => $totalResults,
                'total_pages' => $totalPages,
                'page' => $page,
            ];

            // Ensure other filters don't impact display
            $filters['actor'] = null;
            $filters['actor_id'] = null;
            $filters['min_rating'] = 0;
            $filters['from_year'] = null;
            $filters['to_year'] = null;
        } else {
            if (! empty($filters['actor_id'])) {
                $results = $this->tmdbService->getPersonTvCredits($filters['actor_id'], $filters['page']);
                if (! $results || empty($results['results'])) {
                    $results = ['results' => [], 'total_pages' => 0, 'total_results' => 0];
                } elseif (! empty($filters['search'])) {
                    // Si on a aussi une recherche par titre, on filtre les résultats de l'acteur
                    $searchTerm = mb_strtolower($filters['search']);
                    $results['results'] = array_filter($results['results'], function ($item) use ($searchTerm) {
                        $name = mb_strtolower($item['name'] ?? '');
                        $enName = mb_strtolower($item['en_name'] ?? '');

                        return str_contains($name, $searchTerm) || str_contains($enName, $searchTerm);
                    });
                    $results['results'] = array_values($results['results']);
                    $results['total_results'] = count($results['results']);
                    $results['total_pages'] = 1;
                }
            } elseif (! empty($filters['actor'])) {
                // OPTIMIZATION: Use discover API with cast filter instead of checking 30 actors individually
                // This eliminates N+1 problem: was 30 getPersonTvCredits calls, now just 1 discover call
                $personSearch = $this->tmdbService->searchPerson($filters['actor']);
                if (! empty($personSearch['results'])) {
                    // Take first match (already sorted by relevance in searchPerson)
                    // Faster than scoring 30 actors with individual API calls
                    $bestMatch = $personSearch['results'][0];
                    $personId = $bestMatch['id'];

                    // Use discover API with cast filter - single call instead of N+1
                    $filters['with_cast'] = $personId;
                    $results = $this->tmdbService->discoverAsianContent($filters);

                    // Apply title search filter if also present
                    if (! empty($filters['search'])) {
                        $searchTerm = mb_strtolower($filters['search']);
                        $results['results'] = array_filter($results['results'], function ($item) use ($searchTerm) {
                            $name = mb_strtolower($item['name'] ?? '');
                            $enName = mb_strtolower($item['en_name'] ?? '');

                            return str_contains($name, $searchTerm) || str_contains($enName, $searchTerm);
                        });
                        $results['results'] = array_values($results['results']);
                        $results['total_results'] = count($results['results']);
                        // Recalculate pages after filtering
                        $results['total_pages'] = ceil($results['total_results'] / 20);
                    }
                } else {
                    // Actor not found, return empty results
                    $results = ['results' => [], 'total_pages' => 0, 'total_results' => 0];
                }
            }

            if (! isset($results)) {
                if (! empty($filters['search'])) {
                    $results = $this->tmdbService->searchContent($filters['search'], $filters['page']);
                } else {
                    $results = $this->tmdbService->discoverAsianContent($filters);
                }
            }
        }

        // Si aucun résultat (ex: recherche infructueuse ou erreur API)
        if (! $results) {
            $results = ['results' => [], 'total_pages' => 0, 'total_results' => 0];
        }

        // Récupérer les infos de watchlist et ratings si l'utilisateur est connecté
        $userStatus = [];
        if (auth()->check()) {
            // OPTIMIZATION: Only query for tmdb_id, status, and rating (not entire records)
            // Load only if filters actually need them, or get first page results
            $resultIds = array_map(fn ($item) => $item['id'] ?? null, array_filter($results['results'] ?? []));

            if (! empty($resultIds) || ($filters['hide_watched'] || $filters['hide_watching'] || $filters['hide_watchlist'])) {
                // Only load watchlist items for shown results or if filtering
                $watchlistItems = WatchlistItem::where('user_id', auth()->id())
                    ->select(['tmdb_id', 'is_in_watchlist', 'is_watching', 'is_watched', 'rating'])
                    ->get();

                // Map watchlist items with ratings
                $userStatus = $watchlistItems->mapWithKeys(function ($item) {
                    return [$item->tmdb_id => [
                        'is_in_watchlist' => $item->is_in_watchlist,
                        'is_watching' => $item->is_watching,
                        'is_watched' => $item->is_watched,
                        'rating' => $item->rating,
                    ]];
                })->toArray();
            }

            // Appliquer les filtres de masquage
            if ($filters['hide_watched'] || $filters['hide_watching'] || $filters['hide_watchlist']) {
                $results['results'] = array_filter($results['results'], function ($item) use ($userStatus, $filters) {
                    $tmdbId = $item['id'] ?? null;
                    if (! $tmdbId || ! isset($userStatus[$tmdbId])) {
                        return true;
                    }

                    if ($filters['hide_watched'] && $userStatus[$tmdbId]['is_watched']) {
                        return false;
                    }
                    if ($filters['hide_watching'] && $userStatus[$tmdbId]['is_watching']) {
                        return false;
                    }
                    if ($filters['hide_watchlist'] && $userStatus[$tmdbId]['is_in_watchlist']) {
                        return false;
                    }

                    return true;
                });
                // Note: array_filter reset les index, on peut ré-indexer si besoin mais pour le foreach ça va.
                $results['results'] = array_values($results['results']);
                $results['total_results'] = count($results['results']);
                // Recalculate total pages based on filtered results if it's not a single page search
                if (isset($results['total_pages']) && $results['total_pages'] > 1) {
                    $results['total_pages'] = ceil($results['total_results'] / 20); // Assuming 20 per page
                }
            }
        }

        if ($request->ajax()) {
            $html = '';
            $viewName = ($view === 'actors') ? 'kdrams._actor_card' : 'kdrams._card';
            foreach ($results['results'] ?? [] as $item) {
                $html .= view($viewName, [
                    'kdrama' => $item, // For backward compatibility with _card
                    'actor' => $item,  // For _actor_card
                    'filters' => $filters,
                    'userStatus' => $userStatus,
                ])->render();
            }

            return response()->json([
                'html' => $html,
                'total_results' => (int) ($results['total_results'] ?? 0),
                'total_pages' => (int) ($results['total_pages'] ?? 1),
                'current_page' => (int) $filters['page'],
                'current_count' => count($results['results'] ?? []),
            ]);
        }

        return view('kdrams.index', [
            'kdrams' => $results['results'] ?? [],
            'total_pages' => $results['total_pages'] ?? 1,
            'total_results' => $results['total_results'] ?? 0,
            'current_page' => (int) $filters['page'],
            'filters' => $filters,
            'userStatus' => $userStatus,
        ]);
    }

    public function show($id, Request $request)
    {
        // 1. Chercher en base de données
        $kdrama = Kdrama::where('tmdb_id', $id)->first();
        $details = null;

        // Si présent et frais (< 24h), on utilise les données locales
        if ($kdrama && $kdrama->last_updated_at && $kdrama->last_updated_at->gt(now()->subDay())) {
            $details = $kdrama->toArray();
        } else {
            // 2. Sinon (absent ou expiré), appeler TMDB
            $details = $this->tmdbService->getContentDetails($id, 'tv');

            if ($details) {
                // 3. Enregistrer ou mettre à jour en base
                $kdrama = Kdrama::updateOrCreate(
                    ['tmdb_id' => $id],
                    [
                        'name' => $details['name'] ?? null,
                        'en_name' => $details['translations']['en']['name'] ?? ($details['name'] ?? null),
                        'original_name' => $details['original_name'] ?? null,
                        'overview' => $details['overview'] ?? null,
                        'poster_path' => $details['poster_path'] ?? null,
                        'backdrop_path' => $details['backdrop_path'] ?? null,
                        'first_air_date' => $details['first_air_date'] ?? null,
                        'vote_average' => $details['vote_average'] ?? 0,
                        'vote_count' => $details['vote_count'] ?? 0,
                        'genres' => $details['genres'] ?? [],
                        'origin_country' => $details['origin_country'] ?? [],
                        'status' => $details['status'] ?? null,
                        'original_language' => $details['original_language'] ?? null,
                        'number_of_episodes' => $details['number_of_episodes'] ?? null,
                        'number_of_seasons' => $details['number_of_seasons'] ?? null,
                        'last_air_date' => $details['last_air_date'] ?? null,
                        'credits' => $details['credits'] ?? [],
                        'production_companies' => $details['production_companies'] ?? [],
                        'networks' => $details['networks'] ?? [],
                        'similar' => $details['similar'] ?? [],
                        'translations' => $details['translations'] ?? [],
                        'last_updated_at' => now(),
                    ]
                );
                // On met à jour $details avec les données fraîches pour la vue
                $details = $kdrama->toArray();
            }
        }

        if (! $details) {
            abort(404);
        }

        $titleForSearch = $details['translations']['en']['name'] ?? ($details['name'] ?? null);
        $availability = $this->streamingService->getAvailability($id, 'tv', 'fr', $titleForSearch);

        // 4. Récupérer l'état pour l'utilisateur connecté (Watchlist / Regardé / Rating)
        $userStatus = null;
        if (auth()->check()) {
            $watchlistItem = WatchlistItem::where('user_id', auth()->id())
                ->where('tmdb_id', $id)
                ->first();

            if ($watchlistItem) {
                // Create object with watchlist info (including rating from watchlist_items)
                $userStatus = (object) [
                    'id' => $watchlistItem->id,
                    'user_id' => $watchlistItem->user_id,
                    'tmdb_id' => $watchlistItem->tmdb_id,
                    'is_in_watchlist' => $watchlistItem->is_in_watchlist,
                    'is_watching' => $watchlistItem->is_watching,
                    'is_watched' => $watchlistItem->is_watched,
                    'rating' => $watchlistItem->rating,
                ];
            }
        }

        // On s'assure d'avoir un tableau pour la vue (pour la compatibilité avec le code existant)
        // On s'assure que 'tmdb_id' est bien présent dans le tableau $details
        if (! isset($details['tmdb_id']) && isset($details['id'])) {
            $details['tmdb_id'] = $details['id'];
        }

        // Générer les liens de recherche pour les plateformes connues (si pas de données RapidAPI)
        $streamingLinks = StreamingLinkHelper::generateStreamingLinks($details);

        // Mais on passe l'objet Kdrama s'il existe pour avoir accès aux casts si nécessaire
        $viewData = [
            'kdrama' => $details, // $details est un tableau (venant de toArray() ou TMDB)
            'availability' => $availability ?? [],
            'streamingLinks' => $streamingLinks,
            'highlight_actor' => $request->get('actor_id'),
            'userStatus' => $userStatus,
        ];

        // On peut ajouter l'objet modèle s'il est utile pour certains calculs complexes dans la vue
        if ($kdrama instanceof Kdrama) {
            $viewData['model'] = $kdrama;
        }

        return view('kdrams.show', $viewData);
    }

    public function refreshStreaming($id)
    {
        $kdrama = Kdrama::where('tmdb_id', $id)->first();
        $title = null;
        if ($kdrama) {
            $title = $kdrama->en_name ?? $kdrama->name;
        }

        try {
            // Force le rafraîchissement depuis l'API
            $availability = $this->streamingService->getAvailability($id, 'tv', 'fr', $title, true);

            if (empty($availability)) {
                return back()->with('success', __('show.refresh_no_platforms'));
            }

            return back()->with('success', __('show.refresh_success'));
        } catch (\Exception $e) {
            \Log::error("Manual refresh failed for TMDB {$id}: ".$e->getMessage());

            return back()->with('error', __('show.refresh_error', ['error' => $e->getMessage()]));
        }
    }

    public function actorDetails($id)
    {
        // Chercher l'acteur en base de données
        $dbActor = Actor::where('tmdb_id', $id)->first();

        // Vérifier si on a besoin de refetch:
        // 1. Pas en DB
        // 2. Plus d'1 semaine depuis la synchro
        // 3. Champs détaillés manquants (combined_credits surtout pour les dramas)
        $needsRefresh = ! $dbActor || ! $dbActor->last_synced_at ||
                        $dbActor->last_synced_at->addWeek()->isPast() ||
                        ! $dbActor->combined_credits;

        if ($needsRefresh) {
            // Fetcher les détails complets de l'API TMDB
            $apiActor = $this->tmdbService->getPersonDetails($id);

            if (! $apiActor) {
                return response()->json(['error' => __('show.actor_not_found')], 404);
            }

            // Sauvegarder/mettre à jour en base de données
            $creditCount = count($apiActor['combined_credits']['cast'] ?? []);

            if ($dbActor) {
                // Mise à jour existante
                $dbActor->update([
                    'biography' => $apiActor['biography'] ?? null,
                    'birthday' => $apiActor['birthday'] ?? null,
                    'birthplace' => $apiActor['place_of_birth'] ?? null,
                    'known_for' => $apiActor['known_for'] ?? null,
                    'combined_credits' => $apiActor['combined_credits'] ?? null,
                    'external_ids' => $apiActor['external_ids'] ?? null,
                    'tv_credits_count' => $creditCount,
                    'last_synced_at' => now(),
                ]);
            } else {
                // Créer nouveau record
                Actor::create([
                    'tmdb_id' => $id,
                    'name' => $apiActor['name'] ?? null,
                    'biography' => $apiActor['biography'] ?? null,
                    'profile_path' => $apiActor['profile_path'] ?? null,
                    'birthday' => $apiActor['birthday'] ?? null,
                    'birthplace' => $apiActor['place_of_birth'] ?? null,
                    'known_for' => $apiActor['known_for'] ?? null,
                    'combined_credits' => $apiActor['combined_credits'] ?? null,
                    'popularity' => $apiActor['popularity'] ?? 0,
                    'external_ids' => $apiActor['external_ids'] ?? null,
                    'tv_credits_count' => $creditCount,
                    'last_synced_at' => now(),
                ]);
            }

            $actor = $apiActor;
        } else {
            // Utiliser les données en base de données
            $actor = $dbActor->toArray();

            // Important: utiliser tmdb_id comme 'id' pour que le modal envoie le bon ID
            $actor['id'] = $dbActor->tmdb_id;

            // Mapper les champs DB vers les champs attendus par la vue
            $actor['place_of_birth'] = $dbActor->birthplace ?? null;

            // Assurer que external_ids est un array
            if (is_string($dbActor->external_ids)) {
                $actor['external_ids'] = json_decode($dbActor->external_ids, true);
            } elseif ($dbActor->external_ids) {
                $actor['external_ids'] = $dbActor->external_ids;
            } else {
                $actor['external_ids'] = [];
            }

            // Assurer que combined_credits est un array
            if (is_string($dbActor->combined_credits)) {
                $actor['combined_credits'] = json_decode($dbActor->combined_credits, true);
            } elseif ($dbActor->combined_credits) {
                $actor['combined_credits'] = $dbActor->combined_credits;
            } else {
                $actor['combined_credits'] = [];
            }
        }

        if (! $actor) {
            return response()->json(['error' => __('show.actor_not_found')], 404);
        }

        // Assurer que toutes les clés optionnelles existent pour éviter les erreurs undefined array key dans la vue
        $actor['deathday'] = $actor['deathday'] ?? null;
        $actor['latin_name'] = $actor['latin_name'] ?? $actor['name'] ?? null;
        $actor['original_name'] = $actor['original_name'] ?? $actor['name'] ?? null;
        $actor['combined_credits'] = $actor['combined_credits'] ?? [];

        return view('kdrams._actor_modal', [
            'actor' => $actor,
        ])->render();
    }
}
