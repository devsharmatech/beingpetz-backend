<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CouponController extends Controller
{
    public function apply(Request $request)
    {
        
        $coupon = Coupon::where('code', $request->code)
            ->where('is_active', 1)
            ->first();
        
        if (!$coupon) {
            return response()->json(['success' => false,'message' => 'Invalid coupon'], 400);
        }

        if (Carbon::now()->gt($coupon->end_date)) {
            return response()->json(['success' => false,'message' => 'Coupon expired'], 400);
        }

        $amount = $request->amount;

        if ($amount < $coupon->min_order_amount) {
            return response()->json(['success' => false,'message' => 'Minimum amount not reached'], 400);
        }

        $discount = $coupon->type === 'percentage'
            ? ($amount * $coupon->value) / 100
            : $coupon->value;

        if ($coupon->max_discount && $discount > $coupon->max_discount) {
            $discount = $coupon->max_discount;
        }

        return response()->json([
            'success' => true,'message'=>"Applied successfully",
            'discount' => $discount,
            'final_amount' => $amount - $discount
        ]);
    }
}