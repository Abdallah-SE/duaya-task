<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\ActivityLog;
use App\Models\IdleSession;
use App\Models\Penalty;

class ActivityLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        // Create sample activity logs
        foreach ($users as $user) {
            // Login activity
            ActivityLog::create([
                'user_id' => $user->id,
                'action' => 'login',
                'subject_type' => 'App\Models\User',
                'subject_id' => $user->id,
                'ip_address' => '192.168.1.' . rand(1, 255),
                'device' => 'Desktop',
                'browser' => 'Chrome',
            ]);

            // CRUD operations
            $actions = ['create', 'read', 'update', 'delete'];
            $models = ['User', 'Employee', 'Penalty', 'ActivityLog'];
            
            for ($i = 0; $i < 10; $i++) {
                ActivityLog::create([
                    'user_id' => $user->id,
                    'action' => $actions[array_rand($actions)],
                    'subject_type' => 'App\Models\\' . $models[array_rand($models)],
                    'subject_id' => rand(1, 10),
                    'ip_address' => '192.168.1.' . rand(1, 255),
                    'device' => ['Desktop', 'Mobile', 'Tablet'][array_rand([0, 1, 2])],
                    'browser' => ['Chrome', 'Firefox', 'Safari', 'Edge'][array_rand([0, 1, 2, 3])],
                ]);
            }

            // Create sample idle sessions
            for ($i = 0; $i < rand(2, 5); $i++) {
                $idleStarted = now()->subDays(rand(1, 30))->subMinutes(rand(1, 60));
                $idleEnded = $idleStarted->copy()->addMinutes(rand(5, 30));
                $duration = $idleEnded->diffInSeconds($idleStarted);
                
                IdleSession::create([
                    'user_id' => $user->id,
                    'idle_started_at' => $idleStarted,
                    'idle_ended_at' => $idleEnded,
                    'duration_seconds' => $duration > 0 ? $duration : 0,
                ]);
            }

            // Create sample penalties for some users
            if (rand(0, 1)) {
                Penalty::create([
                    'user_id' => $user->id,
                    'reason' => 'Auto logout due to inactivity',
                    'count' => rand(1, 3),
                    'date' => now()->subDays(rand(1, 7)),
                ]);
            }
        }
    }
}