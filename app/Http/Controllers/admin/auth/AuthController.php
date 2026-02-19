<?php

namespace App\Http\Controllers\admin\auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role; // Roles model
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;

class Authcontroller extends Controller
{
    public function login()
    {
        if (Auth::check() && Auth::user()) {
            return $this->redirectToDashboard(Auth::user());
        }
        return view('admin.auth.login');
    }

    public function loginSubmit(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        if (auth()->attempt($request->only('email', 'password'))) {
            $user = auth()->user();
            
            // Check if user is active
            if (isset($user->status) && $user->status != 'active') {
                auth()->logout();
                return redirect('/admin/login')->with('error', 'Your account is inactive.');
            }

            // Get user's role from role_id
            $role = $this->getUserRole($user->role_id);
            
            if (!$role) {
                auth()->logout();
                return redirect('/admin/login')->with('error', 'Invalid user role.');
            }

            // Check if role is allowed to access admin panel
            $allowedRoles = $this->getAllowedRoles();
            
            if (in_array($role->name, $allowedRoles)) {
                Log::info('Login successful: ' . $user->email . ' | Role: ' . $role->name);
                
                // Store role name in session for easy access
                session(['user_role' => $role->name]);
                session(['role_permissions' => $role->permissions ?? []]);
                
                return $this->redirectToDashboard($user, $role);
            } else {
                auth()->logout();
                return redirect('/admin/login')->with('error', 'Unauthorized access. You do not have permission to access admin panel.');
            }
        }
        
        return redirect('/admin/login')->with('error', 'Invalid credentials');
    }

    /**
     * Get user role from role_id
     */
    private function getUserRole($roleId)
    {
        // Cache karein for better performance
        return Role::find($roleId);
        
        // Ya fir User model mein relationship use karein
        // return $user->role; // Agar relationship hai
    }

    /**
     * Get allowed roles for admin panel
     */
    private function getAllowedRoles()
    {
        // Database se allowed roles fetch karein
        // Ya fir config mein define karein
        return ['admin', 'vendor', 'user'];
        
        // Alternative: Database se fetch
        // return Role::where('can_access_admin', 1)->pluck('name')->toArray();
    }

    /**
     * Redirect user to appropriate dashboard based on role
     */
    private function redirectToDashboard($user, $role = null)
    {
        if (!$role) {
            $role = $this->getUserRole($user->role_id);
        }
        
        $dashboardRoutes = [
            'admin' => 'admin.dashboard',
            'vendor' => 'admin.dashboard', // Ya fir 'vendor.dashboard'
            'user' => 'admin.dashboard',   // Ya fir 'user.dashboard'
        ];
        
        if (isset($dashboardRoutes[$role->name])) {
            return redirect()->route($dashboardRoutes[$role->name]);
        }
        
        // Default dashboard
        return redirect()->route('admin.dashboard');
    }

    public function logout()
    {
        // Clear session data
        session()->forget(['user_role', 'role_permissions']);
        
        Auth::logout();
        return redirect('/admin/login')->with('success', 'Logged out successfully');
    }
}