<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminIconsController extends Controller
{
    public function search(Request $request)
    {
        $query = strtolower($request->get('q', ''));
        $iconsPath = base_path('node_modules/@tabler/icons/icons/outline');
        
        $allIcons = [];
        if (is_dir($iconsPath)) {
            $files = scandir($iconsPath);
            foreach ($files as $file) {
                if (str_ends_with($file, '.svg')) {
                    $iconName = str_replace('.svg', '', $file);
                    if (empty($query) || str_contains($iconName, $query)) {
                        $allIcons[] = $iconName;
                    }
                }
            }
        }
        
        sort($allIcons);
        
        // Si c'est une requête AJAX, retourner JSON avec SVG
        if ($request->wantsJson()) {
            $icons = array_slice($allIcons, 0, 100);
            $iconsWithSvg = [];

            foreach ($icons as $iconName) {
                $svgPath = "{$iconsPath}/{$iconName}.svg";
                $svgContent = file_exists($svgPath) ? file_get_contents($svgPath) : null;

                if ($svgContent) {
                    // Modifier le SVG pour avoir la bonne taille et couleur
                    $svgContent = str_replace('<svg', '<svg class="w-6 h-6 text-current"', $svgContent);
                }

                $iconsWithSvg[] = [
                    'name' => $iconName,
                    'svg' => $svgContent,
                ];
            }

            return response()->json([
                'icons' => $iconsWithSvg,
                'total' => count($allIcons),
            ]);
        }

        return view('admin.icons.search', [
            'icons' => $allIcons,
            'search' => $query,
            'count' => count($allIcons),
        ]);
    }
}
