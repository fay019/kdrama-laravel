<?php

namespace App\Http\Controllers;

use App\Models\Kdrama;
use App\Models\Setting;
use App\Models\User;

class AdminController extends Controller
{
    public function index()
    {
        $totalUsers = User::count();
        $totalContents = Kdrama::count();
        $cacheDuration = Setting::get('rapidapi_cache_hours', 24);
        $lastSync = null;

        return view('admin.dashboard', [
            'totalUsers' => $totalUsers,
            'totalContents' => $totalContents,
            'cacheDuration' => $cacheDuration,
            'lastSync' => $lastSync,
        ]);
    }

    public function toggleAdultContent($tmdbId)
    {
        $kdrama = Kdrama::where('tmdb_id', $tmdbId)->first();

        if (! $kdrama) {
            return response()->json(['error' => 'Content not found'], 404);
        }

        $kdrama->adult_only = ! $kdrama->adult_only;
        $kdrama->save();

        return response()->json([
            'status' => 'success',
            'adult_only' => $kdrama->adult_only,
            'message' => $kdrama->adult_only ? 'Marked as adult content' : 'Unmarked as adult content',
        ]);
    }
}
