<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


class AuthApiController extends Controller
{
    public function register1(Request $request)
    {
        $validate = Validator::make(
            $request->all(),
            [
                'email_phone' => 'required|string',
                'password' => 'required|string|min:6',
            ]
        );
        if ($validate->fails()) {
            return response()->json(['status' => false, 'message' => $validate->errors()->first()], 200);
        }
        $input = $request->email_phone;
        $isEmail = filter_var($input, FILTER_VALIDATE_EMAIL);

        if ($isEmail) {
            // It's an email
            $exists = User::where('email', $input)->exists();
            if ($exists) {
                return response()->json(['status' => false, 'message' => 'Email already exists.'], 200);
            }
            $user = User::create([
                'email' => $input,
                'password' => bcrypt($request->password),
            ]);
        } else {
            // It's a phone number
            $exists = User::where('phone', $input)->exists();
            if ($exists) {
                return response()->json(['status' => false, 'message' => 'Phone number already exists.'], 200);
            }
            $user = User::create([
                'phone' => $input,
                'password' => bcrypt($request->password),
            ]);
        }
        $user['otp'] = rand(100000, 999999);
        return response()->json(['status' => true, 'message' => 'Registration successful.', 'data' => $user], 200);
    }
    
    public function register2(Request $request)
    {
    $validate = Validator::make(
        $request->all(),
        [
            'name' => 'required|string',
            'email' => 'required|email',
        ]
    );

    if ($validate->fails()) {
        return response()->json(['status' => false, 'message' => $validate->errors()->first()], 200);
    }

    $input = $request->email;
    $isEmail = filter_var($input, FILTER_VALIDATE_EMAIL);

    $otp = rand(100009, 999999);
    $data=['name'=>$request->name,'password' => bcrypt(rand(1000000,9999999))];
    if ($isEmail) {
        // Check if email exists
        if (User::where('email', $input)->exists()) {
            return response()->json(['status' => false, 'message' => 'Email already exists.'], 200);
        }

        $data['email'] = $input;
        $user = User::create($data);

        // Send OTP directly via email
        $mail= Mail::raw("Your Beingpetz OTP is: $otp", function ($message) use ($input) {
            $message->to($input)
                    ->subject('Your OTP Code - Beingpetz');
        });
    }
    $user['otp']=$otp;
    return response()->json(['status' => true, 'message' => 'Registration successful. OTP sent.', 'data' => $user], 200);
}
 
    public function register(Request $request)
    {
    $validate = Validator::make(
        $request->all(),
        [
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|email',
        ]
    );

    if ($validate->fails()) {
        return response()->json(['status' => false, 'message' => $validate->errors()->first()], 200);
    }

    $input = $request->email;
    $isEmail = filter_var($input, FILTER_VALIDATE_EMAIL);
    $otp = rand(100000, 999999);

    if ($isEmail) {
        $user = User::where('email', $input)->first();

        if ($user) {
            if ($user->isComplete == 1) {
                return response()->json(['status' => false, 'message' => 'Email already exists.'], 200);
            } else {
                // Update partial user
                $user->first_name = $request->first_name;
                $user->last_name = $request->last_name;
                $user->otp = $otp;
                $user->save();
            }
        } else {
            // Create new user
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $input,
                'password' => bcrypt(rand(1000000, 9999999)),
                'otp' => $otp,
                'isComplete' => 0
            ]);
        }

        // Send OTP
        Mail::raw("Your Beingpetz OTP is: $otp", function ($message) use ($input) {
            $message->to($input)->subject('Your OTP Code - Beingpetz');
        });

