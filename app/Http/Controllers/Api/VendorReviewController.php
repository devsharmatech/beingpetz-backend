<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Provider;
use App\Models\ProviderReview;

class VendorReviewController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $provider = Provider::where('user_id', $user->id)->first();
        if (!$provider) return response()->json(['status' => false, 'message' => 'Provider not found'], 200);

        $reviews = ProviderReview::where('provider_id', $provider->id)
            ->with(['user:id,first_name,last_name,name,profile_image', 'serviceBooking.service', 'serviceBooking.pet'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);
            
        $totalReviews = ProviderReview::where('provider_id', $provider->id)->count();
        $averageRating = ProviderReview::where('provider_id', $provider->id)->avg('rating') ?? 0;

        $formattedReviews = $reviews->map(function($review) {
            $userName = $review->user ? ($review->user->first_name ? $review->user->first_name . ' ' . $review->user->last_name : $review->user->name) : 'Anonymous';
            $serviceName = $review->serviceBooking && $review->serviceBooking->service ? $review->serviceBooking->service->name : 'Service';
            $petName = $review->serviceBooking && $review->serviceBooking->pet ? $review->serviceBooking->pet->name : '';
            
            $subtitle = $serviceName;
            if ($petName) {
                $subtitle .= ' • ' . $petName;
                if ($review->serviceBooking->pet->breed) {
                    $subtitle .= ' (' . $review->serviceBooking->pet->breed . ')';
                }
            }

            return [
                'id' => $review->id,
                'user_name' => $userName,
                'user_image' => $review->user && $review->user->profile_image ? asset('storage/' . $review->user->profile_image) : null,
                'subtitle' => $subtitle,
                'rating' => (float)$review->rating,
                'comment' => $review->comment,
                'date' => $review->created_at->format('j M'),
                'full_date' => $review->created_at->format('Y-m-d H:i:s')
            ];
        });

        return response()->json([
            'status' => true,
            'message' => 'Reviews fetched successfully.',
            'data' => [
                'average_rating' => round((float)$averageRating, 1),
                'total_reviews' => $totalReviews,
                'reviews' => $formattedReviews,
                'pagination' => [
                    'current_page' => $reviews->currentPage(),
                    'last_page' => $reviews->lastPage(),
                    'per_page' => $reviews->perPage(),
                    'total' => $reviews->total(),
                ]
            ]
        ], 200);
    }
}
