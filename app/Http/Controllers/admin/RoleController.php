<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RoleController extends Controller
{
    
    public function index()
    {
        $roles = Role::with('permissions')->latest()->paginate(10);
        $permissions = Permission::
            get()
            ->groupBy('module');
            
        return view('admin.roles.index', compact('roles', 'permissions'));
    }

    
    public function create()
    {
        $permissions = Permission::get()
            ->groupBy('module');
            
        return view('admin.roles.create', compact('permissions'));
    }

   
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:roles',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:50',
            'icon' => 'nullable|string|max:100',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
            'can_access_admin' => 'boolean',
            'is_default' => 'boolean',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();
        try {
            $role = Role::create([
                'name' => $request->name,
                'display_name' => $request->display_name,
                'description' => $request->description,
                'color' => $request->color,
                'icon' => $request->icon ?? 'fas fa-user',
                'can_access_admin' => $request->boolean('can_access_admin'),
                'is_default' => $request->boolean('is_default'),
                'is_active' => $request->boolean('is_active', true)
            ]);

            if ($request->has('permissions')) {
                $role->permissions()->sync($request->permissions);
                
                // Store permissions in default_permissions
                $permissionNames = Permission::whereIn('id', $request->permissions)
                    ->pluck('name')
                    ->toArray();
                $role->update(['default_permissions' => $permissionNames]);
            }

            DB::commit();
            
            return redirect()->route('admin.roles.index')
                ->with('success', 'Role created successfully!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error creating role: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show($id)
    {
        $role = Role::with(['permissions', 'users'])->findOrFail($id);
        return view('admin.roles.show', compact('role'));
    }

    public function edit($id)
    {
        $role = Role::with('permissions')->findOrFail($id);
        $permissions = Permission::get()
            ->groupBy('module');
        
        return view('admin.roles.edit', compact('role', 'permissions'));
    }

    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:roles,name,' . $id,
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:50',
            'icon' => 'nullable|string|max:100',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
            'can_access_admin' => 'boolean',
            'is_default' => 'boolean',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();
        try {
            $role->update([
                'name' => $request->name,
                'display_name' => $request->display_name,
                'description' => $request->description,
                'color' => $request->color,
                'icon' => $request->icon ?? 'fas fa-user',
                'can_access_admin' => $request->boolean('can_access_admin'),
                'is_default' => $request->boolean('is_default'),
                'is_active' => $request->boolean('is_active')
            ]);

            if ($request->has('permissions')) {
                $role->permissions()->sync($request->permissions);
                
                // Update default_permissions
                $permissionNames = Permission::whereIn('id', $request->permissions)
                    ->pluck('name')
                    ->toArray();
                $role->update(['default_permissions' => $permissionNames]);
            } else {
                $role->permissions()->detach();
                $role->update(['default_permissions' => null]);
            }

            DB::commit();
            
            return redirect()->route('admin.roles.index')
                ->with('success', 'Role updated successfully!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error updating role: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy($id)
    {
        $role = Role::findOrFail($id);
        
        // Check if role is assigned to users
        if ($role->users()->count() > 0) {
            return redirect()->route('admin.roles.index')
                ->with('error', 'Cannot delete role. It is assigned to ' . $role->users()->count() . ' user(s).');
        }

        // Prevent deletion of system roles
        if (in_array($role->slug, ['admin', 'super-admin'])) {
            return redirect()->route('admin.roles.index')
                ->with('error', 'Cannot delete system roles.');
        }

        DB::beginTransaction();
        try {
            // Detach all permissions first
            $role->permissions()->detach();
            $role->delete();
            
            DB::commit();
            
            return redirect()->route('admin.roles.index')
                ->with('success', 'Role deleted successfully!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.roles.index')
                ->with('error', 'Error deleting role: ' . $e->getMessage());
        }
    }

    public function getRoles(Request $request)
    {
        $roles = Role::select('id', 'name', 'display_name', 'slug')
            ->when($request->search, function($query, $search) {
                return $query->where('name', 'like', "%{$search}%")
                    ->orWhere('display_name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            })
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
            
        return response()->json($roles);
    }

    public function toggleStatus($id)
    {
        $role = Role::findOrFail($id);
        $role->update(['is_active' => !$role->is_active]);
        
        $status = $role->is_active ? 'activated' : 'deactivated';
        
        return redirect()->back()
            ->with('success', "Role {$status} successfully!");
    }
}