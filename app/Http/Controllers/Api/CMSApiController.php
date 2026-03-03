<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Helpers\SettingsHelper;
use Illuminate\Http\Request;

class CMSApiController extends Controller
{
    /**
     * Get Terms and Conditions
     */
    public function getTerms()
    {
        $setting = Setting::where('key', 'terms_and_conditions')
            ->orWhere('key', 'terms_conditions')
            ->first();
        
        return response()->json([
            'status' => true,
            'message' => 'Terms and Conditions fetched successfully.',
            'data' => [
                'content' => $setting ? $setting->value : '',
                'last_updated' => $setting ? $setting->updated_at : null
            ]
        ], 200);
    }

    /**
     * Get Privacy Policy
     */
    public function getPrivacyPolicy()
    {
        $setting = Setting::where('key', 'privacy_policy')->first();
        
        return response()->json([
            'status' => true,
            'message' => 'Privacy Policy fetched successfully.',
            'data' => [
                'content' => $setting ? $setting->value : '',
                'last_updated' => $setting ? $setting->updated_at : null
            ]
        ], 200);
    }

    /**
     * Get all legal/static page settings
     */
    public function getLegal()
    {
        $settings = Setting::whereIn('group', ['legal', 'page'])
            ->where('is_active', true)
            ->get();
        
        $data = [];
        foreach ($settings as $setting) {
            $data[$setting->key] = $setting->value;
        }

        return response()->json([
            'status' => true,
            'message' => 'Legal and Page settings fetched successfully.',
            'data' => $data
        ], 200);
    }

    /**
     * Get system settings (Maintenance mode, etc.)
     */
    public function getSettings()
    {
        return response()->json([
            'status' => true,
            'data' => [
                'maintenance_mode' => SettingsHelper::get('maintenance_mode', '0') == '1',
                'site_name' => SettingsHelper::get('site_name', 'Being Petz'),
                'contact_email' => SettingsHelper::get('site_email', 'admin@beingpetz.com'),
            ]
        ], 200);
    }
}
