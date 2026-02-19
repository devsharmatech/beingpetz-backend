<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\LostFoundReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

use App\Models\Notification;
use App\Services\FirebaseService;


class LostFoundReportController extends Controller
{
     protected $fcm;

    public function __construct()
    {
        $this->fcm = new FirebaseService(env('FIREBASE_PROJECT_ID'),public_path(env('FIREBASE_CREDENTIALS_PATH')));
    }    
    
    public function storeOld(Request $request)
    {
        $reportType = $request->input('report_type');
        $messages = [];
if ($reportType === 'found') {
    $messages['occurred_at.required'] = 'Date of found field is missing, please update';
} elseif ($reportType === 'lost') {
    $messages['occurred_at.required'] = 'Date of lost field is missing, please update.';
}

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'phone' => 'required|string',
            'report_type' => 'required|in:lost,found',
            'pet_type' => 'required|string',
            'pet_name' => 'nullable|string',
            'pet_gender' => 'nullable|in:male,female,unknown',
            'breed' => 'nullable|string',
            'pet_dob' => 'nullable|date',
            'about_pet' => 'nullable|string',
            'location' => 'required|string',
            'occurred_at' => 'required|date',
            'images.*' => 'nullable|image',
            'isHealthy' => 'nullable',
            'isDewormed' => 'nullable',
            'isVaccinated' => 'nullable',
        ],$messages);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
            ], 200);
        }

        $data = $validator->validated();
        $report = new LostFoundReport();
        $report->user_id = $data['user_id'];
        $report->phone = $data['phone'];
        $report->report_type = $data['report_type'];
        $report->pet_type = $data['pet_type'];
        $report->pet_name = $data['pet_name'] ?? null;
        $report->pet_gender = $data['pet_gender'] ?? null;
        $report->breed = $data['breed'] ?? null;
        $report->pet_dob = $data['pet_dob'] ?? null;
        $report->about_pet = $data['about_pet'] ?? null;
        $report->location = $data['location'];
        $report->occurred_at = $data['occurred_at'];
        $report->isHealthy = $request->isHealthy ?? 0;
        $report->isDewormed = $request->isDewormed ?? 0;
        $report->isVaccinated = $request->isVaccinated ?? 0;

        $images = [];
        $manager = new ImageManager(new Driver());

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $filename = uniqid('report_') . '.' . $file->getClientOriginalExtension();
                $path = 'uploads/lostfound/' . $filename;
                $image = $manager->read($file);
                $image->save(public_path($path), 100);
                $images[] = $path;
            }
        }

        $report->images = $images;
        $report->status = 'open';
        $report->save();

        return response()->json([
            'status' => true,
            'message' => 'Report created successfully',
            'data' => $report,
        ], 201);
    }


public function storeOLd2(Request $request)
{
    $reportType = $request->input('report_type');
    $messages = [];

    if ($reportType === 'found') {
        $messages['occurred_at.required'] = 'Date of found field is missing, please update';
    } elseif ($reportType === 'lost') {
        $messages['occurred_at.required'] = 'Date of lost field is missing, please update.';
    }

    $validator = Validator::make($request->all(), [
        'user_id' => 'required|exists:users,id',
        'phone' => 'required|string',
        'report_type' => 'required|in:lost,found',
        'pet_type' => 'required|string',
        'pet_name' => 'nullable|string',
        'pet_gender' => 'nullable|in:male,female,unknown',
        'breed' => 'nullable|string',
        'pet_dob' => 'nullable|date',
        'about_pet' => 'nullable|string',
        'location' => 'required|string',
        'occurred_at' => 'required|date',
        'images.*' => 'nullable|image',
        'isHealthy' => 'nullable',
        'isDewormed' => 'nullable',
        'isVaccinated' => 'nullable',
    ], $messages);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => $validator->errors()->first(),
        ], 200);
    }

    $data = $validator->validated();

    $report = new LostFoundReport();
    $report->user_id = $data['user_id'];
    $report->phone = $data['phone'];
    $report->report_type = $data['report_type'];
    $report->pet_type = $data['pet_type'];
    $report->pet_name = $data['pet_name'] ?? null;
    $report->pet_gender = $data['pet_gender'] ?? null;
    $report->breed = $data['breed'] ?? null;
    $report->pet_dob = $data['pet_dob'] ?? null;
    $report->about_pet = $data['about_pet'] ?? null;
    $report->location = $data['location'];
    $report->occurred_at = $data['occurred_at'];
    $report->isHealthy = $request->isHealthy ?? 0;
    $report->isDewormed = $request->isDewormed ?? 0;
    $report->isVaccinated = $request->isVaccinated ?? 0;

    $images = [];
    $manager = new ImageManager(new Driver());

    if ($request->hasFile('images')) {
        foreach ($request->file('images') as $file) {
            $filename = uniqid('report_') . '.' . $file->getClientOriginalExtension();
            $path = 'uploads/lostfound/' . $filename;
            $image = $manager->read($file);
            $image->save(public_path($path), 100);
            $images[] = $path;
        }
    }

    $report->images = $images;
    $report->status = 'open';
    $report->save();

    // 🔔 Notify all accepted friends
    $friends = DB::table('friend_requests')
        ->where(function ($q) use ($report) {
            $q->where('from_parent_id', $report->user_id)
              ->orWhere('to_parent_id', $report->user_id);
        })
        ->where('status', 'accepted')
        ->get();

    foreach ($friends as $friend) {
        $friendId = $friend->from_parent_id == $report->user_id
            ? $friend->to_parent_id
            : $friend->from_parent_id;

        // Customize notification title and message
        $title = $report->report_type === 'found'
            ? "🐾 Pet Found Alert!"
            : "🐶 Pet Lost Alert!";

        $message = $report->report_type === 'found'
            ? "A pet has been found near {$report->location}. Check details to help reunite with the owner."
            : "A pet has been lost near {$report->location}. Please keep an eye out and help find it.";

        // Create notification record
        $notification = Notification::create([
            'user_id'       => $friendId,           // receiver
            'sender_id'     => $report->user_id,    // reporter
            'notifiable_id' => $report->id,         // report id
            'type'          => $report->report_type === 'found' ? 'found_report' : 'lost_report',
            'title'         => $title,
            'message'       => $message,
            'is_read'       => false,
        ]);

        // Send push notification (if FCM available)
        $receiver = User::find($friendId);
        if ($receiver && $receiver->device_token) {
            try {
                $this->fcm->sendNotification(
                    [$receiver->device_token],
                    [
                        'title' => $title,
                        'body'  => $message,
                        'sender_id' => (string) $report->user_id,
                        'type' => $report->report_type === 'found' ? 'found_report' : 'lost_report',
                        'notification_id' => $notification->id,
                        'notifiable_id' => $report->id,
                    ]
                );
            } catch (\Exception $e) {
                \Log::error("Lost/Found notification failed: " . $e->getMessage());
            }
        }
    }

    return response()->json([
        'status' => true,
        'message' => 'Report created successfully',
        'data' => $report,
    ], 201);
}


