<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\PostHide;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Community;
use App\Models\Pet;
use App\Models\User;
use App\Models\CommunityMessage;
use Illuminate\Support\Str;

class ReportController extends Controller
{
    public function addReport(Request $request)
    {
    $request->validate([
        'report_by' => 'required|integer|exists:users,id',
        'type' => 'required|in:post,comment,pet,profile,community,message',
        'reason' => 'nullable|string',
        'post_id' => 'nullable|integer',
        'comment_id' => 'nullable|integer',
        'pet_id' => 'nullable|integer',
        'profile_id' => 'nullable|integer',
        'community_id' => 'nullable|integer',
        'message_id' => 'nullable|integer',
    ]);

    $userId = $request->input('report_by');
    $type = $request->input('type');

    $typeMap = [
        'post' => 'post_id',
        'comment' => 'comment_id',
        'pet' => 'pet_id',
        'profile' => 'profile_id',
        'community' => 'community_id',
        'message' => 'message_id',
    ];

    $field = $typeMap[$type] ?? null;

    if (!$field || !$request->has($field)) {
        return response()->json([
            'status' => false,
            'message' => "Missing or invalid field for report type '$type'."
        ], 422);
    }

    $targetId = $request->input($field);

    // Check for duplicate report
    $exists = Report::where('report_by', $userId)
        ->where('type', $type)
        ->where($field, $targetId)
        ->exists();

    if ($exists) {
        return response()->json([
            'status' => false,
            'message' => 'You have already reported this item.'
        ], 409);
    }

    // Create the report
    $report = new Report();
    $report->report_by = $userId;
    $report->type = $type;
    $report->$field = $targetId;
    $report->reason = $request->input('reason');
    $report->status = 'pending';
    $report->save();

    return response()->json([
        'status' => true,
        'message' => 'Report submitted successfully.',
        'data' => $report
    ], 201);
  }
  
    public function hideUnhidePost(Request $request)
    {
     $validate=Validator::make($request->all(),['user_id'=>['required','exists:users,id'],'post_id'=>['required','exists:posts,id']]);
     if($validate->fails()){
        return response()->json(['status' => false,'message' =>$validate->errors()->first(),], 200);
     }
     $hide=PostHide::where('post_id',$request->post_id)->where('user_id',$request->user_id)->first();
     if($hide){
        $hide->delete();
        return response()->json(['status' => true,'message' => 'Post unhide successfully.',], 200);   
     }
     $hide=new PostHide();
     $hide->user_id=$request->user_id;
     $hide->post_id=$request->post_id;
     $hide->save();
     
     return response()->json(['status' => true,'message' => 'Post hide successfully.',], 200);
  }

   //  admin function 

