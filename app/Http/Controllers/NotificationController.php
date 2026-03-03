<?php

namespace App\Http\Controllers;
use App\Models\Notification;
use App\Models\User;
use App\Models\Pet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Services\FirebaseService;
use App\Models\GroomingRecord;
use App\Models\GeneralRecord;
use App\Models\MealRecord;
use App\Models\VaccineRecord;
use App\Models\DewormingRecord;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\Community;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\CommunityMembership;
use App\Jobs\SendScheduledNotification;

class NotificationController extends Controller
{
    protected $fcm;

    public function __construct()
    {
        $projectId = config('services.firebase.project_id');
        $credentialsPath = public_path(config('services.firebase.credentials_path'));
        
        $this->fcm = new FirebaseService($projectId, $credentialsPath);
    } 
     public function updateToken2(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'token' => 'required|string'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }
        try {
            $user = User::findOrFail($request->user_id);
            $user->device_token = $request->token;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Device token updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update device token',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    public function updateToken(Request $request)
{
    $validator = Validator::make($request->all(), [
        'user_id' => 'required|exists:users,id',
        'token'   => 'required|string'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status'  => false,
            'message' => $validator->errors()->first(),
            'errors'  => $validator->errors(),
        ], 422);
    }

    try {
        User::where('device_token', $request->token)
            ->where('id', '!=', $request->user_id)
            ->update(['device_token' => null]);
            
        $user = User::findOrFail($request->user_id);
        $user->device_token = $request->token;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Device token updated successfully'
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to update device token',
            'error'   => $e->getMessage()
        ], 500);
    }
}

    
    public function notify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sender_id'     => 'required|exists:users,id',
            'receiver_id'   => 'required|exists:users,id',
            'message'       => 'required|string',
            'title'         => 'required|string',
            'type'          => 'required|string',   
            'notifiable_id' => 'nullable|integer',  
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first(),'errors' => $validator->errors(),], 422);
        }
        $sender   = User::findOrFail($request->sender_id);
        $receiver = User::findOrFail($request->receiver_id);

        
        $notification = Notification::create([
            'user_id'       => $receiver->id, 
            'sender_id'     => $sender->id,          
            'notifiable_id' => $request->notifiable_id,
            'type'          => $request->type,
            'title'         => $request->title,
            'message'       => $request->message,
            'is_read'       => false,
        ]);

        // If no device token → just return
        if (!$receiver->device_token) {
            return response()->json([
                'success' => true,
                'message' => 'Receiver unavailable, notification stored',
            ]);
        }

        try {
            $this->fcm->sendNotification(
                [$receiver->device_token],
                [
                    'title'          => $request->title,
                    'body'           => $request->message,
                    'sender_id'      => (string) $sender->id,
                    'type'           => $request->type,
                    'notifiable_id'  => (string) $request->notifiable_id
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Notification sent',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    
    public function getNotifications(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $notifications = Notification::where('user_id', $request->user_id)
            ->with('sender','user')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Notifications fetched successfully.',
            'data' => $notifications,
        ]);
    }

    public function markNotificationRead(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:notifications,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $notification = Notification::find($request->id);
        $notification->update(['is_read' => 1]);

        return response()->json([
            'status' => true,
            'message' => 'Notification marked as read.',
        ]);
    }

    public function deleteNotification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:notifications,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        Notification::destroy($request->id);

        return response()->json([
            'status' => true,
            'message' => 'Notification deleted.',
        ]);
    }

    public function clearAllNotifications(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $deletedCount = Notification::where('user_id', $request->user_id)->delete();

        return response()->json([
            'status' => true,
            'message' => 'All notifications cleared for the user.',
            'deleted_count' => $deletedCount,
        ]);
    }
    
    
    
    public function checkReminders()
{
    $now = Carbon::now('Asia/Kolkata');
    $today = $now->toDateString();
    $current = $now->format('Y-m-d H:i');

    // Grooming Records
    $this->processRecords(
        GroomingRecord::where('reminder_date', $today)->get(),
        'grooming',
        fn($rec) => "Your pet has a grooming reminder: {$rec->grooming_type}",
        $current
    );
    
    
    // General Records
    $this->processRecords(
        GeneralRecord::where('date',$today)->get(),
        'general',
        fn($rec) => "Reminder: {$rec->notes}",
        $current
    );

    // Meal Records
    $this->processRecords(
        MealRecord::where('reminder_date', $today)->get(),
        'meal',
        fn($rec) => "Meal time reminder for your pet!",
        $current
    );
    
    // Vaccine Records
    $this->processRecords(
        VaccineRecord::where('reminder_date', $today)->get(),
        'vaccine',
        fn($rec) => "Vaccination time reminder for your pet!",
        $current
    );

    // Deworming Records
    $this->processRecords(
        DewormingRecord::where('reminder_date', $today)->get(),
        'deworming',
        fn($rec) => "Your pet has a deworming reminder: {$rec->deworming_type}",
        $current
    );

    return response()->json(['success' => true, 'message' => 'Reminders checked.']);
}

