<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\Provider;
use App\Models\ProviderReview;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────────
    // VENDOR: Get all my reviews + avg rating
    // GET /api/v2/vendor/reviews
    // ─────────────────────────────────────────────────────────────────────────
    public function vendorReviews(Request $request)
    {
        try {
            $user = $request->user();
            $provider = Provider::where('user_id', $user->id)->first();

            if (!$provider) {
                return response()->json(['success' => false, 'message' => 'Vendor profile not found.'], 404);
            }

            $avgRating    = ProviderReview::where('provider_id', $provider->id)->avg('rating') ?? 0;
            $totalReviews = ProviderReview::where('provider_id', $provider->id)->count();

            // Rating breakdown (1★ - 5★ counts)
            $breakdown = [];
            for ($i = 5; $i >= 1; $i--) {
                $breakdown[$i . '_star'] = ProviderReview::where('provider_id', $provider->id)
                    ->where('rating', $i)->count();
            }

            $reviews = ProviderReview::where('provider_id', $provider->id)
                ->with([
                    'user:id,first_name,last_name,username,profile',
                    'serviceBooking.providerService:id,name,category',
                    'serviceBooking.pet:id,name,breed',
                ])
                ->orderBy('created_at', 'desc')
                ->paginate(15);

            $formatted = $reviews->map(function ($review) {
                $userName = $review->user
                    ? ($review->user->first_name ? trim($review->user->first_name . ' ' . $review->user->last_name) : $review->user->username)
                    : 'Anonymous';

                $serviceName = $review->serviceBooking?->providerService?->name ?? 'Service';
                $petName     = $review->serviceBooking?->pet?->name;
                $petBreed    = $review->serviceBooking?->pet?->breed;

                $subtitle = $serviceName;
                if ($petName) {
                    $subtitle .= ' • ' . $petName;
                    if ($petBreed) $subtitle .= ' (' . $petBreed . ')';
                }

                return [
                    'id'          => $review->id,
                    'user_name'   => $userName,
                    'user_image'  => $review->user?->profile_url ?? ($review->user?->profile ? asset('storage/' . $review->user->profile) : null),
                    'subtitle'    => $subtitle,
                    'rating'      => (float)$review->rating,
                    'comment'     => $review->comment,
                    'date'        => $review->created_at->format('j M Y'),
                    'full_date'   => $review->created_at->format('Y-m-d H:i:s'),
                    'service'     => $review->serviceBooking?->providerService?->name,
                    'pet'         => $petName ? ['name' => $petName, 'breed' => $petBreed] : null,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'avg_rating'    => round((float)$avgRating, 1),
                    'total_reviews' => $totalReviews,
                    'breakdown'     => $breakdown,
                    'reviews'       => $formatted,
                    'pagination' => [
                        'current_page' => $reviews->currentPage(),
                        'last_page'    => $reviews->lastPage(),
                        'per_page'     => $reviews->perPage(),
                        'total'        => $reviews->total(),
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to fetch reviews.', 'error' => $e->getMessage()], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // VENDOR: Reply to a review (or update reply)
    // POST /api/v2/vendor/reviews/{reviewId}/reply
    // ─────────────────────────────────────────────────────────────────────────
    public function replyToReview(Request $request, $reviewId)
    {
        try {
            $user = $request->user();
            $provider = Provider::where('user_id', $user->id)->first();

            if (!$provider) {
                return response()->json(['success' => false, 'message' => 'Vendor profile not found.'], 404);
            }

            $review = ProviderReview::where('id', $reviewId)
                ->where('provider_id', $provider->id)
                ->first();

            if (!$review) {
                return response()->json(['success' => false, 'message' => 'Review not found.'], 404);
            }

            $request->validate(['reply' => 'required|string|max:1000']);

            // Store reply in the vendor_reply column (we check/add it)
            if (!\Schema::hasColumn('provider_reviews', 'vendor_reply')) {
                return response()->json(['success' => false, 'message' => 'Reply feature not yet configured on database. Please run migrations.'], 500);
            }

            $review->vendor_reply = $request->reply;
            $review->save();

            return response()->json(['success' => true, 'message' => 'Reply submitted successfully.']);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to submit reply.', 'error' => $e->getMessage()], 500);
        }
    }
}
