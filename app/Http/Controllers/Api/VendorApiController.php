<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Provider;
use App\Models\ServiceBooking;
use App\Models\ProviderEarning;
use App\Models\ProviderReview;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class VendorApiController extends Controller
{
    // Vendor Login
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 200);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['status' => false, 'message' => 'Invalid credentials.'], 200);
        }

        if ($user->role !== 'vendor' && $user->role_name !== 'vendor') {
            return response()->json(['status' => false, 'message' => 'User is not a vendor.'], 200);
        }

        $token = $user->createToken('VendorAuthToken')->plainTextToken;
        $provider = Provider::where('user_id', $user->id)->first();

        return response()->json([
            'status' => true,
            'message' => 'Login successful',
            'token' => $token,
            'data' => [
                'user' => $user,
                'provider' => $provider
            ]
        ], 200);
    }

    // Dashboard
    public function dashboard(Request $request)
    {
        $user = auth()->user();
        $provider = Provider::where('user_id', $user->id)->first();

        if (!$provider) {
            return response()->json(['status' => false, 'message' => 'Provider profile not found.'], 200);
        }

        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();
        
        $todayStart = $now->copy()->startOfDay();
        $todayEnd = $now->copy()->endOfDay();

        // Monthly Earnings
        $totalEarningsThisMonth = ProviderEarning::where('provider_id', $provider->id)
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->where('status', 'cleared')
            ->sum('amount');

        // Bookings count
        $totalBookings = ServiceBooking::where('provider_id', $provider->id)->count();
        $pendingBookings = ServiceBooking::where('provider_id', $provider->id)->where('status', 'pending')->count();

        // Repeats count (unique users returning)
        $repeatsCount = ServiceBooking::where('provider_id', $provider->id)
            ->select('user_id')
            ->groupBy('user_id')
            ->havingRaw('COUNT(*) > 1')
            ->get()
            ->count();

        // Today Overview
        $scheduledToday = ServiceBooking::where('provider_id', $provider->id)
            ->whereBetween('scheduled_at', [$todayStart, $todayEnd])
            ->count();

        $pendingsToday = ServiceBooking::where('provider_id', $provider->id)
            ->where('status', 'pending')
            ->whereBetween('created_at', [$todayStart, $todayEnd])
            ->count();

        $doneToday = ServiceBooking::where('provider_id', $provider->id)
            ->where('status', 'completed')
            ->whereBetween('scheduled_at', [$todayStart, $todayEnd])
            ->count();

        $earnedToday = ProviderEarning::where('provider_id', $provider->id)
            ->where('status', 'cleared')
            ->whereBetween('created_at', [$todayStart, $todayEnd])
            ->sum('amount');

        $averageRating = ProviderReview::where('provider_id', $provider->id)->avg('rating') ?? 0;
        $totalReviews = ProviderReview::where('provider_id', $provider->id)->count();

        return response()->json([
            'status' => true,
            'message' => 'Dashboard data fetched.',
            'data' => [
                'monthly_earnings' => $totalEarningsThisMonth,
                'ratings' => [
                    'average' => $averageRating,
                    'total' => $totalReviews
                ],
                'bookings' => [
                    'total' => $totalBookings,
                    'pending_requests' => $pendingBookings,
                ],
                'repeats_count' => $repeatsCount,
                'today_overview' => [
                    'scheduled' => $scheduledToday,
                    'pendings' => $pendingsToday,
                    'done' => $doneToday,
                    'earned' => $earnedToday,
                ]
            ]
        ], 200);
    }

    // Earnings History
    public function earningsHistory(Request $request)
    {
        $user = auth()->user();
        $provider = Provider::where('user_id', $user->id)->first();
        if (!$provider) return response()->json(['status' => false, 'message' => 'Provider not found'], 200);

        $earnings = ProviderEarning::where('provider_id', $provider->id)
            ->with('booking')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json([
            'status' => true,
            'message' => 'Earnings history fetched.',
            'data' => $earnings
        ], 200);
    }

    // Weekly Chart
    public function weeklyChart(Request $request)
    {
        $user = auth()->user();
        $provider = Provider::where('user_id', $user->id)->first();
        if (!$provider) return response()->json(['status' => false, 'message' => 'Provider not found'], 200);

        $startDate = Carbon::now()->subDays(6)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        $earnings = ProviderEarning::where('provider_id', $provider->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'cleared')
            ->selectRaw('DATE(created_at) as date, SUM(amount) as total')
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $dayName = Carbon::now()->subDays($i)->format('D');
            $chartData[] = [
                'date' => $date,
                'day' => $dayName,
                'amount' => isset($earnings[$date]) ? (float)$earnings[$date]->total : 0
            ];
        }

        return response()->json([
            'status' => true,
            'message' => 'Weekly chart data fetched.',
            'data' => $chartData
        ], 200);
    }

    // Upcoming Bookings
    public function upcomingBookings(Request $request)
    {
        $user = auth()->user();
        $provider = Provider::where('user_id', $user->id)->first();
        if (!$provider) return response()->json(['status' => false, 'message' => 'Provider not found'], 200);

        $bookings = ServiceBooking::where('provider_id', $provider->id)
            ->where('status', 'accepted')
            ->where('scheduled_at', '>=', Carbon::now())
            ->orderBy('scheduled_at', 'asc')
            ->with('user', 'service')
            ->take(5)
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Upcoming bookings fetched.',
            'data' => $bookings
        ], 200);
    }

    // All Bookings
    public function allBookings(Request $request)
    {
        $user = auth()->user();
        $provider = Provider::where('user_id', $user->id)->first();
        if (!$provider) return response()->json(['status' => false, 'message' => 'Provider not found'], 200);

        $query = ServiceBooking::where('provider_id', $provider->id)->with('user', 'service');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $bookings = $query->orderBy('scheduled_at', 'desc')->paginate(15);

        return response()->json([
            'status' => true,
            'message' => 'Bookings fetched.',
            'data' => $bookings
        ], 200);
    }

    // My Services
    public function myServices(Request $request)
    {
        $user = auth()->user();
        $provider = Provider::where('user_id', $user->id)->with('service')->first();
        if (!$provider) return response()->json(['status' => false, 'message' => 'Provider not found'], 200);

        return response()->json([
            'status' => true,
            'message' => 'Services fetched.',
            'data' => [
                'provider' => $provider,
                'service' => $provider->service,
            ]
        ], 200);
    }
    // Reschedule a booking
    public function rescheduleBooking(Request $request, $id)
    {
        $user = auth()->user();
        $provider = Provider::where('user_id', $user->id)->first();
        if (!$provider) return response()->json(['status' => false, 'message' => 'Provider not found'], 200);

        $scheduledAt = $request->input('scheduled_at') ?? $request->input('new_schedule');

        $validator = Validator::make(['scheduled_at' => $scheduledAt], [
            'scheduled_at' => 'required|date|after:now',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 200);
        }

        $booking = ServiceBooking::where('provider_id', $provider->id)->where('id', $id)->first();
        if (!$booking) return response()->json(['status' => false, 'message' => 'Booking not found'], 200);

        $booking->scheduled_at = Carbon::parse($scheduledAt)->format('Y-m-d H:i:s');
        // Depending on business logic, we could change status back to pending to notify user
        // $booking->status = 'pending';
        $booking->save();

        return response()->json([
            'status' => true,
            'message' => 'Booking rescheduled successfully.',
            'data' => $booking
        ], 200);
    }

    // Accept a booking
    public function acceptBooking(Request $request, $id)
    {
        $user = auth()->user();
        $provider = Provider::where('user_id', $user->id)->first();
        if (!$provider) return response()->json(['status' => false, 'message' => 'Provider not found'], 200);

        $booking = ServiceBooking::where('provider_id', $provider->id)->where('id', $id)->first();
        if (!$booking) return response()->json(['status' => false, 'message' => 'Booking not found'], 200);

        if ($booking->status !== 'pending') {
            return response()->json(['status' => false, 'message' => 'Only pending bookings can be accepted.'], 200);
        }

        $booking->status = 'accepted';
        $booking->save();

        return response()->json([
            'status' => true,
            'message' => 'Booking accepted successfully.',
            'data' => $booking
        ], 200);
    }

    // Reject a booking
    public function rejectBooking(Request $request, $id)
    {
        $user = auth()->user();
        $provider = Provider::where('user_id', $user->id)->first();
        if (!$provider) return response()->json(['status' => false, 'message' => 'Provider not found'], 200);

        $booking = ServiceBooking::where('provider_id', $provider->id)->where('id', $id)->first();
        if (!$booking) return response()->json(['status' => false, 'message' => 'Booking not found'], 200);

        if ($booking->status !== 'pending') {
            return response()->json(['status' => false, 'message' => 'Only pending bookings can be rejected.'], 200);
        }

        $booking->status = 'rejected';
        $booking->save();

        return response()->json([
            'status' => true,
            'message' => 'Booking rejected successfully.',
            'data' => $booking
        ], 200);
    }

    // Complete a booking
    public function completeBooking(Request $request, $id)
    {
        $user = auth()->user();
        $provider = Provider::where('user_id', $user->id)->first();
        if (!$provider) return response()->json(['status' => false, 'message' => 'Provider not found'], 200);

        $booking = ServiceBooking::where('provider_id', $provider->id)->where('id', $id)->first();
        if (!$booking) return response()->json(['status' => false, 'message' => 'Booking not found'], 200);

        if ($booking->status !== 'accepted') {
            return response()->json(['status' => false, 'message' => 'Only accepted bookings can be marked as completed.'], 200);
        }

        $booking->status = 'completed';
        $booking->save();

        // Add funds to wallet (ProviderEarning)
        // Check if earning already exists for this booking to prevent duplicates
        $existingEarning = ProviderEarning::where('provider_id', $provider->id)->where('service_booking_id', $booking->id)->first();
        if (!$existingEarning) {
            ProviderEarning::create([
                'provider_id' => $provider->id,
                'service_booking_id' => $booking->id,
                'amount' => $booking->total_amount,
                'status' => 'pending' // pending until withdrawn
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Booking marked as completed. Funds added to your wallet pending balance.',
            'data' => $booking
        ], 200);
    }

    // Get Booking Details
    public function getBookingDetails(Request $request, $id)
    {
        $user = auth()->user();
        $provider = Provider::where('user_id', $user->id)->first();
        if (!$provider) return response()->json(['status' => false, 'message' => 'Provider not found'], 200);

        $booking = ServiceBooking::with(['user', 'service'])->where('provider_id', $provider->id)->where('id', $id)->first();
        if (!$booking) return response()->json(['status' => false, 'message' => 'Booking not found'], 200);

        return response()->json([
            'status' => true,
            'message' => 'Booking details fetched.',
            'data' => $booking
        ], 200);
    }

    // Get Profile
    public function getProfile(Request $request)
    {
        $user = auth()->user();
        $provider = Provider::where('user_id', $user->id)->first();
        
        return response()->json([
            'status' => true,
            'message' => 'Profile fetched.',
            'data' => [
                'user' => $user,
                'provider' => $provider
            ]
        ], 200);
    }

    // Edit Profile
    public function updateProfile(Request $request)
    {
        $user = auth()->user();
        
        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 200);
        }

        $provider = Provider::where('user_id', $user->id)->first();
        
        if ($provider) {
            $provider->update($request->only(['name', 'phone', 'address']));
        }

        if ($request->has('name')) {
            $names = explode(' ', $request->name, 2);
            $user->first_name = $names[0];
            $user->last_name = $names[1] ?? '';
        }
        if ($request->has('phone')) $user->phone = $request->phone;
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Profile updated successfully.',
            'data' => [
                'user' => $user,
                'provider' => $provider
            ]
        ], 200);
    }
}