private function processRecords($records, $type, $messageCallback, $now)
{
    foreach ($records as $record) {
        // build target datetime
        if (isset($record->reminder_date) && isset($record->reminder_time)) {
            $target = Carbon::parse($record->reminder_date . ' ' . $record->reminder_time, 'Asia/Kolkata');
        } elseif (isset($record->date) && isset($record->time)) {
            $target = Carbon::parse($record->date . ' ' . $record->time, 'Asia/Kolkata');
        } else {
            continue;
        }

        // pre-alert times
        $alertTimes = [
            '8h'  => $target->copy()->subHours(8)->format('Y-m-d H:i'),
            '4h'  => $target->copy()->subHours(4)->format('Y-m-d H:i'),
            '1h'  => $target->copy()->subHour()->format('Y-m-d H:i'),
            '30m' => $target->copy()->subMinutes(30)->format('Y-m-d H:i'),
            'now' =>  $target->copy()->format('Y-m-d H:i'),
        ];
       
        foreach ($alertTimes as $key => $alertAt) {
            if ($now == $alertAt) {
                // dd($alertTimes,$now);
                $this->sendNotificationForRecord($record, $type, $messageCallback, $key);
            }
        }
    }
}

private function sendNotificationForRecord($record, $type, $messageCallback, $alertKey)
{
    $pet = Pet::find($record->pet_id);
    if (!$pet || !$pet->user_id) {
        return;
    }

    $recipient = User::find($pet->user_id);
    $sender_id = 100; // system sender

    $title = ucfirst($type) . " Reminder";

    // Handle "before" label
    $beforeText = $alertKey === 'now' ? ' now ' : " ({$alertKey} before)";

    // Custom messages including pet name
    switch ($type) {
        case 'grooming':
            $message = "{$pet->name} has a grooming session ({$record->grooming_type}){$beforeText}";
            break;
        case 'vaccine':
            $message = "{$pet->name} is due for a vaccination ({$record->vaccine_name}){$beforeText}";
            break;
        case 'general':
            $message = "{$pet->name} reminder: {$record->notes}{$beforeText}";
            break;
        case 'meal':
            $message = "It’s meal time for {$pet->name}!{$beforeText}";
            break;
        case 'deworming':
            $message = "{$pet->name} needs deworming ({$record->deworming_type}){$beforeText}";
            break;
        default:
            $message = $messageCallback($record) . $beforeText;
    }

    // Store notification
    $notification = Notification::create([
        'user_id'       => $recipient->id,
        'sender_id'     => $sender_id,
        'notifiable_id' => $record->id,
        'type'          => $type,
        'title'         => $title,
        'message'       => $message,
        'is_read'       => false,
    ]);

    // Send push
    if ($recipient && $recipient->device_token) {
        try {
            $this->fcm->sendNotification(
                [$recipient->device_token],
                [
                    'title' => $title,
                    'body'  => $message,
                    'sender_id' => (string) $sender_id,
                    'type' => $type,
                    'notification_id' => (string) $notification->id,
                    'notifiable_id'   => (string) $record->id,
                ]
            );
        } catch (\Exception $e) {
            \Log::error("{$type} {$alertKey} notification failed: " . $e->getMessage());
        }
    }
}


