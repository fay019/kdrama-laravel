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

        return redirect()->route('admin.settings.index')->with('success', '✅ '.__('admin.settings_save'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'key' => 'required|string|unique:settings,key',
            'group' => 'required|string',
            'label' => 'required|string',
            'value' => 'nullable|string',
        ]);

        // Get max order for this group
        $maxOrder = Setting::where('group', $validated['group'])->max('order') ?? 0;

        Setting::create([
            'key' => $validated['key'],
            'group' => $validated['group'],
            'label' => $validated['label'],
            'value' => $validated['value'] ?? '',
            'order' => $maxOrder + 1,
            'is_deletable' => true,
            'is_sensitive' => $request->has('is_sensitive'),
        ]);

        return redirect()->route('admin.settings.index')->with('success', "✅ Setting '{$validated['key']}' created successfully!");
    }

    public function updateSetting(Request $request, $id)
    {
        $setting = Setting::findOrFail($id);

        $validated = $request->validate([
            'label' => 'required|string',
            'value' => 'nullable|string',
        ]);

        $setting->update([
            'label' => $validated['label'],
            'value' => $validated['value'] ?? '',
            'is_sensitive' => $request->has('is_sensitive'),
        ]);

        return redirect()->route('admin.settings.index')->with('success', "✅ Setting '{$setting->key}' updated successfully!");
    }

    public function deleteSetting(Request $request, $id)
    {
        $setting = Setting::findOrFail($id);

        if (! $setting->is_deletable) {
            return redirect()->route('admin.settings.index')->with('error', '❌ This setting cannot be deleted.');
        }

        $setting->delete();

        return redirect()->route('admin.settings.index')->with('success', '✅ Setting deleted successfully!');
    }

    public function moveSetting(Request $request, $id)
    {
        $setting = Setting::findOrFail($id);
        $direction = $request->input('direction'); // 'up' or 'down'

        $groupSettings = Setting::where('group', $setting->group)
            ->orderBy('order')
            ->get();

        $currentIndex = $groupSettings->search(fn ($s) => $s->id === $setting->id);

        $moved = false;

        if ($direction === 'up' && $currentIndex > 0) {
            $swapWith = $groupSettings[$currentIndex - 1];
            [$setting->order, $swapWith->order] = [$swapWith->order, $setting->order];
            $setting->save();
            $swapWith->save();
            $moved = true;
        } elseif ($direction === 'down' && $currentIndex < $groupSettings->count() - 1) {
            $swapWith = $groupSettings[$currentIndex + 1];
            [$setting->order, $swapWith->order] = [$swapWith->order, $setting->order];
            $setting->save();
            $swapWith->save();
            $moved = true;
        }

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'moved' => $moved,
                'message' => $moved ? '✅ '.__('admin.settings_save') : 'No change needed',
            ]);
        }

        return redirect()->route('admin.settings.index')->with('success', '✅ '.__('admin.settings_save'));
    }
}
