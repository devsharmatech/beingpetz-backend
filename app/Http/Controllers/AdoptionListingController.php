<?php

namespace App\Http\Controllers;

use App\Models\AdoptionListing;
use App\Models\AdoptionListingImage;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\Notification;
use App\Services\FirebaseService;



class AdoptionListingController extends Controller
{
    protected $fcm;

    public function __construct()
    {
        $this->fcm = new FirebaseService(env('FIREBASE_PROJECT_ID'),public_path(env('FIREBASE_CREDENTIALS_PATH')));
    }   
   public function createOld(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'pet_name' => 'required|string|max:255',
            'pet_type' => 'required|string|max:255',
            'breed' => 'nullable|string',
            'gender' => 'required|in:male,female',
            'dob' => 'nullable|date',
            'description' => 'nullable|string',
            'about_pet' => 'nullable|string',
            'is_healthy' => 'nullable|boolean',
            'is_dewormed' => 'nullable|boolean',
            'is_neutered' => 'nullable|boolean',
            'vaccination_done' => 'nullable|boolean',
            'location' => 'nullable|string',
            'latitude' => 'nullable|string',
            'longitude' => 'nullable|string',
            'contact_phone' => 'nullable|string',
            'contact_email' => 'nullable|email',
            'featured_image' => 'nullable|image',
            'gallery_images.*' => 'nullable|image',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 200);
        }

        $data = $validator->validated();

        $listing = new AdoptionListing();
        $listing->user_id=$request->user_id;
        $listing->pet_name=$request->pet_name;
        $listing->pet_type=$request->pet_type;
        $listing->breed=$request->breed;
        $listing->gender=$request->gender;
        $listing->dob=$request->dob;
        $listing->description=$request->description;
        $listing->about_pet=$request->about_pet;
        $listing->is_healthy=$request->is_healthy;
        $listing->is_dewormed=$request->is_dewormed;
        $listing->is_neutered=$request->is_neutered;
        $listing->vaccination_done=$request->vaccination_done;
        $listing->location=$request->location;
        $listing->latitude=$request->latitude;
        $listing->longitude=$request->longitude;
        $listing->contact_phone=$request->contact_phone;
        $listing->contact_email=$request->contact_email;
        $listing->slug = Str::slug($data['pet_name']) . '-' . uniqid();
        $listing->status = 'available';
        $listing->published_at = now();

        $manager = new ImageManager(new Driver());

        if ($request->hasFile('featured_image')) {
            $file = $request->file('featured_image');
            $image = $manager->read($file);
            $filename = uniqid('adopt_featured_') . '.' . $file->getClientOriginalExtension();
            $path = 'uploads/adoptions/' . $filename;
            $image->save(public_path($path), 100);
            $listing->featured_image = $path;
        }
        
        $listing->save();
        // Handle gallery images
        if ($request->hasFile('gallery_images')) {
            foreach ($request->file('gallery_images') as $index => $file) {
                $filename = uniqid('adopt_gallery_') . '.' . $file->getClientOriginalExtension();
                $path = $file->move(public_path('uploads/adoptions/gallery'), $filename);

                AdoptionListingImage::create([
                    'listing_id' => $listing->id,
                    'image_path' => 'uploads/adoptions/gallery/' . $filename,
                    'display_order' => $index,
                ]);
            }
        }

        return response()->json(['status' => true, 'message' => 'Adoption Listing Created Successfully!', 'data' => $listing]);
    }

