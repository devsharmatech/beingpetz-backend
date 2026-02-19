<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\DB;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Truncate tables first
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('role_permission')->truncate();
        Permission::truncate();
        Role::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Create Permissions
        $permissions = [
            // Dashboard
            [
                'name' => 'dashboard',
                'display_name' => 'Dashboard',
                'module' => 'dashboard',
                'description' => 'Access dashboard',
                'icon' => 'fas fa-tachometer-alt',
                'route' => 'admin.dashboard',
            ],
            // Content Management
            [
                'name' => 'categories',
                'display_name' => 'Category Management',
                'module' => 'content',
                'description' => 'Manage categories',
                'icon' => 'fas fa-list-alt',
                'route' => 'admin.categories.index',
            ],
            [
                'name' => 'blogs',
                'display_name' => 'Blog Management',
                'module' => 'content',
                'description' => 'Manage blogs',
                'icon' => 'fas fa-blog',
                'route' => 'admin.blogs.index',
            ],
            [
                'name' => 'events',
                'display_name' => 'Event Management',
                'module' => 'content',
                'description' => 'Manage events',
                'icon' => 'fas fa-calendar-alt',
                'route' => 'admin.events.list',
            ],
            [
                'name' => 'pets',
                'display_name' => 'Pet Management',
                'module' => 'content',
                'description' => 'Manage pets',
                'icon' => 'fas fa-paw',
                'route' => 'admin.pets.list',
            ],
            [
                'name' => 'parents',
                'display_name' => 'Parent Management',
                'module' => 'content',
                'description' => 'Manage parents',
                'icon' => 'fas fa-user-friends',
                'route' => 'admin.parents.index',
            ],
            [
                'name' => 'community',
                'display_name' => 'Community Management',
                'module' => 'content',
                'description' => 'Manage communities',
                'icon' => 'fas fa-users',
                'route' => 'admin.community.index',
            ],
            [
                'name' => 'post',
                'display_name' => 'Post Management',
                'module' => 'content',
                'description' => 'Manage posts',
                'icon' => 'fas fa-newspaper',
                'route' => 'admin.post.index',
            ],
            // Service Management
            [
                'name' => 'services',
                'display_name' => 'Service Management',
                'module' => 'services',
                'description' => 'Manage services',
                'icon' => 'fas fa-concierge-bell',
                'route' => 'admin.services.index',
            ],
            [
                'name' => 'banner',
                'display_name' => 'Banner Management',
                'module' => 'services',
                'description' => 'Manage banners',
                'icon' => 'fas fa-images',
                'route' => 'admin.banner.index',
            ],
            [
                'name' => 'service-banner',
                'display_name' => 'Service Banner Management',
                'module' => 'services',
                'description' => 'Manage service banners',
                'icon' => 'fas fa-ad',
                'route' => 'admin.service-banner.index',
            ],
            // User Management
            [
                'name' => 'uservendors',
                'display_name' => 'User & Vendor Management',
                'module' => 'users',
                'description' => 'Manage users and vendors',
                'icon' => 'fas fa-users-cog',
                'route' => 'admin.uservendors.index',
            ],
            [
                'name' => 'reports',
                'display_name' => 'Report Management',
                'module' => 'users',
                'description' => 'Manage reports',
                'icon' => 'fas fa-flag',
                'route' => 'admin.reports.index',
            ],
            [
                'name' => 'messages',
                'display_name' => 'Message Management',
                'module' => 'users',
                'description' => 'Manage messages',
                'icon' => 'fas fa-comments',
                'route' => 'admin.messages.index',
            ],
            [
                'name' => 'notifications',
                'display_name' => 'Notification Management',
                'module' => 'users',
                'description' => 'Manage notifications',
                'icon' => 'fas fa-bell',
                'route' => 'admin.notifications.index',
            ],
            // Settings
            [
                'name' => 'settings',
                'display_name' => 'Settings',
                'module' => 'settings',
                'description' => 'Access settings',
                'icon' => 'fas fa-cogs',
                'route' => 'admin.settings.index',
            ],
        ];

        foreach ($permissions as $permission) {
            Permission::create($permission);
        }

        // Create Roles
        $roles = [
            [
                'name' => 'admin',
                'display_name' => 'Administrator',
                'description' => 'Has full access to all features',
                'color' => 'danger',
                'icon' => 'fas fa-crown',
                'default_permissions' => ['*'] // All permissions
            ],
            [
                'name' => 'vendor',
                'display_name' => 'Vendor',
                'description' => 'Can manage services and banners',
                'color' => 'warning',
                'icon' => 'fas fa-store',
                'default_permissions' => ['dashboard', 'services', 'banner', 'service-banner']
            ],
            [
                'name' => 'user',
                'display_name' => 'User',
                'description' => 'Can manage pets and community',
                'color' => 'info',
                'icon' => 'fas fa-user',
                'default_permissions' => ['dashboard', 'pets', 'parents', 'community']
            ]
        ];

        foreach ($roles as $roleData) {
            $role = Role::create($roleData);
            
            // If role has all permissions (*), assign all
            if (in_array('*', $roleData['default_permissions'])) {
                $role->permissions()->sync(Permission::pluck('id')->toArray());
            } else {
                // Assign specific permissions
                $permissionIds = Permission::whereIn('name', $roleData['default_permissions'])->pluck('id')->toArray();
                $role->permissions()->sync($permissionIds);
            }
        }

        $this->command->info('Roles and Permissions seeded successfully!');
    }
}