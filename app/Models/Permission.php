<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'module',
        'description',
        'icon',
        'route',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_permission');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

 
    public function scopeByModule($query, $module)
    {
        return $query->where('module', $module);
    }

    
    public function isAssignedToRole($roleId)
    {
        return $this->roles()->where('role_id', $roleId)->exists();
    }
}