public function create(Request $request)
{
    $validator = Validator::make($request->all(), [
        'user_id' => 'required|exists:users,id',
        'pet_name' => 'required|string|max:255',
        'pet_type' => 'required|string|max:255',
        'breed' => 'nullable|string',
        'gender' => 'required|in:male,female',
        'dob' => 'nullable|date',
        'description' => 'nullable|string',
        'about_pet' => 'nullable|string',
        'is_healthy' => 'nullable|boolean',
        'is_dewormed' => 'nullable|boolean',
        'is_neutered' => 'nullable|boolean',
        'vaccination_done' => 'nullable|boolean',
        'location' => 'nullable|string',
        'latitude' => 'nullable|string',
        'longitude' => 'nullable|string',
        'contact_phone' => 'nullable|string',
        'contact_email' => 'nullable|email',
        'featured_image' => 'nullable|image',
        'gallery_images.*' => 'nullable|image',
    ]);

    if ($validator->fails()) {
        return response()->json(['status' => false, 'message' => $validator->errors()->first()], 200);
    }

    $data = $validator->validated();

    $listing = new AdoptionListing();
    $listing->user_id = $request->user_id;
    $listing->pet_name = $request->pet_name;
    $listing->pet_type = $request->pet_type;
    $listing->breed = $request->breed;
    $listing->gender = $request->gender;
    $listing->dob = $request->dob;
    $listing->description = $request->description;
    $listing->about_pet = $request->about_pet;
    $listing->is_healthy = $request->is_healthy ?? 0;
    $listing->is_dewormed = $request->is_dewormed ?? 0;
    $listing->is_neutered = $request->is_neutered ?? 0;
    $listing->vaccination_done = $request->vaccination_done ?? 0;
    $listing->location = $request->location;
    $listing->latitude = $request->latitude;
    $listing->longitude = $request->longitude;
    $listing->contact_phone = $request->contact_phone;
    $listing->contact_email = $request->contact_email;
    $listing->slug = Str::slug($data['pet_name']) . '-' . uniqid();
    $listing->status = 'available';
    $listing->published_at = now();

    $manager = new ImageManager(new Driver());

    if ($request->hasFile('featured_image')) {
        $file = $request->file('featured_image');
        $image = $manager->read($file);
        $filename = uniqid('adopt_featured_') . '.' . $file->getClientOriginalExtension();
        $path = 'uploads/adoptions/' . $filename;
        $image->save(public_path($path), 100);
        $listing->featured_image = $path;
    }

    $listing->save();

    // Handle gallery images
    if ($request->hasFile('gallery_images')) {
        foreach ($request->file('gallery_images') as $index => $file) {
            $filename = uniqid('adopt_gallery_') . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/adoptions/gallery'), $filename);

            AdoptionListingImage::create([
                'listing_id' => $listing->id,
                'image_path' => 'uploads/adoptions/gallery/' . $filename,
                'display_order' => $index,
            ]);
        }
    }

    // 🔔 Send notification to all users except the creator
    $users = User::where('id', '!=', $listing->user_id)->get();

    $title = "🐶 New Pet Available for Adoption!";
    $message = "{$listing->pet_name} ({$listing->pet_type}) is now available for adoption near {$listing->location}.";

    foreach ($users as $user) {
        // Create notification record
        $notification = Notification::create([
            'user_id'       => $user->id,          // receiver
            'sender_id'     => $listing->user_id,  // creator
            'notifiable_id' => $listing->id,       // adoption listing id
            'type'          => 'adoption_listing',
            'title'         => $title,
            'message'       => $message,
            'is_read'       => false,
        ]);

        // Push notification via FCM (if device token exists)
        if ($user->device_token) {
            try {
                $this->fcm->sendNotification(
                    [$user->device_token],
                    [
                        'title' => $title,
                        'body'  => $message,
                        'sender_id' => (string) $listing->user_id,
                        'type' => 'adoption_listing',
                        'notification_id' => (string) $notification->id,
                        'notifiable_id' => (string) $listing->id,
                    ]
                );
            } catch (\Exception $e) {
                \Log::error("Adoption notification failed for user {$user->id}: " . $e->getMessage());
            }
        }
    }

    return response()->json([
        'status' => true,
        'message' => 'Adoption Listing Created Successfully and Notification Sent!',
        'data' => $listing,
    ], 201);
}

