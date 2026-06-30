<?php

namespace App\Services\V2;

use App\Models\User;
use App\Models\Pet;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ValidationService
{
    /**
     * Check if username is unique globally.
     *
     * @param string $username
     * @param int|null $excludeUserId
     * @return bool
     */
    public static function isUsernameUnique(string $username, ?int $excludeUserId = null): bool
    {
        $query = User::where('username', $username);

        if ($excludeUserId) {
            $query->where('id', '!=', $excludeUserId);
        }

        return !$query->exists();
    }

    /**
     * Validate username format.
     *
     * @param string $username
     * @return array ['valid' => bool, 'message' => string]
     */
    public static function validateUsernameFormat(string $username): array
    {
        // Username must be 3-30 characters
        if (strlen($username) < 3 || strlen($username) > 30) {
            return [
                'valid' => false,
                'message' => 'Username must be between 3 and 30 characters.'
            ];
        }

        // Username must start with a letter
        if (!preg_match('/^[a-zA-Z]/', $username)) {
            return [
                'valid' => false,
                'message' => 'Username must start with a letter.'
            ];
        }

        // Username can only contain letters, numbers, and underscores
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            return [
                'valid' => false,
                'message' => 'Username can only contain letters, numbers, and underscores.'
            ];
        }

        // Username cannot contain consecutive underscores
        if (strpos($username, '__') !== false) {
            return [
                'valid' => false,
                'message' => 'Username cannot contain consecutive underscores.'
            ];
        }

        // Username cannot start or end with an underscore
        if (preg_match('/^[_]|[_]$/', $username)) {
            return [
                'valid' => false,
                'message' => 'Username cannot start or end with an underscore.'
            ];
        }

        return [
            'valid' => true,
            'message' => 'Username is valid.'
        ];
    }

    /**
     * Check if pet unique ID is unique.
     *
     * @param string $petUniqueId
     * @param int|null $excludePetId
     * @return bool
     */
    public static function isPetUniqueIdUnique(string $petUniqueId, ?int $excludePetId = null): bool
    {
        $query = Pet::where('pet_unique_id', $petUniqueId);

        if ($excludePetId) {
            $query->where('id', '!=', $excludePetId);
        }

        return !$query->exists();
    }

    /**
     * Generate a unique pet ID.
     *
     * @return string
     */
    public static function generateUniquePetId(): string
    {
        do {
            $petId = 'PET' . strtoupper(Str::random(8));
        } while (!self::isPetUniqueIdUnique($petId));

        return $petId;
    }

    /**
     * Generate a unique user ID.
     *
     * @return string
     */
    public static function generateUniqueUserId(): string
    {
        do {
            $userId = 'USR' . strtoupper(Str::random(10));
        } while (User::where('user_id', $userId)->exists());

        return $userId;
    }

    /**
     * Validate phone number format.
     *
     * @param string $phone
     * @param string|null $countryCode
     * @return array ['valid' => bool, 'message' => string]
     */
    public static function validatePhoneNumber(string $phone, ?string $countryCode = null): array
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (empty($phone)) {
            return [
                'valid' => false,
                'message' => 'Phone number is required.'
            ];
        }

        // Check length (most phone numbers are between 7-15 digits)
        if (strlen($phone) < 7 || strlen($phone) > 15) {
            return [
                'valid' => false,
                'message' => 'Phone number must be between 7 and 15 digits.'
            ];
        }

        return [
            'valid' => true,
            'message' => 'Phone number is valid.',
            'normalized' => $phone
        ];
    }

    /**
     * Validate ownership - check if user owns the pet.
     *
     * @param int $userId
     * @param int $petId
     * @return bool
     */
    public static function validatePetOwnership(int $userId, int $petId): bool
    {

        return Pet::where('id', $petId)
            ->where('user_id', $userId)
            ->exists();
    }

    /**
     * Validate captcha answer.
     *
     * @param string $captchaAnswer
     * @param string $captchaKey
     * @return array ['valid' => bool, 'message' => string]
     */
    public static function validateCaptcha(string $captchaAnswer, string $captchaKey): array
    {
        // In a real implementation, this would verify against a stored captcha
        // For now, we'll use a simple cache-based implementation
        $storedAnswer = Cache::get('captcha_' . $captchaKey);

        if (!$storedAnswer) {
            return [
                'valid' => false,
                'message' => 'Captcha has expired. Please refresh and try again.'
            ];
        }

        if (strtolower(trim($captchaAnswer)) !== strtolower($storedAnswer)) {
            return [
                'valid' => false,
                'message' => 'Invalid captcha answer. Please try again.'
            ];
        }

        // Clear the captcha after successful validation
        Cache::forget('captcha_' . $captchaKey);

        return [
            'valid' => true,
            'message' => 'Captcha validated successfully.'
        ];
    }

    /**
     * Generate a captcha.
     *
     * @return array ['key' => string, 'question' => string]
     */
    public static function generateCaptcha(): array
    {
        $key = Str::random(16);

        // Simple math captcha
        $num1 = rand(1, 10);
        $num2 = rand(1, 10);
        $answer = $num1 + $num2;

        Cache::put('captcha_' . $key, (string) $answer, 300); // 5 minutes

        return [
            'key' => $key,
            'question' => "What is {$num1} + {$num2}?"
        ];
    }

    /**
     * Validate posted_by_type and posted_by_id ownership.
     *
     * @param int $authenticatedUserId
     * @param string $postedByType
     * @param int $postedById
     * @return array ['valid' => bool, 'message' => string]
     */
    public static function validatePostedBy(int $authenticatedUserId, string $postedByType, int $postedById): array
    {
        if (!in_array($postedByType, ['parent', 'pet'])) {
            return [
                'valid' => false,
                'message' => 'Invalid posted_by_type. Must be "parent" or "pet".'
            ];
        }

        if ($postedByType === 'parent') {
            // posted_by_id must equal authenticated user
            
            if ($postedById !== $authenticatedUserId) {
                return [
                    'valid' => false,
                    'message' => 'You can only post as yourself when posting as parent.'
                ];
            }
        } else {
            // pet - check ownership
            if (!self::validatePetOwnership($authenticatedUserId, $postedById)) {
                return [
                    'valid' => false,
                    'message' => 'You can only post as pets that belong to you.'
                ];
            }
        }

        return [
            'valid' => true,
            'message' => 'Posted by validation successful.'
        ];
    }

    /**
     * Validate input data for sanitization.
     *
     * @param array $data
     * @return array Sanitized data
     */
    public static function sanitizeInput(array $data): array
    {
        $sanitized = [];

        foreach ($data as $key => $value) {
            if (is_string($value)) {
                // Trim whitespace
                $value = trim($value);
                // Remove null bytes
                $value = str_replace("\0", '', $value);
                // Basic XSS prevention
                $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            }
            $sanitized[$key] = $value;
        }

        return $sanitized;
    }
    
    public static function validatePetIdFormat(string $petId): array
    {
        // Pet ID must be 3-20 characters
        if (strlen($petId) < 3 || strlen($petId) > 20) {
            return [
                'valid' => false,
                'message' => 'Pet ID must be between 3 and 20 characters.'
            ];
        }

        // Pet ID must start with a letter
        if (!preg_match('/^[a-zA-Z]/', $petId)) {
            return [
                'valid' => false,
                'message' => 'Pet ID must start with a letter.'
            ];
        }

        // Pet ID can only contain letters, numbers, and hyphens/underscores
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $petId)) {
            return [
                'valid' => false,
                'message' => 'Pet ID can only contain letters, numbers, hyphens, and underscores.'
            ];
        }

        return [
            'valid' => true,
            'message' => 'Pet ID format is valid.'
        ];
    }
}
