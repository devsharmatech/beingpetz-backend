<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\V2\ValidationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class AuthController extends Controller
{
    /**
     * Register a new pet parent (V2) - Step 1: Send OTP.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'first_name' => 'required|string|max:255',
                'last_name' => 'nullable|string|max:255',
                // 'username' => 'required|string|max:30',
                'email' => 'required|email|max:255',
                // 'phone' => 'required|string|max:20',
                // 'country_code' => 'nullable|string|max:10',
                // 'latitude' => 'nullable|numeric|between:-90,90',
                // 'longitude' => 'nullable|numeric|between:-180,180',
                // 'captcha_answer' => 'required|string',
                // 'captcha_key' => 'required|string',
                // 'profile' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validated = $validator->validated();

            // Validate phone
            // $phoneValidation = ValidationService::validatePhoneNumber($validated['phone'], $validated['country_code'] ?? null);
            // if (!$phoneValidation['valid']) {
            //     return response()->json(['success' => false, 'message' => $phoneValidation['message']], 422);
            // }

            $email = strtolower($validated['email']);

            // Check absolute uniqueness of completed users
            // if (User::where('phone', $phone)->where('isComplete', 1)->exists()) {
            //     return response()->json(['success' => false, 'message' => 'This phone number is already registered. Please login.'], 422);
            // }
            if (User::where('email', $email)->where('isComplete', 1)->exists()) {
                return response()->json(['success' => false, 'message' => 'This email is already registered. Please login.'], 422);
            }

            // Find if an incomplete user exists with this email or phone to allow re-registration
            $user = User::where(function ($q) use ($email) {
                $q->where('email', $email);
            })->where('isComplete', 0)->first();

            // Now check username uniqueness (excluding the current incomplete user if found)
            // $usernameFormat = ValidationService::validateUsernameFormat($validated['username']);
            // if (!$usernameFormat['valid']) {
            //     return response()->json(['success' => false, 'message' => $usernameFormat['message']], 422);
            // }

            // if (!ValidationService::isUsernameUnique($validated['username'], $user ? $user->id : null)) {
            //     return response()->json(['success' => false, 'message' => 'Username is already taken. Please choose another.'], 422);
            // }

            // Validate captcha
            // $captchaValidation = ValidationService::validateCaptcha($validated['captcha_answer'], $validated['captcha_key']);
            // if (!$captchaValidation['valid']) {
            //     return response()->json(['success' => false, 'message' => $captchaValidation['message']], 422);
            // }

            $sanitized = ValidationService::sanitizeInput([
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'email' => $email,
            ]);

            // $nameParts = $this->splitName($sanitized['name']);

            // Profile upload
            // $profilePath = null;
            // if ($request->hasFile('profile')) {
            //     $profilePath = $this->uploadProfileImage($request->file('profile'));
            // }

            $otp = rand(100000, 999999);

            // Create or update incomplete user based on above lookup
            if ($user) {
                $user->update([
                    'first_name' => $sanitized['first_name'],
                    'last_name' => $sanitized['last_name'],
                    'email' => $sanitized['email'],
                    'otp' => $otp,
                    'otp_expires_at' => now()->addMinutes(10),
                ]);
            } else {
                $userId = ValidationService::generateUniqueUserId();
                $user = User::create([
                    'user_id' => $userId,
                    'first_name' => $sanitized['first_name'],
                    'last_name' => $sanitized['last_name'],
                    
                    'email' => $sanitized['email'],
                    
                    'password' => Hash::make(rand(1111111111, 9999999999)),
                    
                    'role' => 'user',
                    'isComplete' => 0,
                    'otp' => $otp,
                    'otp_expires_at' => now()->addMinutes(10),
                ]);
            }

            // Send OTP email
            try {
                Log::info("Attempting to send OTP email to: " . $user->email);
                Mail::raw("Your Beingpetz OTP is: $otp", function ($message) use ($user) {
                    $message->to($user->email)->subject('Your OTP Code - Beingpetz');
                });
                Log::info("OTP email sent successfully to: " . $user->email);
            } catch (\Exception $e) {
                Log::error("Failed to send OTP email to " . $user->email . ". Error: " . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'OTP sent successfully to your email.',
                'data' => [
                    'email' => $user->email
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Registration failed. Please try again.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Register Step 2: Verify OTP.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyRegister(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'otp' => 'required|digits:6',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
            }

            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json(['success' => false, 'message' => 'User not found!'], 404);
            }
            if ($user->isComplete == 1) {
                return response()->json(['success' => false, 'message' => 'User is already registered and verified. Please login.'], 400);
            }
            if ((string) $user->otp !== (string) $request->otp) {
                return response()->json(['success' => false, 'message' => 'Invalid OTP.'], 400);
            }
            if (!$user->otp_expires_at || now()->greaterThan($user->otp_expires_at)) {
                return response()->json(['success' => false, 'message' => 'OTP has expired or was not set. Please register again.'], 400);
            }

            $user->isComplete = 1;
            $user->otp = null;
            $user->otp_expires_at = null;
            $user->save();

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Registration successfully done!',
                'data' => [
                    'user' => $this->formatUserResponse($user),
                    'token' => $token,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Verification failed.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update Password (Protected).
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatePassword(Request $request)
    {
        try {
            $user = $request->user();

            $validator = Validator::make($request->all(), [
                'current_password' => 'required|string',
                'new_password' => 'required|string|min:8|confirmed',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
            }

            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json(['success' => false, 'message' => 'Current password does not match our records.'], 401);
            }

            $user->password = Hash::make($request->new_password);
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Password updated successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to update password.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Login user (V2) - Step 1: Send OTP.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'username' => 'nullable|string',
                'email' => 'nullable|email',
                'phone' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
            }

            $validated = $validator->validated();

            if (empty($validated['username']) && empty($validated['phone']) && empty($validated['email'])) {
                return response()->json(['success' => false, 'message' => 'Please provide an email, username, or phone number to login.'], 422);
            }

            $user = null;
            if (!empty($validated['email'])) {
                $user = User::where('email', strtolower(trim($validated['email'])))->first();
            } elseif (!empty($validated['username'])) {
                $user = User::where('username', strtolower(trim($validated['username'])))->first();
            } elseif (!empty($validated['phone'])) {
                $phone = preg_replace('/[^0-9]/', '', $validated['phone']);
                $user = User::where('phone', $phone)->first();
            }

            if (!$user || $user->isComplete == 0 || $user->deleted_at == 0) {
                return response()->json(['success' => false, 'message' => 'No active account found with those credentials.'], 401);
            }

            $otp = rand(100000, 999999);
            $user->otp = $otp;
            $user->otp_expires_at = now()->addMinutes(10);
            $user->save();

            if (!empty($user->email)) {
                try {
                    Log::info("Attempting to send Login OTP email to: " . $user->email);
                    Mail::raw("Your OTP for login is: $otp (valid for 10 minutes)", function ($message) use ($user) {
                        $message->to($user->email)->subject('Your Login OTP');
                    });
                    Log::info("Login OTP email sent successfully to: " . $user->email);
                } catch (\Exception $e) {
                    Log::error("Failed to send Login OTP email to " . $user->email . ". Error: " . $e->getMessage());
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'OTP sent to your email.',
                'data' => [
                    'email' => $user->email,
                    'phone' => $user->phone
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Login failed.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Login Step 2: Verify OTP.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyLogin(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'nullable|email',
                'phone' => 'nullable|string',
                'username' => 'nullable|string',
                'otp' => 'required|digits:6',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
            }

            $user = null;
            if ($request->filled('email')) {
                $user = User::where('email', $request->email)->first();
            } elseif ($request->filled('username')) {
                $user = User::where('username', $request->username)->first();
            } elseif ($request->filled('phone')) {
                $phone = preg_replace('/[^0-9]/', '', $request->phone);
                $user = User::where('phone', $phone)->first();
            }

            if (!$user) {
                return response()->json(['success' => false, 'message' => 'User not found.'], 404);
            }
            if ((string) $user->otp !== (string) $request->otp) {
                return response()->json(['success' => false, 'message' => 'Invalid OTP.'], 400);
            }
            if (now()->greaterThan($user->otp_expires_at)) {
                return response()->json(['success' => false, 'message' => 'OTP has expired.'], 400);
            }

            $user->otp = null;
            $user->otp_expires_at = null;
            $user->last_login = now();
            $user->save();

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'OTP verified successfully. Logged in.',
                'data' => [
                    'user' => $this->formatUserResponse($user),
                    'token' => $token,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Verification failed.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Resend OTP (V2).
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function resendOtp(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'nullable|email',
                'phone' => 'nullable|string',
                'username' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
            }

            $user = null;
            if ($request->filled('email')) {
                $user = User::where('email', $request->email)->first();
            } elseif ($request->filled('username')) {
                $user = User::where('username', $request->username)->first();
            } elseif ($request->filled('phone')) {
                $phone = preg_replace('/[^0-9]/', '', $request->phone);
                $user = User::where('phone', $phone)->first();
            }

            if (!$user) {
                return response()->json(['success' => false, 'message' => 'User not found.'], 404);
            }

            // Regenerate OTP
            $otp = rand(100000, 999999);
            $user->otp = $otp;
            $user->otp_expires_at = now()->addMinutes(10);
            $user->save();

            // Send OTP email
            if (!empty($user->email)) {
                try {
                    Log::info("Resending OTP email to: " . $user->email);
                    Mail::raw("Your Beingpetz OTP is: $otp (valid for 10 minutes)", function ($message) use ($user) {
                        $message->to($user->email)->subject('Resent OTP Code - Beingpetz');
                    });
                    Log::info("Resent OTP email successfully sent to: " . $user->email);
                } catch (\Exception $e) {
                    Log::error("Failed to resend OTP email to " . $user->email . ". Error: " . $e->getMessage());
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'A new OTP has been sent to your email.',
                'data' => [
                    'email' => $user->email,
                    'phone' => $user->phone
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to resend OTP.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Forgot Password - Step 1: Send OTP.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function forgotPassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'nullable|email',
                'phone' => 'nullable|string',
                'username' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
            }

            $validated = $validator->validated();

            if (empty($validated['email']) && empty($validated['phone']) && empty($validated['username'])) {
                return response()->json(['success' => false, 'message' => 'Please provide an email, username, or phone number.'], 422);
            }

            $user = null;
            if ($request->filled('email')) {
                $user = User::where('email', strtolower($request->email))->first();
            } elseif ($request->filled('username')) {
                $user = User::where('username', strtolower($request->username))->first();
            } elseif ($request->filled('phone')) {
                $phone = preg_replace('/[^0-9]/', '', $request->phone);
                $user = User::where('phone', $phone)->first();
            }

            if (!$user || $user->deleted_at == 0) {
                return response()->json(['success' => false, 'message' => 'User not found or account is inactive.'], 404);
            }

            $otp = rand(100000, 999999);
            $user->otp = $otp;
            $user->otp_expires_at = now()->addMinutes(15);
            $user->save();

            // Send OTP email
            if ($user->email) {
                try {
                    Mail::raw("Your password reset OTP is: $otp (valid for 15 minutes)", function ($message) use ($user) {
                        $message->to($user->email)->subject('Password Reset OTP - Beingpetz');
                    });
                } catch (\Exception $e) {
                    Log::error("Failed to send forgot password email: " . $e->getMessage());
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'OTP has been sent to your registered email.',
                'data' => [
                    'email' => $user->email,
                    'phone' => $user->phone
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to process request.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Forgot Password - Step 2: Verify & Reset.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetPassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'nullable|email',
                'phone' => 'nullable|string',
                'username' => 'nullable|string',
                'otp' => 'required|digits:6',
                'password' => 'required|string|min:8|confirmed',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
            }

            $user = null;
            if ($request->filled('email')) {
                $user = User::where('email', strtolower($request->email))->first();
            } elseif ($request->filled('username')) {
                $user = User::where('username', strtolower($request->username))->first();
            } elseif ($request->filled('phone')) {
                $phone = preg_replace('/[^0-9]/', '', $request->phone);
                $user = User::where('phone', $phone)->first();
            }

            if (!$user) {
                return response()->json(['success' => false, 'message' => 'User not found.'], 404);
            }

            if ((string) $user->otp !== (string) $request->otp) {
                return response()->json(['success' => false, 'message' => 'Invalid OTP.'], 400);
            }

            if (now()->greaterThan($user->otp_expires_at)) {
                return response()->json(['success' => false, 'message' => 'OTP has expired.'], 400);
            }

            $user->password = Hash::make($request->password);
            $user->otp = null;
            $user->otp_expires_at = null;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Password reset successfully. You can now login with your new password.'
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to reset password.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Generate captcha for registration.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateCaptcha()
    {
        $captcha = ValidationService::generateCaptcha();

        return response()->json([
            'success' => true,
            'data' => $captcha
        ]);
    }

    /**
     * Update parent profile (V2).
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProfile(Request $request)
    {
        try {
            $user = $request->user();

            $validator = Validator::make($request->all(), [
                'name' => 'nullable|string|max:255',
                'username' => 'nullable|string|max:30|unique:users,username,' . $user->id,
                'email' => 'required|string|max:30|unique:users,email,' . $user->id,
                'phone' => 'nullable|string|max:30|unique:users,phone,' . $user->id,
                'city' => 'nullable|string|max:100',
                'country_code' => 'nullable|string|max:10',
                'state' => 'nullable|string|max:100',
                'locality' => 'nullable|string|max:255',
                'latitude' => 'nullable|numeric|between:-90,90',
                'longitude' => 'nullable|numeric|between:-180,180',
                'profile' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $validator->errors()], 422);
            }

            $updateData = [];

            if ($request->filled('name')) {
                $parts = $this->splitName($request->name);
                $updateData['first_name'] = $parts['first_name'];
                $updateData['last_name'] = $parts['last_name'];
            }
            if ($request->filled('username')) {
                $usernameValidation = ValidationService::validateUsernameFormat($request->username);
                if (!$usernameValidation['valid']) {
                    return response()->json(['success' => false, 'message' => $usernameValidation['message']], 422);
                }
                $updateData['username'] = strtolower(trim($request->username));
            }
            if ($request->filled('email'))
                $updateData['email'] = $request->email;
            if ($request->filled('country_code'))
                $updateData['country_code'] = $request->country_code;
            if ($request->filled('first_name'))
                $updateData['first_name'] = $request->first_name;
            if ($request->filled('last_name'))
                $updateData['last_name'] = $request->last_name;
            if ($request->filled('phone'))
                $updateData['phone'] = $request->phone;
            if ($request->filled('city'))
                $updateData['city'] = $request->city;
            if ($request->filled('state'))
                $updateData['state'] = $request->state;
            if ($request->filled('locality'))
                $updateData['locality'] = $request->locality;
            if ($request->filled('latitude'))
                $updateData['latitude'] = $request->latitude;
            if ($request->filled('longitude'))
                $updateData['longitude'] = $request->longitude;

            if ($request->hasFile('profile')) {
                if ($user->profile) {
                    $oldPath = public_path($user->profile);
                    if (File::exists($oldPath)) {
                        File::delete($oldPath);
                    }
                }
                $updateData['profile'] = $this->uploadProfileImage($request->file('profile'));
            }

            if (!empty($updateData)) {
                $user->update($updateData);
            }

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => $this->formatUserResponse($user->fresh())
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Profile update failed.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update only the parent profile picture (V2).
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProfilePicture(Request $request)
    {
        try {
            $user = $request->user();

            $validator = Validator::make($request->all(), [
                'profile' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => $validator->errors()], 422);
            }

            if ($user->profile) {
                $oldPath = public_path($user->profile);
                if (File::exists($oldPath)) {
                    File::delete($oldPath);
                }
            }

            $profilePath = $this->uploadProfileImage($request->file('profile'));
            $user->update(['profile' => $profilePath]);

            return response()->json([
                'success' => true,
                'message' => 'Profile picture updated successfully',
                'data' => [
                    'profile' => $profilePath,
                    'profile_url' => url($profilePath),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Profile picture update failed.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Upload and resize a profile image.
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @return string  Relative path stored in DB
     */
    protected function uploadProfileImage($file): string
    {
        $manager = new ImageManager(new Driver());
        $filename = uniqid('profile_') . '.' . $file->getClientOriginalExtension();
        $dir = public_path('uploads/profile');

        if (!File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        $manager->read($file)->scale(width: 400)->save($dir . '/' . $filename);

        return 'uploads/profile/' . $filename;
    }

    /**
     * Split full name into first and last name.
     *
     * @param string $name
     * @return array
     */
    protected function splitName(string $name): array
    {
        $parts = explode(' ', trim($name), 2);
        return [
            'first_name' => $parts[0],
            'last_name' => $parts[1] ?? '',
        ];
    }

    /**
     * Format user response.
     *
     * @param User $user
     * @return array
     */
    protected function formatUserResponse(User $user): array
    {
        return [
            'id' => $user->id,
            'user_id' => $user->user_id,
            'name' => $user->first_name . ' ' . $user->last_name,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'username' => $user->username,
            'phone' => $user->phone,
            'country_code' => $user->country_code,
            'latitude' => $user->latitude,
            'longitude' => $user->longitude,
            'profile' => $user->profile,
            'role' => $user->role,
            'profile_url' => $user->profile ? url($user->profile) : null,
            'created_at' => $user->created_at,
        ];
    }
    /**
     * Check if a username is available.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkUsername(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|min:3|max:30',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'available' => false,
                'message' => 'Invalid username format.',
                'errors' => $validator->errors()
            ], 422);
        }

        $username = trim($request->query('username'));

        // Use existing ValidationService to check format and uniqueness
        $formatCheck = ValidationService::validateUsernameFormat($username);
        if (!$formatCheck['valid']) {
            return response()->json([
                'available' => false,
                'message' => $formatCheck['message']
            ]);
        }

        $isUnique = ValidationService::isUsernameUnique($username);

        if ($isUnique) {
            return response()->json([
                'available' => true,
                'message' => 'Username is available'
            ]);
        }

        return response()->json([
            'available' => false,
            'message' => 'Username already exists'
        ]);
    }
}
