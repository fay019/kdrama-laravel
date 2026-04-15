<?php

namespace App\Http\Controllers;

use App\Helpers\StreamingLinkHelper;
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
        $filters = [
            'origins' => ['KR'],
            'sort' => $request->get('sort', 'popularity.desc'),
            'page' => $request->get('page', 1),
            'min_rating' => $request->get('min_rating', 0),
            'from_year' => $request->get('from_year'),
            'to_year' => $request->get('to_year'),
            'search' => $request->get('search'),
            'actor' => $request->get('actor'),
            'actor_id' => $request->get('actor_id'),
            'hide_watched' => $request->boolean('hide_watched'),
            'hide_watching' => $request->boolean('hide_watching'),
            'hide_watchlist' => $request->boolean('hide_watchlist'),
        ];

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
            $personSearch = $this->tmdbService->searchPerson($filters['actor']);
            if (! empty($personSearch['results'])) {
                // On cherche l'acteur qui a le plus de crédits TV coréens parmi les 10 premiers résultats
                $bestMatch = null;
                $maxCredits = -1;

                foreach (array_slice($personSearch['results'], 0, 30) as $person) {
                    if (isset($person['known_for_department']) && $person['known_for_department'] === 'Acting') {
                        $credits = $this->tmdbService->getPersonTvCredits($person['id']);
                        $krCreditsCount = count($credits['results'] ?? []);

                        // Score = (credits * 3) + popularity
                        $score = ($krCreditsCount * 3) + ($person['popularity'] ?? 0);

                        // Bonus pour correspondance exacte du nom (insensible à la casse)
                        $cleanName = mb_strtolower($person['name']);
                        $cleanQuery = mb_strtolower($filters['actor']);
                        if ($cleanName === $cleanQuery) {
                            $score += 10;
                        }

                        // Bonus pour photo de profil (signe d'un acteur plus connu)
                        if (! empty($person['profile_path'])) {
                            $score += 2;
                        }

                        if ($score > $maxCredits) {
                            $maxCredits = $score;
                            $bestMatch = $person;
                        }
                    }
                }

                // Si aucun n'a de crédits TV KR dans 'Acting', on prend le plus populaire de 'Acting'
                if (! $bestMatch || $maxCredits <= 0) {
                    foreach (array_slice($personSearch['results'], 0, 5) as $person) {
                        if (isset($person['known_for_department']) && $person['known_for_department'] === 'Acting') {
                            if (! $bestMatch || $person['popularity'] > $bestMatch['popularity']) {
                                $bestMatch = $person;
                            }
                        }
                    }
                }

                // Si toujours rien, on prend le tout premier résultat de la recherche
                $personId = $bestMatch ? $bestMatch['id'] : $personSearch['results'][0]['id'];

                $filters['with_cast'] = $personId;
                $results = $this->tmdbService->getPersonTvCredits($personId, $filters['page']);

                // Si l'acteur n'a pas de crédits TV coréens, on s'assure que $results est bien vide
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
                    $results['total_pages'] = 1; // On a déjà tout chargé pour cet acteur (TMDB ne pagine pas les crédits)
                }
            } else {
                // Si l'acteur n'est pas trouvé, on force 0 résultats
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

        // Si aucun résultat (ex: recherche infructueuse ou erreur API)
        if (! $results) {
            $results = ['results' => [], 'total_pages' => 0, 'total_results' => 0];
        }

        // Récupérer les infos de watchlist et ratings si l'utilisateur est connecté
        $userStatus = [];
        if (auth()->check()) {
            // Get watchlist items
            $watchlistItems = WatchlistItem::where('user_id', auth()->id())->get();

            // Map watchlist items with ratings
            $userStatus = $watchlistItems->mapWithKeys(function ($item) {
                return [$item->tmdb_id => [
                    'is_in_watchlist' => $item->is_in_watchlist,
                    'is_watching' => $item->is_watching,
                    'is_watched' => $item->is_watched,
                    'rating' => $item->rating,
                ]];
            })->toArray();

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
            foreach ($results['results'] ?? [] as $kdrama) {
                $html .= view('kdrams._card', [
                    'kdrama' => $kdrama,
                    'filters' => $filters,
                    'userStatus' => $userStatus,
                ])->render();
            }

            return response()->json([
                'html' => $html,
                'next_page' => $filters['page'] + 1,
                'has_more' => ($filters['page'] < ($results['total_pages'] ?? 1)),
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
            'current_page' => $filters['page'],
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
        $actor = $this->tmdbService->getPersonDetails($id);

        if (! $actor) {
            return response()->json(['error' => __('show.actor_not_found')], 404);
        }

        return view('kdrams._actor_modal', [
            'actor' => $actor,
        ])->render();
    }
}
