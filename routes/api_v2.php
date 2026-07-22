<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V2\AuthController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\AdoptionListingController;
use App\Http\Controllers\LostFoundReportController;
use App\Http\Controllers\FriendRequestController;
use App\Http\Controllers\Api\V2\PetController;
use App\Http\Controllers\Api\V2\PostController;
use App\Http\Controllers\Api\V2\CommentController;
use App\Http\Controllers\Api\V2\RepostController;
use App\Http\Controllers\Api\V2\EngagementController;
use App\Http\Controllers\Api\V2\FriendController;
use App\Http\Controllers\Api\V2\ProfileController;
use App\Http\Controllers\Api\V2\ModerationController;
use App\Http\Controllers\Api\V2\ServiceController;
use App\Http\Controllers\Api\V2\ReviewController;
use App\Http\Controllers\Api\{
    MarketplaceController,
    ProductController,
    CompanyController,
    AddressController,
    CouponController,
    OrderController,
    ContestController
};

/*
|--------------------------------------------------------------------------
| API V2 Routes
|--------------------------------------------------------------------------
|
| These routes are for Phase 2 - Module 1 features.
| All new functionality is isolated under /api/v2 prefix.
| Existing /api/v1 routes remain unchanged.
|
*/

// Public routes (no authentication required)
Route::get('/v2/unauthenticated', function () {
    return response()->json([
        'success' => false,
        'message' => 'Unauthenticated. Please provide a valid token.',
    ], 401);
})->name('login');

Route::group(['prefix' => 'v2/auth'], function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/register-verify', [AuthController::class, 'verifyRegister']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/login-verify', [AuthController::class, 'verifyLogin']);
    Route::post('/resend-otp', [AuthController::class, 'resendOtp']);
    Route::get('/check-username', [AuthController::class, 'checkUsername'])->middleware('throttle:60,1');
    Route::get('/captcha', [AuthController::class, 'generateCaptcha']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
});

