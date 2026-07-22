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
            $query = Provider::where('is_active', true)->with(['user:id,profile_image']);

            // Filter by service category (e.g. Grooming, Veterinary, Boarding)
            if ($request->filled('type')) {
                $query->whereJsonContains('services', $request->type);
            }

            // Filter by area / location
            if ($request->filled('area')) {
                $query->where('area', 'like', '%' . $request->area . '%');
            }

            // Filter by accepted pet type (e.g. Dog, Cat)
            if ($request->filled('pet_type')) {
                $query->whereJsonContains('accepted_pet_types', $request->pet_type);
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

                return [
                    'id'               => $provider->id,
                    'name'             => $provider->name,
                    'business_name'    => $provider->business_name,
                    'area'             => $provider->area,
                    'services'         => $provider->services,
                    'accepted_pet_types' => $provider->accepted_pet_types,
                    'accepted_pet_sizes' => $provider->accepted_pet_sizes,
                    'start_pricing'    => $provider->start_pricing,
                    'experience_years' => $provider->experience_years,
                    'profile_image'    => $provider->user?->profile_image ? asset('storage/' . $provider->user->profile_image) : null,
                    'work_gallery'     => $provider->work_gallery ?? [],
                    'avg_rating'       => round((float)$avgRating, 1),
                    'total_reviews'    => $totalReviews,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $formatted,
                'pagination' => [
                    'current_page' => $providers->currentPage(),
                    'last_page'    => $providers->lastPage(),
                    'per_page'     => $providers->perPage(),
                    'total'        => $providers->total(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to fetch services.', 'error' => $e->getMessage()], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 2. PROVIDER DETAIL + ALL THEIR SERVICES
    // GET /api/v2/services/{providerId}
    // ─────────────────────────────────────────────────────────────────────────
    public function show($providerId)
    {
        try {
            $provider = Provider::where('id', $providerId)
                ->where('is_active', true)
                ->with(['user:id,profile_image,first_name,last_name,name'])
                ->firstOrFail();

            // All active custom services for this provider
            $services = ProviderService::where('provider_id', $providerId)
                ->where('is_active', true)
                ->get(['id', 'category', 'name', 'description', 'price', 'duration_minutes', 'cover_image']);

            // Reviews summary
            $avgRating = ProviderReview::where('provider_id', $providerId)->avg('rating') ?? 0;
            $totalReviews = ProviderReview::where('provider_id', $providerId)->count();

            // Latest 5 reviews
            $reviews = ProviderReview::where('provider_id', $providerId)
                ->with(['user:id,first_name,last_name,name,profile_image'])
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get()
                ->map(function ($r) {
                    return [
                        'user_name'   => $r->user ? ($r->user->first_name ? $r->user->first_name . ' ' . $r->user->last_name : $r->user->name) : 'Anonymous',
                        'user_image'  => $r->user?->profile_image ? asset('storage/' . $r->user->profile_image) : null,
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
                        'profile_image'           => $provider->user?->profile_image ? asset('storage/' . $provider->user->profile_image) : null,
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
                'pet_id'         => 'nullable|exists:pets,id',
                'notes'          => 'nullable|string|max:500',
                // Payment gateway details (sent AFTER user completes payment on frontend)
                'transaction_id'          => 'nullable|string',
                'payment_gateway_order_id' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
            }

            $user = $request->user();

            // Get service to determine pricing
            $service = ProviderService::where('id', $request->service_id)
                ->where('provider_id', $request->provider_id)
                ->where('is_active', true)
                ->first();

            if (!$service) {
                return response()->json(['success' => false, 'message' => 'This service is not available from this provider.'], 422);
            }

            // Determine payment status
            // If cash => payment is done in person, mark as pending
            // If online gateway and transaction_id provided => mark as paid
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
                'pet_id'                   => $request->pet_id,
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
                    'pet:id,name,breed,species',
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
                        'id'      => $booking->pet->id,
                        'name'    => $booking->pet->name,
                        'breed'   => $booking->pet->breed,
                        'species' => $booking->pet->species,
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
