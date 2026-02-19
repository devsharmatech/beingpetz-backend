<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'color',
        'icon',
        'default_permissions',
        'is_active'
    ];

    protected $casts = [
        'default_permissions' => 'array',
        'is_active' => 'boolean'
    ];

   
    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permission');
    }

    public function hasPermission($permissionName)
    {
        return $this->permissions()->where('name', $permissionName)->exists();
    }

    public function getPermissionNames()
    {
        return $this->permissions()->pluck('name')->toArray();
    }

   
    public function assignPermission($permissionId)
    {
        return $this->permissions()->attach($permissionId);
    }

    public function removePermission($permissionId)
    {
        return $this->permissions()->detach($permissionId);
    }

   
    public function syncPermissions(array $permissionIds)
    {
        return $this->permissions()->sync($permissionIds);
    }
}