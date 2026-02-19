<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Role;
use App\Models\Permission;

class SetupRolesPermissions extends Command
{
    protected $signature = 'setup:roles-permissions';
    protected $description = 'Setup roles and permissions in the system';

    public function handle()
    {
        $this->info('Setting up roles and permissions...');
        
        // Run seeders
        $this->call('db:seed', ['--class' => 'RolePermissionSeeder']);
        $this->call('db:seed', ['--class' => 'AdminUserSeeder']);
        
        $this->info('Roles and permissions setup completed!');
        
        // Show summary
        $this->info("\nSummary:");
        $this->table(
            ['ID', 'Role', 'Permissions Count'],
            Role::withCount('permissions')->get()->map(function ($role) {
                return [
                    $role->id,
                    $role->display_name,
                    $role->permissions_count
                ];
            })
        );
        
        $this->info("\nPermissions:");
        $this->table(
            ['ID', 'Name', 'Module', 'Route'],
            Permission::all()->map(function ($permission) {
                return [
                    $permission->id,
                    $permission->display_name,
                    $permission->module,
                    $permission->route
                ];
            })
        );
        
        return Command::SUCCESS;
    }
}