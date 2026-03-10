<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class AdminSettingsController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->groupBy('group');
        return view('admin.settings.index', ['settings' => $settings]);
    }

    public function update(Request $request)
    {
        foreach ($request->all() as $key => $value) {
            if ($key !== '_token') {
                Setting::set($key, $value);
            }
        }

        return redirect()->route('admin.settings.index')->with('success', __('admin.settings.settings_saved'));
    }
}
