<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Kdrama;
use App\Models\Setting;
use Illuminate\Http\Request;

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
}
