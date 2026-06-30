<?php

namespace App\Http\Controllers;

use App\Models\CommunityMessage;
use App\Models\CommunityPoll;
use App\Models\CommunityPollOption;
use App\Models\CommunityPollVote;
use App\Models\CommunityMessageLike;
use App\Models\DeletedCommunityMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;

class CommunityChatController extends Controller
{
   
    protected function handleMediaUpload(Request $request, string $field): ?string
    {
        if ($request->hasFile($field)) {
            $file = $request->file($field);
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = 'uploads/messages/' . $filename;
            $file->move(public_path('uploads/messages'), $filename);
            return $path;
        }
        return null;
    }

    public function toggleLike(Request $request)
    {
    $request->validate([
        'message_id' => 'required|exists:community_messages,id',
        'member_id' => 'required|exists:users,id',
    ]);

    $messageId = $request->message_id;
    $memberId =  $request->member_id;

    $like = CommunityMessageLike::where('message_id', $messageId)
                                ->where('member_id', $memberId)
                                ->first();

     if ($like) {
        $like->delete();
        return response()->json([
            'status' => true,
            'code'=>'unliked',
            'message' => 'Message unliked successfully.'
        ]);
     } else {
        CommunityMessageLike::create([
            'message_id' => $messageId,
            'member_id' => $memberId,
        ]);
        return response()->json([
            'status' => true,
            'code'=>'liked',
            'message' => 'Message liked successfully.'
        ]);
      }
    }

    public function sendMessage(Request $request)
    {
        $v = Validator::make($request->all(), [
            'community_id' => 'required|exists:communities,id',
            'message_id' =>     'nullable|exists:community_messages,id',
            'parent_id'      => 'required|exists:users,id',
            'message_type' => 'required|in:text,image,video,audio,poll',
            'message_text' => 'nullable|string',
            'media'        => 'nullable|file|mimes:jpeg,png,jpg,gif,mp4,mp3,wav|max:20480',
            'question'  => 'exclude_unless:message_type,poll|required|string',
            'options'   => 'exclude_unless:message_type,poll|required|array|min:2',
            'options.*' => 'exclude_unless:message_type,poll|string|max:255',
        ]);
        if ($v->fails()) {
            return response()->json(['status' => false, 'message' => $v->errors()->first(), 'errors' => $v->errors()], 422);
        }
        $data = $v->validated();

        $mediaPath = $this->handleMediaUpload($request, 'media');

        $message = CommunityMessage::create([
            'community_id' => $data['community_id'],
            'parent_id'      => $data['parent_id'],
            'isReply'      =>  isset($data['message_id'])?1:0,
            'message_id'      => $data['message_id'],
            'message_type' => $data['message_type'],
            'message_text' => $data['message_text'] ?? null,
            'media_path'   => $mediaPath,
        ]);

        // If it's a poll, persist poll and options
        if ($data['message_type'] === 'poll') {
            $poll = CommunityPoll::create([
                'message_id' => $message->id,
                'question'   => $data['question'],
            ]);
            foreach ($data['options'] as $opt) {
                CommunityPollOption::create([
                    'poll_id'     => $poll->id,
                    'option_text' => $opt,
                ]);
            }
        }

        return response()->json(['status' => true, 'message' => 'Message sent', 'data' => $message->load('poll.options')], 201);
    }

    
    public function getMessagesOld(Request $request)
    {
        $v = Validator::make($request->all(), [
            'community_id' => 'required|exists:communities,id',
            'user_id' => 'nullable|exists:users,id',
        ]);
        if ($v->fails()) {
            return response()->json(['status' => false, 'message' => $v->errors()->first()], 422);
        }

        $messages = CommunityMessage::where('community_id', $request->community_id)
            ->with(['user','old_message', 'poll.options', 'poll.options.votes'])
            ->orderBy('created_at', 'asc')
            ->get();
        
        $messages->map(function ($message) {
        if (isset($message->poll) && $message->poll && $message->poll->options) {
            $totalVotes = $message->poll->options->sum(function ($option) {
                return $option->votes->count();
            });

            foreach ($message->poll->options as $option) {
                $voteCount = $option->votes->count();
                $option->vote_percentage = $totalVotes > 0
                    ? round(($voteCount / $totalVotes) * 100, 2)
                    : 0;
            }
          }
          return $message;
        });
        return response()->json(['status' => true, 'data' => $messages], 200);
    }
    
