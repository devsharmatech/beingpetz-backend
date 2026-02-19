<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\UserCreatedMail;

class UserVendorController extends Controller
{
    public function index()
    {
        $users = User::whereIn('role', ['user', 'vendor'])
            ->latest()
            ->paginate(10);
        
        return view('admin.uservendors.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::all();
        return view('admin.uservendors.create' , compact('roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:15',
            'role_id' => 'required|exists:roles,id', 
            'city' => 'nullable|string',
            'state' => 'nullable|string',
            'locality' => 'nullable|string',
            'permissions' => 'nullable|array',
        ]);

        $role = Role::find($request->role_id);
        if (!$role) {
            return redirect()->back()
                ->with('error', 'Selected role not found.')
                ->withInput();
        }

        $password = Str::random(10);
        
        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($password),
            'role' => $role->name, 
            'role_id' => $request->role_id, 
            'city' => $request->city,
            'state' => $request->state,
            'locality' => $request->locality,
            'isComplete' => false,
            'permissions' => $request->permissions ?? [],
        ]);

        try {
            Mail::to($user->email)->send(new UserCreatedMail($user, $password));
            
            return redirect()->route('admin.uservendors.index')
                ->with('success', 'User/Vendor created successfully. Password sent to email.');
                
        } catch (\Exception $e) {
            Log::error('Email sending failed: ' . $e->getMessage());
            
            return redirect()->route('admin.uservendors.index')
                ->with('warning', 'User/Vendor created successfully but email sending failed.');
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'phone' => 'required|string|max:15',
            'role_id' => 'required|exists:roles,id',
            'city' => 'nullable|string',
            'state' => 'nullable|string',
            'locality' => 'nullable|string',
            'permissions' => 'nullable|array',
        ]);

        $user = User::findOrFail($id);
        
        // Role का name पाने के लिए
        $role = Role::find($request->role_id);
        if (!$role) {
            return redirect()->back()
                ->with('error', 'Selected role not found.')
                ->withInput();
        }

        $user->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'role' => $role->name, // Role का name update करें
            'role_id' => $request->role_id, // Role की ID update करें
            'city' => $request->city,
            'state' => $request->state,
            'locality' => $request->locality,
            'permissions' => $request->permissions ?? [],
        ]);

        return redirect()->route('admin.uservendors.index')
            ->with('success', 'User/Vendor updated successfully.');
    }

    // public function show(string $id)
    // {
    //     $user = User::whereIn('role', ['user', 'vendor'])->findOrFail($id);
        
    //     return view('admin.uservendors.show', compact('user'));
    // }
    public function show(string $id)
    {
        $user = User::whereIn('role', ['user', 'vendor'])
                    ->with('roleRelation')
                    ->findOrFail($id);
        
        $rolePermissions = [];
        if ($user->roleRelation && $user->roleRelation->default_permissions) {
         
            $defaultPermissions = $user->roleRelation->default_permissions;
            
            if (is_array($defaultPermissions)) {
                $rolePermissions = $defaultPermissions;
            } elseif (is_string($defaultPermissions)) {
                $decoded = json_decode($defaultPermissions, true);
                $rolePermissions = is_array($decoded) ? $decoded : [];
            }
        }
        
        $userPermissions = [];
        if ($user->permissions) {
            if (is_array($user->permissions)) {
                $userPermissions = $user->permissions;
            } elseif (is_string($user->permissions)) {
                $decoded = json_decode($user->permissions, true);
                $userPermissions = is_array($decoded) ? $decoded : [];
            }
        }
        
        $allPermissions = array_unique(array_merge($rolePermissions, $userPermissions));
        
        return view('admin.uservendors.show', compact('user', 'rolePermissions', 'userPermissions', 'allPermissions'));
    }

    public function edit(string $id)
    {
        $roles = Role::all();
        $user = User::whereIn('role', ['user', 'vendor'])->findOrFail($id);
        
        return view('admin.uservendors.edit', compact('user','roles'));
    }


    public function destroy(string $id)
    {
        $user = User::whereIn('role', ['user', 'vendor'])->findOrFail($id);
        $user->delete();

        return redirect()->route('admin.uservendors.index')
            ->with('success', 'User/Vendor deleted successfully.');
    }

    public function resetPassword(Request $request, $id)
    {
        $user = User::whereIn('role', ['user', 'vendor'])->findOrFail($id);
        $newPassword = Str::random(10);
        
        $user->update([
            'password' => Hash::make($newPassword)
        ]);

        try {
            Mail::to($user->email)->send(new UserCreatedMail($user, $newPassword));
            
            return redirect()->back()
                ->with('success', 'Password reset successfully. New password sent to email.');
                
        } catch (\Exception $e) {
            Log::error('Password reset email failed: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('warning', 'Password reset but email sending failed.');
        }
    }
}