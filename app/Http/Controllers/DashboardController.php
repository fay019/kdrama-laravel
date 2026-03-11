<?php

namespace App\Http\Controllers;

use App\Models\WatchlistItem;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $watchlist = WatchlistItem::where('user_id', $user->id)
            ->where('is_in_watchlist', true)
            ->where('is_watched', false)
            ->where('is_watching', false)
            ->with(['kdrama'])
            ->orderBy('added_at', 'desc')
            ->get();

        $watching = WatchlistItem::where('user_id', $user->id)
            ->where('is_watching', true)
            ->with(['kdrama'])
            ->orderBy('updated_at', 'desc')
            ->get();

        $watched = WatchlistItem::where('user_id', $user->id)
            ->where('is_watched', true)
            ->with(['kdrama'])
            ->orderBy('updated_at', 'desc')
            ->get();

        // Get rated items (items with a rating)
        $rated = WatchlistItem::where('user_id', $user->id)
            ->whereNotNull('rating')
            ->with(['kdrama'])
            ->orderBy('updated_at', 'desc')
            ->get();

        // Calculate rating statistics
        $ratedCount = $rated->count();
        $avgRating = $ratedCount > 0 ? round($rated->avg('rating'), 1) : 0;

        // Statistiques
        $stats = [
            'total_watchlist' => $watchlist->count(),
            'total_watching' => $watching->count(),
            'total_watched' => $watched->count(),
            'total_rated' => $ratedCount,
            'avg_rating' => $avgRating,
        ];

        return view('dashboard', [
            'watchlist' => $watchlist,
            'watching' => $watching,
            'watched' => $watched,
            'rated' => $rated,
            'stats' => $stats,
            'user' => $user,
        ]);
    }
}
