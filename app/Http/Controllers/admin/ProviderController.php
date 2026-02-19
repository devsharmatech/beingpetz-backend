<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Provider;
use App\Models\Service;
use Illuminate\Http\Request;

class ProviderController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_id' => 'required|exists:services,id',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        $provider = Provider::create($validated);

        // Update service providers count
        $service = Service::find($request->service_id);
        $service->update([
            'providers_count' => $service->providers()->count()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Provider added successfully!'
        ]);
    }

    public function update(Request $request, Provider $provider)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        $provider->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Provider updated successfully!'
        ]);
    }

    public function destroy(Provider $provider)
    {
        $service = $provider->service;
        $provider->delete();

        // Update service providers count
        $service->update([
            'providers_count' => $service->providers()->count()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Provider deleted successfully!'
        ]);
    }
}