  public function index()
    {
        $reports = Report::with([
            'reported_user:id,first_name,last_name,email',
            'post:id,content,parent_id,deleted_at',
            'comment:id,comment,user_id,deleted_at',
            'community:id,name,deleted_at',
            'pet:id,name,user_id',
            'profile:id,first_name,last_name,email,deleted_at',
            'message:id,message_text,parent_id'
        ])->latest()->get();

        return view('admin.report.index', compact('reports'));
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,resolved,closed,approved,rejected' // Adjust based on your actual values
        ]);

        $report = Report::findOrFail($id);
        $report->update(['status' => $request->status]);

        return redirect()->back()->with('success', 'Report status updated successfully.');
    }

    public function destroy($id)
    {
        $report = Report::findOrFail($id);
        $report->delete();

        return redirect()->back()->with('success', 'Report deleted successfully.');
    }

    // New method to delete reported content without affecting feed
    public function deleteContent(Request $request, $id)
    {
        $report = Report::findOrFail($id);
        
        try {
            switch($report->type) {
                case 'post':
                    if ($report->post) {
                        // Soft delete the post
                        $report->post->delete();
                    }
                    break;
                    
                case 'comment':
                    if ($report->comment) {
                        // Soft delete the comment
                        $report->comment->delete();
                    }
                    break;
                    
                case 'community':
                    if ($report->community) {
                        // Soft delete the community
                        $report->community->delete();
                    }
                    break;
                    
                case 'pet':
                    if ($report->pet) {
                        // Soft delete the pet
                        $report->pet->delete();
                    }
                    break;
                    
                case 'profile':
                    if ($report->profile) {
                        // You might want to suspend or restrict the profile instead of deleting
                        $report->profile->update(['is_active' => false]);
                    }
                    break;
                    
                case 'message':
                    if ($report->message) {
                        // Soft delete the message
                        $report->message->delete();
                    }
                    break;
            }
            
            // Update report status to resolved
            $report->update(['status' => 'resolved']);
            
            return redirect()->back()->with('success', 'Content deleted successfully and report marked as resolved.');
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error deleting content: ' . $e->getMessage());
        }
    }

    // New method to get content preview
    public function getPreview($id)
    {
        $report = Report::with([
            'post',
            'comment',
            'community', 
            'pet',
            'profile',
            'message'
        ])->findOrFail($id);

        $previewData = [
            'type' => $report->type,
            'content' => null,
            'author' => null,
            'created_at' => null
        ];

        switch($report->type) {
            case 'post':
                if ($report->post) {
                    $previewData['content'] = $report->post->content;
                    $previewData['author'] = $report->post->user->name ?? 'Unknown';
                    $previewData['created_at'] = $report->post->created_at;
                }
                break;
                
            case 'comment':
                if ($report->comment) {
                    $previewData['content'] = $report->comment->comment;
                    $previewData['author'] = $report->comment->user->name ?? 'Unknown';
                    $previewData['created_at'] = $report->comment->created_at;
                }
                break;
                
            case 'community':
                if ($report->community) {
                    $previewData['content'] = $report->community->name;
                    $previewData['author'] = $report->community->user->name ?? 'Unknown';
                    $previewData['created_at'] = $report->community->created_at;
                }
                break;
                
            case 'pet':
                if ($report->pet) {
                    $previewData['content'] = $report->pet->name;
                    $previewData['author'] = $report->pet->user->name ?? 'Unknown';
                    $previewData['created_at'] = $report->pet->created_at;
                }
                break;
                
            case 'profile':
                if ($report->profile) {
                    $previewData['content'] = $report->profile->first_name . ' ' . $report->profile->last_name;
                    $previewData['author'] = $report->profile->email;
                    $previewData['created_at'] = $report->profile->created_at;
                }
                break;
                
            case 'message':
                if ($report->message) {
                    $previewData['content'] = $report->message->message_text;
                    $previewData['author'] = $report->message->user->name ?? 'Unknown';
                    $previewData['created_at'] = $report->message->created_at;
                }
                break;
        }

        return response()->json($previewData);
    }

    public function export()
{
    $reports = Report::with([
        'reported_user:id,first_name,last_name,email',
        'post:id,content',
        'comment:id,comment',
        'community:id,name',
        'pet:id,name',
        'profile:id,first_name,last_name,email',
        'message:id,message_text'
    ])->latest()->get();

    $filename = "reports_log_" . date('Y-m-d_H-i-s') . ".csv";
    
    $handle = fopen('php://output', 'w');
    fputcsv($handle, [
        'ID', 'Reported By', 'Type', 'Content', 'Reason', 
        'Status', 'Report Date', 'Action Taken'
    ]);

    foreach ($reports as $report) {
        fputcsv($handle, [
            $report->id,
            $report->reported_user ? $report->reported_user->email : 'N/A',
            $report->type,
            $this->getContentPreview($report),
            $report->reason,
            $report->status,
            $report->created_at->format('Y-m-d H:i:s'),
            $report->status === 'resolved' ? 'Content Deleted' : 'Pending'
        ]);
    }

    fclose($handle);

    return response()->streamDownload(function() use ($handle) {
        //
    }, $filename);
}

private function getContentPreview($report)
{
    switch($report->type) {
        case 'post': return Str::limit($report->post->content ?? 'N/A', 100);
        case 'comment': return Str::limit($report->comment->comment ?? 'N/A', 100);
        case 'community': return $report->community->name ?? 'N/A';
        case 'pet': return $report->pet->name ?? 'N/A';
        case 'profile': return $report->profile ? $report->profile->first_name . ' ' . $report->profile->last_name : 'N/A';
        case 'message': return Str::limit($report->message->message_text ?? 'N/A', 100);
        default: return 'N/A';
    }
}
}
