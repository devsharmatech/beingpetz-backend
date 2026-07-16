<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Provider;
use App\Models\ProviderAvailability;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class VendorCalendarController extends Controller
{
    // Fetch default shifts and specific blocked dates
    public function getAvailability(Request $request)
    {
        $user = auth()->user();
        $provider = Provider::where('user_id', $user->id)->first();
        if (!$provider) return response()->json(['status' => false, 'message' => 'Provider not found'], 200);

        // Fetch default shifts (where date is null)
        $defaultShifts = ProviderAvailability::where('provider_id', $provider->id)->whereNull('date')->first();

        // If no default shifts set, provide a fallback
        if (!$defaultShifts) {
            $defaultShifts = [
                'morning_active' => true,
                'afternoon_active' => true,
                'evening_active' => false
            ];
        }

        // Fetch blocked dates (where date is not null and is_blocked is true)
        // Usually, the app fetches for a specific month, so we can filter by current/next months
        $blockedDates = ProviderAvailability::where('provider_id', $provider->id)
            ->whereNotNull('date')
            ->where('is_blocked', true)
            ->where('date', '>=', Carbon::now()->startOfMonth()->format('Y-m-d'))
            ->pluck('date')
            ->map(function($date) {
                return Carbon::parse($date)->format('Y-m-d');
            });

        return response()->json([
            'status' => true,
            'message' => 'Availability calendar fetched.',
            'data' => [
                'default_shifts' => [
                    'morning' => (bool) ($defaultShifts['morning_active'] ?? $defaultShifts->morning_active),
                    'afternoon' => (bool) ($defaultShifts['afternoon_active'] ?? $defaultShifts->afternoon_active),
                    'evening' => (bool) ($defaultShifts['evening_active'] ?? $defaultShifts->evening_active),
                ],
                'blocked_dates' => $blockedDates
            ]
        ], 200);
    }

    // Update default shifts or specific blocked dates
    public function updateAvailability(Request $request)
    {
        $user = auth()->user();
        $provider = Provider::where('user_id', $user->id)->first();
        if (!$provider) return response()->json(['status' => false, 'message' => 'Provider not found'], 200);

        $validator = Validator::make($request->all(), [
            'shifts' => 'nullable|array',
            'shifts.morning' => 'boolean',
            'shifts.afternoon' => 'boolean',
            'shifts.evening' => 'boolean',
            'blocked_dates' => 'nullable|array',
            'blocked_dates.*' => 'date_format:Y-m-d'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 200);
        }

        // 1. Update Default Shifts
        if ($request->has('shifts')) {
            ProviderAvailability::updateOrCreate(
                ['provider_id' => $provider->id, 'date' => null],
                [
                    'morning_active' => $request->input('shifts.morning', true),
                    'afternoon_active' => $request->input('shifts.afternoon', true),
                    'evening_active' => $request->input('shifts.evening', false),
                ]
            );
        }

        // 2. Update Blocked Dates
        // This takes an array of dates to block. We should probably just sync them.
        // For a true sync, we remove existing blocked dates from the current month onwards, then add the new ones.
        if ($request->has('blocked_dates')) {
            $datesToBlock = $request->input('blocked_dates');
            
            // Delete existing blocked dates for the provider (or just ones in the payload)
            // For simplicity, we just clear and recreate them to ensure sync
            ProviderAvailability::where('provider_id', $provider->id)
                ->whereNotNull('date')
                ->where('is_blocked', true)
                ->delete();

            foreach ($datesToBlock as $date) {
                ProviderAvailability::create([
                    'provider_id' => $provider->id,
                    'date' => $date,
                    'is_blocked' => true,
                    'morning_active' => false,
                    'afternoon_active' => false,
                    'evening_active' => false
                ]);
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Calendar settings saved successfully.'
        ], 200);
    }
}
