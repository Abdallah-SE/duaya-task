<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Note: This seeder is currently empty as the system uses:
     * - idle_settings table for global settings
     * - role_settings table for per-role monitoring control
     * - Individual user settings are not stored separately
     */
    public function run(): void
    {
        // Individual user idle settings are not stored in this system
        // The system uses global idle_settings and role-based monitoring control
        // via role_settings table instead of per-user settings
        
        // If you need to create any user-specific settings in the future,
        // you can add them here
    }
}