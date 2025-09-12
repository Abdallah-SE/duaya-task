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
        $users = User::all();

        foreach ($users as $user) {
            // Create default idle settings for each user
            IdleSetting::create([
                'user_id' => $user->id,
                'idle_timeout' => 5, // 5 seconds as per task requirements
                'idle_monitoring_enabled' => true,
                'max_idle_warnings' => 3, // 3 warnings before logout
            ]);
        }
    }
}