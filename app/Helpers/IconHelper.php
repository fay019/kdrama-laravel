<?php

namespace App\Helpers;

class IconHelper
{
    /**
     * Get icon SVG content by name (supports both Tabler and Simple Icons)
     */
    public static function getSvg(string $iconName, array $attributes = []): string
    {
        // Try Tabler first (if available)
        $tablerPath = base_path('node_modules/@tabler/icons/icons/outline/' . $iconName . '.svg');
        if (file_exists($tablerPath)) {
            $svgContent = file_get_contents($tablerPath);
            if (!empty($attributes)) {
                $svgContent = str_replace('<svg', '<svg ' . self::buildAttributes($attributes), $svgContent);
            }
            return $svgContent;
        }

        // Try Simple Icon (Composer package - always available)
        $simpleIconPath = base_path('vendor/codeat3/blade-simple-icons/resources/svg/' . $iconName . '.svg');
        if (file_exists($simpleIconPath)) {
            $svgContent = file_get_contents($simpleIconPath);
            if (!empty($attributes)) {
                $svgContent = str_replace('<svg', '<svg ' . self::buildAttributes($attributes), $svgContent);
            }
            return $svgContent;
        }

        return '';
    }

    /**
     * Build SVG attributes string
     */
    private static function buildAttributes(array $attributes): string
    {
        $parts = [];
        foreach ($attributes as $key => $value) {
            $parts[] = "{$key}=\"{$value}\"";
        }
        return implode(' ', $parts);
    }

    /**
     * Map PDF field icons
     */
    public static function getPdfFieldIcon(string $fieldName): string
    {
        $iconMap = [
            'status' => 'circle-check',      // For "Vu" status
            'year' => 'calendar-check',       // For year
            'vote_average' => 'star',         // For TMDB vote
            'rating' => 'heart',              // For personal rating
            'genres' => 'mask',               // For genres
            'synopsis' => 'book',             // For synopsis
        ];

        $iconName = $iconMap[$fieldName] ?? null;
        if (!$iconName) {
            return '';
        }

        return self::getSvg($iconName, [
            'width' => '16',
            'height' => '16',
            'viewBox' => '0 0 24 24',
            'stroke-width' => '2',
            'stroke' => 'currentColor',
            'fill' => 'none',
        ]);
    }

    /**
     * Get list of available Simple Icons (Social Media & Popular Networks)
     * Source: https://simpleicons.org/
     *
     * Usage in Blade:
     * <x-si-{icon-name} class="w-5 h-5 fill-current" />
     */
    public static function getSimpleIcons(): array
    {
        return [
            // Social Media & Communication
            'instagram' => 'Instagram',
            'facebook' => 'Facebook',
            'x' => 'X (Twitter)',
            'tiktok' => 'TikTok',
            'youtube' => 'YouTube',
            'linkedin' => 'LinkedIn',
            'pinterest' => 'Pinterest',
            'snapchat' => 'Snapchat',
            'telegram' => 'Telegram',
            'whatsapp' => 'WhatsApp',
            'discord' => 'Discord',
            'twitch' => 'Twitch',
            'reddit' => 'Reddit',
            'bluesky' => 'Bluesky',
            'mastodon' => 'Mastodon',
            'threads' => 'Threads',

            // Video & Media
            'vimeo' => 'Vimeo',
            'imdb' => 'IMDb',
            'netflix' => 'Netflix',
            'primevideo' => 'Prime Video',
            'appletv' => 'Apple TV',
            'disneyplus' => 'Disney+',
            'hulu' => 'Hulu',
            'spotify' => 'Spotify',
            'soundcloud' => 'SoundCloud',

            // Dev & Tech
            'github' => 'GitHub',
            'gitlab' => 'GitLab',
            'bitbucket' => 'Bitbucket',
            'stackoverflow' => 'Stack Overflow',
            'npm' => 'NPM',
            'composer' => 'Composer',

            // Knowledge & Reference
            'wikipedia' => 'Wikipedia',
            'wikidata' => 'Wikidata',

            // Other Popular
            'email' => 'Email',
            'website' => 'Website',
            'rss' => 'RSS',
        ];
    }

    /**
     * Get icon with fallback - tries Tabler first, then Simple Icons
     */
    public static function getIconWithFallback(string $iconName): array
    {
        // Check Tabler Icons first (if available)
        $tablerPath = base_path('node_modules/@tabler/icons/icons/outline/' . $iconName . '.svg');
        if (file_exists($tablerPath)) {
            return [
                'name' => $iconName,
                'type' => 'tabler',
                'label' => $iconName,
                'exists' => true,
            ];
        }

        // Check Simple Icons
        $simpleIcons = self::getSimpleIcons();
        if (isset($simpleIcons[$iconName])) {
            return [
                'name' => $iconName,
                'type' => 'simple',
                'label' => $simpleIcons[$iconName],
                'exists' => true,
            ];
        }

        // Icon doesn't exist anywhere
        return [
            'name' => $iconName,
            'type' => 'missing',
            'label' => $iconName,
            'exists' => false,
        ];
    }

    /**
     * Get a specific Simple Icon label by name
     */
    public static function getSimpleIconLabel(string $iconName): ?string
    {
        $icons = self::getSimpleIcons();
        return $icons[$iconName] ?? null;
    }

    /**
     * Check if a Simple Icon exists
     */
    public static function hasSimpleIcon(string $iconName): bool
    {
        return array_key_exists($iconName, self::getSimpleIcons());
    }
}
