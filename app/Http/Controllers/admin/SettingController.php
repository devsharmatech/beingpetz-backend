<?php
// app/Http/Controllers/Admin/SettingController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::where('is_active', true)
            ->orderBy('group')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('group');

        $groups = [
            'website' => 'Website Settings',
            'email' => 'Email Settings', 
            'notification' => 'Notification Settings',
            'page' => 'Page Settings'
        ];

        return view('admin.settings.genral_settings', compact('settings', 'groups'));
    }

    public function manage($group)
    {
        $settings = Setting::where('group', $group)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $groupNames = [
            'website' => 'Website Settings',
            'email' => 'Email Settings',
            'notification' => 'Notification Settings',
            'page' => 'Page Settings'
        ];

        $groupTitle = $groupNames[$group] ?? ucfirst($group) . ' Settings';

        return view('admin.settings.manage', compact('settings', 'group', 'groupTitle'));
    }

    public function update(Request $request, $group)
    {
        $settings = Setting::where('group', $group)->get();

        foreach ($settings as $setting) {
            $value = $request->input($setting->key);
            
            if ($setting->type === 'boolean') {
                $value = $request->has($setting->key) ? '1' : '0';
            }

            if ($setting->type === 'password' && empty($value)) {
                continue; // Don't update password if empty
            }

            $setting->update(['value' => $value]);
            // Clear specific cache for this setting
            Cache::forget('setting_' . $setting->key);
        }

        // Clear general settings cache
        Cache::forget('settings');
        Cache::forget('all_settings');

        return redirect()->route('admin.settings.index')
            ->with('success', ucfirst($group) . ' settings updated successfully!');
    }
}