private function sendNotificationForRecord__($record, $type, $messageCallback, $alertKey)
{
    $pet = Pet::find($record->pet_id);
    if (!$pet || !$pet->user_id) {
        return;
    }

    $recipient = User::find($pet->user_id);
    $sender_id = 100; // system sender

    $title = ucfirst($type) . " Reminder";

    // Custom messages including pet name
    switch ($type) {
        case 'grooming':
            $message = "{$pet->name} has a grooming session ({$record->grooming_type}) ($alertKey before)";
            break;
        case 'vaccine':
             $message = "{$pet->name} is due for a vaccination ({$record->vaccine_name}) {$alertKey} before.";
            break;
        case 'general':
            $message = "{$pet->name} reminder: {$record->notes} ($alertKey before)";
            break;
        case 'meal':
            $message = "It’s meal time for {$pet->name}! ($alertKey before)";
            break;
        case 'deworming':
            $message = "{$pet->name} needs deworming ({$record->deworming_type}) ($alertKey before)";
            break;
        default:
            $message = $messageCallback($record) . " ($alertKey before)";
    }

    // Store notification
    $notification = Notification::create([
        'user_id'       => $recipient->id,
        'sender_id'     => $sender_id,
        'notifiable_id' => $record->id,
        'type'          => $type,
        'title'         => $title,
        'message'       => $message,
        'is_read'       => false,
    ]);

    // Send push
    if ($recipient && $recipient->device_token) {
        try {
            $this->fcm->sendNotification(
                [$recipient->device_token],
                [
                    'title' => $title,
                    'body'  => $message,
                    'sender_id' => (string) $sender_id,
                    'type' => $type,
                    'notification_id' => (string) $notification->id,
                    'notifiable_id'   => (string) $record->id,
                ]
            );
        } catch (\Exception $e) {
            \Log::error("{$type} {$alertKey} notification failed: " . $e->getMessage());
        }
    }
}


