<?php

namespace App\Http\Controllers\V2Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use Validator;
use PDF;

class OrderController extends Controller
{
    // LIST + FILTER
    public function index(Request $request)
    {
        $query = Order::with(['user','payment']);

        if ($request->order_status) {
            $query->where('order_status', $request->order_status);
        }

        if ($request->payment_status) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->search) {
            $query->where('order_number', 'like', "%{$request->search}%");
        }

        $data = $query->latest()->paginate(10);

        return response()->json([
            'success'=>true,
            'data'=>$data
        ]);
    }

    // ORDER DETAIL
    public function show($id)
    {
        $order = Order::with([
            'user',
            'items.product',
            'items',
            'payment'
        ])->findOrFail($id);

        return response()->json([
            'success'=>true,
            'data'=>$order
        ]);
    }

    // UPDATE ORDER STATUS
    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'order_status' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success'=>false,
                'message'=>$validator->errors()->first()
            ]);
        }

        $order = Order::findOrFail($id);

        $order->update([
            'order_status' => $request->order_status
        ]);

        return response()->json([
            'success'=>true,
            'message'=>'Order status updated'
        ]);
    }

    // UPDATE PAYMENT STATUS
    public function updatePayment(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'payment_status' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success'=>false,
                'message'=>$validator->errors()->first()
            ]);
        }

        $order = Order::findOrFail($id);

        $order->update([
            'payment_status' => $request->payment_status
        ]);

        return response()->json([
            'success'=>true,
            'message'=>'Payment status updated'
        ]);
    }

    // PRINT VIEW
    public function print($id)
    {
        $order = Order::with(['items','user','payment'])->findOrFail($id);

        return view('admin.orders.print', compact('order'));
    }

    // PDF DOWNLOAD
    public function pdf($id)
    {
        $order = Order::with(['items','user','payment'])->findOrFail($id);

        $pdf = PDF::loadView('admin.orders.pdf', compact('order'));

        return $pdf->download('order_'.$order->order_number.'.pdf');
    }
}