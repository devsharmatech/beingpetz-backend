<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Provider;
use App\Models\SupportMessage;
use Illuminate\Support\Facades\Validator;

class VendorSupportController extends Controller
{
    public function messages(Request $request)
    {
        $user = auth()->user();
        $provider = Provider::where('user_id', $user->id)->first();
        if (!$provider) return response()->json(['status' => false, 'message' => 'Provider not found'], 200);

        $messages = SupportMessage::where('provider_id', $provider->id)
            ->orderBy('created_at', 'asc') // Chat order
            ->get()
            ->map(function($msg) {
                return [
                    'id' => $msg->id,
                    'message' => $msg->message,
                    'is_from_admin' => (bool)$msg->is_from_admin,
                    'time' => $msg->created_at->format('h:i A'),
                    'date' => $msg->created_at->format('Y-m-d')
                ];
            });

        return response()->json([
            'status' => true,
            'message' => 'Support chat fetched.',
            'data' => [
                'greeting' => "Hi! How can we help you with " . ($provider->business_name ?? 'your business') . " today?",
                'messages' => $messages
            ]
        ], 200);
    }

    public function sendMessage(Request $request)
    {
        $user = auth()->user();
        $provider = Provider::where('user_id', $user->id)->first();
        if (!$provider) return response()->json(['status' => false, 'message' => 'Provider not found'], 200);

        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 200);
        }

        $msg = SupportMessage::create([
            'provider_id' => $provider->id,
            'message' => $request->message,
            'is_from_admin' => false
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Message sent successfully.',
            'data' => [
                'id' => $msg->id,
                'message' => $msg->message,
                'is_from_admin' => false,
                'time' => $msg->created_at->format('h:i A'),
                'date' => $msg->created_at->format('Y-m-d')
            ]
        ], 200);
    }
}
