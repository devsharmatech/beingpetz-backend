<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class LegalController extends Controller
{
    public function index()
    {
        $settings = Setting::where('group', 'legal')
            ->orderBy('sort_order')
            ->get();
            
        return view('admin.legal.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $settings = Setting::where('group', 'legal')->get();

        foreach ($settings as $setting) {
            $value = $request->input($setting->key);
            
            // Handle richtext specifically if needed, but standard input works too
            $setting->update(['value' => $value]);
        }

        // Clear settings cache if you use it
        Cache::forget('settings');

        return redirect()->route('admin.legal.index')
            ->with('success', 'Legal pages updated successfully!');
    }
}
