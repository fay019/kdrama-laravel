<?php

namespace App\Services;

use App\Models\WatchlistItem;
use App\Models\User;
use Carbon\Carbon;
use Mpdf\Mpdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class WatchlistExportService
{
    /**
     * Export watchlist to PDF with improved duplicate handling
     */
    public function exportToPDF(int $userId, array $options = []): string
    {
        $user = User::findOrFail($userId);

        // Get locale from options or use user's preferred language
        $locale = $options['locale'] ?? ($user->preferred_language ?? 'fr');
        app()->setLocale($locale);

        // Get filtered items with better duplicate prevention
        $items = $this->getFilteredWatchlist($userId, $options);

        // Log pour déboguer
        Log::info("Export PDF - User {$userId}: " . count($items) . " items récupérés");

        // Calculer stats - compter par status (plus fiable)
        $totalItems = count($items);
        $watchedCount = 0;
        $watchingCount = 0;
        $toWatchCount = 0;

        foreach ($items as $item) {
            $status = $item['status'] ?? null;
            if ($status === 'watched') {
                $watchedCount++;
            } elseif ($status === 'watching') {
                $watchingCount++;
            } elseif ($status === 'to_watch') {
                $toWatchCount++;
            }
        }

        // Formater les options pour l'affichage
        $displayOptions = $this->formatOptionsForDisplay($options);

        // Paginer les items
        $itemsPerPage = 4;
        $pages = array_chunk($items, $itemsPerPage);
        $totalPages = count($pages);

        // Préparer les données pour la vue
        $viewData = [
            'user' => $user,
            'pages' => $pages,
            'totalItems' => $totalItems,
            'watchedCount' => $watchedCount,
            'watchingCount' => $watchingCount,
            'toWatchCount' => $toWatchCount,
            'options' => $options,
            'displayOptions' => $displayOptions,
            'selectedColumns' => $options['columns'] ?? [],
            'locale' => $locale,
            'totalPages' => $totalPages,
        ];

        // Générer le HTML
        $html = view('exports.watchlist-pdf', $viewData)->render();

        // Remplacer les émojis pour une meilleure compatibilité PDF
        $html = $this->replaceEmojisForPDF($html);

        // Utiliser mPDF pour générer le PDF
        try {
            $tempDir = storage_path('app/temp');
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            $mpdf = new Mpdf([
                'tempDir' => $tempDir,
                'mode' => 'utf-8',
                'format' => 'A4',
                'margin_left' => 15,
                'margin_right' => 15,
                'margin_top' => 20,
                'margin_bottom' => 20,
                'default_font' => 'dejavusans',
                'default_font_size' => 10,
                'orientation' => 'P',
            ]);

            // Enable Unicode support
            $mpdf->useAdobeCJK = true;
            $mpdf->autoScriptToLang = true;
            $mpdf->autoLangToFont = true;
            $mpdf->allow_charset_conversion = true;
            $mpdf->charset_in = 'UTF-8';

            // Écrire le HTML
            $mpdf->WriteHTML($html);

            // Retourner le PDF comme string
            return $mpdf->Output('', 'S');

        } catch (\Exception $e) {
            Log::error("PDF generation failed: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get filtered and sorted watchlist - VERSION CORRIGÉE SANS ERREUR 500
     */
    private function getFilteredWatchlist(int $userId, array $options = []): array
    {
        // Options par défaut
        $filters = $options['filters'] ?? ['watched' => true, 'to_watch' => true, 'watching' => true];
        $sort = $options['sort'] ?? 'added_at';
        $columns = $options['columns'] ?? [];

        // Récupérer les items avec une requête simple sans jointures complexes
        $query = WatchlistItem::where('user_id', $userId)
            ->with('kdrama');

        // Appliquer les filtres
        $query->where(function ($q) use ($filters) {
            $hasFilter = false;

            if (!empty($filters['watched'])) {
                $q->orWhere('is_watched', true);
                $hasFilter = true;
            }
            if (!empty($filters['watching'])) {
                $q->orWhere('is_watching', true);
                $hasFilter = true;
            }
            if (!empty($filters['to_watch'])) {
                $q->orWhere('is_in_watchlist', true);
                $hasFilter = true;
            }

            // Si aucun filtre, ne rien faire (ça retournera tous les items)
        });

        // Récupérer les résultats
        $items = $query->get();

        // Formatter les items et gérer les doublons en PHP (plus sûr que SQL)
        $formatted = [];
        $seenKeys = []; // Pour traquer les combinaisons TMDB ID + statut

        foreach ($items as $item) {
            $kdrama = $item->kdrama;

            if (!$kdrama) {
                continue; // Skip si pas de kdrama associé
            }

            $kdramaArray = $kdrama->toArray();

            // Déterminer le statut
            $status = 'to_watch';
            if ($item->is_watched) {
                $status = 'watched';
            } elseif ($item->is_watching) {
                $status = 'watching';
            }

            // Créer une clé unique basée sur TMDB ID + statut
            $uniqueKey = $kdrama->tmdb_id . '_' . $status;

            // Vérifier si on a déjà vu cette combinaison
            if (isset($seenKeys[$uniqueKey])) {
                continue;
            }

            $seenKeys[$uniqueKey] = true;

            // Ajouter l'URL du poster
            if (!empty($kdramaArray['poster_path'])) {
                $kdramaArray['poster_url'] = "https://image.tmdb.org/t/p/w200{$kdramaArray['poster_path']}";
            }

            // Ajouter les genres et networks s'ils existent
            if (!empty($kdramaArray['genres']) && is_string($kdramaArray['genres'])) {
                $kdramaArray['genres'] = json_decode($kdramaArray['genres'], true) ?? [];
            }

            if (!empty($kdramaArray['networks']) && is_string($kdramaArray['networks'])) {
                $kdramaArray['networks'] = json_decode($kdramaArray['networks'], true) ?? [];
            }

            // Fusionner avec les données de la watchlist
            $formattedItem = array_merge($kdramaArray, [
                'watchlist_id' => $item->id,
                'is_watched' => $item->is_watched,
                'is_watching' => $item->is_watching,
                'is_in_watchlist' => $item->is_in_watchlist,
                'added_at' => $item->added_at,
                'rating' => $item->rating,
                'user_rating' => $item->rating,
                'status' => $status,
                'unique_key' => $uniqueKey,
            ]);

            // Formater le titre
            $formattedItem['display_title'] = $this->formatTitle($formattedItem);

            $formatted[] = $formattedItem;
        }

        // Trier les items en PHP (plus fiable)
        $formatted = $this->sortItems($formatted, $sort);

        return $formatted;
    }

    /**
     * Sort items by selected criteria (version PHP pure)
     */
    private function sortItems(array $items, string $sort = 'added_at'): array
    {
        usort($items, function ($a, $b) use ($sort) {
            switch ($sort) {
                case 'title':
                    $titleA = $this->formatTitle($a);
                    $titleB = $this->formatTitle($b);
                    return strcasecmp($titleA, $titleB);

                case 'rating':
                    $ratingA = $a['user_rating'] ?? 0;
                    $ratingB = $b['user_rating'] ?? 0;
                    return $ratingB <=> $ratingA;

                case 'vote_average':
                    $voteA = $a['vote_average'] ?? 0;
                    $voteB = $b['vote_average'] ?? 0;
                    return $voteB <=> $voteA;

                case 'added_at':
                default:
                    $aDate = !empty($a['added_at']) ? Carbon::parse($a['added_at']) : Carbon::now();
                    $bDate = !empty($b['added_at']) ? Carbon::parse($b['added_at']) : Carbon::now();
                    return $bDate <=> $aDate;
            }
        });

        return $items;
    }

    /**
     * Format title: FR → EN → Original
     */
    private function formatTitle(array $item): string
    {
        // Chercher dans les translations si disponibles
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

        return $item['original_name'] ?? 'Titre inconnu';
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
            1 => '👎 Bof',
            2 => '👍 Bien',
            3 => '👍👍 Très bien',
            default => '',
        };
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
        if (!empty($filters['watched'])) $filterLabels[] = 'Regardés';
        if (!empty($filters['watching'])) $filterLabels[] = 'En cours';
        if (!empty($filters['to_watch'])) $filterLabels[] = 'À regarder';
        $display['filters'] = implode(' + ', $filterLabels) ?: 'Tous';

        // Colonnes
        $columns = $options['columns'] ?? [];
        $columnLabels = [];
        if (!empty($columns['poster'])) $columnLabels[] = 'Images';
        if (!empty($columns['title'])) $columnLabels[] = 'Titre';
        if (!empty($columns['status'])) $columnLabels[] = 'Statut';
        if (!empty($columns['rating'])) $columnLabels[] = 'Rating';
        if (!empty($columns['year'])) $columnLabels[] = 'Année';
        if (!empty($columns['vote_average'])) $columnLabels[] = 'Vote TMDB';
        if (!empty($columns['genres'])) $columnLabels[] = 'Genres';
        if (!empty($columns['synopsis'])) $columnLabels[] = 'Synopsis';
        if (!empty($columns['networks'])) $columnLabels[] = 'Networks';
        $display['columns'] = implode(', ', $columnLabels) ?: 'Colonnes par défaut';

        // Tri
        $sortLabels = [
            'added_at' => 'Date d\'ajout (récent)',
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

        if (file_exists($cacheFile)) {
            $fileAge = time() - filemtime($cacheFile);
            $sevenDaysInSeconds = 7 * 24 * 60 * 60;

            if ($fileAge < $sevenDaysInSeconds) {
                return file_get_contents($cacheFile);
            } else {
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

    /**
     * Replace emojis with text for PDF compatibility
     */
    private function replaceEmojisForPDF(string $html): string
    {
        $emojiMap = [
            '✅' => '[Vu]',
            '👎' => '[Bof]',
            '👍' => '[Bien]',
            '👍👍' => '[Très bien]',
            '📅' => '[Année]',
            '⭐' => '[Note]',
            '📺' => '[TV]',
            '👤' => '[User]',
            '🌟' => '[Populaire]',
            '🎬' => '[En cours]',
            '📧' => '[Email]',
            '⚙' => '[Options]',
            '🍿' => '[KDrama]',
            '🎭' => '[Genres]',
            '💭' => '[Rating]',
            '📊' => '[Stats]',
            '📌' => '[Statut]',
            '📖' => '[Synopsis]',
            '📭' => '[Vide]',
            '️' => '', // Remove zero-width joiner
        ];

        return str_replace(array_keys($emojiMap), array_values($emojiMap), $html);
    }

    /**
     * Export to CSV
     */
    public function exportToCSV(int $userId, array $options = []): string
    {
        $items = $this->getFilteredWatchlist($userId, $options);
        $selectedColumns = $options['columns'] ?? [];

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
                $status = '';
                if (!empty($item['is_watched'])) {
                    $status = 'Vu';
                } elseif (!empty($item['is_watching'])) {
                    $status = 'En cours';
                } else {
                    $status = 'À regarder';
                }
                $row[] = "\"{$status}\"";
            }

            if ($selectedColumns['rating'] ?? true) {
                $rating = $this->formatRating($item['rating'] ?? null);
                $row[] = "\"{$rating}\"";
            }

            if ($selectedColumns['year'] ?? true) {
                $year = !empty($item['first_air_date']) ? Carbon::parse($item['first_air_date'])->year : 'N/A';
                $row[] = $year;
            }

            if ($selectedColumns['vote_average'] ?? true) {
                $voteAverage = $item['vote_average'] ?? 0;
                $row[] = number_format($voteAverage, 1);
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
                $synopsis = str_replace(['"', "\n", "\r"], ['""', ' ', ' '], $item['overview'] ?? '');
                $synopsis = substr($synopsis, 0, 200);
                $row[] = "\"{$synopsis}\"";
            }

            $csv .= implode(',', $row) . "\n";
        }

        return $csv;
    }

    /**
     * Format genres list
     */
    private function formatGenres($genres): string
    {
        if (empty($genres)) {
            return '';
        }

        if (is_string($genres)) {
            return $genres;
        }

        if (is_array($genres)) {
            $genreNames = [];
            foreach ($genres as $genre) {
                if (is_array($genre) && !empty($genre['name'])) {
                    $genreNames[] = $genre['name'];
                } elseif (is_string($genre)) {
                    $genreNames[] = $genre;
                }
            }
            return implode(', ', $genreNames);
        }

        return '';
    }
}
