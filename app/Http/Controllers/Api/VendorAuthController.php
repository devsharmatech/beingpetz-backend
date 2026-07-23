<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Provider;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class VendorAuthController extends Controller
{
    /**
     * Vendor Registration with dynamic service fields
     */
    public function register(Request $request)
    {
        // 1. Base Validation
        $baseRules = [
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|unique:users,phone',
            'business_name' => 'required|string|max:255',
            'legal_name' => 'required|string|max:255',
            'services' => 'required|array',
            'services.*' => 'in:veterinary,grooming,boarding,training,walking,home sitting',
            'area' => 'required|string|max:255',
            'experience_years' => 'required|string|max:255',
            'primary_gov_doc' => 'required|file|mimes:jpeg,png,jpg,pdf|max:5120',
            'alternate_id_doc' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:5120',
            'proof_of_expertise' => 'nullable|array',
            'proof_of_expertise.*' => 'file|mimes:jpeg,png,jpg,pdf|max:5120',
            'work_gallery' => 'nullable|array',
            'work_gallery.*' => 'file|mimes:jpeg,png,jpg,pdf|max:5120',
            'dpdp_consent' => 'required|boolean',
            'weekly_schedule' => 'nullable|array',
            'start_pricing' => 'nullable|numeric',
            'consultation_fee' => 'nullable|numeric',
            'accepted_pet_types' => 'nullable|array',
            'accepted_pet_sizes' => 'nullable|array',
            'emergency_contact_number' => 'nullable|string',
            'location' => 'nullable|string',
        ];

        // 2. Determine conditional rules based on selected services
        $services = $request->input('services', []);
        $servicesArray = is_array($services) ? $services : [];

        $conditionalRules = [];

        // Veterinary Rules
        if (in_array('veterinary', $servicesArray)) {
            $conditionalRules['vet_clinic_name'] = 'required|string|max:255';
            $conditionalRules['registration_number'] = 'required|string|max:255';
            $conditionalRules['clinic_address'] = 'required|string';
            $conditionalRules['specialization'] = 'required|array';
            $conditionalRules['available_in_emergency'] = 'required|boolean';
        }

        // Grooming Rules
        if (in_array('grooming', $servicesArray)) {
            $conditionalRules['facility_capacity'] = 'required|integer|min:1';
            $conditionalRules['cage_free_facility'] = 'required|boolean';
            $conditionalRules['trade_licence_number'] = 'required|string|max:255';
            $conditionalRules['breed_specialization'] = 'nullable|array';
        }

        // Trainer / Walker Rules
        if (in_array('training', $servicesArray) || in_array('walking', $servicesArray)) {
            $conditionalRules['certifications'] = 'nullable|string';
            $conditionalRules['types_of_training_offered'] = 'nullable|array';
            $conditionalRules['max_dog_weight'] = 'nullable|integer';
            $conditionalRules['breed_exp_log'] = 'nullable|string';
            $conditionalRules['professional_reference_1'] = 'nullable|string';
            $conditionalRules['professional_reference_2'] = 'nullable|string';
            $conditionalRules['gp_tracking_required'] = 'nullable|boolean';
        }

        // Home Boarding / Pet Sitting Rules
        if (in_array('boarding', $servicesArray) || in_array('home sitting', $servicesArray)) {
            $conditionalRules['property_type'] = 'required|string';
            $conditionalRules['household_composition'] = 'required|string';
            $conditionalRules['supervision_level'] = 'required|string';
            $conditionalRules['max_pet_capacity'] = 'required|integer';
            $conditionalRules['pet_size_restrictions'] = 'nullable|string';
            $conditionalRules['accept_separation_anxiety'] = 'nullable|boolean';
            $conditionalRules['accept_reactive_pets'] = 'nullable|boolean';
            $conditionalRules['accept_untrained'] = 'nullable|boolean';
            $conditionalRules['offer_transportation'] = 'nullable|boolean';
            $conditionalRules['video_walkthrough'] = 'nullable|file|mimes:mp4,mov,avi|max:20480';
        }

        $validator = Validator::make($request->all(), array_merge($baseRules, $conditionalRules));

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // 3. Process File Uploads
        $uploadFile = function($file, $folder) {
            return $file ? $file->store($folder, 'public') : null;
        };

        $primaryGovDoc = $uploadFile($request->file('primary_gov_doc'), 'vendor_docs');
        $alternateIdDoc = $request->hasFile('alternate_id_doc') ? $uploadFile($request->file('alternate_id_doc'), 'vendor_docs') : null;
        $videoWalkthrough = $request->hasFile('video_walkthrough') ? $uploadFile($request->file('video_walkthrough'), 'vendor_videos') : null;

        $proofOfExpertise = [];
        if ($request->hasFile('proof_of_expertise')) {
            foreach ($request->file('proof_of_expertise') as $file) {
                $proofOfExpertise[] = $uploadFile($file, 'vendor_certificates');
            }
        }

        $workGallery = [];
        if ($request->hasFile('work_gallery')) {
            foreach ($request->file('work_gallery') as $file) {
                $workGallery[] = $uploadFile($file, 'vendor_gallery');
            }
        }

        // 4. Create User (Vendor Role)
        $firstName = $request->first_name;
        $lastName = $request->last_name;

        if (empty($firstName) && $request->has('legal_name')) {
            $nameParts = explode(' ', $request->legal_name, 2);
            $firstName = $nameParts[0];
            $lastName = $nameParts[1] ?? '';
        }

        $user = User::create([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $request->email,
            'phone' => $request->phone,
            'role' => 'vendor',
            'password' => Hash::make(uniqid()) // Dummy password as OTP is used for login
        ]);

        // 5. Gather Service-Specific Data
        $serviceSpecificData = [];
        $allFields = array_keys($conditionalRules);
        foreach ($allFields as $field) {
            if ($request->has($field)) {
                $serviceSpecificData[$field] = $request->input($field);
            }
        }

        // 6. Create Provider Profile
        $provider = Provider::create([
            'user_id' => $user->id,
            'name' => trim($firstName . ' ' . $lastName),
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->location ?? $request->area,
            'business_name' => $request->business_name,
            'legal_name' => $request->legal_name,
            'services' => $servicesArray,
            'area' => $request->area,
            'experience_years' => $request->experience_years,
            'start_pricing' => $request->start_pricing,
            'consultation_fee' => $request->consultation_fee,
            'accepted_pet_types' => $request->accepted_pet_types,
            'accepted_pet_sizes' => $request->accepted_pet_sizes,
            'emergency_contact_number' => $request->emergency_contact_number,
            'weekly_schedule' => $request->weekly_schedule,
            'primary_gov_doc' => $primaryGovDoc,
            'alternate_id_doc' => $alternateIdDoc,
            'proof_of_expertise' => $proofOfExpertise,
            'work_gallery' => $workGallery,
            'video_walkthrough' => $videoWalkthrough,
            'dpdp_consent' => $request->dpdp_consent,
            'service_specific_data' => $serviceSpecificData,
            'is_active' => false // Typically requires admin approval
        ]);

        // Auto-create initial ProviderService entries for each registered service category
        if (!empty($servicesArray) && is_array($servicesArray)) {
            foreach ($servicesArray as $cat) {
                $catName = ucfirst($cat);
                \App\Models\ProviderService::firstOrCreate(
                    [
                        'provider_id' => $provider->id,
                        'category'    => $catName,
                        'name'        => $catName . ' Service',
                    ],
                    [
                        'description'      => $catName . ' service offered by ' . ($provider->business_name ?? $provider->name),
                        'price'            => $provider->start_pricing ? (float)$provider->start_pricing : 500,
                        'duration_minutes' => 60,
                        'is_active'        => 1,
                    ]
                );
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Vendor registered successfully. Please login using your phone number to receive an OTP.',
            'data' => [
                'user' => $user,
                'provider' => $provider
            ]
        ], 201);
    }

    /**
     * Send OTP to vendor's phone number
     */
    public function sendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('phone', $request->phone)->where('role', 'vendor')->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'No vendor account found with this phone number.'
            ], 404);
        }

        // Generate a 6-digit OTP
        $otp = rand(100000, 999999);
        
        $user->update([
            'otp' => $otp,
            'otp_expires_at' => Carbon::now()->addMinutes(10)
        ]);

        // Send OTP via WhatsApp
        $token = env('INSIGN_SMS_TOKEN');
        $phoneNumberId = env('INSIGN_PHONE_NUMBER_ID');
        
        // Ensure phone number has country code for WhatsApp (default to 91 if length is 10)
        $cleanPhone = preg_replace('/[^0-9]/', '', $user->phone);
        if (strlen($cleanPhone) === 10) {
            $cleanPhone = '91' . $cleanPhone;
        }

        $payload = [
            "messaging_product" => "whatsapp",
            "recipient_type" => "individual",
            "to" => $cleanPhone,
            "type" => "template",
            "template" => [
                "name" => "verification",
                "language" => [
                    "code" => "en_US"
                ],
                "components" => [
                    [
                        "type" => "body",
                        "parameters" => [
                            [
                                "type" => "text",
                                "text" => (string) $otp
                            ]
                        ]
                    ],
                    [
                        "type" => "button",
                        "sub_type" => "url",
                        "index" => "0",
                        "parameters" => [
                            [
                                "type" => "text",
                                "text" => (string) $otp
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $url = "https://multichannel.insignsms.com/api/v1/whatsapp/{$phoneNumberId}/messages"; 
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer " . $token,
            "Content-Type: application/json"
        ]);

        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpcode !== 200) {
            \Illuminate\Support\Facades\Log::error('WhatsApp OTP Failed', ['response' => $response, 'httpcode' => $httpcode]);
            return response()->json([
                'status' => false,
                'message' => 'Failed to send OTP via WhatsApp. Please try again later.'
            ], 500);
        }

        return response()->json([
            'status' => true,
            'message' => 'OTP sent successfully to your registered WhatsApp number.'
        ]);
    }

    /**
     * Verify OTP and Login
     */
    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'otp' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('phone', $request->phone)->where('role', 'vendor')->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'No vendor account found with this phone number.'
            ], 404);
        }

        // Check if OTP is correct and not expired
        if ($user->otp !== $request->otp) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid OTP.'
            ], 400);
        }

        if (Carbon::now()->isAfter($user->otp_expires_at)) {
            return response()->json([
                'status' => false,
                'message' => 'OTP has expired. Please request a new one.'
            ], 400);
        }

        // OTP is valid, generate token
        $token = $user->createToken('VendorAuthToken')->plainTextToken;

        // Clear OTP
        $user->update([
            'otp' => null,
            'otp_expires_at' => null
        ]);

        $provider = Provider::where('user_id', $user->id)->first();

        return response()->json([
            'status' => true,
            'message' => 'Login successful',
            'token' => $token,
            'data' => [
                'user' => $user,
                'provider' => $provider
            ]
        ]);
    }
}
