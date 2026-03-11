<?php

namespace App\Http\Controllers;

use App\Models\WatchlistItem;
use App\Models\Kdrama;
use App\Models\ExportLog;
use App\Services\TmdbService;
use App\Services\WatchlistExportService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class WatchlistController extends Controller
{
    private $tmdbService;

    public function __construct(TmdbService $tmdbService)
    {
        $this->tmdbService = $tmdbService;
    }

    public function index()
    {
        $items = WatchlistItem::where('user_id', auth()->id())
            ->where(function ($query) {
                $query->where('is_in_watchlist', true)
                    ->orWhere('is_watching', true)
                    ->orWhere('is_watched', true);
            })
            ->with(['kdrama'])
            ->orderBy('added_at', 'desc')
            ->get();

        $toWatch = $items->where('is_in_watchlist', true)->where('is_watched', false)->where('is_watching', false);
        $watching = $items->where('is_watching', true);
        $watched = $items->where('is_watched', true);

        return view('watchlist', [
            'items' => $items,
            'toWatch' => $toWatch,
            'watching' => $watching,
            'watched' => $watched,
        ]);
    }

    public function remove($tmdbId)
    {
        WatchlistItem::where('user_id', auth()->id())
            ->where('tmdb_id', $tmdbId)
            ->delete();

        return back()->with('success', __('watchlist.removed'));
    }

    // API endpoints for AJAX
    public function toggleWatchlist($tmdbId)
    {
        $userId = auth()->id();

        $item = WatchlistItem::where('user_id', $userId)
            ->where('tmdb_id', $tmdbId)
            ->first();

        if ($item && $item->is_in_watchlist) {
            // Retirer de la watchlist
            $item->is_in_watchlist = false;
            if (!$item->is_watched) {
                $item->delete();
            } else {
                $item->save();
            }

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'status' => 'success',
                    'action' => 'removed',
                    'message' => __('watchlist.removed_ajax'),
                    'inWatchlist' => false,
                    'inWatched' => $item && $item->is_watched
                ]);
            }
            return back()->with('success', __('watchlist.removed'));
        } else {
            // S'assurer qu'il est en cache pour l'affichage
            $kdrama = Kdrama::where('tmdb_id', $tmdbId)->first();
            if (!$kdrama) {
                $details = $this->tmdbService->getContentDetails($tmdbId, 'tv');
                if ($details) {
                    Kdrama::updateOrCreate(['tmdb_id' => $tmdbId], [
                        'name' => $details['name'],
                        'en_name' => $details['translations']['en']['name'] ?? $details['name'],
                        'original_name' => $details['original_name'] ?? null,
                        'first_air_date' => $details['first_air_date'] ?? null,
                        'poster_path' => $details['poster_path'] ?? null,
                        'vote_average' => $details['vote_average'] ?? 0,
                        'production_companies' => $details['production_companies'] ?? [],
                        'networks' => $details['networks'] ?? [],
                        'last_updated_at' => now(),
                    ]);
                }
            }

            // Vérifier si c'était en "regardé" avant (pour le message de basculement)
            $wasWatched = $item && $item->is_watched;

            // Ajouter à la watchlist (et retirer de "regardé" et "en train de voir" automatiquement)
            WatchlistItem::updateOrCreate(
                ['user_id' => $userId, 'tmdb_id' => $tmdbId],
                ['is_in_watchlist' => true, 'is_watching' => false, 'is_watched' => false, 'added_at' => now()]
            );

            if (request()->ajax() || request()->wantsJson()) {
                $message = __('watchlist.added');
                if ($wasWatched) {
                    $message .= ' ' . __('watchlist.removed_from_watched_suffix');
                }
                return response()->json([
                    'status' => 'success',
                    'action' => 'added',
                    'message' => $message,
                    'inWatchlist' => true,
                    'inWatched' => false
                ]);
            }
            return back()->with('success', __('watchlist.added'));
        }
    }

    public function toggleWatched($tmdbId)
    {
        $userId = auth()->id();

        $item = WatchlistItem::where('user_id', $userId)
            ->where('tmdb_id', $tmdbId)
            ->first();

        if ($item && $item->is_watched) {
            // Retirer des regardés et supprimer la note associée
            $item->is_watched = false;
            $item->rating = null; // Clear rating when marking as not watched

            if (!$item->is_in_watchlist) {
                $item->delete();
            } else {
                $item->save();
            }

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'status' => 'success',
                    'action' => 'removed',
                    'message' => __('watchlist.marked_to_watch'),
                    'inWatched' => false,
                    'inWatchlist' => false
                ]);
            }
            return back()->with('success', __('watchlist.removed_from_watched'));
        } else {
            // S'assurer qu'il est en cache pour l'affichage
            $kdrama = Kdrama::where('tmdb_id', $tmdbId)->first();
            if (!$kdrama) {
                $details = $this->tmdbService->getContentDetails($tmdbId, 'tv');
                if ($details) {
                    Kdrama::updateOrCreate(['tmdb_id' => $tmdbId], [
                        'name' => $details['name'],
                        'en_name' => $details['translations']['en']['name'] ?? $details['name'],
                        'original_name' => $details['original_name'] ?? null,
                        'first_air_date' => $details['first_air_date'] ?? null,
                        'poster_path' => $details['poster_path'] ?? null,
                        'vote_average' => $details['vote_average'] ?? 0,
                        'production_companies' => $details['production_companies'] ?? [],
                        'networks' => $details['networks'] ?? [],
                        'last_updated_at' => now(),
                    ]);
                }
            }

            // Vérifier si c'était en "watchlist" avant (pour le message de basculement)
            $wasInWatchlist = $item && $item->is_in_watchlist;

            // Ajouter aux regardés
            WatchlistItem::updateOrCreate(
                ['user_id' => $userId, 'tmdb_id' => $tmdbId],
                ['is_watched' => true, 'is_in_watchlist' => false, 'is_watching' => false]
            );

            if (request()->ajax() || request()->wantsJson()) {
                $message = __('watchlist.marked_watched');
                if ($wasInWatchlist) {
                    $message .= ' ' . __('watchlist.removed_from_watchlist_suffix');
                }
                return response()->json([
                    'status' => 'success',
                    'action' => 'added',
                    'message' => $message,
                    'inWatched' => true,
                    'inWatchlist' => false
                ]);
            }
            return back()->with('success', __('watchlist.marked_watched'));
        }
    }

    public function toggleWatching($tmdbId)
    {
        $userId = auth()->id();

        $item = WatchlistItem::where('user_id', $userId)
            ->where('tmdb_id', $tmdbId)
            ->first();

        if ($item && $item->is_watching) {
            // Retirer de "en train de voir"
            $item->is_watching = false;

            if (!$item->is_in_watchlist && !$item->is_watched) {
                $item->delete();
            } else {
                $item->save();
            }

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'status' => 'success',
                    'action' => 'removed',
                    'message' => __('watchlist.marked_to_watch'),
                    'inWatching' => false,
                    'inWatchlist' => false,
                    'inWatched' => false
                ]);
            }
            return back()->with('success', __('watchlist.removed_from_watching'));
        } else {
            // S'assurer qu'il est en cache pour l'affichage
            $kdrama = Kdrama::where('tmdb_id', $tmdbId)->first();
            if (!$kdrama) {
                $details = $this->tmdbService->getContentDetails($tmdbId, 'tv');
                if ($details) {
                    Kdrama::updateOrCreate(['tmdb_id' => $tmdbId], [
                        'name' => $details['name'],
                        'en_name' => $details['translations']['en']['name'] ?? $details['name'],
                        'original_name' => $details['original_name'] ?? null,
                        'first_air_date' => $details['first_air_date'] ?? null,
                        'poster_path' => $details['poster_path'] ?? null,
                        'vote_average' => $details['vote_average'] ?? 0,
                        'production_companies' => $details['production_companies'] ?? [],
                        'networks' => $details['networks'] ?? [],
                        'last_updated_at' => now(),
                    ]);
                }
            }

            // Marquer comme "en train de voir"
            WatchlistItem::updateOrCreate(
                ['user_id' => $userId, 'tmdb_id' => $tmdbId],
                ['is_watching' => true, 'is_in_watchlist' => false, 'is_watched' => false]
            );

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'status' => 'success',
                    'action' => 'added',
                    'message' => __('watchlist.marked_watching'),
                    'inWatching' => true,
                    'inWatchlist' => false,
                    'inWatched' => false
                ]);
            }
            return back()->with('success', __('watchlist.marked_watching'));
        }
    }

    public function checkStatus($tmdbId)
    {
        if (!auth()->check()) {
            return response()->json(['inWatchlist' => false, 'inWatching' => false, 'inWatched' => false]);
        }

        $item = WatchlistItem::where('user_id', auth()->id())
            ->where('tmdb_id', $tmdbId)
            ->first();

        return response()->json([
            'inWatchlist' => $item && $item->is_in_watchlist,
            'inWatching' => $item && $item->is_watching,
            'inWatched' => $item && $item->is_watched
        ]);
    }

    public function deleteItem($tmdbId)
    {
        $userId = auth()->id();

        $item = WatchlistItem::where('user_id', $userId)
            ->where('tmdb_id', $tmdbId)
            ->first();

        if (!$item) {
            return response()->json(['error' => __('watchlist.not_found')], 404);
        }

        // Delete watchlist item (rating will be deleted with it)
        $item->delete();

        return response()->json(['success' => true, 'message' => __('watchlist.deleted')]);
    }

    public function rateItem($tmdbId, Request $request)
    {
        $userId = auth()->id();
        $rating = $request->input('rating');

        // Validation: rating must be null or 1, 2, 3
        if ($rating !== null && !in_array($rating, [1, 2, 3])) {
            return response()->json(['error' => __('watchlist.rating_invalid')], 400);
        }

        // Check if item is watched
        $watchlistItem = WatchlistItem::where('user_id', $userId)
            ->where('tmdb_id', $tmdbId)
            ->first();

        // Item must exist and be marked as watched
        if (!$watchlistItem || !$watchlistItem->is_watched) {
            return response()->json(['error' => __('watchlist.rating_not_watched')], 403);
        }

        // Update rating in watchlist_items
        $watchlistItem->rating = $rating;
        $watchlistItem->save();

        if ($rating === null) {
            $message = __('watchlist.rating_removed');
        } else {
            $emoji = match($rating) {
                1 => '👎',
                2 => '👍',
                3 => '👍👍',
            };
            $message = "$emoji " . __('watchlist.rating_saved');
        }

        return response()->json([
            'status' => 'success',
            'rating' => $rating,
            'message' => $message
        ]);
    }

    /**
     * Show export options modal
     */
    public function showExportModal()
    {
        return view('watchlist._export-modal');
    }

    /**
     * Handle export request
     */
    public function export(Request $request, WatchlistExportService $exportService)
    {
        $validated = $request->validate([
            'format' => 'required|in:csv,pdf',
            'filters.watched' => 'sometimes|boolean',
            'filters.watching' => 'sometimes|boolean',
            'filters.to_watch' => 'sometimes|boolean',
            'columns' => 'sometimes|array',
            'sort' => 'sometimes|in:added_at,title,rating,vote_average',
            'send_email' => 'sometimes|boolean',
        ]);

        $userId = auth()->id();
        $user = auth()->user();
        $format = $validated['format'];
        $sendEmail = $validated['send_email'] ?? false;

        $options = [
            'filters' => [
                'watched' => $request->boolean('filters.watched', true),
                'watching' => $request->boolean('filters.watching', true),
                'to_watch' => $request->boolean('filters.to_watch', true),
            ],
            'columns' => $request->input('columns', [
                'poster' => true,
                'title' => true,
                'status' => true,
                'rating' => true,
                'year' => true,
                'vote_average' => true,
                'genres' => true,
                'networks' => false,
            ]),
            'sort' => $validated['sort'] ?? 'added_at',
        ];

        if ($format === 'csv') {
            return $this->exportCSV($exportService, $userId, $user, $options, $sendEmail);
        }

        return $this->exportPDF($exportService, $userId, $user, $options, $sendEmail);
    }

    /**
     * Export to CSV
     */
    private function exportCSV(WatchlistExportService $exportService, int $userId, $user, array $options, bool $sendEmail = false)
    {
        $startTime = microtime(true);
        $csv = $exportService->exportToCSV($userId, $options);
        $generationTime = round((microtime(true) - $startTime) * 1000); // in milliseconds
        $filename = $exportService->generateFilename($user->name, 'csv');

        // Log the export
        $stats = $this->getExportStats($userId, $options);
        ExportLog::create([
            'user_id' => $userId,
            'format' => 'csv',
            'item_count' => $stats['totalItems'],
            'file_size' => strlen($csv),
            'cache_hash' => null,
            'was_cached' => false,
            'generation_time' => $generationTime,
            'filters' => $options['filters'] ?? [],
        ]);

        // Envoyer par email si demandé
        if ($sendEmail) {
            \App\Jobs\SendExportEmail::dispatch(
                user: $user,
                format: 'csv',
                content: base64_encode($csv), // Encoder en base64 pour la queue
                filename: $filename,
                stats: $stats,
            );
        }

        return response($csv)
            ->header('Content-Type', 'text/csv; charset=utf-8')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    /**
     * Export to PDF using Browsershot with cache support
     */
    private function exportPDF(WatchlistExportService $exportService, int $userId, $user, array $options, bool $sendEmail = false)
    {
        $startTime = microtime(true);

        // Check cache first
        $cachedPdf = $exportService->getCachedPDF($userId, $options);
        $wasCached = $cachedPdf !== null;

        // Use cached PDF or generate new one
        $pdfContent = $wasCached ? $cachedPdf : $exportService->exportToPDFWithCache($userId, $options);
        $cacheHash = $exportService->generateCacheHash($userId, $options);

        $generationTime = round((microtime(true) - $startTime) * 1000); // in milliseconds
        $filename = $exportService->generateFilename($user->name, 'pdf');

        // Log the export
        $stats = $this->getExportStats($userId, $options);
        ExportLog::create([
            'user_id' => $userId,
            'format' => 'pdf',
            'item_count' => $stats['totalItems'],
            'file_size' => strlen($pdfContent),
            'cache_hash' => $cacheHash,
            'was_cached' => $wasCached,
            'generation_time' => $generationTime,
            'filters' => $options['filters'] ?? [],
        ]);

        // Envoyer par email si demandé
        if ($sendEmail) {
            \App\Jobs\SendExportEmail::dispatch(
                user: $user,
                format: 'pdf',
                content: base64_encode($pdfContent), // Encoder en base64 pour la queue
                filename: $filename,
                stats: $stats,
            );
        }

        return response($pdfContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    /**
     * Get export statistics
     */
    private function getExportStats(int $userId, array $options): array
    {
        $items = \App\Models\WatchlistItem::where('user_id', $userId)
            ->where(function ($query) use ($options) {
                $filters = $options['filters'] ?? ['watched' => true, 'to_watch' => true];
                if ($filters['watched'] ?? false) {
                    $query->orWhere('is_watched', true);
                }
                if ($filters['to_watch'] ?? false) {
                    $query->orWhere('is_in_watchlist', true);
                }
                if ($filters['watching'] ?? false) {
                    $query->orWhere('is_watching', true);
                }
            })
            ->get();

        $totalItems = $items->count();
        $watchedCount = $items->where('is_watched', true)->count();
        $watchingCount = $items->where('is_watching', true)->count();
        $toWatchCount = $totalItems - $watchedCount - $watchingCount;

        return [
            'totalItems' => $totalItems,
            'watchedCount' => $watchedCount,
            'watchingCount' => $watchingCount,
            'toWatchCount' => $toWatchCount,
        ];
    }
}
