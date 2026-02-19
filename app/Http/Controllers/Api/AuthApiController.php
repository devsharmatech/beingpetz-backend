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
            $user = User::where('email', $input)->where('isComplete', 1)->first();
        } else {
            $user = User::where('phone', $input)->where('isComplete', 1)->first();
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
    $user = User::where('email', $request->email)->where('isComplete', 1)->first();

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
    $user = User::where('email', $request->email)->where('isComplete', 1)->first();

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
    $user = User::where('email', $request->email)->where('isComplete', 1)->with('pets')->first();

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

    
    $user = User::where('email', $request->email)->first();

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
                'email' => 'required|email|unique:users,email,' . $request->user_id,
                'phone' => 'required|unique:users,phone,' . $request->user_id,
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
                  }
                    $user->profile = null;
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
                  }
                $user->delete();
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
            $exist = User::where('email', $input)->first();
            if (!$exist) {
                return response()->json(['status' => false, 'message' => 'This Email not register with us.'], 200);
            }
            
        } else {
            // It's a phone number
            $exist = User::where('phone', $input)->first();
            if (!$exist) {
               return response()->json(['status' => false, 'message' => 'This Phone not register with us.'], 200);
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
        $user = User::where('id', $request->user_id)->first();
        $user->password=bcrypt($request->password);
        $user->save();
        return response()->json(['status' => true, 'message' => 'Password updated successfully.', 'data' => $user], 200);
    }
}