public function cleanup()
{
        $communities = Community::all();
        foreach ($communities as $community) {
            $members = CommunityMembership::where('community_id', $community->id)->get();
            $count   = $members->count();
             

            if ($count == 0) {
                
                $community->delete();
            } elseif ($count === 1) {
                $member = $members->first();
                if ($member->role !== 'super_admin') {
                    $member->update(['role' => 'super_admin']);
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Community cleanup completed successfully',
        ]);
    }

    // admin dashboard function

    public function index()
    {
        $notifications = Notification::with(['sender'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.notifications.index', compact('notifications'));
    }

    public function create()
    {
        return view('admin.notifications.create', [
            'title' => 'Create Push Notification'
        ]);
    }

    public function edit(Notification $notification)
    {
        return view('admin.notifications.create', [
            'title' => 'Edit Push Notification',
            'notification' => $notification
        ]);
    }

    public function show(Notification $notification)
    {
        return view('admin.notifications.show', compact('notification'));
    } 
    // public function store(Request $request)
    // {
    //     $validated = $request->validate([
    //         'title' => 'required|string|max:255',
    //         'message' => 'required|string',
    //         'type' => 'required|in:info,alert,promo,update',
    //         'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
    //         'audience_type' => 'required|in:all,custom',
    //         'locations' => 'required_if:audience_type,custom|array',
    //         'status' => 'boolean',
    //         'enable_schedule' => 'boolean',
    //         'schedule_time' => 'nullable|date|after:now',
    //         'remove_image' => 'boolean'
    //     ]);

    //     // Handle audience data
    //     $audience = null;
    //     if ($request->audience_type === 'custom' && $request->has('locations')) {
    //         $audience = ['locations' => $request->locations];
    //     }

    //     // Handle schedule time
    //     $scheduleTime = null;
    //     if ($request->enable_schedule && $request->schedule_time) {
    //         $scheduleTime = $request->schedule_time;
    //     }

    //     // Handle image upload
    //     $imagePath = null;
    //     if ($request->hasFile('image')) {
    //         $imagePath = $request->file('image')->store('notifications', 'public');
    //     }

    //     // Create notification
    //     Notification::create([
    //         'title' => $validated['title'],
    //         'message' => $validated['message'],
    //         'type' => $validated['type'],
    //         'image' => $imagePath,
    //         'audience' => $audience,
    //         'status' => $validated['status'] ?? false,
    //         'schedule_time' => $scheduleTime, // Add this
    //         'sender_id' => auth()->id(),
    //     ]);

    //     return redirect()->route('admin.notifications.index')
    //         ->with('success', 'Notification created successfully!');
    // }

    // Store notification (admin panel)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'          => 'required|string|max:255',
            'message'        => 'required|string',
            'type'           => 'required|in:info,alert,promo,update',
            'image'          => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'audience_type'  => 'required|in:all,custom',
            'locations'      => 'required_if:audience_type,custom|array',
            'status'         => 'boolean',
            'enable_schedule'=> 'boolean',
            'schedule_time'  => 'nullable|date|after:now',
            'remove_image'   => 'boolean'
        ]);

        $audience = null;
        if ($request->audience_type === 'custom' && $request->has('locations')) {
            $audience = ['locations' => $request->locations];
        }

        $scheduleTime = null;
        if ($request->enable_schedule && $request->schedule_time) {
            $scheduleTime = $request->schedule_time;
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('notifications', 'public');
        }

        $notification = Notification::create([
            'user_id'       => $validated['audience_type'] === 'single' ? ($request->user_id ?? auth()->id()) : null,
            'title'         => $validated['title'],
            'message'       => $validated['message'],
            'type'          => $validated['type'],
            'image'         => $imagePath,
            'audience'      => array_merge($audience ?? [], ['type' => $validated['audience_type']]),
            'status'        => $validated['status'] ?? true,
            'scheduled_at'  => $scheduleTime,
            'sender_id'     => auth()->id(),
            'is_sent'       => false,
        ]);

        // If scheduled → dispatch job
        if ($scheduleTime) {
            SendScheduledNotification::dispatch($notification)
                ->delay(Carbon::parse($scheduleTime));
        } else {
            // Send immediately
            $this->sendNotification($notification);
        }

        return redirect()->route('admin.notifications.index')
            ->with('success', 'Notification created successfully!');
    }


    public function update(Request $request, Notification $notification)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'required|in:info,alert,promo,update',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'audience_type' => 'required|in:all,custom',
            'locations' => 'required_if:audience_type,custom|array',
            'status' => 'boolean',
            'enable_schedule' => 'boolean',
            'schedule_time' => 'nullable|date|after:now',
            'remove_image' => 'boolean'
        ]);

        // Handle audience data
        $audience = null;
        if ($request->audience_type === 'custom' && $request->has('locations')) {
            $audience = ['locations' => $request->locations];
        }

        // Handle schedule time
        $scheduleTime = null;
        if ($request->enable_schedule && $request->schedule_time) {
            $scheduleTime = $request->schedule_time;
        }

        // Handle image
        $imagePath = $notification->image;
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($notification->image) {
                Storage::disk('public')->delete($notification->image);
            }
            $imagePath = $request->file('image')->store('notifications', 'public');
        } elseif ($request->remove_image) {
            if ($notification->image) {
                Storage::disk('public')->delete($notification->image);
            }
            $imagePath = null;
        }

        $notification->update([
            'title'        => $validated['title'],
            'message'      => $validated['message'],
            'type'         => $validated['type'],
            'image'        => $imagePath,
            'audience'     => array_merge($audience ?? [], ['type' => $validated['audience_type']]),
            'status'       => $validated['status'] ?? true,
            'scheduled_at' => $scheduleTime,
        ]);

        // If not sent yet and scheduled/immediate, handle accordingly
        if (!$notification->is_sent) {
            if ($scheduleTime) {
                SendScheduledNotification::dispatch($notification)
                    ->delay(Carbon::parse($scheduleTime));
            } else {
                $this->sendNotification($notification);
            }
        }

        return redirect()->route('admin.notifications.index')
            ->with('success', 'Notification updated successfully!');
    }

    public function destroy(Notification $notification)
    {
        // Delete image if exists
        if ($notification->image) {
            Storage::disk('public')->delete($notification->image);
        }

        $notification->delete();

        return redirect()->route('admin.notifications.index')
            ->with('success', 'Notification deleted successfully!');
    }
    public function toggleStatus(Notification $notification)
    {
        $notification->update([
            'status' => !$notification->status
        ]);

        return response()->json([
            'success' => true,
            'status' => $notification->status
        ]);
    }

    private function sendNotification(Notification $notification)
    {
        // Get target users based on audience
        $users = $this->getTargetUsers($notification);

        Log::info('Sending notification to users', ['count' => $users->count()]);

        // Send push notification to each user
        foreach ($users as $user) {
            $this->sendPushNotification($user, $notification);
        }

        $notification->update(['is_sent' => true, 'status' => true]);
    }

    private function getTargetUsers(Notification $notification)
    {
        $query = User::query()->whereNotNull('device_token');

        if ($notification->audience) {
            $audience = $notification->audience;
            $type = $audience['type'] ?? 'all';

            if ($type === 'single' && $notification->user_id) {
                $query->where('id', $notification->user_id);
            } elseif ($type === 'custom') {
                if (!empty($audience['locations'])) {
                    $query->whereIn('location', $audience['locations']);
                }

                if (!empty($audience['user_status'])) {
                    $query->where('status', $audience['user_status']);
                }

                if (!empty($audience['user_groups'])) {
                    $query->whereHas('groups', function ($q) use ($audience) {
                        $q->whereIn('name', $audience['user_groups']);
                    });
                }
            }
            // If type is 'all', no extra filters needed beyond device_token
        }

        return $query->get();
    }

    private function sendPushNotification(User $user, Notification $notification)
    {
        if (!$user->device_token) {
            return;
        }

        try {
            $this->fcm->sendNotification(
                [$user->device_token],
                [
                    'title'          => $notification->title,
                    'body'           => $notification->message,
                    'sender_id'      => (string) $notification->sender_id,
                    'type'           => $notification->type,
                    'notification_id'=> (string) $notification->id,
                    'image'          => $notification->image ? asset('storage/' . $notification->image) : null,
                ]
            );
        } catch (\Exception $e) {
            Log::error('Firebase send error: ' . $e->getMessage());
        }
    }

   public function bulkUpload(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:5120'
        ]);

        try {
            $file = $request->file('csv_file');
            $csvData = array_map('str_getcsv', file($file));
            
            // Remove header row
            array_shift($csvData);
            
            $imported = 0;
            $errors = [];
            
            foreach ($csvData as $index => $row) {
                try {
                    if (count($row) >= 3 && !empty(trim($row[0]))) {
                        
                        // Validate required fields
                        $title = trim($row[0]);
                        $message = trim($row[1]);
                        $type = trim($row[2] ?? 'info');
                        
                        if (empty($title) || empty($message)) {
                            $errors[] = "Row " . ($index + 1) . ": Title and Message are required";
                            continue;
                        }
                        
                        // Validate type
                        $validTypes = ['info', 'alert', 'promo', 'update'];
                        if (!in_array($type, $validTypes)) {
                            $type = 'info'; // Default type
                        }
                        
                        // Handle image URL if provided
                        $imagePath = null;
                        if (!empty($row[3])) {
                            $imageUrl = trim($row[3]);
                            // You can add image download logic here if needed
                        }
                        
                        Notification::create([
                            'title' => $title,
                            'message' => $message,
                            'type' => $type,
                            'image' => $imagePath,
                            'status' => true,
                            'sender_id' => auth()->id() // Correct field name
                        ]);
                        $imported++;
                    }
                } catch (\Exception $e) {
                    $errors[] = "Row " . ($index + 1) . ": " . $e->getMessage();
                }
            }
            
            $message = "Successfully imported {$imported} notifications!";
            if (!empty($errors)) {
                $message .= " Errors: " . implode(', ', array_slice($errors, 0, 3));
            }
            
            return redirect()->back()->with('success', $message);
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error importing notifications: ' . $e->getMessage());
        }
    }

    

    // public function schedule(Request $request, Notification $notification)
    // {
    //     $request->validate([
    //         'scheduled_at' => 'required|date|after:now'
    //     ]);

    //     $notification->update([
    //         'scheduled_at' => $request->scheduled_at,
    //         'status' => false, // Initially inactive
    //         'is_sent' => false // Not sent yet
    //     ]);

    //     return redirect()->back()->with('success', 'Notification scheduled successfully for ' . $request->scheduled_at);
    // }

    // Schedule notification
    public function schedule(Request $request, Notification $notification)
    {
        $request->validate([
            'scheduled_at' => 'required|date|after:now'
        ]);

        $notification->update([
            'scheduled_at' => $request->scheduled_at,
            'status'       => true,
            'is_sent'      => false,
        ]);

        SendScheduledNotification::dispatch($notification)
            ->delay(now()->parse($request->scheduled_at));

        return redirect()->back()
            ->with('success', 'Notification scheduled successfully for ' . $request->scheduled_at);
    }


}
