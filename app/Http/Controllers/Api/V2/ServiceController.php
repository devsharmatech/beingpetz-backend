<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\Provider;
use App\Models\ProviderService;
use App\Models\ProviderReview;
use App\Models\ServiceBooking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ServiceController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────────
    // 1. LIST & FILTER PROVIDERS / SERVICES
    // GET /api/v2/services
    // Filters: type, area, pet_type, min_price, max_price, search
    // ─────────────────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        try {
            // Fetch all providers with user & custom services relations
            $query = Provider::with(['user:id,profile,locality,city,state', 'providerServices']);

            // Filter by service category — case-insensitive (e.g. grooming = Grooming = GROOMING)
            if ($request->filled('type')) {
                $type = strtolower($request->type);
                $query->where(function ($q) use ($type) {
                    $q->whereRaw("JSON_SEARCH(LOWER(services), 'one', ?) IS NOT NULL", [$type])
                      ->orWhereHas('service', function ($sq) use ($type) {
                          $sq->whereRaw("LOWER(name) = ?", [$type]);
                      })
                      ->orWhereHas('providerServices', function ($psq) use ($type) {
                          $psq->whereRaw("LOWER(category) = ?", [$type]);
                      });
                });
            }

            // Filter by area / location
            if ($request->filled('area')) {
                $area = $request->area;
                $query->where(function ($q) use ($area) {
                    $q->where('area', 'like', '%' . $area . '%')
                      ->orWhere('address', 'like', '%' . $area . '%');
                });
            }

            // Filter by accepted pet type — case-insensitive (e.g. dog = Dog = DOG)
            if ($request->filled('pet_type')) {
                $petType = strtolower($request->pet_type);
                $query->whereRaw("JSON_SEARCH(LOWER(accepted_pet_types), 'one', ?) IS NOT NULL", [$petType]);
            }

            // Filter by price range (based on start_pricing)
            if ($request->filled('min_price')) {
                $query->where('start_pricing', '>=', $request->min_price);
            }
            if ($request->filled('max_price')) {
                $query->where('start_pricing', '<=', $request->max_price);
            }

            // Search by name or business name
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                      ->orWhere('business_name', 'like', '%' . $search . '%');
                });
            }

            $providers = $query->orderBy('created_at', 'desc')->paginate(15);

            $formatted = $providers->map(function ($provider) {
                $avgRating = ProviderReview::where('provider_id', $provider->id)->avg('rating') ?? 0;
                $totalReviews = ProviderReview::where('provider_id', $provider->id)->count();

                // Fallback for area
                $area = $provider->area ?? $provider->address;
                if (!$area && $provider->user) {
                    $area = implode(', ', array_filter([$provider->user->locality, $provider->user->city, $provider->user->state]));
                }

                // Fallback for pricing
                $startPricing = $provider->start_pricing;
                if (!$startPricing) {
                    $startPricing = ProviderService::where('provider_id', $provider->id)->min('price');
                }

                // Services category list
                $services = $provider->services ?? [];
                if (empty($services)) {
                    $customCats = ProviderService::where('provider_id', $provider->id)->pluck('category')->unique()->values()->toArray();
                    if (!empty($customCats)) {
                        $services = $customCats;
                    }
                }

                // Full service objects (from provider_services table or generated from registered services)
                $customServices = $this->formatProviderCustomServices($provider);

                return [
                    'id'                 => $provider->id,
                    'name'               => $provider->name,
                    'business_name'      => $provider->business_name ?? $provider->name,
                    'area'               => $area ?: null,
                    'services'           => $services,
                    'custom_services'    => $customServices,
                    'accepted_pet_types' => $provider->accepted_pet_types ?? [],
                    'accepted_pet_sizes' => $provider->accepted_pet_sizes ?? [],
                    'start_pricing'      => $startPricing ? (float)$startPricing : null,
                    'experience_years'   => $provider->experience_years ? (int)$provider->experience_years : null,
                    'profile_image'      => $provider->user?->profile_url ?? ($provider->user?->profile ? asset('storage/' . $provider->user->profile) : null),
                    'work_gallery'       => $provider->work_gallery ?? [],
                    'avg_rating'         => round((float)$avgRating, 1),
                    'total_reviews'      => $totalReviews,
                ];
            });

            // Also load legacy vendors from specific category tables (veterinary_doctors, pet_shops, etc.)
            $legacyVendors = $this->getLegacyProviders($request);
            $merged = $formatted->concat($legacyVendors);

            return response()->json([
                'success' => true,
                'data' => $merged->values(),
                'pagination' => [
                    'current_page' => $providers->currentPage(),
                    'last_page'    => $providers->lastPage(),
                    'per_page'     => $providers->perPage(),
                    'total'        => $providers->total() + $legacyVendors->count(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to fetch services.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Helper to format custom services for a provider
     */
    private function formatProviderCustomServices($provider)
    {
        // 1. Fetch custom services from provider_services table
        $customServices = ProviderService::where('provider_id', $provider->id)
            ->where(function ($q) {
                $q->where('is_active', 1)->orWhereNull('is_active');
            })
            ->get()
            ->map(function ($s) {
                return [
                    'id'               => $s->id,
                    'category'         => $s->category,
                    'name'             => $s->name,
                    'description'      => $s->description,
                    'price'            => (float)$s->price,
                    'duration_minutes' => (int)$s->duration_minutes,
                    'cover_image'      => $s->cover_image ? asset('storage/' . $s->cover_image) : null,
                ];
            })->values()->toArray();

        // 2. If no custom_services created in DB yet, auto-create ProviderService records so every service has a real DB ID for booking
        if (empty($customServices)) {
            $registeredServices = $provider->services;
            
            // Fallback to service_id if services column is null
            if (empty($registeredServices) && $provider->service_id) {
                $svc = \App\Models\Service::find($provider->service_id);
                if ($svc) {
                    $registeredServices = [$svc->name];
                }
            }

            if (!empty($registeredServices)) {
                $registeredServices = is_array($registeredServices) ? $registeredServices : json_decode($registeredServices, true);
                if (is_array($registeredServices)) {
                    foreach ($registeredServices as $cat) {
                        $catName = ucfirst($cat);
                        $ps = ProviderService::firstOrCreate(
                            [
                                'provider_id' => $provider->id,
                                'category'    => $catName,
                                'name'        => $catName . ' Service',
                            ],
                            [
                                'description'      => $catName . ' service offered by ' . ($provider->business_name ?? $provider->name),
                                'price'            => $provider->start_pricing ? (float)$provider->start_pricing : 500,
                                'duration_minutes' => 60,
                                'is_active'        => 1,
                            ]
                        );

                        $customServices[] = [
                            'id'               => $ps->id,
                            'category'         => $ps->category,
                            'name'             => $ps->name,
                            'description'      => $ps->description,
                            'price'            => (float)$ps->price,
                            'duration_minutes' => (int)$ps->duration_minutes,
                            'cover_image'      => $ps->cover_image ? asset('storage/' . $ps->cover_image) : null,
                        ];
                    }
                }
            }
        }

        return $customServices;
    }

    /**
     * Helper to fetch legacy vendors from category-specific tables
     */
    private function getLegacyProviders(Request $request)
    {
        $categoryTables = [
            'veterinary'   => ['table' => 'veterinary_doctors', 'name' => 'veterinarian_name', 'biz' => 'clinic_name', 'area' => 'clinic_location', 'city' => 'city', 'state' => 'state'],
            'shop'         => ['table' => 'pet_shops', 'name' => 'owner_name', 'biz' => 'shop_name', 'area' => 'address_line_1', 'city' => 'city', 'state' => 'state'],
            'grooming'     => ['table' => 'pet_groomers', 'name' => 'owner_name', 'biz' => 'business_name', 'area' => 'address_line1', 'city' => 'city', 'state' => 'state'],
            'training'     => ['table' => 'pet_trainers', 'name' => 'trainer_name', 'biz' => 'training_business_name', 'area' => 'address_line1', 'city' => 'city', 'state' => 'state'],
            'walking'      => ['table' => 'walkers', 'name' => 'walker_name', 'biz' => 'business_name', 'area' => 'address_line1', 'city' => 'city', 'state' => 'state'],
            'behaviourist' => ['table' => 'pet_behaviourists', 'name' => 'full_name', 'biz' => 'business_name', 'area' => 'address_line1', 'city' => 'city', 'state' => 'state'],
            'resort'       => ['table' => 'pet_resort_owners', 'name' => 'owner_name', 'biz' => 'resort_name', 'area' => 'address_line1', 'city' => 'city', 'state' => 'state'],
            'shelter'      => ['table' => 'pet_shelters', 'name' => 'owner_name', 'biz' => 'shelter_name', 'area' => 'address_line_1', 'city' => 'city', 'state' => 'state'],
            'sitter'       => ['table' => 'pet_sitters', 'name' => 'owner_name', 'biz' => 'business_name', 'area' => 'address_line1', 'city' => 'city', 'state' => 'state'],
        ];

        $legacyResults = collect();
        $filterType = $request->filled('type') ? strtolower($request->type) : null;
        $filterArea = $request->filled('area') ? strtolower($request->area) : null;
        $filterSearch = $request->filled('search') ? strtolower($request->search) : null;

        foreach ($categoryTables as $cat => $config) {
            if ($filterType && !str_contains($cat, $filterType) && !str_contains($filterType, $cat)) {
                continue;
            }

            if (!\Schema::hasTable($config['table'])) continue;

            $rows = \DB::table($config['table'])->get();
            foreach ($rows as $row) {
                $name = $row->{$config['name']} ?? 'Vendor';
                $biz = $row->{$config['biz']} ?? $name;
                $area = implode(', ', array_filter([$row->{$config['area']} ?? null, $row->{$config['city']} ?? null, $row->{$config['state']} ?? null]));

                if ($filterArea && !str_contains(strtolower($area), $filterArea)) {
                    continue;
                }

                if ($filterSearch && !str_contains(strtolower($name), $filterSearch) && !str_contains(strtolower($biz), $filterSearch)) {
                    continue;
                }

                $legacyResults->push([
                    'id'                 => 'legacy_' . $config['table'] . '_' . $row->id,
                    'name'               => $name,
                    'business_name'      => $biz,
                    'area'               => $area ?: null,
                    'services'           => [$cat],
                    'custom_services'    => [
                        [
                            'id'               => null,
                            'category'         => ucfirst($cat),
                            'name'             => $biz,
                            'description'      => $row->certifications ?? $row->special_certification ?? null,
                            'price'            => null,
                            'duration_minutes' => null,
                            'cover_image'      => null,
                        ]
                    ],
                    'accepted_pet_types' => ['dogs', 'cats'],
                    'accepted_pet_sizes' => ['small', 'medium', 'large'],
                    'start_pricing'      => null,
                    'experience_years'   => $row->years_of_experience ?? $row->experience ?? null,
                    'profile_image'      => null,
                    'work_gallery'       => [],
                    'avg_rating'         => 0,
                    'total_reviews'      => 0,
                    'source_table'       => $config['table'],
                ]);
            }
        }

        return $legacyResults;
    }

    /**
     * Helper to show details of a legacy vendor from category table
     */
    private function showLegacyProvider($providerId)
    {
        // ID format: legacy_{table}_{id} e.g. legacy_veterinary_doctors_5
        $parts = explode('_', $providerId);
        $id = array_pop($parts);
        array_shift($parts); // remove 'legacy'
        $table = implode('_', $parts);

        if (!\Schema::hasTable($table)) {
            return response()->json(['success' => false, 'message' => 'Provider not found.'], 404);
        }

        $row = \DB::table($table)->where('id', $id)->first();
        if (!$row) {
            return response()->json(['success' => false, 'message' => 'Provider not found.'], 404);
        }

        $name = $row->veterinarian_name ?? $row->owner_name ?? $row->trainer_name ?? $row->walker_name ?? $row->full_name ?? 'Vendor';
        $biz = $row->clinic_name ?? $row->shop_name ?? $row->business_name ?? $row->training_business_name ?? $row->resort_name ?? $row->shelter_name ?? $name;
        $area = implode(', ', array_filter([$row->clinic_location ?? $row->address_line_1 ?? $row->address_line1 ?? null, $row->city ?? null, $row->state ?? null]));

        return response()->json([
            'success' => true,
            'data' => [
                'provider' => [
                    'id'                 => $providerId,
                    'name'               => $name,
                    'business_name'      => $biz,
                    'area'               => $area ?: null,
                    'address'            => $area ?: null,
                    'services'           => [str_replace(['pet_', '_owners', '_doctors'], '', $table)],
                    'accepted_pet_types' => ['dogs', 'cats'],
                    'accepted_pet_sizes' => ['small', 'medium', 'large'],
                    'experience_years'   => $row->years_of_experience ?? $row->experience ?? null,
                    'start_pricing'      => null,
                    'consultation_fee'   => null,
                    'weekly_schedule'    => null,
                    'work_gallery'       => [],
                    'profile_image'      => null,
                ],
                'custom_services' => [],
                'ratings' => [
                    'avg_rating'    => 0,
                    'total_reviews' => 0,
                    'recent'        => [],
                ]
            ]
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 2. PROVIDER DETAIL + ALL THEIR SERVICES
    // GET /api/v2/services/{providerId}
    // ─────────────────────────────────────────────────────────────────────────
    public function show($providerId)
    {
        try {
            // Check if this is a legacy provider ID (e.g. legacy_veterinary_doctors_5)
            if (str_starts_with($providerId, 'legacy_')) {
                return $this->showLegacyProvider($providerId);
            }

            // Find provider in primary providers table
            $provider = Provider::where('id', $providerId)
                ->with(['user:id,profile,first_name,last_name,username'])
                ->first();

            if (!$provider) {
                return response()->json(['success' => false, 'message' => 'Provider not found.'], 404);
            }

            // Custom services list (from provider_services table or generated from registered services)
            $services = $this->formatProviderCustomServices($provider);

            // Reviews summary
            $avgRating = ProviderReview::where('provider_id', $providerId)->avg('rating') ?? 0;
            $totalReviews = ProviderReview::where('provider_id', $providerId)->count();

            // Latest 5 reviews
            $reviews = ProviderReview::where('provider_id', $providerId)
                ->with(['user:id,first_name,last_name,username,profile'])
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get()
                ->map(function ($r) {
                    return [
                        'user_name'   => $r->user ? ($r->user->first_name ? trim($r->user->first_name . ' ' . $r->user->last_name) : $r->user->username) : 'Anonymous',
                        'user_image'  => $r->user?->profile_url ?? ($r->user?->profile ? asset('storage/' . $r->user->profile) : null),
                        'rating'      => (float)$r->rating,
                        'comment'     => $r->comment,
                        'date'        => $r->created_at->format('j M Y'),
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'provider' => [
                        'id'                      => $provider->id,
                        'name'                    => $provider->name,
                        'business_name'           => $provider->business_name,
                        'area'                    => $provider->area,
                        'address'                 => $provider->address,
                        'services'                => $provider->services,
                        'accepted_pet_types'      => $provider->accepted_pet_types,
                        'accepted_pet_sizes'      => $provider->accepted_pet_sizes,
                        'experience_years'        => $provider->experience_years,
                        'start_pricing'           => $provider->start_pricing,
                        'consultation_fee'        => $provider->consultation_fee,
                        'weekly_schedule'         => $provider->weekly_schedule,
                        'work_gallery'            => $provider->work_gallery ?? [],
                        'profile_image'           => $provider->user?->profile_url ?? ($provider->user?->profile ? asset('storage/' . $provider->user->profile) : null),
                    ],
                    'custom_services' => $services,
                    'ratings' => [
                        'avg_rating'    => round((float)$avgRating, 1),
                        'total_reviews' => $totalReviews,
                        'recent'        => $reviews,
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Provider not found.', 'error' => $e->getMessage()], 404);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 3. BOOK A SERVICE (WITH PAYMENT INIT)
    // POST /api/v2/services/book
    // ─────────────────────────────────────────────────────────────────────────
    public function book(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'provider_id'    => 'required|exists:providers,id',
                'service_id'     => 'required|exists:provider_services,id',
                'scheduled_at'   => 'required|date|after:now',
                'payment_method' => 'required|in:razorpay,upi,cash,card,stripe',
                'pet_id'         => 'nullable|integer',
                'notes'          => 'nullable|string|max:500',
                'address'        => 'nullable|string|max:1000',
                'address_id'     => 'nullable|integer',
                // Payment gateway details (sent AFTER user completes payment on frontend)
                'transaction_id'          => 'nullable|string',
                'payment_gateway_order_id' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
            }

            $user = $request->user();

            // Verify pet_id if provided
            $petId = null;
            if ($request->filled('pet_id')) {
                $petExists = \App\Models\Pet::where('id', $request->pet_id)->exists();
                if ($petExists) {
                    $petId = $request->pet_id;
                }
            }

            // Get service to determine pricing
            $service = ProviderService::where('id', $request->service_id)
                ->where('provider_id', $request->provider_id)
                ->where('is_active', true)
                ->first();

            if (!$service) {
                return response()->json(['success' => false, 'message' => 'This service is not available from this provider.'], 422);
            }

            // Address lookup if address_id provided
            $bookingAddress = $request->address;
            if ($request->filled('address_id') && empty($bookingAddress)) {
                $savedAddress = \App\Models\UserAddress::where('id', $request->address_id)
                    ->where('user_id', $user->id)
                    ->first();
                if ($savedAddress) {
                    $bookingAddress = implode(', ', array_filter([
                        $savedAddress->name ? 'Contact: ' . $savedAddress->name : null,
                        $savedAddress->phone ? 'Phone: ' . $savedAddress->phone : null,
                        $savedAddress->address_line1,
                        $savedAddress->address_line2,
                        $savedAddress->city,
                        $savedAddress->state,
                        $savedAddress->postal_code,
                    ]));
                }
            }

            // Determine payment status
            $paymentStatus = 'pending';
            if ($request->payment_method !== 'cash' && $request->filled('transaction_id')) {
                $paymentStatus = 'paid';
            }

            $booking = ServiceBooking::create([
                'user_id'                  => $user->id,
                'provider_id'              => $request->provider_id,
                'service_id'               => $request->service_id,
                'scheduled_at'             => $request->scheduled_at,
                'status'                   => 'pending',
                'total_amount'             => $service->price,
                'payment_method'           => $request->payment_method,
                'payment_gateway'          => in_array($request->payment_method, ['razorpay', 'stripe']) ? $request->payment_method : null,
                'payment_status'           => $paymentStatus,
                'transaction_id'           => $request->transaction_id,
                'payment_gateway_order_id' => $request->payment_gateway_order_id,
                'notes'                    => $request->notes,
                'pet_id'                   => $petId,
                'address_id'               => $request->address_id,
                'address'                  => $bookingAddress,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Booking created successfully! Waiting for vendor confirmation.',
                'data' => [
                    'booking_id'     => $booking->id,
                    'service_name'   => $service->name,
                    'total_amount'   => $booking->total_amount,
                    'scheduled_at'   => $booking->scheduled_at->format('Y-m-d H:i:s'),
                    'status'         => $booking->status,
                    'payment_status' => $booking->payment_status,
                    'payment_method' => $booking->payment_method,
                    'transaction_id' => $booking->transaction_id,
                    'pet_id'         => $booking->pet_id,
                    'pet'            => $booking->pet ? ['id' => $booking->pet->id, 'name' => $booking->pet->name, 'breed' => $booking->pet->breed] : null,
                    'address'        => $booking->address,
                    'address_id'     => $booking->address_id,
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Booking failed.', 'error' => $e->getMessage()], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 4. CUSTOMER BOOKING HISTORY
    // GET /api/v2/services/bookings?status=pending
    // ─────────────────────────────────────────────────────────────────────────
    public function myBookings(Request $request)
    {
        try {
            $user = $request->user();

            $query = ServiceBooking::where('user_id', $user->id)
                ->with([
                    'provider:id,name,business_name,area',
                    'providerService:id,name,category,price,duration_minutes,cover_image',
                    'pet:id,name,breed',
                    'review:id,service_booking_id,rating,comment',
                ]);

            // Filter by status
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            $bookings = $query->orderBy('created_at', 'desc')->paginate(15);

            $formatted = $bookings->map(function ($booking) {
                return [
                    'id'              => $booking->id,
                    'service_name'    => $booking->providerService?->name ?? 'N/A',
                    'service_category'=> $booking->providerService?->category ?? '',
                    'provider_name'   => $booking->provider?->business_name ?? $booking->provider?->name,
                    'provider_area'   => $booking->provider?->area,
                    'scheduled_at'    => $booking->scheduled_at?->format('Y-m-d H:i:s'),
                    'status'          => $booking->status,
                    'total_amount'    => $booking->total_amount,
                    'payment_status'  => $booking->payment_status,
                    'payment_method'  => $booking->payment_method,
                    'address'         => $booking->address,
                    'address_id'      => $booking->address_id,
                    'pet'             => $booking->pet ? ['id' => $booking->pet->id, 'name' => $booking->pet->name, 'breed' => $booking->pet->breed] : null,
                    'has_review'      => $booking->review ? true : false,
                    'my_rating'       => $booking->review?->rating,
                    'created_at'      => $booking->created_at->format('Y-m-d H:i:s'),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formatted,
                'pagination' => [
                    'current_page' => $bookings->currentPage(),
                    'last_page'    => $bookings->lastPage(),
                    'per_page'     => $bookings->perPage(),
                    'total'        => $bookings->total(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to fetch bookings.', 'error' => $e->getMessage()], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 5. SINGLE BOOKING DETAIL
    // GET /api/v2/services/bookings/{id}
    // ─────────────────────────────────────────────────────────────────────────
    public function bookingDetail(Request $request, $id)
    {
        try {
            $user = $request->user();

            $booking = ServiceBooking::where('id', $id)
                ->where('user_id', $user->id)
                ->with([
                    'provider:id,name,business_name,area,address,phone',
                    'providerService:id,name,category,price,duration_minutes,cover_image',
                    'pet:id,name,breed,type,gender,avatar',
                    'review:id,service_booking_id,rating,comment,created_at',
                ])
                ->first();

            if (!$booking) {
                return response()->json(['success' => false, 'message' => 'Booking not found.'], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id'                       => $booking->id,
                    'status'                   => $booking->status,
                    'scheduled_at'             => $booking->scheduled_at?->format('Y-m-d H:i:s'),
                    'notes'                    => $booking->notes,
                    'address'                  => $booking->address,
                    'address_id'               => $booking->address_id,
                    'service' => [
                        'name'             => $booking->providerService?->name,
                        'category'         => $booking->providerService?->category,
                        'price'            => $booking->providerService?->price,
                        'duration_minutes' => $booking->providerService?->duration_minutes,
                        'cover_image'      => $booking->providerService?->cover_image ? asset('storage/' . $booking->providerService->cover_image) : null,
                    ],
                    'provider' => [
                        'id'            => $booking->provider?->id,
                        'name'          => $booking->provider?->business_name ?? $booking->provider?->name,
                        'area'          => $booking->provider?->area,
                        'address'       => $booking->provider?->address,
                        'phone'         => $booking->provider?->phone,
                    ],
                    'pet' => $booking->pet ? [
                        'id'     => $booking->pet->id,
                        'name'   => $booking->pet->name,
                        'type'   => $booking->pet->type,
                        'breed'  => $booking->pet->breed,
                        'gender' => $booking->pet->gender,
                        'avatar' => $booking->pet->avatar ? asset('storage/' . $booking->pet->avatar) : null,
                    ] : null,
                    'payment' => [
                        'total_amount'             => $booking->total_amount,
                        'payment_status'           => $booking->payment_status,
                        'payment_method'           => $booking->payment_method,
                        'payment_gateway'          => $booking->payment_gateway,
                        'transaction_id'           => $booking->transaction_id,
                        'payment_gateway_order_id' => $booking->payment_gateway_order_id,
                    ],
                    'review' => $booking->review ? [
                        'rating'  => (float)$booking->review->rating,
                        'comment' => $booking->review->comment,
                        'date'    => $booking->review->created_at->format('Y-m-d'),
                    ] : null,
                    'created_at' => $booking->created_at->format('Y-m-d H:i:s'),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to fetch booking details.', 'error' => $e->getMessage()], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 6. CANCEL A BOOKING
    // POST /api/v2/services/bookings/{id}/cancel
    // Only works if status is 'pending'
    // ─────────────────────────────────────────────────────────────────────────
    public function cancelBooking(Request $request, $id)
    {
        try {
            $user = $request->user();

            $booking = ServiceBooking::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$booking) {
                return response()->json(['success' => false, 'message' => 'Booking not found.'], 404);
            }

            if ($booking->status !== 'pending') {
                return response()->json(['success' => false, 'message' => 'Only pending bookings can be cancelled.'], 422);
            }

            $booking->status = 'rejected';
            // If paid online, flag for refund
            if ($booking->payment_status === 'paid') {
                $booking->payment_status = 'refunded';
            }
            $booking->save();

            return response()->json(['success' => true, 'message' => 'Booking cancelled successfully.']);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to cancel booking.', 'error' => $e->getMessage()], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 7. SUBMIT RATING & REVIEW
    // POST /api/v2/services/bookings/{id}/review
    // Only allowed after status = 'completed'
    // ─────────────────────────────────────────────────────────────────────────
    public function submitReview(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'rating'  => 'required|numeric|min:1|max:5',
                'comment' => 'nullable|string|max:1000',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
            }

            $user = $request->user();

            $booking = ServiceBooking::where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$booking) {
                return response()->json(['success' => false, 'message' => 'Booking not found.'], 404);
            }

            if ($booking->status !== 'completed') {
                return response()->json(['success' => false, 'message' => 'You can only review a completed booking.'], 422);
            }

            // Check if already reviewed
            if ($booking->review()->exists()) {
                return response()->json(['success' => false, 'message' => 'You have already submitted a review for this booking.'], 422);
            }

            $review = \App\Models\ProviderReview::create([
                'provider_id'        => $booking->provider_id,
                'user_id'            => $user->id,
                'service_booking_id' => $booking->id,
                'rating'             => $request->rating,
                'comment'            => $request->comment,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Thank you! Your review has been submitted.',
                'data' => [
                    'review_id' => $review->id,
                    'rating'    => (float)$review->rating,
                    'comment'   => $review->comment,
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to submit review.', 'error' => $e->getMessage()], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 8. PAYMENT TRANSACTION DETAIL
    // GET /api/v2/services/bookings/{id}/payment
    // ─────────────────────────────────────────────────────────────────────────
    public function paymentDetail(Request $request, $id)
    {
        try {
            $user = $request->user();

            $booking = ServiceBooking::where('id', $id)
                ->where('user_id', $user->id)
                ->with(['providerService:id,name,price', 'provider:id,name,business_name'])
                ->first();

            if (!$booking) {
                return response()->json(['success' => false, 'message' => 'Booking not found.'], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'booking_id'               => $booking->id,
                    'service_name'             => $booking->providerService?->name,
                    'provider_name'            => $booking->provider?->business_name ?? $booking->provider?->name,
                    'total_amount'             => $booking->total_amount,
                    'payment_status'           => $booking->payment_status,
                    'payment_method'           => $booking->payment_method,
                    'payment_gateway'          => $booking->payment_gateway,
                    'transaction_id'           => $booking->transaction_id,
                    'payment_gateway_order_id' => $booking->payment_gateway_order_id,
                    'booking_status'           => $booking->status,
                    'scheduled_at'             => $booking->scheduled_at?->format('Y-m-d H:i:s'),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to fetch payment details.', 'error' => $e->getMessage()], 500);
        }
    }
}
