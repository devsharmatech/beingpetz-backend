<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;



class User extends Authenticatable
{
   
     use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'username',
        'email',
        'phone',
        'country_code',
        'password',
        'otp',
        'otp_expires_at',
        'isComplete',
        'city',
        'state',
        'locality',
        'profile',
        'latitude',
        'longitude',
        'role', // Keep for backward compatibility
        'role_id',
        'custom_permissions',
        'last_login',
        'last_active_at',
        'last_activity_details',
        'deleted_at'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login' => 'datetime',
            'last_active_at' => 'datetime',
            'password' => 'hashed',
            'custom_permissions' => 'array',
            'isComplete' => 'boolean',
            'otp_expires_at' => 'datetime'
        ];
    }
    public function getProfileUrlAttribute()
    {
        if (!$this->profile) return null;
        if (filter_var($this->profile, FILTER_VALIDATE_URL)) return $this->profile;
        return asset($this->profile);
    }

    protected $appends = ['profile_url'];
    public function pets()
    {
        // Try different possible foreign keys
        if (Schema::hasColumn('pets', 'user_id')) {
            return $this->hasMany(Pet::class, 'user_id');
        } elseif (Schema::hasColumn('pets', 'owner_id')) {
            return $this->hasMany(Pet::class, 'owner_id');
        } elseif (Schema::hasColumn('pets', 'parent_id')) {
            return $this->hasMany(Pet::class, 'parent_id');
        }
        
        // Default fallback
        return $this->hasMany(Pet::class);
    }

    public function petsCount()
    {
        // Agar direct count store karte hain database mein
        return $this->pets_count ?? 0;
    }

    // Check if user is active
    public function isDailyActive()
    {
        return $this->last_login && $this->last_login->gt(now()->subDay());
    }

    public function isWeeklyActive()
    {
        return $this->last_login && $this->last_login->gt(now()->subWeek());
    }

    public function isMonthlyActive()
    {
        return $this->last_login && $this->last_login->gt(now()->subMonth());
    }

    // Scope for user and vendor roles only
    public function scopeUserVendor($query)
    {
        return $query->whereIn('role', ['user', 'vendor']);
    }

    // App\Models\User.php
    public function getFormattedCreatedAtAttribute()
    {
        return $this->created_at 
            ? $this->created_at->format('Y-m-d H:i:s')
            : 'N/A';
    }

    public function roleRelation()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

   public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Get the user's actual role name
     */
    public function getRoleNameAttribute()
    {
        return $this->roleRelation ? $this->roleRelation->name : ($this->attributes['role'] ?? 'user');
    }

    /**
     * Check if user has permission
     */
    public function hasPermission($permissionName)
    {
        // If user is not authenticated
        if (!$this->id) {
            return false;
        }

        // If user has custom permissions, check them first
        if (!empty($this->custom_permissions)) {
            if (in_array('*', $this->custom_permissions) || in_array($permissionName, $this->custom_permissions)) {
                return true;
            }
        }

        // Check role permissions using relationship
        if ($this->roleRelation) {
            return $this->roleRelation->hasPermission($permissionName);
        }

        return false;
    }

    /**
     * Check if user has any of the given permissions
     */
    public function hasAnyPermission(array $permissions)
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if user has all of the given permissions
     */
    public function hasAllPermissions(array $permissions)
    {
        foreach ($permissions as $permission) {
            if (!$this->hasPermission($permission)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Get all permissions for user (role permissions + custom permissions)
     */
    public function getAllPermissions()
    {
        $permissions = [];
        
        // Get role permissions using relationship
        if ($this->roleRelation) {
            $permissions = array_merge($permissions, $this->roleRelation->getPermissionNames());
        }
        
        // Add custom permissions
        if (!empty($this->custom_permissions)) {
            $permissions = array_merge($permissions, $this->custom_permissions);
        }
        
        return array_unique($permissions);
    }

    /**
     * Get sidebar menu items based on permissions
     */
    public function getSidebarMenu()
    {
        // Get all active permissions with routes
        $permissions = Permission::active()
            ->whereNotNull('route')
            ->get()
            ->keyBy('name');
        
        $menuItems = [];
        
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission->name)) {
                $menuItems[$permission->name] = [
                    'route' => $permission->route,
                    'icon' => $permission->icon,
                    'label' => $permission->display_name,
                    'module' => $permission->module
                ];
            }
        }
        
        // Sort by module
        $sortedMenu = [];
        foreach ($menuItems as $key => $item) {
            $module = $item['module'] ?? 'other';
            $sortedMenu[$module][$key] = $item;
        }
        
        return $sortedMenu;
    }

    /**
     * Check if user is admin
     */
    public function isAdmin()
    {
        return $this->role_name === 'admin';
    }

    /**
     * Check if user is vendor
     */
    public function isVendor()
    {
        return $this->role_name === 'vendor';
    }

    /**
     * Check if user is regular user
     */
    public function isRegularUser()
    {
        return $this->role_name === 'user';
    }

    /**
     * Get full name
     */
    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }
    
}
