<?php

namespace App\Services;

use App\Models\WatchlistItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Spatie\Browsershot\Browsershot;

class WatchlistExportService
{
    /**
     * Export watchlist to CSV respecting selected columns
     */
    public function exportToCSV(int $userId, array $options = []): string
    {
        $items = $this->getFilteredWatchlist($userId, $options);
        $selectedColumns = $options['columns'] ?? [];

        // Construire l'en-tête CSV en fonction des colonnes sélectionnées
        $headers = ['Titre'];
        if ($selectedColumns['status'] ?? true) $headers[] = 'Statut';
        if ($selectedColumns['rating'] ?? true) $headers[] = 'Rating';
        if ($selectedColumns['year'] ?? true) $headers[] = 'Année';
        if ($selectedColumns['vote_average'] ?? true) $headers[] = 'Vote TMDB';
        if ($selectedColumns['genres'] ?? true) $headers[] = 'Genres';
        if ($selectedColumns['networks'] ?? false) $headers[] = 'Networks';
        if ($selectedColumns['synopsis'] ?? false) $headers[] = 'Synopsis';

        $csv = implode(',', $headers) . "\n";

        foreach ($items as $item) {
            $row = [];
            $title = $this->formatTitle($item);
            $row[] = "\"{$title}\"";

            if ($selectedColumns['status'] ?? true) {
                $status = $item['is_watched'] ? 'Vu' : 'À regarder';
                $row[] = "\"{$status}\"";
            }

            if ($selectedColumns['rating'] ?? true) {
                $rating = $this->formatRating($item['rating'] ?? null);
                $row[] = "\"{$rating}\"";
            }

            if ($selectedColumns['year'] ?? true) {
                $year = $item['first_air_date'] ? Carbon::parse($item['first_air_date'])->year : 'N/A';
                $row[] = $year;
            }

            if ($selectedColumns['vote_average'] ?? true) {
                $voteAverage = $item['vote_average'] ?? 0;
                $row[] = $voteAverage;
            }

            if ($selectedColumns['genres'] ?? true) {
                $genres = $this->formatGenres($item['genres'] ?? []);
                $row[] = "\"{$genres}\"";
            }

            if ($selectedColumns['networks'] ?? false) {
                $networks = $this->formatGenres($item['networks'] ?? []);
                $row[] = "\"{$networks}\"";
            }

            if ($selectedColumns['synopsis'] ?? false) {
                $synopsis = str_replace(['"', "\n", "\r"], ['""', ' ', ''], $item['overview'] ?? '');
                $row[] = "\"{$synopsis}\"";
            }

            $csv .= implode(',', $row) . "\n";
        }

        return $csv;
    }

    /**
     * Export watchlist to PDF using Browsershot
     */
    public function exportToPDF(int $userId, array $options = []): string
    {
        $user = \App\Models\User::findOrFail($userId);
        $items = $this->getFilteredWatchlist($userId, $options);

        // Calculer stats
        $totalItems = count($items);
        $watchedCount = count(array_filter($items, fn($i) => $i['is_watched']));
        $toWatchCount = $totalItems - $watchedCount;

        // Formater les options pour l'affichage
        $displayOptions = $this->formatOptionsForDisplay($options);

        // Paginer les items (50 par page)
        $itemsPerPage = 50;
        $pages = array_chunk($items, $itemsPerPage);

        $html = view('exports.watchlist-pdf', [
            'user' => $user,
            'pages' => $pages,
            'totalItems' => $totalItems,
            'watchedCount' => $watchedCount,
            'toWatchCount' => $toWatchCount,
            'options' => $options,
            'displayOptions' => $displayOptions,
            'selectedColumns' => $options['columns'] ?? [],
        ])->render();

        // Utiliser Browsershot (Headless Chrome) pour générer le PDF
        $pdf = Browsershot::html($html)
            ->paperSize(210, 297) // A4 in mm
            ->margins(10, 10, 10, 10)
            ->pdf();

        return $pdf;
    }

