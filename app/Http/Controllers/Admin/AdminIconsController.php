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
        $allIcons = [];

        // Get Tabler Icons from Composer package
        $tablerPath = base_path('vendor/secondnetwork/blade-tabler-icons/resources/svg');
        if (is_dir($tablerPath)) {
            $files = scandir($tablerPath);
            foreach ($files as $file) {
                if (str_ends_with($file, '.svg')) {
                    $iconName = str_replace('.svg', '', $file);
                    if (empty($query) || str_contains($iconName, $query)) {
                        $allIcons[] = [
                            'name' => $iconName,
                            'type' => 'tabler',
                            'label' => $iconName,
                        ];
                    }
                }
            }
        }

        // Get Simple Icons from file system (always available via Composer)
        $simpleIconsPath = base_path('vendor/codeat3/blade-simple-icons/resources/svg');
        if (is_dir($simpleIconsPath)) {
            $files = scandir($simpleIconsPath);
            foreach ($files as $file) {
                if (str_ends_with($file, '.svg')) {
                    $iconName = str_replace('.svg', '', $file);
                    if (empty($query) || str_contains($iconName, $query)) {
                        $allIcons[] = [
                            'name' => $iconName,
                            'type' => 'simple',
                            'label' => $iconName,
                        ];
                    }
                }
            }
        }

        // Sort icons
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
                    $svgContent = $this->getTablerIconSvg($icon['name']);
                } else {
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
     * Get Tabler Icon SVG content from Composer package
     */
    private function getTablerIconSvg(string $iconName): ?string
    {
        $svgPath = base_path('vendor/secondnetwork/blade-tabler-icons/resources/svg/' . $iconName . '.svg');
        if (file_exists($svgPath)) {
            $content = file_get_contents($svgPath);
            // Add sizing class
            return str_replace('<svg', '<svg class="w-6 h-6 stroke-current"', $content);
        }
        return null;
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
