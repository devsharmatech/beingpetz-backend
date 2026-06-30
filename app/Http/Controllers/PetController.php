<?php

namespace App\Http\Controllers;

use App\Models\Pet;
use App\Models\WeightRecord;
use App\Models\FriendRequest;
use App\Models\MealRecord;
use App\Models\VaccineRecord;
use App\Models\Vaccine;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PetController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'breed' => 'required|string|max:255',
            'gender' => 'nullable|in:male,female,unknown',
            'dob' => 'nullable|date',
            'bio' => 'nullable|string',
            'avatar' => 'required|image',
        ], [
    'avatar.required' => 'Pet profile pic is missing.',
    'avatar.image' => 'Pet profile pic must be a valid image file (jpg, png, etc).',
]);

        
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 200);
        }

        $data = $validator->validated();
        $avtar_path="";
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $manager = new ImageManager(Driver::class);
            // $imagePath = parse_url($user->profile, PHP_URL_PATH);
            // $localImagePath = public_path($imagePath);
            $image = $manager->read($file);
            $filename = uniqid('avatar_') . '.' . $file->getClientOriginalExtension();
            $image->resize(400, 400)->save(public_path('uploads/pets/' . $filename));
            // if (File::exists($localImagePath)) {
            //     File::delete($localImagePath);
            // }
            $avtar_path = 'uploads/pets/' . $filename;
        }
        
        $pet = new Pet();
        $pet->user_id = $data['user_id'];
        $pet->name    = $data['name'];
        $pet->type    = $data['type'];
        $pet->breed   = $data['breed'];
        $pet->gender  = $data['gender'] ?? null;
        $pet->dob     = $data['dob'] ?? null;
        $pet->bio     = $data['bio'] ?? null;
        $pet->avatar  = $avtar_path ?? null;

        $pet->save();

        return response()->json([
            'status' => true,
            'message' => 'Pet created successfully',
            'data' => $pet,
        ], 201);
    }  
    
    public function updateMyPet(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:pets,id',
            'user_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'breed' => 'required|string|max:255',
            'gender' => 'nullable|in:male,female,unknown',
            'dob' => 'nullable|date',
            'bio' => 'nullable|string',
            'avatar' => 'nullable|image',
        ]);

        
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 200);
        }

        $data = $validator->validated();
        $pet = Pet::where('id',$request->id)->first();
        if(!$pet){
          return response()->json([
            'status' => false,
            'message' => 'Pet not found!'
          ], 200);  
        }
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $manager = new ImageManager(Driver::class);
            $imagePath = parse_url($pet->avatar, PHP_URL_PATH);
            $localImagePath = public_path($imagePath);
            $image = $manager->read($file);
            $filename = uniqid('avatar_') . '.' . $file->getClientOriginalExtension();
            $image->resize(400, 400)->save(public_path('uploads/pets/' . $filename));
            if (File::exists($localImagePath)) {
                File::delete($localImagePath);
            }
            $avtar_path = 'uploads/pets/' . $filename;
            $pet->avatar  = $avtar_path ?? null;
        }
        $pet->user_id = $data['user_id'];
        $pet->name    = $data['name'];
        $pet->type    = $data['type'];
        $pet->breed   = $data['breed'];
        $pet->gender  = $data['gender'] ?? null;
        $pet->dob     = $data['dob'] ?? null;
        $pet->bio     = $data['bio'] ?? null;
        $pet->save();

        return response()->json([
            'status' => true,
            'message' => 'Pet updated successfully',
            'data' => $pet,
        ], 201);
    }  
    
    public function getMyPets(Request $request){
         $validator = Validator::make($request->all(), [
             "user_id"=>"required|exists:users,id"
         ]);
         if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 200);
        }
        $pets=Pet::where('user_id',$request->user_id)->get();
        return response()->json([
            'status' => true,
            'message' => 'Your pets successfully fetched!',
            'data' => $pets,
        ], 201);
    }
    
    public function getDetailPet(Request $request){
         $vaccines = Vaccine::all()->keyBy('core_vaccine');
         $validator = Validator::make($request->all(), [
             "pet_id"=>"required|exists:pets,id"
         ]);
         if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 200);
        }
        $pet=Pet::where('id',$request->pet_id)->first();
        $parentId=$pet->user_id;
        
        $records = WeightRecord::where('pet_id', $request->pet_id)
                    ->orderBy('date','desc')->get();
        $friends = FriendRequest::where(function($query) use ($parentId) {
            $query->where('from_parent_id', $parentId)
                  ->orWhere('to_parent_id', $parentId);
            })->where('status', 'accepted')->count();      
        $friendsData = FriendRequest::where(function($query) use ($parentId) {
            $query->where('from_parent_id', $parentId)
                  ->orWhere('to_parent_id', $parentId);
            })->with(['fromParent', 'toParent'])->where('status', 'accepted')->get(); 
            
        $mealRecords = MealRecord::where('pet_id', $request->pet_id)->count();
        $mealRecordsData = MealRecord::where('pet_id', $request->pet_id)->get();
        
        $vaccineRecords = VaccineRecord::where('pet_id', $request->pet_id)
                    ->orderBy('date','desc')->count();
        $vaccineRecordsData = VaccineRecord::where('pet_id', $request->pet_id)
                    ->orderBy('date','desc')->get()->map(function ($item) use ($vaccines) {
            return [
                'id' => $item->id,
                'pet_id' => $item->pet_id,
                'vaccine_name' => $item->vaccine_name,
                'type' => $item->type,
                'next_vaccine' => $item->next_vaccine,
                'date' => $item->date,
                'reminder_date' => $item->reminder_date,
                'reminder_time' => $item->reminder_time,
                'image_path' => $item->image_path,
                'bg_color' => $item->bg_color,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
                'vaccine' => $vaccines->get($item->vaccine_name) ?? null,
            ];
        });
        
        return response()->json([
            'status' => true,
            'message' => 'Detail successfully fetched!',
            'data' => $pet,
            'records'=>$records,
            'friends'=>$friends,
            'vaccines'=>$vaccineRecords,
            'meals'=>$mealRecords,
            'friendsData'=>$friendsData,
            'mealsData'=>$mealRecordsData,
            'vaccineRecordsData'=>$vaccineRecordsData,
        ], 201);
    }
    
    
    public function deletePet(Request $request){
         $validator = Validator::make($request->all(), [
             "pet_id"=>"required|exists:pets,id",
             "parent_id"=>"required|exists:users,id"
         ]);
         if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 200);
        }
        $pet=Pet::where('id',$request->pet_id)->where('user_id',$request->parent_id)->first();
        if(!$pet){
            $pet->delete();
        }
        return response()->json([
            'status' => true,
            'message' => 'Pet Account deleted successfully!',
        ], 200);
    }
    
    public function togglePhoneVisibility(Request $request)
    {
    $validator = Validator::make($request->all(), [
        'user_id' => 'required|exists:users,id',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => $validator->errors()->first(),
        ], 422);
    }

    $user = User::find($request->user_id);
    $user->isPhoneShow = $user->isPhoneShow ? 0 : 1;
    $user->save();
    
    return response()->json([
        'status' => true,
        'message' => 'Phone visibility has been ' . ($user->isPhoneShow ? 'enabled' : 'disabled') . '.',
    ]);
   }
   
    public function toggleNameVisibility(Request $request)
    {
    $validator = Validator::make($request->all(), [
        'user_id' => 'required|exists:users,id',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => $validator->errors()->first(),
        ], 422);
    }

    $user = User::find($request->user_id);
    $user->isNameShow = $user->isNameShow ? 0 : 1;
    $user->save();
    
    return response()->json([
        'status' => true,
        'message' => 'Name visibility has been ' . ($user->isNameShow ? 'enabled' : 'disabled') . '.',
    ]);
   }
   
    public function showParentInfo($unid){
        $pet=Pet::where('unid',$unid)->first();
        
        if(!$pet){
            abort(404);
        }
        $parentInfo=User::where('id',$pet->user_id)->first();
        if(!$parentInfo){
            abort(404);
        }
        return view('show-parent-detail',compact('parentInfo','pet'));
    }



    // pets admin panel functions can be added here

    public function pet_list()
    {
        $pets = Pet::with('user')->latest()->get();
        return view('admin.pets.index', compact('pets'));
    }

    
    public function pet_save(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'gender' => 'required|in:male,female,unknown',
            'type' => 'required|string|max:255',
            'breed' => 'nullable|string|max:255',
            'dob' => 'nullable|date',
            'bio' => 'nullable|string',
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

        $petData = $request->except('avatar');

        if ($request->hasFile('avatar')) {
            $petData['avatar'] = $request->file('avatar')->store('pets', 'public');
        }

        Pet::create($petData);

        return redirect()->route('pets.list')->with('success', 'Pet added successfully.');
    }

    public function pet_update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'gender' => 'required|in:male,female,unknown',
            'type' => 'required|string|max:255',
            'breed' => 'nullable|string|max:255',
            'dob' => 'nullable|date',
            'bio' => 'nullable|string',
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

        $pet = Pet::findOrFail($id);
        $petData = $request->except('avatar');

        // Image update (keep old if not changed)
        if ($request->hasFile('avatar')) {
            if ($pet->avatar) {
                Storage::disk('public')->delete($pet->avatar);
            }
            $petData['avatar'] = $request->file('avatar')->store('pets', 'public');
        }

        $pet->update($petData);

        return redirect()->route('admin.pets.list')->with('success', 'Pet updated successfully.');
    }

    public function pet_delete($id)
    {
        $pet = Pet::findOrFail($id);
        
        if ($pet->avatar) {
            Storage::disk('public')->delete($pet->avatar);
        }

        $pet->delete();

        return redirect()->route('admin.pets.list')->with('success', 'Pet deleted successfully.');
    }

    public function export(Request $request)
    {
        try {
            $type = $request->get('type', 'all');
            
            if ($type === 'current_page') {
                // Get current page data (you might need to adjust this based on your DataTable implementation)
                $pets = Pet::with('user')->latest()->get();
            } else {
                // Get all data
                $pets = Pet::with('user')->latest()->get();
            }

            $fileName = 'pets_export_' . date('Y-m-d_H-i-s') . '.csv';

            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            ];

            $callback = function() use ($pets) {
                $file = fopen('php://output', 'w');
                
                // Add BOM for UTF-8
                fwrite($file, "\xEF\xBB\xBF");
                
                // Headers
                fputcsv($file, [
                    'ID',
                    'Pet Name',
                    'Type',
                    'Breed',
                    'Gender',
                    'Date of Birth',
                    'Age (Years)',
                    'Bio',
                    'Owner Name',
                    'Owner Phone',
                    'Created At',
                    'Updated At'
                ]);

                // Data
                foreach ($pets as $pet) {
                    fputcsv($file, [
                        $pet->id,
                        $pet->name,
                        $pet->type,
                        $pet->breed ?? 'N/A',
                        ucfirst($pet->gender),
                        $pet->dob ? Carbon::parse($pet->dob)->format('Y-m-d') : 'N/A',
                        $pet->dob ? Carbon::parse($pet->dob)->age : 'N/A',
                        $pet->bio ?? 'N/A',
                        $pet->user->first_name . ' ' . $pet->user->last_name,
                        $pet->user->phone ?? 'N/A',
                        $pet->created_at->format('Y-m-d H:i:s'),
                        $pet->updated_at->format('Y-m-d H:i:s')
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);

        } catch (\Exception $e) {
            Log::error('Pet Export Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to export data: ' . $e->getMessage());
        }
    }
}


