<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Permission;
use Illuminate\Support\Facades\Validator;

class PermissionController extends Controller
{
    public function index()
    {
        $permissions = Permission::orderBy('module')->paginate(20);
        
        $modules = Permission::select('module')->distinct()->orderBy('module')->pluck('module');
        
        return view('admin.permissions.index', compact('permissions', 'modules'));
    }

   
    public function create()
    {
        $modules = Permission::select('module')->distinct()->orderBy('module')->pluck('module');
        $defaultModules = ['Dashboard', 'Content', 'Services', 'Users', 'Settings', 'System'];
        
        return view('admin.permissions.create', compact('modules', 'defaultModules'));
    }

    
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:permissions',
            'display_name' => 'required|string|max:255',
            'module' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:100',
            'route' => 'nullable|string|max:255',
            'order' => 'nullable|integer',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            Permission::create([
                'name' => $request->name,
                'display_name' => $request->display_name,
                'module' => $request->module,
                'description' => $request->description,
                'icon' => $request->icon ?? 'fas fa-key',
                'route' => $request->route,
                'order' => $request->order ?? 0,
                'is_active' => $request->boolean('is_active', true)
            ]);

            return redirect()->route('admin.permissions.index')
                ->with('success', 'Permission created successfully!');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error creating permission: ' . $e->getMessage())
                ->withInput();
        }
    }

   
    public function edit($id)
    {
        $permission = Permission::findOrFail($id);
        $modules = Permission::select('module')->distinct()->orderBy('module')->pluck('module');
        $defaultModules = ['Dashboard', 'Content', 'Services', 'Users', 'Settings', 'System'];
        
        return view('admin.permissions.edit', compact('permission', 'modules', 'defaultModules'));
    }

    public function update(Request $request, $id)
    {
        $permission = Permission::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:permissions,name,' . $id,
            'display_name' => 'required|string|max:255',
            'module' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:100',
            'route' => 'nullable|string|max:255',
            'order' => 'nullable|integer',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $permission->update([
                'name' => $request->name,
                'display_name' => $request->display_name,
                'module' => $request->module,
                'description' => $request->description,
                'icon' => $request->icon ?? 'fas fa-key',
                'route' => $request->route,
                'order' => $request->order ?? 0,
                'is_active' => $request->boolean('is_active')
            ]);

            return redirect()->route('admin.permissions.index')
                ->with('success', 'Permission updated successfully!');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error updating permission: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified permission
     */
    public function destroy($id)
    {
        $permission = Permission::findOrFail($id);
        
        // Check if permission is assigned to roles
        if ($permission->roles()->count() > 0) {
            return redirect()->route('admin.permissions.index')
                ->with('error', 'Cannot delete permission. It is assigned to ' . $permission->roles()->count() . ' role(s).');
        }

        try {
            $permission->delete();
            
            return redirect()->route('admin.permissions.index')
                ->with('success', 'Permission deleted successfully!');
                
        } catch (\Exception $e) {
            return redirect()->route('admin.permissions.index')
                ->with('error', 'Error deleting permission: ' . $e->getMessage());
        }
    }

    /**
     * Get permissions by module
     */
    public function getByModule(Request $request)
    {
        $module = $request->module;
        $permissions = Permission::where('module', $module)
            ->orderBy('order')
            ->get();
            
        return response()->json($permissions);
    }

    /**
     * Toggle permission status
     */
    public function toggleStatus($id)
    {
        $permission = Permission::findOrFail($id);
        $permission->update(['is_active' => !$permission->is_active]);
        
        $status = $permission->is_active ? 'activated' : 'deactivated';
        
        return redirect()->back()
            ->with('success', "Permission {$status} successfully!");
    }
}