        return response()->json([
            'status' => true,
            'message' => 'OTP sent successfully.',
            'data' => $user
        ], 200);
    }

    return response()->json(['status' => false, 'message' => 'Invalid email address.'], 200);
}


     
    public function registerVerify(Request $request)
    {
        $validate = Validator::make($request->all(), [
            "user_id" => ['required', 'exists:users,id'],
        ]);
        if ($validate->fails()) {
            return response()->json(['status' => false, 'message' => $validate->errors()->first()]);
        }
        $user = User::where('id', $request->user_id)->first();
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'User not found!']);
        } else {
            $user->isComplete = 1;
            $user->save();
            return response()->json(['status' => true, 'message' => 'Registration successfully done!', 'user' => $user]);
        }
    }
    
    public function myDetails(Request $request)
    {
        $validate = Validator::make($request->all(), [
            "user_id" => ['required', 'exists:users,id'],
        ]);
        if ($validate->fails()) {
            return response()->json(['status' => false, 'message' => $validate->errors()->first()]);
        }
        $user = User::where('id', $request->user_id)->with('pets')->first();
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'User not found!']);
        } else {
           return response()->json(['status' => true, 'message' => 'Data fetched successfully done!', 'user' => $user]);
        }
    }

    public function login_old(Request $request)
    {
        $validate = Validator::make(
            $request->all(),
            [
                'email_phone' => 'required|string',
                'password' => 'required|string|min:6',
            ]
        );
        if ($validate->fails()) {
            return response()->json(['status' => false, 'message' => $validate->errors()->first()], 200);
        }
        $input = $request->email_phone;
        $isEmail = filter_var($input, FILTER_VALIDATE_EMAIL);
        if ($isEmail) {
            $user = User::where('email', $input)->where('isComplete', 1)->where('deleted_at', 1)->first();
        } else {
            $user = User::where('phone', $input)->where('isComplete', 1)->where('deleted_at', 1)->first();
        }
        if (!isset($user->id)) {
            return response()->json(['status' => false, 'message' => "User not found."], 200);
        }

        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['status' => false, 'message' => "Incorrect password."], 200);
        }
        return response()->json([
            'status' => true,
            'message' => 'Login successful.',
            'user' => $user,
        ], 200);
    }

public function login_old2(Request $request)
{
    // Validate input
    $validate = Validator::make($request->all(), [
        'email' => 'required|email',
    ]);

    if ($validate->fails()) {
        return response()->json(['status' => false, 'message' => $validate->errors()->first()], 200);
    }

    // Find user by email
    $user = User::where('email', $request->email)->where('isComplete', 1)->where('deleted_at', 1)->first();

    if (!$user) {
        return response()->json(['status' => false, 'message' => "User not found."], 200);
    }

    // Generate OTP
    $otp = rand(100000, 999999);

    // Send OTP via email
    Mail::raw("Your OTP for login is: $otp", function ($message) use ($user) {
        $message->to($user->email)
                ->subject('Your Login OTP');
    });

    // Return response
    return response()->json([
        'status' => true,
        'message' => 'OTP sent to your email.',
        'user' => $user,
        'otp' => $otp, // remove in production
    ], 200);
}

public function login(Request $request)
{
    // Validate input
    $validate = Validator::make($request->all(), [
        'email' => 'required|email',
    ]);

    if ($validate->fails()) {
        return response()->json(['status' => false, 'message' => $validate->errors()->first()], 200);
    }

    // Find user by email
    $user = User::where('email', $request->email)->where('isComplete', 1)->where('deleted_at', 1)->first();

    if (!$user) {
        return response()->json(['status' => false, 'message' => "User not found."], 200);
    }

    $otp = rand(100000, 999999);

    $user->otp = $otp;
    $user->otp_expires_at = now()->addMinutes(3);
    $user->save();

    // Send OTP via email
    Mail::raw("Your OTP for login is: $otp (valid for 3 minutes)", function ($message) use ($user) {
        $message->to($user->email)
                ->subject('Your Login OTP');
    });

    // Return response
    return response()->json([
        'status' => true,
        'message' => 'OTP sent to your email (valid for 3 minutes).',
        'user' => $user,
    ], 200);
}

public function verifyOtpLogin(Request $request)
{
    // Validate input
    $validate = Validator::make($request->all(), [
        'email' => 'required|email',
        'otp'   => 'required|digits:6',
    ]);

    if ($validate->fails()) {
        return response()->json([
            'status' => false,
            'message' => $validate->errors()->first()
        ], 200);
    }

    // Find user
    $user = User::where('email', $request->email)->where('isComplete', 1)->where('deleted_at', 1)->with('pets')->first();

    if (!$user) {
        return response()->json(['status' => false, 'message' => "User not found."], 200);
    }

    // Check OTP
    if ($user->otp != $request->otp) {
        return response()->json(['status' => false, 'message' => 'Invalid OTP.'], 200);
    }

    // Check expiry
    if (now()->greaterThan($user->otp_expires_at)) {
        return response()->json(['status' => false, 'message' => 'OTP has expired.'], 200);
    }

    // OTP is valid → clear it
    $user->otp = null;
    $user->otp_expires_at = null;
    $user->save();
    
    return response()->json([
        'status' => true,
        'message' => 'OTP verified successfully. Logged in.',
        'user' => $user
    ], 200);
}


