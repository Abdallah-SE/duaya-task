<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Employee;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get roles
        $adminRole = Role::findByName('admin');
        $employeeRole = Role::findByName('employee');

        // Create admin user
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@duaya.com',
            'password' => Hash::make('password'),
        ]);
        $admin->assignRole($adminRole);

        // Create employee users with employee records
        $employee1 = User::create([
            'name' => 'Employee 1',
            'email' => 'employee1@duaya.com',
            'password' => Hash::make('password'),
        ]);
        $employee1->assignRole($employeeRole);

        Employee::create([
            'user_id' => $employee1->id,
            'job_title' => 'Software Developer',
            'department' => 'IT',
            'hire_date' => now()->subMonths(6),
        ]);

        $employee2 = User::create([
            'name' => 'Employee 2',
            'email' => 'employee2@duaya.com',
            'password' => Hash::make('password'),
        ]);
        $employee2->assignRole($employeeRole);

        Employee::create([
            'user_id' => $employee2->id,
            'job_title' => 'HR Specialist',
            'department' => 'HR',
            'hire_date' => now()->subMonths(3),
        ]);

        $employee3 = User::create([
            'name' => 'Employee 3',
            'email' => 'employee3@duaya.com',
            'password' => Hash::make('password'),
        ]);
        $employee3->assignRole($employeeRole);

        Employee::create([
            'user_id' => $employee3->id,
            'job_title' => 'Accountant',
            'department' => 'Finance',
            'hire_date' => now()->subMonths(1),
        ]);
    }
}