    /**
     * Get filtered and sorted watchlist
     */
    private function getFilteredWatchlist(int $userId, array $options = []): array
    {
        // Options par défaut
        $filters = $options['filters'] ?? ['watched' => true, 'to_watch' => true];
        $sort = $options['sort'] ?? 'added_at';
        $columns = $options['columns'] ?? [
            'title' => true,
            'status' => true,
            'rating' => true,
            'year' => true,
            'vote_average' => true,
            'genres' => true,
        ];

        // Récupérer les items
        $items = WatchlistItem::where('user_id', $userId)
            ->where(function ($query) use ($filters) {
                if ($filters['watched'] ?? false) {
                    $query->orWhere('is_watched', true);
                }
                if ($filters['to_watch'] ?? false) {
                    $query->orWhere('is_in_watchlist', true);
                }
            })
            ->with('kdrama')
            ->get();

        // Formatter les items
        $formatted = $items->map(function ($item) use ($columns) {
            $kdrama = $item->kdrama ? $item->kdrama->toArray() : [];

            // Convertir poster en base64 si images sont activées
            if (($columns['poster'] ?? false) && ($kdrama['poster_path'] ?? false)) {
                $kdrama['poster_base64'] = $this->getPosterAsBase64($kdrama['poster_path']);
            }

            return array_merge($kdrama, [
                'is_watched' => $item->is_watched,
                'is_in_watchlist' => $item->is_in_watchlist,
                'added_at' => $item->added_at,
                'rating' => $item->rating,
            ]);
        })->toArray();

        // Trier
        $formatted = $this->sortItems($formatted, $sort);

        return $formatted;
    }

    /**
     * Get poster as base64
     */
    private function getPosterAsBase64(string $posterPath): string
    {
        try {
            $url = "https://image.tmdb.org/t/p/w200{$posterPath}";
            $response = Http::timeout(5)->get($url);

            if ($response->successful()) {
                $base64 = base64_encode($response->body());
                return "data:image/jpeg;base64,{$base64}";
            }
        } catch (\Exception $e) {
            // Retourner vide si erreur
        }

        return '';
    }

    /**
     * Sort items by selected criteria
     */
    private function sortItems(array $items, string $sort = 'added_at'): array
    {
        usort($items, function ($a, $b) use ($sort) {
            switch ($sort) {
                case 'title':
                    return strcasecmp($this->formatTitle($a), $this->formatTitle($b));

                case 'rating':
                    return ($b['rating'] ?? 0) <=> ($a['rating'] ?? 0);

                case 'vote_average':
                    return ($b['vote_average'] ?? 0) <=> ($a['vote_average'] ?? 0);

                case 'added_at':
                default:
                    $aDate = $a['added_at'] ? Carbon::parse($a['added_at']) : Carbon::now();
                    $bDate = $b['added_at'] ? Carbon::parse($b['added_at']) : Carbon::now();
                    return $bDate <=> $aDate; // Descendant
            }
        });

        return $items;
    }

    /**
     * Format title: FR → EN → Original
     */
    private function formatTitle(array $item): string
    {
        // Chercher dans les translations
        if (isset($item['translations']) && is_array($item['translations'])) {
            if (!empty($item['translations']['fr']['name'] ?? null)) {
                return $item['translations']['fr']['name'];
            }
            if (!empty($item['translations']['en']['name'] ?? null)) {
                return $item['translations']['en']['name'];
            }
        }

        // Fallback sur les champs directs
        if (!empty($item['name'])) {
            return $item['name'];
        }

        if (!empty($item['en_name'])) {
            return $item['en_name'];
        }

        return $item['original_name'] ?? 'N/A';
    }

    /**
     * Format rating emoji
     */
    private function formatRating(?int $rating): string
    {
        if ($rating === null) {
            return '';
        }

        return match ($rating) {
            1 => '👎',
            2 => '👍',
            3 => '👍👍',
            default => '',
        };
    }

    /**
     * Format genres list
     */
    private function formatGenres(array $genres): string
    {
        if (empty($genres)) {
            return '';
        }

        return implode(', ', array_map(fn($g) => $g['name'] ?? '', $genres));
    }