// Protected routes (authentication required)
Route::middleware(['auth:sanctum'])->prefix('v2')->group(function () {

    // Auth - Profile management
    Route::prefix('auth')->group(function () {
        Route::post('/profile', [AuthController::class, 'updateProfile']);         // use _method=PUT in form-data
        Route::post('/profile/picture', [AuthController::class, 'updateProfilePicture']);
        Route::post('/password/update', [AuthController::class, 'updatePassword']);
    });
    
    Route::prefix('blogs')->group(function () {
        Route::get('/', [BlogController::class, 'allBlogsAPI']);     
    });
    Route::prefix('events')->group(function () {
        Route::get('/', [EventController::class, 'getAllEvents']);     
    });
    Route::prefix('friends')->group(function () {
        Route::post('/request-latest', [FriendRequestController::class, 'latestReceivedRequests']);     
        Route::get('/user-friends-list', [FriendRequestController::class, 'userFriendList']);     
        Route::get('/{user_id}/user-profile/view', [FriendRequestController::class, 'showUserProfile']);     
    });

    // Pet Registration (Enhanced)
    Route::prefix('pets')->group(function () {
        Route::get('/check-id/match', [PetController::class, 'checkPetId'])->middleware('throttle:60,1');
        
        Route::get('/', [PetController::class, 'index']);
        Route::post('/', [PetController::class, 'store']);
        Route::get('/{id}', [PetController::class, 'show']);
        Route::get('/{id}/posts', [PostController::class, 'petPosts']);
        Route::put('/{id}', [PetController::class, 'update']);      // use _method=PUT in form-data
        Route::post('/{id}/avatar', [PetController::class, 'updateAvatar']);
        Route::delete('/{id}', [PetController::class, 'destroy']);
    });

    // Post Creation (as Pet or Parent)
    Route::prefix('posts')->group(function () {
        Route::get('/', [PostController::class, 'index']);
        Route::get('/hashtag/{hashtag}', [PostController::class, 'getPostsByHashtag']);
        Route::get('/my', [PostController::class, 'myPosts']);
        Route::get('/user-posts', [PostController::class, 'userPosts']);
        Route::get('/{pet_id}/pet-posts', [PostController::class, 'petPosts']);
        Route::post('/', [PostController::class, 'store']);
        Route::get('/{id}', [PostController::class, 'show']);
        Route::post('/{id}/update', [PostController::class, 'update']);  // use POST for multipart support
        Route::delete('/{id}', [PostController::class, 'destroy']);

        // Engagement APIs
        Route::get('/{id}/likes', [EngagementController::class, 'getLikes']);
        Route::get('/{id}/comments', [EngagementController::class, 'getComments']);
        Route::get('/{id}/shares', [EngagementController::class, 'getShares']);
        Route::get('/{id}/repost-users', [EngagementController::class, 'getWhoRepost']);
        Route::post('/{id}/like', [EngagementController::class, 'like']);
        Route::post('/{id}/share', [EngagementController::class, 'share']);
    });

    // Comment System
    Route::prefix('comments')->group(function () {
        Route::post('/', [CommentController::class, 'store']);
        Route::post('/like', [CommentController::class, 'likeUnlike']);
        Route::get('/like/{comment_id}/user-list', [CommentController::class, 'commentLikedUsers']);
        Route::get('/{id}', [CommentController::class, 'show']);
        Route::post('/{id}/update', [CommentController::class, 'update']);  // use POST for multipart support
        Route::delete('/{id}', [CommentController::class, 'destroy']);
    });
    Route::prefix('posts')->group(function () {
        Route::get('/{post_id}/all-comments', [CommentController::class, 'getPostComments']);
    });
    Route::prefix('adoption')->group(function () {
        Route::post('/mark-as-adopted', [AdoptionListingController::class, 'markAsAdopted']);
    });
    Route::prefix('lost-found')->group(function () {
        Route::post('/marked', [LostFoundReportController::class, 'markLostFoundReportResolved']);
    });

    // Repost System
    Route::prefix('reposts')->group(function () {
        Route::post('/', [RepostController::class, 'store']);
        Route::get('/{id}', [RepostController::class, 'show']);
        Route::delete('/{id}', [RepostController::class, 'destroy']);
    });

    // Friend Request Logs
    Route::prefix('friends')->group(function () {
        Route::get('/requests/logs', [FriendController::class, 'requestLogs']);
        Route::post('/unfriend',[FriendController::class,'unfriend']);
    });

    // Profile Data Aggregation
    Route::get('/profile/{user_id}', [ProfileController::class, 'show']);
    Route::get('/profile/my/details', [ProfileController::class, 'me']);

    // Content Moderation
    Route::prefix('moderation')->group(function () {
        Route::post('/check', [ModerationController::class, 'check']);
    });

    // ─── Customer: Browse & Book Services ────────────────────────────────────
    Route::prefix('services')->group(function () {
        // Browse & search providers/services (with filters)
        Route::get('/', [ServiceController::class, 'index']);

        // Provider detail page + all their services
        Route::get('/{providerId}', [ServiceController::class, 'show'])->where('providerId', '[0-9]+');

        // Book a service (create booking + payment record)
        Route::post('/book', [ServiceController::class, 'book']);

        // Customer booking history (filter: ?status=pending|accepted|completed|rejected)
        Route::get('/bookings', [ServiceController::class, 'myBookings']);

        // Single booking detail
        Route::get('/bookings/{id}', [ServiceController::class, 'bookingDetail']);

        // Cancel a pending booking
        Route::post('/bookings/{id}/cancel', [ServiceController::class, 'cancelBooking']);

        // Submit rating & review (only for completed bookings)
        Route::post('/bookings/{id}/review', [ServiceController::class, 'submitReview']);

        // Payment transaction detail for a booking
        Route::get('/bookings/{id}/payment', [ServiceController::class, 'paymentDetail']);
    });

    // ─── Vendor: Reviews & Ratings ───────────────────────────────────────────
    Route::prefix('vendor')->group(function () {
        Route::get('/reviews', [ReviewController::class, 'vendorReviews']);
        Route::post('/reviews/{reviewId}/reply', [ReviewController::class, 'replyToReview']);
    });
    // ─── Marketplace ────────────────────────────────────────────────────────
    Route::prefix('/market-place')->group(function () {

        // 🏠 HOME
        Route::get('/home', [MarketplaceController::class, 'home']);

        // 📦 PRODUCTS
        Route::get('/products/category/{id}', [ProductController::class, 'byCategory']);
        Route::get('/products/company/{id}', [CompanyController::class, 'companyProducts']);
        Route::get('/product/{id}', [ProductController::class, 'show']);

        // 📍 ADDRESS
        Route::post('/address', [AddressController::class, 'store']);
        Route::get('/address', [AddressController::class, 'index']);
        Route::put('/address/{id}', [AddressController::class, 'update']);
        Route::delete('/address/{id}', [AddressController::class, 'destroy']);

        // 🎟️ COUPON
        Route::post('/apply-coupon', [CouponController::class, 'apply']);

        // 🛒 ORDER
        Route::post('/order', [OrderController::class, 'store']);
        Route::get('/orders', [OrderController::class, 'index']);
    });

}); // End auth:sanctum middleware group


Route::prefix('v2')->group(function () {

    // Public
    Route::get('/contests', [ContestController::class, 'index']);
    Route::get('/contest/{id}', [ContestController::class, 'show']);
    Route::get('/contest/leaderboard/{id}', [ContestController::class, 'leaderboard']);
    Route::get('/contest/winners/{id}', [ContestController::class, 'winners']);

    // Protected
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::post('/contest/entry', [ContestController::class, 'submitEntry']);
        Route::post('/contest/vote', [ContestController::class, 'vote']);
        Route::get('/contest/my/entries', [ContestController::class, 'myEntries']);
    });

});
