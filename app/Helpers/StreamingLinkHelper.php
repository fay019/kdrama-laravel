<?php

namespace App\Helpers;

class StreamingLinkHelper
{
    /**
     * Mappe les noms de réseaux TMDB aux URLs de recherche
     */
    private static $networkUrls = [
        'Netflix' => 'https://www.netflix.com/search?q=',
        'Apple TV+' => 'https://tv.apple.com/search?term=',
        'Amazon Prime Video' => 'https://www.primevideo.com/search?keyword=',
        'Disney+' => 'https://www.disneyplus.com/search?query=',
    ];

    /**
     * Génère les liens de recherche basés sur les networks
     */
    public static function generateStreamingLinks($kdrama)
    {
        $networks = $kdrama['networks'] ?? [];
        $title = $kdrama['en_name'] ?? $kdrama['name'] ?? '';

        if (empty($networks) || empty($title)) {
            return [];
        }

        $links = [];

        foreach ($networks as $network) {
            $networkName = $network['name'] ?? '';

            if (isset(self::$networkUrls[$networkName])) {
                $searchUrl = self::$networkUrls[$networkName] . urlencode($title);

                $links[] = [
                    'name' => $networkName,
                    'url' => $searchUrl,
                    'icon' => self::getNetworkIcon($networkName),
                    'color' => self::getNetworkColor($networkName),
                ];
            }
        }

        return $links;
    }

    /**
     * Retourne l'emoji/icône pour chaque plateforme
     */
    private static function getNetworkIcon($networkName)
    {
        $icons = [
            'Netflix' => '🎬',
            'Apple TV+' => '🍎',
            'Amazon Prime Video' => '📦',
            'Disney+' => '✨',
        ];

        return $icons[$networkName] ?? '📺';
    }

    /**
     * Retourne la couleur pour chaque plateforme
     */
    private static function getNetworkColor($networkName)
    {
        $colors = [
            'Netflix' => 'from-red-600 to-red-700',
            'Apple TV+' => 'from-gray-700 to-gray-800',
            'Amazon Prime Video' => 'from-blue-600 to-blue-700',
            'Disney+' => 'from-purple-600 to-purple-700',
        ];

        return $colors[$networkName] ?? 'from-slate-600 to-slate-700';
    }
}