public function editAdoption(Request $request)
{
    $validator = Validator::make($request->all(), [
        'user_id' => 'required|exists:users,id',
        'listing_id' => 'required|exists:adoption_listings,id',
        'pet_name' => 'required|string|max:255',
        'pet_type' => 'required|string|max:255',
        'breed' => 'nullable|string',
        'gender' => 'required|in:male,female',
        'dob' => 'nullable|date',
        'description' => 'nullable|string',
        'about_pet' => 'nullable|string',
        'is_healthy' => 'nullable|boolean',
        'is_dewormed' => 'nullable|boolean',
        'is_neutered' => 'nullable|boolean',
        'vaccination_done' => 'nullable|boolean',
        'location' => 'nullable|string',
        'latitude' => 'nullable|string',
        'longitude' => 'nullable|string',
        'contact_phone' => 'nullable|string',
        'contact_email' => 'nullable|email',
        'featured_image' => 'nullable|image',
        'gallery_images.*' => 'nullable|image',
        'delete_gallery_images' => 'nullable|array',
        'delete_gallery_images.*' => 'exists:adoption_listing_images,id',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => $validator->errors()->first()
        ], 200);
    }

    DB::beginTransaction();

    try {

        $listing = AdoptionListing::where('id', $request->listing_id)
            ->where('user_id', $request->user_id)
            ->first();

        if (!$listing) {
            return response()->json([
                'status' => false,
                'message' => 'Listing not found.'
            ], 404);
        }

        $listing->pet_name = $request->pet_name;
        $listing->pet_type = $request->pet_type;
        $listing->breed = $request->breed;
        $listing->gender = $request->gender;
        $listing->dob = $request->dob;
        $listing->description = $request->description;
        $listing->about_pet = $request->about_pet;
        $listing->is_healthy = $request->is_healthy ?? 0;
        $listing->is_dewormed = $request->is_dewormed ?? 0;
        $listing->is_neutered = $request->is_neutered ?? 0;
        $listing->vaccination_done = $request->vaccination_done ?? 0;
        $listing->location = $request->location;
        $listing->latitude = $request->latitude;
        $listing->longitude = $request->longitude;
        $listing->contact_phone = $request->contact_phone;
        $listing->contact_email = $request->contact_email;

        $manager = new ImageManager(new Driver());

        // Replace Featured Image
        if ($request->hasFile('featured_image')) {

            if ($listing->featured_image && file_exists(public_path($listing->featured_image))) {
                unlink(public_path($listing->featured_image));
            }

            $file = $request->file('featured_image');
            $image = $manager->read($file);

            $filename = uniqid('adopt_featured_') . '.' . $file->getClientOriginalExtension();
            $path = 'uploads/adoptions/' . $filename;

            $image->save(public_path($path), 100);

            $listing->featured_image = $path;
        }

        $listing->save();

        // Delete Selected Gallery Images
        if ($request->filled('delete_gallery_images')) {

            $images = AdoptionListingImage::where('listing_id', $listing->id)
                ->whereIn('id', $request->delete_gallery_images)
                ->get();

            foreach ($images as $image) {

                if ($image->image_path && file_exists(public_path($image->image_path))) {
                    unlink(public_path($image->image_path));
                }

                $image->delete();
            }
        }

        // Upload New Gallery Images
        if ($request->hasFile('gallery_images')) {

            $order = AdoptionListingImage::where('listing_id', $listing->id)->count();

            foreach ($request->file('gallery_images') as $file) {

                $filename = uniqid('adopt_gallery_') . '.' . $file->getClientOriginalExtension();

                $file->move(public_path('uploads/adoptions/gallery'), $filename);

                AdoptionListingImage::create([
                    'listing_id' => $listing->id,
                    'image_path' => 'uploads/adoptions/gallery/' . $filename,
                    'display_order' => $order++,
                ]);
            }
        }

        DB::commit();

        return response()->json([
            'status' => true,
            'message' => 'Adoption Listing Updated Successfully!',
            'data' => $listing->load('galleryImages')
        ]);

    } catch (\Exception $e) {

        DB::rollBack();

        return response()->json([
            'status' => false,
            'message' => $e->getMessage(),
        ], 500);
    }
}

    public function myListings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 200);
        }

        $listings = AdoptionListing::where('user_id', $request->user_id)
            ->orderBy('created_at', 'desc')
            ->with('galleryImages')
            ->paginate(10);

        return response()->json(['status' => true, 'data' => $listings]);
    }

    public function allListings(Request $request)
    {
        $listings = AdoptionListing::with(['galleryImages', 'user'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json(['status' => true, 'data' => $listings]);
    }

    public function delete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'listing_id' => 'required|exists:adoption_listings,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 200);
        }

        $listing = AdoptionListing::where('id', $request->listing_id)
            ->where('user_id', $request->user_id)
            ->first();

        if (!$listing) {
            return response()->json(['status' => false, 'message' => 'Listing not found!']);
        }

        // Delete featured image
        if ($listing->featured_image && file_exists(public_path($listing->featured_image))) {
            unlink(public_path($listing->featured_image));
        }

        // Delete gallery images
        foreach ($listing->galleryImages as $image) {
            if (file_exists(public_path($image->image_path))) {
                unlink(public_path($image->image_path));
            }
            $image->delete();
        }

        $listing->delete();

        return response()->json(['status' => true, 'message' => 'Listing deleted successfully!']);
    }
    
    public function markAsAdopted(Request $request)
{
    try {

        $validator = Validator::make($request->all(), [
            'listing_id' => 'required|exists:adoption_listings,id',
        ]);

        if ($validator->fails()) {

            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $user = $request->user();
       
        // 🔥 Verify listing belongs to logged-in user
        $listing = AdoptionListing::where('id', $request->listing_id)
            ->where('user_id', $user->id)
            ->first();

        if (!$listing) {

            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to update this listing.',
            ], 403);
        }

        // 🔥 Toggle status
        if ($listing->status === 'adopted') {

            $listing->status = 'active';
            $listing->adopted_at = null;

            $message = 'Pet marked as available for adoption.';
        } else {

            $listing->status = 'adopted';
            $listing->adopted_at = now();

            $message = 'Pet marked as adopted successfully.';
        }

        $listing->save();

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => [
                'id' => $listing->id,
                'status' => $listing->status,
                'adopted_at' => $listing->adopted_at,
            ]
        ]);

    } catch (\Exception $e) {

        return response()->json([
            'success' => false,
            'message' => 'Failed to update adoption status.',
            'error' => $e->getMessage(),
        ], 500);
    }
 }
}
