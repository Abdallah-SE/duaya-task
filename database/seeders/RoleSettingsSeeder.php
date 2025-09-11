<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\RoleSetting;

class RoleSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all existing roles (both admin and web guards)
        $roles = Role::all();
        
        foreach ($roles as $role) {
            // Create or update role settings
            RoleSetting::updateOrCreate(
                ['role_id' => $role->id],
                [
                    'idle_monitoring_enabled' => $this->getDefaultMonitoringStatus($role->name)
                ]
            );
        }
    }
    
    /**
     * Get default monitoring status based on role name and guard
     */
    private function getDefaultMonitoringStatus(string $roleName): bool
    {
        return match ($roleName) {
            'admin' => true, // Enable monitoring for admins to test the system
            'employee' => true, // Employees need idle monitoring
            'manager' => true, // Managers need idle monitoring
            'supervisor' => true, // Supervisors need idle monitoring
            default => true, // Default to enabled for other roles
        };
    }
}
