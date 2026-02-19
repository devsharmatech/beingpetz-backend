<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    public function profile()
    {
        $id = Auth::id();
        $user = User::find($id);
        return view('admin.settings.profile', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'locality' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Update user data
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->city = $request->city;
        $user->state = $request->state;
        $user->locality = $request->locality;

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully!'
        ]);
    }


    public function updateAvatar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $user = Auth::user();

        // Delete old profile photo if it exists
        if ($user->profile && file_exists(public_path($user->profile))) {
            unlink(public_path($user->profile));
        }

        // Store new avatar in public/uploads/profile/
        $avatar = $request->file('avatar');
        $avatarName = time() . '_' . uniqid() . '.' . $avatar->getClientOriginalExtension();
        $avatar->move(public_path('uploads/profile'), $avatarName);

        // Update user record
        $user->profile = 'uploads/profile/' . $avatarName;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Avatar updated successfully!',
            'avatar_url' => asset($user->profile),
        ]);
    }



     public function changePassword(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'current_password'      => 'required',
            'new_password'          => 'required|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors()
            ]);
        }

        $user = Auth::user();

        // Check current password
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect.'
            ]);
        }

        // Update password
        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully!'
        ]);
    }
}
