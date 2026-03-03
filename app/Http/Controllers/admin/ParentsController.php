<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class ParentsController extends Controller
{
    // public function index()
    // {
    //     $parents = User::where('role', 'parent')
    //         ->withCount('pets') // This will add a 'pets_count' attribute
    //         ->get();
    //     return view('admin.parents.index', compact('parents'));
    // }

    

    public function index(Request $request)
    {
        $baseQuery = User::where('role', 'parent')->where('deleted_at', 1);
        
        $query = (clone $baseQuery)->withCount('pets');
        
        // Apply filters...
        $filter = $request->get('filter', 'all');
        
        if ($filter === 'daily') {
            $query->where('last_login', '>=', now()->subDay());
        } elseif ($filter === 'weekly') {
            $query->where('last_login', '>=', now()->subWeek());
        } elseif ($filter === 'monthly') {
            $query->where('last_login', '>=', now()->subMonth());
        }
        
        $parents = $query->get();
        
        // Get counts using base query
        $totalUsersCount = $baseQuery->count();
        $dailyActiveCount = (clone $baseQuery)->where('last_login', '>=', now()->subDay())->count();
        $weeklyActiveCount = (clone $baseQuery)->where('last_login', '>=', now()->subWeek())->count();
        $monthlyActiveCount = (clone $baseQuery)->where('last_login', '>=', now()->subMonth())->count();
        
        return view('admin.parents.index', compact(
            'parents', 
            'filter',
            'totalUsersCount',
            'dailyActiveCount', 
            'weeklyActiveCount', 
            'monthlyActiveCount'
        ));
    }

    public function create()
    {
        return view('admin.parents.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|unique:users,phone',
            'locality' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'password' => 'required|string|min:8|confirmed',
            'profile' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

        $parentData = $request->except('password', 'password_confirmation', 'profile');
        $parentData['password'] = Hash::make($request->password);
        $parentData['role'] = 'parent';

        if ($request->hasFile('profile')) {
            // Save to public/uploads/profile directory
            $profileImage = $request->file('profile');
            $imageName = time() . '_' . uniqid() . '.' . $profileImage->getClientOriginalExtension();
            $profileImage->move(public_path('uploads/profile'), $imageName);
            $parentData['profile'] = 'uploads/profile/' . $imageName;
        }

        User::create($parentData);

        return redirect()->route('admin.parents.index')->with('success', 'Parent created successfully.');
    }

    public function show(string $id)
    {
        $parent = User::where('role', 'parent')->findOrFail($id);
        return view('admin.parents.show', compact('parent'));
    }

    public function edit(string $id)
    {
        $parent = User::where('role', 'parent')->findOrFail($id);
        return view('admin.parents.edit', compact('parent'));
    }

    public function update(Request $request, string $id)
    {
        $parent = User::where('role', 'parent')->findOrFail($id);

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => [
                'required',
                'email',
                \Illuminate\Validation\Rule::unique('users', 'email')->ignore($id)->where(function ($query) {
                    return $query->where('deleted_at', 1);
                })
            ],
            'phone' => [
                'nullable',
                'string',
                \Illuminate\Validation\Rule::unique('users', 'phone')->ignore($id)->where(function ($query) {
                    return $query->where('deleted_at', 1);
                })
            ],
            'locality' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'password' => 'nullable|string|min:8|confirmed',
            'profile' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

        $oldEmail = $parent->email;
        $newEmail = $request->email;
        $parentData = $request->except('password', 'password_confirmation', 'profile');

        if ($request->filled('password')) {
            $parentData['password'] = Hash::make($request->password);
        }

        if ($request->hasFile('profile')) {
            // Delete old profile image if exists
            if ($parent->profile && file_exists(public_path($parent->profile))) {
                unlink(public_path($parent->profile));
            }

            // Save new profile image to public/uploads/profile directory
            $profileImage = $request->file('profile');
            $imageName = time() . '_' . uniqid() . '.' . $profileImage->getClientOriginalExtension();
            $profileImage->move(public_path('uploads/profile'), $imageName);
            $parentData['profile'] = 'uploads/profile/' . $imageName;
        }

        $parent->update($parentData);

        // If email was changed, send notification mail
        if ($oldEmail !== $newEmail) {
            try {
                Mail::raw("Your Beingpetz account email has been updated to $newEmail. If you didn't do this, please contact support.", function ($message) use ($newEmail) {
                    $message->to($newEmail)->subject('Email Updated - Beingpetz');
                });
            } catch (\Exception $e) {
                \Log::error('Parent email update notification failed: ' . $e->getMessage());
            }
        }

        return redirect()->route('admin.parents.index')->with('success', 'Parent updated successfully.');
    }

    public function destroy(string $id)
    {
        $parent = User::where('role', 'parent')->findOrFail($id);

        $parent->deleted_at = 0;   // 0 = deleted
        $parent->save();

        return redirect()->route('admin.parents.index')
                        ->with('success', 'Parent deleted successfully.');
    }


 
public function exportCSV(Request $request)
{
    $filter = $request->get('filter');
    
    // Basic query - without pets count first
    $query = User::where('role', 'parent');
    
    // Apply activity filters
    if ($filter === 'daily') {
        $query->where('last_login', '>=', Carbon::now()->subDay());
    } elseif ($filter === 'weekly') {
        $query->where('last_login', '>=', Carbon::now()->subWeek());
    } elseif ($filter === 'monthly') {
        $query->where('last_login', '>=', Carbon::now()->subMonth());
    }

    $parents = $query->get();

    $fileName = 'parents_' . date('Y-m-d_H-i-s') . '.csv';

    $headers = [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
    ];

    $callback = function() use ($parents) {
        $file = fopen('php://output', 'w');
        
        // Add BOM for UTF-8
        fputs($file, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));
        
        // CSV Headers
        fputcsv($file, [
            'ID',
            'First Name',
            'Last Name',
            'Email',
            'Phone',
            'Locality',
            'City',
            'State',
            'Pets Count',
            'Last Active',
            'Last Activity Details',
            'Created Date',
            'Status'
        ]);

        // CSV Data
        foreach ($parents as $parent) {
            // Manual pets count - safe approach
            $petsCount = 0;
            try {
                // Try different possible foreign key names
                if (\Schema::hasColumn('pets', 'parent_id')) {
                    $petsCount = \DB::table('pets')->where('parent_id', $parent->id)->count();
                } elseif (\Schema::hasColumn('pets', 'user_id')) {
                    $petsCount = \DB::table('pets')->where('user_id', $parent->id)->count();
                } elseif (\Schema::hasColumn('pets', 'owner_id')) {
                    $petsCount = \DB::table('pets')->where('owner_id', $parent->id)->count();
                }
            } catch (\Exception $e) {
                // If any error, set pets count to 0
                $petsCount = 0;
            }

            $status = 'Inactive';
            $lastActive = $parent->last_active_at ?? $parent->last_login;
            if ($lastActive) {
                if ($lastActive->gt(Carbon::now()->subDay())) {
                    $status = 'Daily Active';
                } elseif ($lastActive->gt(Carbon::now()->subWeek())) {
                    $status = 'Weekly Active';
                } elseif ($lastActive->gt(Carbon::now()->subMonth())) {
                    $status = 'Monthly Active';
                }
            }

            fputcsv($file, [
                $parent->id,
                $parent->first_name,
                $parent->last_name ?? 'N/A',
                $parent->email,
                $parent->phone ?? 'N/A',
                $parent->locality ?? 'N/A',
                $parent->city ?? 'N/A',
                $parent->state ?? 'N/A',
                $petsCount,
                $lastActive ? $lastActive->format('Y-m-d H:i:s') : 'Never',
                $parent->last_activity_details ?? 'Logged in',
                $parent->created_at->format('Y-m-d H:i:s'),
                $status
            ]);
        }

        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}

}