public function socialLogin(Request $request)
{
    $validate = Validator::make($request->all(), [
        'email' => 'required|email',
        'name'  => 'required|string|max:255',
    ]);

    if ($validate->fails()) {
        return response()->json(['status' => false, 'message' => $validate->errors()->first()], 200);
    }

    $fullName = trim($request->name);
    $nameParts = explode(' ', $fullName, 2); 
    $firstName = $nameParts[0];
    $lastName = isset($nameParts[1]) ? $nameParts[1] : '';

    
    $user = User::where('email', $request->email)->where('deleted_at', 1)->first();

    if (!$user) {
        $user = User::create([
            'first_name' => $firstName,
            'last_name'  => $lastName,
            'email'      => $request->email,
            'isComplete' => 1,
            'password'   => bcrypt(Str::random(10)),
        ]);
    }

    return response()->json([
        'status' => true,
        'message' => 'Login successful via social account.',
        'user' => $user,
    ], 200);
}

    public function updateProfile(Request $request)
    {
        $validate = Validator::make(
            $request->all(),
            [
                "user_id" => 'required|exists:users,id',
                "first_name" => 'required|string',
                "last_name" => 'nullable|string',
                'email' => [
                    'required',
                    'email',
                    \Illuminate\Validation\Rule::unique('users', 'email')->ignore($request->user_id)->where(function ($query) {
                        return $query->where('deleted_at', 1);
                    })
                ],
                'phone' => [
                    'required',
                    \Illuminate\Validation\Rule::unique('users', 'phone')->ignore($request->user_id)->where(function ($query) {
                        return $query->where('deleted_at', 1);
                    })
                ],
                'latitude' => 'nullable|string',
                'longitude' => 'nullable|string',
                'locality' => 'nullable|string',
                'city' => 'nullable|string',
                'state' => 'nullable|string',
                'profile' => 'nullable|image',
            ]
        );
        if ($validate->fails()) {
            return response()->json(['status' => false, 'message' => $validate->errors()->first()], 200);
        }
        $user = User::where('id', $request->user_id)->first();
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'User not found!']);
        } else {
            $oldEmail = $user->email;
            $newEmail = $request->email;

            if ($request->hasFile('profile')) {
                    $file = $request->file('profile');
                    $manager = new ImageManager(Driver::class);
                    $imagePath = parse_url($user->profile, PHP_URL_PATH);
                    $localImagePath = public_path($imagePath);
                    $image = $manager->read($file);
                    $filename = uniqid('profile_') . '.' . $file->getClientOriginalExtension();
                    $image->resize(400, 400)->save(public_path('uploads/profile/' . $filename));
                    if (File::exists($localImagePath)) {
                        File::delete($localImagePath);
                    }
                    $user->profile = 'uploads/profile/' . $filename;
                    $user->save();
            }
            
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->email = $request->email;
            $user->phone = $request->phone;
            $user->latitude = $request->latitude;
            $user->longitude = $request->longitude;
            $user->locality = $request->locality;
            $user->city = $request->city;
            $user->state = $request->state;
            $user->save();

            // If email was changed, send notification mail
            if ($oldEmail !== $newEmail) {
                try {
                    Mail::raw("Your Beingpetz account email has been updated to $newEmail. If you didn't do this, please contact support.", function ($message) use ($newEmail) {
                        $message->to($newEmail)->subject('Email Updated - Beingpetz');
                    });
                } catch (\Exception $e) {
                    \Log::error('API profile email update notification failed: ' . $e->getMessage());
                }
            }

            return response()->json(['status' => true, 'message' => 'Successfully update your changes!', 'user' => $user]);
        }
    }


    public function updateProfilePicture(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'profile' => 'nullable|image'
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()]);
        } else {
            $user = User::where('id', $request->user_id)->first();
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found.'
                ]);
            } else {
                if ($request->hasFile('profile')) {
                    $file = $request->file('profile');
                    $manager = new ImageManager(Driver::class);
                    $imagePath = parse_url($user->profile, PHP_URL_PATH);
                    $localImagePath = public_path($imagePath);
                    $image = $manager->read($file);
                    $filename = uniqid('profile_') . '.' . $file->getClientOriginalExtension();
                    $image->resize(400, 400)->save(public_path('uploads/profile/' . $filename));
                    if (File::exists($localImagePath)) {
                        File::delete($localImagePath);
                    }
                    $user->profile = 'uploads/profile/' . $filename;
                    $user->save();
                }
                return response()->json([
                    'status' => true,
                    'message' => 'Profile updated successfully.',
                    'user' => $user,
                ]);
            }
        }
    }
    
    public function deleteProfilePicture(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()]);
        } else {
            $user = User::where('id', $request->user_id)->first();
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found.'
                ]);
            } else {
                 if ($user->profile!="") {
                    $imagePath = parse_url($user->profile, PHP_URL_PATH);
                    $localImagePath = public_path($imagePath);
                    if (File::exists($localImagePath)) {
                        File::delete($localImagePath);
                    }
                    $user->profile = null;
                  }
                    $user->save();
                return response()->json([
                    'status' => true,
                    'message' => 'Profile deleted successfully.',
                    'user' => $user,
                ]);
            }
        }
    }
    
    public function deleteAccount(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()]);
        } else {
            $user = User::where('id', $request->user_id)->first();
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found.'
                ]);
            } else {
                if ($user->profile!="") {
                    $imagePath = parse_url($user->profile, PHP_URL_PATH);
                    $localImagePath = public_path($imagePath);
                    if (File::exists($localImagePath)) {
                        File::delete($localImagePath);
                    }
                    $user->profile = null;
                 }
                
                // Perform soft delete to prevent foreign key errors
                $user->deleted_at = 0; // 0 for inactive/deleted

                // 1. Posts & Comments
                // Find user's post IDs to hide their reposts
                $userPostIds = DB::table('posts')->where('parent_id', $user->id)->pluck('id');

                if ($userPostIds->isNotEmpty()) {
                    // Hide any posts that reposted this user's posts
                    DB::table('posts')
                        ->whereIn('repost_id', $userPostIds)
                        ->update(['status' => 'inactive', 'deleted_at' => 0]);
                }

                // Posts: Set to inactive, soft delete, and clear media
                $posts = DB::table('posts')->where('parent_id', $user->id)->get();
                foreach ($posts as $p) {
                    if (!empty($p->featured_image)) {
                        $localPath = public_path(parse_url($p->featured_image, PHP_URL_PATH));
                        if (File::exists($localPath)) File::delete($localPath);
                    }
                    if (!empty($p->featured_video)) {
                        $localPath = public_path(parse_url($p->featured_video, PHP_URL_PATH));
                        if (File::exists($localPath)) File::delete($localPath);
                    }
                    if (!empty($p->media_urls)) {
                        $urls = json_decode($p->media_urls, true);
                        if (is_array($urls)) {
                            foreach ($urls as $url) {
                                $localPath = public_path(parse_url($url, PHP_URL_PATH));
                                if (File::exists($localPath)) File::delete($localPath);
                            }
                        }
                    }
                }
                DB::table('posts')
                    ->where('parent_id', $user->id)
                    ->update([
                        'status' => 'inactive', 
                        'deleted_at' => 0,
                        'featured_image' => null,
                        'featured_video' => null,
                        'media_urls' => null
                    ]);
                
                DB::table('comments')
                    ->where('parent_id', $user->id)
                    ->update(['status' => 'inactive', 'deleted_at' => now()]);
                
                DB::table('likes')
                    ->where('parent_id', $user->id)
                    ->delete();

                DB::table('comment_likes')
                    ->where('user_id', $user->id)
                    ->delete();

                // 2. Contests
                // Contest Entries: Soft-delete
                DB::table('contest_entries')
                    ->where('user_id', $user->id)
                    ->update(['deleted_at' => now()]);

                // Contest Votes: Delete
                DB::table('contest_votes')
                    ->where('user_id', $user->id)
                    ->delete();

                // 3. Communities
                // Memberships: Set to inactive
                DB::table('community_memberships')
                    ->where('parent_id', $user->id)
                    ->update(['status' => 0]);
                
                // Community Messages: Anonymize text to not break reply chains
                DB::table('community_messages')
                    ->where('parent_id', $user->id)
                    ->update([
                        'message_text' => 'This message was deleted.',
                        'media_path' => null
                    ]);

                // 4. Listings & Reports
                // Adoption Listings: Delete media and then row
                $adoptionListings = DB::table('adoption_listings')->where('user_id', $user->id)->get();
                foreach ($adoptionListings as $listing) {
                    if (!empty($listing->featured_image)) {
                        $localPath = public_path(parse_url($listing->featured_image, PHP_URL_PATH));
                        if (File::exists($localPath)) File::delete($localPath);
                    }
                }
                DB::table('adoption_listings')
                    ->where('user_id', $user->id)
                    ->delete();

                // Lost/Found Reports: Delete media and set to cancelled
                $lfReports = DB::table('lost_found_reports')->where('user_id', $user->id)->get();
                foreach ($lfReports as $report) {
                    if (!empty($report->images)) {
                        $urls = json_decode($report->images, true);
                        if (is_array($urls)) {
                            foreach ($urls as $url) {
                                $localPath = public_path(parse_url($url, PHP_URL_PATH));
                                if (File::exists($localPath)) File::delete($localPath);
                            }
                        }
                    }
                }
                DB::table('lost_found_reports')
                    ->where('user_id', $user->id)
                    ->update(['status' => 'cancelled', 'images' => null]);

                // 5. Pets
                // Soft delete pets and clear profile pictures
                $pets = DB::table('pets')->where('user_id', $user->id)->get();
                foreach ($pets as $pet) {
                    if (!empty($pet->avatar)) {
                        $localPath = public_path(parse_url($pet->avatar, PHP_URL_PATH));
                        if (File::exists($localPath)) File::delete($localPath);
                    }
                }
                DB::table('pets')
                    ->where('user_id', $user->id)
                    ->update(['deleted_at' => now(), 'avatar' => null]);

                // Suffix email and phone to allow re-registration
                if ($user->email) {
                    $user->email = 'deleted_' . time() . '_' . $user->email;
                }
                if ($user->phone) {
                    $user->phone = 'deleted_' . time() . '_' . $user->phone;
                }
                $user->save();

                // Revoke all tokens
                if (method_exists($user, 'tokens')) {
                    $user->tokens()->delete();
                }

                return response()->json([
                    'status' => true,
                    'message' => 'Account deleted successfully.',
                ]);
            }
        }
    }
    
    
     public function forgetPassword(Request $request)
     {
        $validate = Validator::make(
            $request->all(),
            [
                'email_phone' => 'required|string',
            ]
        );
        if ($validate->fails()) {
            return response()->json(['status' => false, 'message' => $validate->errors()->first()], 200);
        }
        $input = $request->email_phone;
        $isEmail = filter_var($input, FILTER_VALIDATE_EMAIL);

        if ($isEmail) {
            $exist = User::where('email', $input)->where('deleted_at', 1)->first();
            if (!$exist) {
                return response()->json(['status' => false, 'message' => 'This Email not registered with us or account is inactive.'], 200);
            }
            
        } else {
            // It's a phone number
            $exist = User::where('phone', $input)->where('deleted_at', 1)->first();
            if (!$exist) {
               return response()->json(['status' => false, 'message' => 'This Phone not registered with us or account is inactive.'], 200);
            }
            
        }
        $exist['otp'] = rand(100000, 999999);
        return response()->json(['status' => true, 'message' => 'OTP send successfully.', 'data' => $exist], 200);
    }
    
     public function changePassword(Request $request)
     {
        $validate = Validator::make(
            $request->all(),
            [
                'user_id' => 'required|exists:users,id',
                'password' => 'required|string|min:6',
            ]
        );
        if ($validate->fails()) {
            return response()->json(['status' => false, 'message' => $validate->errors()->first()], 200);
        }
        $user = User::where('id', $request->user_id)->where('deleted_at', 1)->first();
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'User not found or account is inactive.'], 200);
        }
        $user->password=bcrypt($request->password);
        $user->save();
        return response()->json(['status' => true, 'message' => 'Password updated successfully.', 'data' => $user], 200);
    }
}
