<?php
namespace App\Helpers;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingsHelper
{
    public static function get($key, $default = null)
    {
        return Cache::remember('setting_' . $key, 3600, function () use ($key, $default) {
            $setting = Setting::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    public static function set($key, $value)
    {
        $setting = Setting::where('key', $key)->first();
        if ($setting) {
            $setting->update(['value' => $value]);
            Cache::forget('setting_' . $key);
            return true;
        }
        return false;
    }

    public static function getAll()
    {
        return Cache::remember('all_settings', 3600, function () {
            return Setting::where('is_active', true)
                ->get()
                ->pluck('value', 'key')
                ->toArray();
        });
    }
}