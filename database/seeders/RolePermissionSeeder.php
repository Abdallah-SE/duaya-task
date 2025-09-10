<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create permissions
        $permissions = [
            'view-dashboard',
            'view-activity-logs',
            'view-penalties',
            'view-idle-sessions',
            'manage-user-settings',
            'manage-idle-monitoring',
            'view-reports',
            'manage-employees',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles
        $adminRole = Role::create(['name' => 'admin']);
        $employeeRole = Role::create(['name' => 'employee']);

        // Assign all permissions to admin
        $adminRole->givePermissionTo(Permission::all());

        // Assign limited permissions to employee
        $employeeRole->givePermissionTo([
            'view-dashboard',
            'view-activity-logs',
            'view-idle-sessions',
        ]);
    }
}