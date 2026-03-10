<?php

namespace App\Helpers;

class IconHelper
{
    /**
     * Get icon SVG content by name
     */
    public static function getSvg(string $iconName, array $attributes = []): string
    {
        $iconsPath = base_path('node_modules/@tabler/icons/icons/outline');
        $svgPath = "{$iconsPath}/{$iconName}.svg";

        if (!file_exists($svgPath)) {
            return ''; // Return empty if icon not found
        }

        $svgContent = file_get_contents($svgPath);

        // Add attributes like width, height, class
        if (!empty($attributes)) {
            $svgContent = str_replace('<svg', '<svg ' . $this->buildAttributes($attributes), $svgContent);
        }

        return $svgContent;
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
}
