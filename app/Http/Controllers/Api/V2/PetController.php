<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\Pet;
use App\Services\V2\ValidationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class PetController extends Controller
{
    /**
     * Display a listing of the user's pets (V2).
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            $pets = Pet::where('user_id', $user->id)->get();

            return response()->json([
                'success' => true,
                'message' => 'Pets successfully fetched!',
                'data' => $pets->map(fn($pet) => $this->formatPetResponse($pet))
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve pets.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a new pet (V2 Enhanced).
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $user = $request->user();

            $validator = Validator::make($request->all(), [
                'pet_name' => 'required|string|max:255',
                'pet_unique_id' => 'nullable|string|max:50|unique:pets,pet_unique_id',
                'breed' => 'nullable|string|max:255',
                'age' => 'nullable|integer|min:0|max:50',
                'gender' => 'nullable|in:male,female,unknown',
                'blood_group' => 'nullable|string|max:10',
                'microchip_number' => 'nullable|string|max:100|unique:pets,microchip_number',
                'insurance_number' => 'nullable|string|max:100',
                'insurance_provider' => 'nullable|string|max:255',
                'govt_license_number' => 'nullable|string|max:100',
                'dob' => 'nullable|date',
                'bio' => 'nullable|string|max:1000',
                'avatar' => 'nullable|file|image|mimes:jpeg,png,jpg,webp|max:5120',
                'type' => 'nullable|string|max:100',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validated = $validator->validated();

            // Generate unique pet ID if not provided
            $petUniqueId = $validated['pet_unique_id'] ?? ValidationService::generateUniquePetId();

            // Ensure generated ID is unique
            if (!ValidationService::isPetUniqueIdUnique($petUniqueId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pet unique ID is already taken.'
                ], 422);
            }

            // Handle avatar upload
            $avatarPath = null;
            if ($request->hasFile('avatar')) {
                $avatarPath = $this->uploadPetAvatar($request->file('avatar'));
            }

            // Sanitize inputs
            $sanitized = ValidationService::sanitizeInput([
                'pet_name' => $validated['pet_name'],
                'breed' => $validated['breed'] ?? null,
                'blood_group' => $validated['blood_group'] ?? null,
                'microchip_number' => $validated['microchip_number'] ?? null,
                'insurance_number' => $validated['insurance_number'] ?? null,
                'insurance_provider' => $validated['insurance_provider'] ?? null,
                'govt_license_number' => $validated['govt_license_number'] ?? null,
                'bio' => $validated['bio'] ?? null,
                'type' => $validated['type'] ?? 'dog',
            ]);

            // Create pet
            $pet = Pet::create([
                'user_id' => $user->id,
                'pet_unique_id' => $petUniqueId,
                'name' => $sanitized['pet_name'],
                'breed' => $sanitized['breed'],
                'age' => $validated['age'] ?? null,
                'gender' => $validated['gender'] ?? 'unknown',
                'blood_group' => $sanitized['blood_group'],
                'microchip_number' => $sanitized['microchip_number'],
                'insurance_number' => $sanitized['insurance_number'],
                'insurance_provider' => $sanitized['insurance_provider'],
                'govt_license_number' => $sanitized['govt_license_number'],
                'dob' => $validated['dob'] ?? null,
                'bio' => $sanitized['bio'],
                'avatar' => $avatarPath,
                'type' => $sanitized['type'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pet registered successfully',
                'data' => $this->formatPetResponse($pet)
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to register pet. Please try again.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified pet.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $pet = Pet::find($id);

            if (!$pet) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pet not found'
                ], 404);
            }

            // Get summary of records (counts)
            $recordSummary = [
                'vaccines' => \App\Models\VaccineRecord::where('pet_id', $id)->count(),
                'weights' => \App\Models\WeightRecord::where('pet_id', $id)->count(),
                'meals' => \App\Models\MealRecord::where('pet_id', $id)->count(),
                'grooming' => \App\Models\GroomingRecord::where('pet_id', $id)->count(),
                'deworming' => \App\Models\DewormingRecord::where('pet_id', $id)->count(),
                'general' => \App\Models\GeneralRecord::where('pet_id', $id)->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => array_merge($this->formatPetResponse($pet), [
                    'record_summary' => $recordSummary
                ])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve pet.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified pet.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {

        try {
            $user = $request->user();
            $pet = Pet::find($id);
            if (!$pet) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pet not found'
                ], 404);
            }

            // Validate ownership
            if (!ValidationService::validatePetOwnership($user->id, $pet->id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to update this pet.'
                ], 403);
            }

            $validator = Validator::make($request->all(), [
                'pet_name' => 'sometimes|string|max:255',
                'pet_unique_id' => 'sometimes|string|max:50|unique:pets,pet_unique_id,' . $id,
                'breed' => 'nullable|string|max:255',
                'age' => 'nullable|integer|min:0|max:50',
                'gender' => 'nullable|in:male,female,unknown',
                'blood_group' => 'nullable|string|max:10',
                'microchip_number' => 'nullable|string|max:100|unique:pets,microchip_number,' . $id,
                'insurance_number' => 'nullable|string|max:100',
                'insurance_provider' => 'nullable|string|max:255',
                'govt_license_number' => 'nullable|string|max:100',
                'dob' => 'nullable|date',
                'bio' => 'nullable|string|max:1000',
                'avatar' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
                'type' => 'nullable|string|max:100',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validated = $validator->validated();

            // Debug: check if request is empty (often happens with PUT + multipart/form-data)

            if (empty($request->all())) {
                return response()->json([
                    'success' => false,
                    'message' => 'Request data is empty. If you are using multipart/form-data with PUT, please use POST method and add _method=PUT field.',
                ], 400);
            }

            // Sanitize all validated inputs
            $sanitized = ValidationService::sanitizeInput($validated);

            // Update pet
            $updateData = [];

            // Map pet_name to name
            if (array_key_exists('pet_name', $sanitized)) {
                $updateData['name'] = $sanitized['pet_name'];
            }

            // List of fields to update directly
            $fields = [
                'pet_unique_id', 'breed', 'age', 'gender', 'blood_group',
                'microchip_number', 'insurance_number', 'insurance_provider',
                'govt_license_number', 'dob', 'bio', 'type'
            ];

            foreach ($fields as $field) {
                if (array_key_exists($field, $sanitized)) {
                    $updateData[$field] = $sanitized[$field];
                }
            }

            // Handle avatar upload
            if ($request->hasFile('avatar')) {
                if ($pet->avatar) {
                    $oldPath = public_path($pet->avatar);
                    if (File::exists($oldPath)) {
                        File::delete($oldPath);
                    }
                }
                $updateData['avatar'] = $this->uploadPetAvatar($request->file('avatar'));
            }

            // If no data was processed for update, return an error
            if (empty($updateData)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No valid fields provided for update. Available fields: pet_name, breed, age, gender, blood_group, microchip_number, insurance_number, insurance_provider, govt_license_number, dob, bio, type, avatar.',
                    'received_data' => $request->all()
                ], 422);
            }

            $pet->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Pet updated successfully',
                'data' => $this->formatPetResponse($pet->fresh())
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update pet.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified pet.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $id)
    {
        try {
            $user = $request->user();
            $pet = Pet::find($id);

            if (!$pet) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pet not found'
                ], 404);
            }

            // Validate ownership
            if (!ValidationService::validatePetOwnership($user->id, $pet->id)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to delete this pet.'
                ], 403);
            }

            $pet->delete();

            return response()->json([
                'success' => true,
                'message' => 'Pet deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete pet.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Format pet response.
     *
     * @param Pet $pet
     * @return array
     */
    /**
     * Upload and resize a pet avatar image.
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @return string  Relative path stored in DB
     */
    protected function uploadPetAvatar($file): string
    {
        $manager = new ImageManager(new Driver());
        $filename = uniqid('pet_') . '.' . $file->getClientOriginalExtension();
        $dir = public_path('uploads/pets');

        if (!File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        $manager->read($file)->scale(width: 400)->save($dir . '/' . $filename);

        return 'uploads/pets/' . $filename;
    }

    /**
     * Update only the pet's avatar picture (V2).
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateAvatar(Request $request, $id)
    {
        try {
            $user = $request->user();
            $pet = Pet::find($id);

            if (!$pet) {
                return response()->json(['success' => false, 'message' => 'Pet not found'], 404);
            }

            if (!ValidationService::validatePetOwnership($user->id, $pet->id)) {
                return response()->json(['success' => false, 'message' => 'You do not have permission to update this pet.'], 403);
            }

            $validator = Validator::make($request->all(), [
                'avatar' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Delete old avatar
            if ($pet->avatar) {
                $oldPath = public_path($pet->avatar);
                if (File::exists($oldPath)) {
                    File::delete($oldPath);
                }
            }

            $avatarPath = $this->uploadPetAvatar($request->file('avatar'));
            $pet->update(['avatar' => $avatarPath]);

            return response()->json([
                'success' => true,
                'message' => 'Pet avatar updated successfully',
                'data' => [
                    'avatar' => $avatarPath,
                    'avatar_url' => url($avatarPath),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update pet avatar.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Format pet response.
     *
     * @param Pet $pet
     * @return array
     */
    protected function formatPetResponse(Pet $pet): array
    {
        return [
            'id' => $pet->id,
            'pet_unique_id' => $pet->pet_unique_id,
            'name' => $pet->name,
            'breed' => $pet->breed,
            'age' => $pet->age,
            'gender' => $pet->gender,
            'blood_group' => $pet->blood_group,
            'microchip_number' => $pet->microchip_number,
            'insurance_number' => $pet->insurance_number,
            'insurance_provider' => $pet->insurance_provider,
            'govt_license_number' => $pet->govt_license_number,
            'dob' => $pet->dob,
            'bio' => $pet->bio,
            'avatar' => $pet->avatar,
            'avatar_url' => $pet->avatar ? url($pet->avatar) : null,
            'type' => $pet->type,
            'user_id' => $pet->user_id,
            'created_at' => $pet->created_at,
            'updated_at' => $pet->updated_at,
        ];
    }
    
    public function checkPetId(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pet_unique_id' => 'required|string|min:3|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'available' => false,
                'message' => 'Invalid Pet ID format.',
                'errors' => $validator->errors()
            ], 422);
        }

        $petId = trim($request->query('pet_unique_id'));
        
        // Use ValidationService to check format and uniqueness
        $formatCheck = ValidationService::validatePetIdFormat($petId);
        if (!$formatCheck['valid']) {
            return response()->json([
                'available' => false,
                'message' => $formatCheck['message']
            ]);
        }

        $isUnique = ValidationService::isPetUniqueIdUnique($petId);

        if ($isUnique) {
            return response()->json([
                'available' => true,
                'message' => 'Pet ID is available'
            ]);
        }

        return response()->json([
            'available' => false,
            'message' => 'Pet ID already exists'
        ]);
    }
}
