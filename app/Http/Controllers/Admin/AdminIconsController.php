<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\IconHelper;
use Illuminate\Http\Request;

class AdminIconsController extends Controller
{
    public function search(Request $request)
    {
        $query = strtolower($request->get('q', ''));
        $iconsPath = base_path('node_modules/@tabler/icons/icons/outline');

        // Get Tabler Icons from file system
        $tabledIcons = [];
        if (is_dir($iconsPath)) {
            $files = scandir($iconsPath);
            foreach ($files as $file) {
                if (str_ends_with($file, '.svg')) {
                    $iconName = str_replace('.svg', '', $file);
                    if (empty($query) || str_contains($iconName, $query)) {
                        $tabledIcons[] = [
                            'name' => $iconName,
                            'type' => 'tabler',
                            'label' => $iconName,
                        ];
                    }
                }
            }
        }

        // Get ALL Simple Icons from file system
        $simpleIcons = [];
        $simpleIconsPath = base_path('vendor/codeat3/blade-simple-icons/resources/svg');
        if (is_dir($simpleIconsPath)) {
            $files = scandir($simpleIconsPath);
            foreach ($files as $file) {
                if (str_ends_with($file, '.svg')) {
                    $iconName = str_replace('.svg', '', $file);
                    if (empty($query) || str_contains($iconName, $query)) {
                        $simpleIcons[] = [
                            'name' => $iconName,
                            'type' => 'simple',
                            'label' => $iconName,
                        ];
                    }
                }
            }
        }

        // Combine all icons
        $allIcons = array_merge($tabledIcons, $simpleIcons);
        usort($allIcons, function ($a, $b) {
            return strcmp($a['name'], $b['name']);
        });

        // Si c'est une requête AJAX, retourner JSON avec SVG
        if ($request->wantsJson()) {
            $offset = max(0, intval($request->get('offset', 0)));
            $limit = 100;
            $icons = array_slice($allIcons, $offset, $limit);
            $iconsWithSvg = [];

            foreach ($icons as $icon) {
                $svgContent = null;

                if ($icon['type'] === 'tabler') {
                    $svgPath = "{$iconsPath}/{$icon['name']}.svg";
                    if (file_exists($svgPath)) {
                        $svgContent = file_get_contents($svgPath);
                        $svgContent = str_replace('<svg', '<svg class="w-6 h-6 text-current"', $svgContent);
                    }
                } elseif ($icon['type'] === 'simple') {
                    // Get actual Simple Icon SVG
                    $svgContent = $this->getSimpleIconSvg($icon['name']);
                }

                if ($svgContent) {
                    $iconsWithSvg[] = [
                        'name' => $icon['name'],
                        'type' => $icon['type'],
                        'label' => $icon['label'],
                        'svg' => $svgContent,
                    ];
                }
            }

            return response()->json([
                'icons' => $iconsWithSvg,
                'total' => count($allIcons),
            ]);
        }

        return view('admin.icons.search', [
            'icons' => array_slice($allIcons, 0, 100),
            'search' => $query,
            'count' => count($allIcons),
        ]);
    }

    /**
     * Check if a Simple Icon SVG file exists
     */
    private function simpleIconExists(string $iconName): bool
    {
        $svgPath = base_path('vendor/codeat3/blade-simple-icons/resources/svg/' . $iconName . '.svg');
        return file_exists($svgPath);
    }

    /**
     * Get Simple Icon SVG content
     */
    private function getSimpleIconSvg(string $iconName): ?string
    {
        $svgPath = base_path('vendor/codeat3/blade-simple-icons/resources/svg/' . $iconName . '.svg');
        if (file_exists($svgPath)) {
            $content = file_get_contents($svgPath);
            // Add sizing class
            return str_replace('<svg', '<svg class="w-6 h-6 fill-current"', $content);
        }
        return null;
    }
}
