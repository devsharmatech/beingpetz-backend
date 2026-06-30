<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{Order, OrderItem, ProductVariant};
use Illuminate\Http\Request;
use DB;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $order = Order::create([
                'user_id' => $request->user_id,
                'address_id' => $request->address_id,
                'total_amount' => $request->total,
                'discount_amount' => $request->discount ?? 0,
                'final_amount' => $request->final,
                'payment_method' => $request->payment_method,
                'order_status' => 'pending',
                'order_number' => 'ORD-' . time(),
                'placed_at' => now()
            ]);

            foreach ($request->items as $item) {

                $variant = ProductVariant::find($item['variant_id']);

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $variant->product_id,
                    'variant_id' => $variant->id,
                    'company_id' => $variant->product->company_id,
                    'product_name' => $variant->product->name,
                    'quantity' => $item['qty'],
                    'price' => $variant->sale_price ?? $variant->price
                ]);

                // reduce stock
                $variant->decrement('stock', $item['qty']);
            }

            DB::commit();

            return response()->json(['success' => true,'message'=>"Order placed Successfully",'data'=>$order]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false,'message'=>"Something went wrong",'error' => $e->getMessage()], 500);
        }
    }

    // 📜 Order history
    public function index(Request $request)
    {
        $orders=Order::with('items')
            ->where('user_id', $request->user_id)
            ->latest()
            ->get();
        return response()->json(['success' => true,'message'=>"History fetched Successfully",'data'=>$orders]);
    }
}