    public function getMessages(Request $request)
    {
     $v = Validator::make($request->all(), [
        'community_id' => 'required|exists:communities,id',
        'user_id' => 'nullable|exists:users,id',
     ]);

     if ($v->fails()) {
        return response()->json(['status' => false, 'message' => $v->errors()->first()], 422);
     }

     $query = CommunityMessage::where('community_id', $request->community_id)
        ->with(['user', 'old_message', 'poll.options', 'poll.options.votes','likes'])
        ->orderBy('created_at', 'asc');
    
    // Apply user-specific deletion filter
    if (!empty($request->user_id)) {
        $query->whereNotIn('id', function ($q) use ($request) {
            $q->select('community_message_id')
              ->from('deleted_community_messages')
              ->where('user_id', $request->user_id);
        });
    }

    $messages = $query->get();
   
    // Calculate vote percentages
    $messages->map(function ($message) {
        $message->like_count = $message->likes->count();
        if (isset($message->poll) && $message->poll && $message->poll->options) {
            $totalVotes = $message->poll->options->sum(function ($option) {
                return $option->votes->count();
            });

            foreach ($message->poll->options as $option) {
                $voteCount = $option->votes->count();
                $option->vote_percentage = $totalVotes > 0
                    ? round(($voteCount / $totalVotes) * 100, 2)
                    : 0;
            }
        }
        return $message;
    });

    return response()->json(['status' => true, 'data' => $messages], 200);
    }

    public function deleteForMe(Request $request)
    {
        $v = Validator::make($request->all(), [
            'message_id' => 'required|exists:community_messages,id',
            'user_id' => 'required|exists:users,id',
        ]);
        if ($v->fails()) {
            return response()->json(['status' => false, 'message' => $v->errors()->first()], 422);
        }


        // Add entry to deleted messages
        DeletedCommunityMessage::firstOrCreate([
        'user_id' => $request->user_id,
        'community_message_id' => $request->message_id,
         ], [
        'deleted_at' => now(),
       ]);

       return response()->json(['status' => true, 'message' => 'Message deleted for you.']);
     }
     
    public function deleteForAll(Request $request)
    {
        $v = Validator::make($request->all(), [
            'message_id' => 'required|exists:community_messages,id',
            'user_id' => 'required|exists:users,id',
        ]);
        if ($v->fails()) {
            return response()->json(['status' => false, 'message' => $v->errors()->first()], 422);
        }
        $message = CommunityMessage::findOrFail($request->message_id);
        if(isset($message->id) && $message->parent_id==$request->user_id){
            $message->delete();
          return response()->json(['status' => true, 'message' => 'Message deleted for all.']);
        }else{
            
        return response()->json(['status' => false, 'message' => 'You are not authorized to delete this message!']);
        }
     }
    
    public function votePoll(Request $request)
    {
        $v = Validator::make($request->all(), [
            'poll_id'   => 'required|exists:community_polls,id',
            'option_id' => 'required|exists:community_poll_options,id',
            'parent_id'   => 'required|exists:users,id',
        ]);
        if ($v->fails()) {
            return response()->json(['status' => false, 'message' => $v->errors()->first()], 422);
        }

        // Prevent double vote
        if (CommunityPollVote::where('poll_id', $request->poll_id)->where('parent_id', $request->parent_id)->exists()) {
            return response()->json(['status' => false, 'message' => 'Already voted'], 200);
        }

        $vote = CommunityPollVote::create([
            'poll_id'   => $request->poll_id,
            'option_id' => $request->option_id,
            'parent_id'   => $request->parent_id,
        ]);
        
    
    $poll = CommunityPoll::with(['options.votes'])->find($request->poll_id);
    
    $totalVotes = $poll->options->sum(function ($option) {
        return $option->votes->count();
    });
    
    foreach ($poll->options as $option) {
        $voteCount = $option->votes->count();
        $option->vote_percentage = $totalVotes > 0
            ? round(($voteCount / $totalVotes) * 100, 2)
            : 0;
    }

    return response()->json([
        'status'  => true,
        'message' => 'Vote recorded',
        'data'    => [
            'poll_id' => $poll->id,
            'options' => $poll->options,
            'total_votes' => $totalVotes
        ]
    ], 201);

  }
}