public function store(Request $request)
{
    $reportType = $request->input('report_type');
    $messages = [];

    if ($reportType === 'found') {
        $messages['occurred_at.required'] = 'Date of found field is missing, please update';
    } elseif ($reportType === 'lost') {
        $messages['occurred_at.required'] = 'Date of lost field is missing, please update.';
    }

    $validator = Validator::make($request->all(), [
        'user_id' => 'required|exists:users,id',
        'phone' => 'required|string',
        'report_type' => 'required|in:lost,found',
        'pet_type' => 'required|string',
        'pet_name' => 'nullable|string',
        'pet_gender' => 'nullable|in:male,female,unknown',
        'breed' => 'nullable|string',
        'pet_dob' => 'nullable|date',
        'about_pet' => 'nullable|string',
        'location' => 'required|string',
        'occurred_at' => 'required|date',
        'images.*' => 'nullable|image',
        'isHealthy' => 'nullable',
        'isDewormed' => 'nullable',
        'isVaccinated' => 'nullable',
    ], $messages);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => $validator->errors()->first(),
        ], 200);
    }

    $data = $validator->validated();

    $report = new LostFoundReport();
    $report->user_id = $data['user_id'];
    $report->phone = $data['phone'];
    $report->report_type = $data['report_type'];
    $report->pet_type = $data['pet_type'];
    $report->pet_name = $data['pet_name'] ?? null;
    $report->pet_gender = $data['pet_gender'] ?? null;
    $report->breed = $data['breed'] ?? null;
    $report->pet_dob = $data['pet_dob'] ?? null;
    $report->about_pet = $data['about_pet'] ?? null;
    $report->location = $data['location'];
    $report->occurred_at = $data['occurred_at'];
    $report->isHealthy = $request->isHealthy ?? 0;
    $report->isDewormed = $request->isDewormed ?? 0;
    $report->isVaccinated = $request->isVaccinated ?? 0;

    $images = [];
    $manager = new ImageManager(new Driver());

    if ($request->hasFile('images')) {
        foreach ($request->file('images') as $file) {
            $filename = uniqid('report_') . '.' . $file->getClientOriginalExtension();
            $path = 'uploads/lostfound/' . $filename;
            $image = $manager->read($file);
            $image->save(public_path($path), 100);
            $images[] = $path;
        }
    }

    $report->images = $images;
    $report->status = 'open';
    $report->save();

    // 🔔 Notify all users except the reporter
    $users = User::where('id', '!=', $report->user_id)->get();

    // Define title and message based on report type
    $title = $report->report_type === 'found'
        ? "🐾 Pet Found Alert!"
        : "🐶 Pet Lost Alert!";

    $message = $report->report_type === 'found'
        ? "A pet has been found near {$report->location}. Check details to help reunite with the owner."
        : "A pet has been lost near {$report->location}. Please keep an eye out and help find it.";

    foreach ($users as $user) {
        // Create notification record
        $notification = Notification::create([
            'user_id'       => $user->id,          // receiver
            'sender_id'     => $report->user_id,   // reporter
            'notifiable_id' => $report->id,        // report id
            'type'          => $report->report_type === 'found' ? 'found_report' : 'lost_report',
            'title'         => $title,
            'message'       => $message,
            'is_read'       => false,
        ]);

        // Send push notification (if FCM token exists)
        if ($user->device_token) {
            try {
                $this->fcm->sendNotification(
                    [$user->device_token],
                    [
                        'title' => $title,
                        'body'  => $message,
                        'sender_id' => (string) $report->user_id,
                        'type' => $report->report_type === 'found' ? 'found_report' : 'lost_report',
                        'notification_id' => (string) $notification->id,
                        'notifiable_id' => (string) $report->id,
                    ]
                );
            } catch (\Exception $e) {
                \Log::error("Lost/Found notification failed for user {$user->id}: " . $e->getMessage());
            }
        }
    }

    return response()->json([
        'status' => true,
        'message' => 'Report created successfully and notification sent to all users.',
        'data' => $report,
    ], 201);
}


    // Get All Reports
    public function getAllReports()
    {
        $reports = LostFoundReport::with('user')->latest()->paginate(10);

        return response()->json([
            'status' => true,
            'data' => $reports,
        ]);
    }

    // Get My Reports
    public function getMyReports(Request $request)
    {
        $validator = Validator::make($request->all(), [
        'user_id' => 'required|exists:users,id',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => $validator->errors()->first(),
            'errors' => $validator->errors(),
        ], 200);
    }

    $reports = LostFoundReport::where('user_id', $request->user_id)
                ->orderBy('created_at', 'desc')
                ->paginate(10); // paginate

    return response()->json([
        'status' => true,
        'message' => 'My Lost and Found Reports fetched successfully!',
        'data' => $reports,
    ]);
    }

    // Update Report
    public function update(Request $request)
    {
        

        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'user_id' => 'required|exists:users,id',
            'phone' => 'nullable|string',
            'report_type' => 'nullable|in:lost,found',
            'pet_type' => 'nullable|string',
            'pet_name' => 'nullable|string',
            'pet_gender' => 'nullable|in:male,female,unknown',
            'breed' => 'nullable|string',
            'pet_dob' => 'nullable|date',
            'about_pet' => 'nullable|string',
            'location' => 'nullable|string',
            'occurred_at' => 'nullable|date',
            'images.*' => 'nullable|image',
            'isHealthy' => 'nullable',
            'isDewormed' => 'nullable',
            'isVaccinated' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
            ], 200);
        }
        $report = LostFoundReport::where('id', $request->id)->where('user_id', $request->user_id)->first();
        if (!$report) {
            return response()->json(['status' => false, 'message' => 'Report not found'], 404);
        }
        $data = $validator->validated();

        $report->user_id = $data['user_id'];
        $report->phone = $data['phone'];
        $report->report_type = $data['report_type'];
        $report->pet_type = $data['pet_type'];
        $report->pet_gender = $data['pet_gender'] ?? null;
        $report->pet_name = $data['pet_name'] ?? null;
        $report->breed = $data['breed'] ?? null;
        $report->pet_dob = $data['pet_dob'] ?? null;
        $report->about_pet = $data['about_pet'] ?? null;
        $report->location = $data['location'];
        $report->occurred_at = $data['occurred_at'];
       $report->isHealthy = $request->isHealthy ?? 0;
        $report->isDewormed = $request->isDewormed ?? 0;
        $report->isVaccinated = $request->isVaccinated ?? 0;
        
        $manager = new ImageManager(new Driver());

        if ($request->hasFile('images')) {
            // Delete old images
            if (!empty($report->images)) {
                foreach ($report->images as $oldImage) {
                    $oldPath = public_path($oldImage);
                    if (File::exists($oldPath)) {
                        File::delete($oldPath);
                    }
                }
            }

            $images = [];
            foreach ($request->file('images') as $file) {
                $filename = uniqid('report_') . '.' . $file->getClientOriginalExtension();
                $path = 'uploads/lostfound/' . $filename;
                $image = $manager->read($file);
                $image->save(public_path($path), 100);
                $images[] = $path;
            }
            $report->images = $images;
        }

        $report->save();

        return response()->json([
            'status' => true,
            'message' => 'Report updated successfully',
            'data' => $report,
        ]);
    }

    // Delete Report
    public function destroy(Request $request)
    {
        $validator = Validator::make($request->all(), [
        'user_id' => 'required|exists:users,id',
        'report_id' => 'required|exists:lost_found_reports,id',
    ]);

        if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => $validator->errors()->first(),
            'errors' => $validator->errors(),
        ], 200);
    }
        $report = LostFoundReport::where('id', $request->report_id)->where('user_id',$request->user_id)->first();
        if (!$report) {
            return response()->json(['status' => false, 'message' => 'Report not found'], 404);
        }

        // Delete images
        if (!empty($report->images)) {
            foreach ($report->images ?? [] as $image) {
                $path = public_path($image);
                if (File::exists($path)) {
                    File::delete($path);
                }
            }
        }

        $report->delete();

        return response()->json([
            'status' => true,
            'message' => 'Report deleted successfully',
        ]);
    }
}
