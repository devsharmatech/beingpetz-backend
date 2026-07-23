<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Provider;
use App\Models\FriendMessage; 

class VendorMessageController extends Controller
{
    public function history(Request $request)
    {
        $user = auth()->user();

        // Robust provider lookup using email, phone, or user_id
        $provider = Provider::findForUser($user);

        // Allow access if provider record exists OR user role is vendor
        if (!$provider && $user->role !== 'vendor' && ($user->role_name ?? '') !== 'vendor') {
            return response()->json(['status' => false, 'message' => 'Provider not found'], 200);
        }

        $messages = FriendMessage::where('sender_id', $user->id)
            ->orWhere('receiver_id', $user->id)
            ->with(['sender', 'receiver'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'status' => true,
            'message' => 'Message history fetched.',
            'data' => $messages
        ], 200);
    }

    public function chatWithCustomer(Request $request, $customerId)
    {
        $user = auth()->user();
        
        // Fetch conversation between this vendor and the specific customer
        $messages = FriendMessage::where(function($q) use ($user, $customerId) {
                $q->where('sender_id', $user->id)->where('receiver_id', $customerId);
            })
            ->orWhere(function($q) use ($user, $customerId) {
                $q->where('sender_id', $customerId)->where('receiver_id', $user->id);
            })
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Chat fetched successfully.',
            'data' => $messages
        ], 200);
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'message' => 'required|string'
        ]);

        $user = auth()->user();

        $message = FriendMessage::create([
            'sender_id' => $user->id,
            'receiver_id' => $request->receiver_id,
            'message' => $request->message,
            'type' => 'text',
            'status' => 0 // unread
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Message sent successfully.',
            'data' => $message
        ], 200);
    }
}
