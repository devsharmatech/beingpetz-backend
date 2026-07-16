<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Provider;
use App\Models\ProviderEarning;
use App\Models\PayoutRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class VendorEarningController extends Controller
{
    public function wallet(Request $request)
    {
        $user = auth()->user();
        $provider = Provider::where('user_id', $user->id)->first();
        if (!$provider) return response()->json(['status' => false, 'message' => 'Provider not found'], 200);

        // Calculate Pending Payout (cleared earnings - requested payouts)
        $totalClearedEarnings = ProviderEarning::where('provider_id', $provider->id)
            ->where('status', 'cleared')
            ->sum('amount');
        
        $totalPayoutRequests = PayoutRequest::where('provider_id', $provider->id)
            ->whereIn('status', ['pending', 'completed'])
            ->sum('amount');
            
        $pendingPayout = max(0, $totalClearedEarnings - $totalPayoutRequests);

        // Lifetime Earnings
        $lifetimeEarnings = $totalClearedEarnings;

        // This Month Earnings
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        $thisMonthEarnings = ProviderEarning::where('provider_id', $provider->id)
            ->where('status', 'cleared')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        // Monthly Progress (Jan to Jun etc)
        $monthlyProgress = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthStart = Carbon::now()->subMonths($i)->startOfMonth();
            $monthEnd = Carbon::now()->subMonths($i)->endOfMonth();
            
            $monthEarnings = ProviderEarning::where('provider_id', $provider->id)
                ->where('status', 'cleared')
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->sum('amount');
                
            $monthlyProgress[] = [
                'month' => $monthStart->format('M'), // Jan, Feb, etc
                'amount' => (float)$monthEarnings
            ];
        }

        // Recent Transactions (Combine Earnings and Payouts)
        $recentEarnings = ProviderEarning::where('provider_id', $provider->id)
            ->with('booking.service')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function($e) {
                return [
                    'id' => 'E-'.$e->id,
                    'title' => $e->booking ? $e->booking->service->name : 'Service Payment',
                    'subtitle' => $e->booking && $e->booking->pet ? $e->booking->pet->name : 'Payment',
                    'amount' => (float)$e->amount,
                    'type' => 'credit',
                    'date' => $e->created_at->format('Y-m-d H:i:s'),
                    'display_date' => $e->created_at->diffForHumans()
                ];
            });

        $recentPayouts = PayoutRequest::where('provider_id', $provider->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function($p) {
                return [
                    'id' => 'P-'.$p->id,
                    'title' => 'Payout Withdrawal',
                    'subtitle' => ucfirst($p->status),
                    'amount' => (float)$p->amount,
                    'type' => 'debit',
                    'date' => $p->created_at->format('Y-m-d H:i:s'),
                    'display_date' => $p->created_at->diffForHumans()
                ];
            });

        $transactions = collect($recentEarnings)->merge($recentPayouts)->sortByDesc('date')->values()->take(15);

        return response()->json([
            'status' => true,
            'message' => 'Wallet details fetched successfully.',
            'data' => [
                'pending_payout' => (float)$pendingPayout,
                'this_month_earnings' => (float)$thisMonthEarnings,
                'lifetime_earnings' => (float)$lifetimeEarnings,
                'monthly_progress' => $monthlyProgress,
                'recent_transactions' => $transactions
            ]
        ], 200);
    }

    public function withdraw(Request $request)
    {
        $user = auth()->user();
        $provider = Provider::where('user_id', $user->id)->first();
        if (!$provider) return response()->json(['status' => false, 'message' => 'Provider not found'], 200);

        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:100'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 200);
        }

        $totalClearedEarnings = ProviderEarning::where('provider_id', $provider->id)
            ->where('status', 'cleared')
            ->sum('amount');
        
        $totalPayoutRequests = PayoutRequest::where('provider_id', $provider->id)
            ->whereIn('status', ['pending', 'completed'])
            ->sum('amount');
            
        $pendingPayout = max(0, $totalClearedEarnings - $totalPayoutRequests);

        if ($request->amount > $pendingPayout) {
            return response()->json(['status' => false, 'message' => 'Insufficient funds for this withdrawal.'], 200);
        }

        $payout = PayoutRequest::create([
            'provider_id' => $provider->id,
            'amount' => $request->amount,
            'status' => 'pending'
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Withdrawal request submitted successfully.',
            'data' => $payout
        ], 200);
    }
}
