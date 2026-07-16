<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Provider;
use App\Models\ProviderService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class VendorServiceController extends Controller
{
    // List all services
    public function index(Request $request)
    {
        $user = auth()->user();
        $provider = Provider::where('user_id', $user->id)->first();
        if (!$provider) return response()->json(['status' => false, 'message' => 'Provider not found'], 200);

        $services = ProviderService::where('provider_id', $provider->id)->get();

        // Optionally, count bookings per service if we have them linked. 
        // For now, returning a mock count or 0.
        $services = $services->map(function($service) {
            $service->booked_count = 0; // To be implemented later if service_bookings links to provider_services
            if ($service->cover_image) {
                $service->cover_image_url = asset('storage/' . $service->cover_image);
            }
            return $service;
        });

        // Group by category to match UI tabs easily
        $grouped = $services->groupBy('category');

        return response()->json([
            'status' => true,
            'message' => 'Services fetched successfully.',
            'data' => [
                'total_active' => $services->where('is_active', true)->count(),
                'total_services' => $services->count(),
                'all' => $services,
                'categories' => $grouped
            ]
        ], 200);
    }

    // Create new service
    public function store(Request $request)
    {
        $user = auth()->user();
        $provider = Provider::where('user_id', $user->id)->first();
        if (!$provider) return response()->json(['status' => false, 'message' => 'Provider not found'], 200);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'category' => 'required|string',
            'price' => 'required|numeric|min:0',
            'duration_minutes' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 200);
        }

        $imagePath = null;
        if ($request->hasFile('cover_image')) {
            $imagePath = $request->file('cover_image')->store('providers/services', 'public');
        }

        $service = ProviderService::create([
            'provider_id' => $provider->id,
            'category' => $request->category,
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'duration_minutes' => $request->duration_minutes,
            'cover_image' => $imagePath,
            'is_active' => true
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Service created successfully.',
            'data' => $service
        ], 200);
    }

    // Toggle service status
    public function toggleStatus(Request $request, $id)
    {
        $user = auth()->user();
        $provider = Provider::where('user_id', $user->id)->first();
        if (!$provider) return response()->json(['status' => false, 'message' => 'Provider not found'], 200);

        $service = ProviderService::where('provider_id', $provider->id)->where('id', $id)->first();
        if (!$service) return response()->json(['status' => false, 'message' => 'Service not found'], 200);

        $service->is_active = !$service->is_active;
        $service->save();

        return response()->json([
            'status' => true,
            'message' => 'Service status updated.',
            'data' => $service
        ], 200);
    }

    // Edit service
    public function update(Request $request, $id)
    {
        $user = auth()->user();
        $provider = Provider::where('user_id', $user->id)->first();
        if (!$provider) return response()->json(['status' => false, 'message' => 'Provider not found'], 200);

        $service = ProviderService::where('provider_id', $provider->id)->where('id', $id)->first();
        if (!$service) return response()->json(['status' => false, 'message' => 'Service not found'], 200);

        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'category' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'duration_minutes' => 'nullable|integer|min:1',
            'description' => 'nullable|string',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 200);
        }

        if ($request->hasFile('cover_image')) {
            $imagePath = $request->file('cover_image')->store('providers/services', 'public');
            $service->cover_image = $imagePath;
        }

        if ($request->has('name')) $service->name = $request->name;
        if ($request->has('category')) $service->category = $request->category;
        if ($request->has('price')) $service->price = $request->price;
        if ($request->has('duration_minutes')) $service->duration_minutes = $request->duration_minutes;
        if ($request->has('description')) $service->description = $request->description;

        $service->save();

        return response()->json([
            'status' => true,
            'message' => 'Service updated successfully.',
            'data' => $service
        ], 200);
    }

    // Delete service
    public function destroy(Request $request, $id)
    {
        $user = auth()->user();
        $provider = Provider::where('user_id', $user->id)->first();
        if (!$provider) return response()->json(['status' => false, 'message' => 'Provider not found'], 200);

        $service = ProviderService::where('provider_id', $provider->id)->where('id', $id)->first();
        if (!$service) return response()->json(['status' => false, 'message' => 'Service not found'], 200);

        $service->delete();

        return response()->json([
            'status' => true,
            'message' => 'Service deleted successfully.'
        ], 200);
    }
}
