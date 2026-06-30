<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Pet;
use App\Models\V2\V2Post;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * Get aggregated profile data for a user.
     *
     * @param Request $request
     * @param int $user_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, $user_id)
    {
        // dd($user_id);
        try {
            $user = User::find($user_id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            // Get user profile
            $profile = $this->getUserProfile($user);

            // Get user's pets
            $pets = $this->getUserPets($user->id);

            // Get parent posts (posted by user as parent)
            $parentPosts = $this->getParentPosts($user->id);

            // Get pet posts grouped by pet
            $petPosts = $this->getPetPostsGrouped($user->id);

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => $profile,
                    'pets' => $pets,
                    'parent_posts' => $parentPosts,
                    'pet_posts' => $petPosts,
                    'stats' => [
                        'total_pets' => count($pets),
                        'total_parent_posts' => count($parentPosts),
                        'total_pet_posts' => array_sum(array_map('count', $petPosts)),
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve profile.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get current user's profile.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function me(Request $request)
    {

        return $this->show($request, $request->user()->id);
    }

    /**
     * Search for users and pets.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        try {
            $queryStr = trim($request->input('query'));
            $perPage = $request->input('per_page', 20);

            if (empty($queryStr)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Search query is required.'
                ], 422);
            }

            // Search Users
            $users = User::where('isComplete', 1)
                ->where(function ($q) use ($queryStr) {
                    $q->where('first_name', 'LIKE', "%{$queryStr}%")
                      ->orWhere('last_name', 'LIKE', "%{$queryStr}%")
                      ->orWhere('username', 'LIKE', "%{$queryStr}%");
                })
                ->limit(20)
                ->get();

            // Search Pets
            $pets = Pet::where(function ($q) use ($queryStr) {
                    $q->where('name', 'LIKE', "%{$queryStr}%")
                      ->orWhere('pet_unique_id', 'LIKE', "%{$queryStr}%");
                })
                ->limit(20)
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'users' => $users->map(function (User $u) {
                        return $this->getUserProfile($u);
                    }),
                    'pets' => $pets->map(function ($p) {
                        return [
                            'id' => $p->id,
                            'name' => $p->name,
                            'pet_unique_id' => $p->pet_unique_id,
                            'breed' => $p->breed,
                            'avatar_url' => $p->avatar ? url($p->avatar) : null,
                            'type' => $p->type,
                        ];
                    }),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Search failed.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user profile data.
     *
     * @param User $user
     * @return array
     */
    protected function getUserProfile(User $user): array
    {
        return [
            'id' => $user->id,
            'user_id' => $user->user_id,
            'name' => $user->first_name . ' ' . $user->last_name,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'username' => $user->username,
            'phone' => $user->phone,
            'country_code' => $user->country_code,
            'email' => $user->email,
            'profile' => $user->profile,
            'latitude' => $user->latitude,
            'longitude' => $user->longitude,
            'city' => $user->city,
            'state' => $user->state,
            'locality' => $user->locality,
            'created_at' => $user->created_at,
        ];
    }

    /**
     * Get user's pets.
     *
     * @param int $userId
     * @return array
     */
    protected function getUserPets(int $userId): array
    {
        $pets = Pet::where('user_id', $userId)->get();

        return $pets->map(function ($pet) {
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
                'type' => $pet->type,
                'created_at' => $pet->created_at,
            ];
        })->toArray();
    }

    /**
     * Get parent posts (posted by user as parent).
     *
     * @param int $userId
     * @return array
     */
    protected function getParentPosts(int $userId): array
    {
        $posts = V2Post::where('posted_by_type', 'parent')
            ->where('posted_by_id', $userId)
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return $posts->map(function (V2Post $post) {
            return $this->formatPostResponse($post);
        })->toArray();
    }

    /**
     * Get pet posts grouped by pet.
     *
     * @param int $userId
     * @return array
     */
    protected function getPetPostsGrouped(int $userId): array
    {
        // Get user's pets
        $petIds = Pet::where('user_id', $userId)->pluck('id')->toArray();

        if (empty($petIds)) {
            return [];
        }

        $groupedPosts = [];

        foreach ($petIds as $petId) {
            $posts = V2Post::where('posted_by_type', 'pet')
                ->where('posted_by_id', $petId)
                ->where('status', 'active')
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->get();

            if ($posts->isNotEmpty()) {
                $groupedPosts['pet_' . $petId] = $posts->map(function (V2Post $post) {
                    return $this->formatPostResponse($post);
                })->toArray();
            }
        }

        return $groupedPosts;
    }

    /**
     * Format post response.
     *
     * @param V2Post $post
     * @return array
     */
    protected function formatPostResponse(V2Post $post): array
    {
        return [
            'id' => $post->id,
            'posted_by_type' => $post->posted_by_type,
            'posted_by_id' => $post->posted_by_id,
            'content' => $post->content,
            'media_urls' => $post->media_urls,
            'likes_count' => $post->likes()->count(),
            'comments_count' => $post->comments()->where('status', 'active')->count(),
            'shares_count' => $post->shares()->count(),
            'reposts_count' => $post->reposts()->count(),
            'created_at' => $post->created_at,
        ];
    }
}
