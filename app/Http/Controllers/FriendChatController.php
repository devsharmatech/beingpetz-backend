<?php

namespace App\Http\Controllers;

use App\Models\FriendMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FriendChatController extends Controller
{
   
    protected function handleMediaUpload(Request $request, string $field): ?string
    {
        if ($request->hasFile($field)) {
            $file = $request->file($field);
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/messages'), $filename);
            return 'uploads/messages/' . $filename;
        }
        return null;
    }

    
    public function sendMessage(Request $request)
    {
        $v = Validator::make($request->all(), [
            'from_pet_id'   => 'required|exists:pets,id',
            'to_pet_id'     => 'required|exists:pets,id|different:from_pet_id',
            'message_type'=> 'required|in:text,image,video,audio',
            'message_text'=> 'nullable|string',
            'media'       => 'nullable|file|mimes:jpeg,png,jpg,gif,mp4,mp3,wav|max:20480',
        ]);
        if ($v->fails()) {
            return response()->json(['status'=>false,'message'=>$v->errors()->first(),'errors'=>$v->errors()],422);
        }
        $data = $v->validated();

        $mediaPath = $this->handleMediaUpload($request, 'media');

        $message = FriendMessage::create([
            'sender_id'    => $data['from_pet_id'],
            'receiver_id'  => $data['to_pet_id'],
            'message_type' => $data['message_type'],
            'message_text' => $data['message_text'] ?? null,
            'media_path'   => $mediaPath,
        ]);

        return response()->json(['status'=>true,'message'=>'Message sent','data'=>$message],201);
    }

   
    public function getMessages(Request $request)
    {
        $v = Validator::make($request->all(), [
            'from_pet_id'   => 'required|exists:pets,id',
            'to_pet_id'     => 'required|exists:pets,id|different:from_pet_id',
        ]);
        if ($v->fails()) {
            return response()->json(['status'=>false,'message'=>$v->errors()->first()],422);
        }

        $msgs = FriendMessage::where(function($q) use ($request) {
            $q->where('sender_id',$request->from_pet_id)
              ->where('receiver_id',$request->to_pet_id);
        })->orWhere(function($q) use ($request) {
            $q->where('sender_id',$request->from_pet_id)
              ->where('receiver_id',$request->to_pet_id);
        })->orderBy('created_at','asc')->get();

        return response()->json(['status'=>true,'data'=>$msgs],200);
    }


    public function index()
    {
        $messages = FriendMessage::with(['sender', 'receiver'])
            ->latest()
            ->paginate(20);

        return view('admin.messages.index', compact('messages'));
    }

    
    public function markAsRead(Request $request, $id)
    {
        try {
            $message = FriendMessage::findOrFail($id);
            
            $message->update([
                'is_seen' => 1,
                'seen_at' => now() // if you have this column
            ]);

            return redirect()->route('admin.messages.index')
                ->with('success', 'Message marked as read successfully!');

        } catch (\Exception $e) {
            return redirect()->route('admin.messages.index')
                ->with('error', 'Failed to mark message as read: ' . $e->getMessage());
        }
    }

    public function conversationHistory($senderId, $receiverId)
    {
        $conversation = FriendMessage::where(function($query) use ($senderId, $receiverId) {
                $query->where('sender_id', $senderId)
                      ->where('receiver_id', $receiverId);
            })
            ->orWhere(function($query) use ($senderId, $receiverId) {
                $query->where('sender_id', $receiverId)
                      ->where('receiver_id', $senderId);
            })
            ->with(['sender', 'receiver'])
            ->orderBy('created_at', 'asc')
            ->get();

        $sender = User::findOrFail($senderId);
        $receiver = User::findOrFail($receiverId);

        return view('admin.messages.conversation', compact('conversation', 'sender', 'receiver'));
    }

    public function destroy($id)
    {
        $message = FriendMessage::findOrFail($id);
        $message->delete();

        return redirect()->back()->with('success', 'Message deleted successfully!');
    }

    
    public function bulkDelete(Request $request)
{
    $request->validate([
        'ids' => 'required|array',
        'ids.*' => 'exists:friend_messages,id'
    ]);

    FriendMessage::whereIn('id', $request->ids)->delete();

    return redirect()->back()->with('success', 'Selected messages deleted successfully!');
}
}
