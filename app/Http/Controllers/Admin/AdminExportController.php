<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExportLog;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class AdminExportController extends Controller
{
    /**
     * Show cache management page
     */
    public function cacheIndex()
    {
        $exportsDir = storage_path('app/exports');
        $files = [];
        $totalSize = 0;

        if (is_dir($exportsDir)) {
            $sevenDaysInSeconds = 7 * 24 * 60 * 60;
            $now = time();

            foreach (glob("{$exportsDir}/*.pdf") as $file) {
                $fileSize = filesize($file);
                $totalSize += $fileSize;
                $fileAge = $now - filemtime($file);
                $isExpired = $fileAge > $sevenDaysInSeconds;
                $daysRemaining = max(0, ceil((7 - ($fileAge / (24 * 60 * 60)))));

                $files[] = [
                    'name' => basename($file),
                    'size' => $fileSize,
                    'size_mb' => round($fileSize / (1024 * 1024), 2),
                    'created_at' => \Carbon\Carbon::createFromTimestamp(filemtime($file)),
                    'is_expired' => $isExpired,
                    'days_remaining' => $daysRemaining,
                    'path' => $file,
                ];
            }
        }

        // Trier par date (plus récents en premier)
        usort($files, function ($a, $b) {
            return $b['created_at']->timestamp <=> $a['created_at']->timestamp;
        });

        return view('admin.exports.cache', [
            'files' => $files,
            'totalSize' => $totalSize,
            'totalSizeMb' => round($totalSize / (1024 * 1024), 2),
            'fileCount' => count($files),
        ]);
    }

    /**
     * Show export statistics
     */
    public function statsIndex()
    {
        $days = request('days', 30);

        // Statistiques générales
        $totalExports = ExportLog::count();
        $pdfExports = ExportLog::where('format', 'pdf')->count();
        $csvExports = ExportLog::where('format', 'csv')->count();
        $cachedExports = ExportLog::where('was_cached', true)->count();

        // Espace disque
        $exportsDir = storage_path('app/exports');
        $totalDiskSize = 0;
        if (is_dir($exportsDir)) {
            foreach (glob("{$exportsDir}/*.pdf") as $file) {
                $totalDiskSize += filesize($file);
            }
        }

        // Top utilisateurs
        $topUsers = ExportLog::select('user_id')
            ->selectRaw('COUNT(*) as export_count')
            ->selectRaw('SUM(file_size) as total_size')
            ->groupBy('user_id')
            ->with('user')
            ->orderByDesc('export_count')
            ->limit(5)
            ->get();

        // Exports par jour (derniers X jours)
        $exportsPerDay = ExportLog::selectRaw('DATE(created_at) as date')
            ->selectRaw('COUNT(*) as count')
            ->selectRaw('SUM(CASE WHEN format = "pdf" THEN 1 ELSE 0 END) as pdf_count')
            ->selectRaw('SUM(CASE WHEN format = "csv" THEN 1 ELSE 0 END) as csv_count')
            ->where('created_at', '>=', now()->subDays($days))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Temps moyen de génération
        $avgTimeNotCached = ExportLog::where('was_cached', false)
            ->avg('generation_time') ?? 0;
        $avgTimeCached = ExportLog::where('was_cached', true)
            ->avg('generation_time') ?? 0;

        return view('admin.exports.stats', [
            'totalExports' => $totalExports,
            'pdfExports' => $pdfExports,
            'csvExports' => $csvExports,
            'cachedExports' => $cachedExports,
            'totalDiskSize' => $totalDiskSize,
            'totalDiskSizeMb' => round($totalDiskSize / (1024 * 1024), 2),
            'topUsers' => $topUsers,
            'exportsPerDay' => $exportsPerDay,
            'avgTimeNotCached' => round($avgTimeNotCached, 0),
            'avgTimeCached' => round($avgTimeCached, 0),
            'days' => $days,
        ]);
    }

    /**
     * Delete specific cache file
     */
    public function deleteCache($filename)
    {
        $filePath = storage_path("app/exports/{$filename}");

        // Sécurité: vérifier que le fichier est dans le bon répertoire
        if (strpos(realpath($filePath), storage_path('app/exports')) === 0 && file_exists($filePath)) {
            unlink($filePath);
            return back()->with('success', __('admin.export.cache.cache_deleted', ['filename' => $filename]));
        }

        return back()->with('error', __('admin.export.cache.file_not_found'));
    }

    /**
     * Purge all cache
     */
    public function purgeAllCache()
    {
        $exportsDir = storage_path('app/exports');
        $deleted = 0;

        if (is_dir($exportsDir)) {
            foreach (glob("{$exportsDir}/*.pdf") as $file) {
                if (unlink($file)) {
                    $deleted++;
                }
            }
        }

        return back()->with('success', __('admin.export.cache.cache_purged_all', ['count' => $deleted]));
    }

    /**
     * Purge expired cache only
     */
    public function purgeExpiredCache()
    {
        $exportsDir = storage_path('app/exports');
        $sevenDaysInSeconds = 7 * 24 * 60 * 60;
        $now = time();
        $deleted = 0;

        if (is_dir($exportsDir)) {
            foreach (glob("{$exportsDir}/*.pdf") as $file) {
                $fileAge = $now - filemtime($file);
                if ($fileAge > $sevenDaysInSeconds) {
                    if (unlink($file)) {
                        $deleted++;
                    }
                }
            }
        }

        return back()->with('success', __('admin.export.cache.cache_purged_expired', ['count' => $deleted]));
    }

    /**
     * Export watchlist for a specific user (admin only) with full options
     */
    public function exportUserWatchlist($userId, Request $request, \App\Services\WatchlistExportService $exportService)
    {
        $user = \App\Models\User::findOrFail($userId);

        // Validate request
        $validated = $request->validate([
            'format' => 'required|in:csv,pdf',
            'filters.watched' => 'sometimes|boolean',
            'filters.watching' => 'sometimes|boolean',
            'filters.to_watch' => 'sometimes|boolean',
            'columns' => 'sometimes|array',
            'sort' => 'sometimes|in:added_at,title,rating,vote_average',
            'send_email' => 'sometimes|boolean',
        ]);

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
            return $this->exportCSVForUser($exportService, $userId, $user, $options, $sendEmail);
        }

        return $this->exportPDFForUser($exportService, $userId, $user, $options, $sendEmail);
    }

    /**
     * Export CSV for user
     */
    private function exportCSVForUser(\App\Services\WatchlistExportService $exportService, int $userId, $user, array $options, bool $sendEmail = false)
    {
        $csv = $exportService->exportToCSV($userId, $options);
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
            'generation_time' => 0,
            'filters' => $options['filters'] ?? [],
        ]);

        // Send email if requested
        if ($sendEmail) {
            $sentByAdmin = auth()->user()->id !== $userId ? auth()->user() : null;
            \App\Jobs\SendExportEmail::dispatch(
                user: $user,
                format: 'csv',
                content: base64_encode($csv),
                filename: $filename,
                stats: $stats,
                sentByAdmin: $sentByAdmin,
            );
        }

        return response($csv)
            ->header('Content-Type', 'text/csv; charset=utf-8')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    /**
     * Export PDF for user
     */
    private function exportPDFForUser(\App\Services\WatchlistExportService $exportService, int $userId, $user, array $options, bool $sendEmail = false)
    {
        $startTime = microtime(true);

        // Check cache first
        $cachedPdf = $exportService->getCachedPDF($userId, $options);
        $wasCached = $cachedPdf !== null;

        // Use cached PDF or generate new one
        $pdfContent = $wasCached ? $cachedPdf : $exportService->exportToPDFWithCache($userId, $options);
        $cacheHash = $exportService->generateCacheHash($userId, $options);

        $generationTime = round((microtime(true) - $startTime) * 1000);
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

        // Send email if requested
        if ($sendEmail) {
            $sentByAdmin = auth()->user()->id !== $userId ? auth()->user() : null;
            \App\Jobs\SendExportEmail::dispatch(
                user: $user,
                format: 'pdf',
                content: base64_encode($pdfContent),
                filename: $filename,
                stats: $stats,
                sentByAdmin: $sentByAdmin,
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
            })
            ->get();

        $totalItems = $items->count();
        $watchedCount = $items->where('is_watched', true)->count();
        $toWatchCount = $totalItems - $watchedCount;

        return [
            'totalItems' => $totalItems,
            'watchedCount' => $watchedCount,
            'toWatchCount' => $toWatchCount,
        ];
    }
}
