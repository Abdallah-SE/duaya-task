<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\IdleSetting;

class IdleSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default idle settings (global settings)
        IdleSetting::getDefault();

        // Create role settings for existing roles
        $roles = \Spatie\Permission\Models\Role::all();
        foreach ($roles as $role) {
            \App\Models\RoleSetting::create([
                'role_id' => $role->id,
                'idle_monitoring_enabled' => true, // Default: enabled for all roles
            ]);
        }
    }
}