    /**
     * Format options for display in PDF
     */
    private function formatOptionsForDisplay(array $options): array
    {
        $display = [];

        // Filtres
        $filters = $options['filters'] ?? [];
        $filterLabels = [];
        if ($filters['watched'] ?? false) $filterLabels[] = 'Regardés';
        if ($filters['to_watch'] ?? false) $filterLabels[] = 'À regarder';
        $display['filters'] = implode(' + ', $filterLabels) ?: 'Aucun filtre';

        // Colonnes
        $columns = $options['columns'] ?? [];
        $columnLabels = [];
        if ($columns['poster'] ?? false) $columnLabels[] = 'Images';
        if ($columns['title'] ?? false) $columnLabels[] = 'Titre';
        if ($columns['status'] ?? false) $columnLabels[] = 'Statut';
        if ($columns['rating'] ?? false) $columnLabels[] = 'Rating';
        if ($columns['year'] ?? false) $columnLabels[] = 'Année';
        if ($columns['vote_average'] ?? false) $columnLabels[] = 'Vote TMDB';
        if ($columns['genres'] ?? false) $columnLabels[] = 'Genres';
        if ($columns['synopsis'] ?? false) $columnLabels[] = 'Synopsis';
        if ($columns['networks'] ?? false) $columnLabels[] = 'Networks';
        $display['columns'] = implode(', ', $columnLabels);

        // Tri
        $sortLabels = [
            'added_at' => 'Date d\'ajout',
            'title' => 'Titre (A-Z)',
            'rating' => 'Rating personnel',
            'vote_average' => 'Vote TMDB',
        ];
        $display['sort'] = $sortLabels[$options['sort'] ?? 'added_at'] ?? 'Date d\'ajout';

        return $display;
    }


    /**
     * Generate filename
     */
    public function generateFilename(string $username, string $format = 'pdf'): string
    {
        $date = now()->format('Y-m-d');
        $filename = "watchlist_{$username}_{$date}.{$format}";

        // Sanitize filename
        return preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
    }

    /**
     * Generate cache hash for export parameters
     */
    public function generateCacheHash(int $userId, array $options = []): string
    {
        $cacheKey = json_encode([
            'user_id' => $userId,
            'filters' => $options['filters'] ?? [],
            'columns' => $options['columns'] ?? [],
            'sort' => $options['sort'] ?? 'added_at',
        ]);

        return md5($cacheKey);
    }

    /**
     * Get cached PDF if exists and not expired (7 days)
     */
    public function getCachedPDF(int $userId, array $options = []): ?string
    {
        $hash = $this->generateCacheHash($userId, $options);
        $cacheFile = storage_path("app/exports/watchlist_{$userId}_{$hash}.pdf");

        // Vérifier si le fichier existe et n'est pas expiré (7 jours)
        if (file_exists($cacheFile)) {
            $fileAge = time() - filemtime($cacheFile);
            $sevenDaysInSeconds = 7 * 24 * 60 * 60;

            if ($fileAge < $sevenDaysInSeconds) {
                return file_get_contents($cacheFile);
            } else {
                // Supprimer le fichier expiré
                unlink($cacheFile);
            }
        }

        return null;
    }

    /**
     * Save PDF to cache
     */
    public function cachePDF(int $userId, array $options, string $pdfContent): void
    {
        $hash = $this->generateCacheHash($userId, $options);
        $cacheDir = storage_path('app/exports');

        // Créer le répertoire s'il n'existe pas
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }

        $cacheFile = "{$cacheDir}/watchlist_{$userId}_{$hash}.pdf";
        file_put_contents($cacheFile, $pdfContent);
    }

    /**
     * Export to PDF with cache support
     */
    public function exportToPDFWithCache(int $userId, array $options = []): string
    {
        // Vérifier le cache en premier
        $cached = $this->getCachedPDF($userId, $options);
        if ($cached !== null) {
            return $cached;
        }

        // Générer le PDF
        $pdf = $this->exportToPDF($userId, $options);

        // Sauvegarder en cache
        $this->cachePDF($userId, $options, $pdf);

        return $pdf;
    }
}
