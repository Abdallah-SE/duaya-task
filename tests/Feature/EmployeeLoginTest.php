<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Employee;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class EmployeeLoginTest extends TestCase
{
    use RefreshDatabase;

    protected $employee;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create employee role if it doesn't exist
        if (!Role::where('name', 'employee')->exists()) {
            Role::create(['name' => 'employee']);
        }
        
        // Create admin role if it doesn't exist
        if (!Role::where('name', 'admin')->exists()) {
            Role::create(['name' => 'admin']);
        }
        
        // Create employee user
        $this->employee = User::create([
            'name' => 'Test Employee',
            'email' => 'employee@test.com',
            'password' => Hash::make('password123'),
        ]);
        $this->employee->assignRole('employee');
        
        // Create employee record
        Employee::create([
            'user_id' => $this->employee->id,
            'job_title' => 'Software Developer',
            'department' => 'IT',
            'hire_date' => now()->subMonths(6),
        ]);
    }

    /**
     * Test employee can access employee login page
     */
    public function test_employee_can_access_employee_login_page(): void
    {
        $response = $this->get('/employee/login');
        $response->assertStatus(200);
    }

    /**
     * Test employee can login with valid credentials
     */
    public function test_employee_can_login_with_valid_credentials(): void
    {
        $response = $this->post('/employee/login', [
            'email' => 'employee@test.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/employee/dashboard');
        $this->assertAuthenticated();
        $this->assertTrue(auth()->user()->hasRole('employee'));
    }

    /**
     * Test employee login fails with invalid credentials
     */
    public function test_employee_login_fails_with_invalid_credentials(): void
    {
        $response = $this->post('/employee/login', [
            'email' => 'employee@test.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    /**
     * Test non-employee user cannot login via employee route
     */
    public function test_non_employee_user_cannot_login_via_employee_route(): void
    {
        // Create admin user
        $admin = User::create([
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
            'password' => Hash::make('password123'),
        ]);
        $admin->assignRole('admin');

        $response = $this->post('/employee/login', [
            'email' => 'admin@test.com',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    /**
     * Test employee login requires email and password
     */
    public function test_employee_login_requires_email_and_password(): void
    {
        $response = $this->post('/employee/login', []);

        $response->assertSessionHasErrors(['email', 'password']);
    }

    /**
     * Test employee login with invalid email format
     */
    public function test_employee_login_with_invalid_email_format(): void
    {
        $response = $this->post('/employee/login', [
            'email' => 'invalid-email',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    /**
     * Test employee can access employee dashboard after login
     */
    public function test_employee_can_access_employee_dashboard_after_login(): void
    {
        $this->actingAs($this->employee);
        
        $response = $this->get('/employee/dashboard');
        $response->assertStatus(200);
    }

    /**
     * Test employee logout works correctly
     */
    public function test_employee_logout_works_correctly(): void
    {
        $this->actingAs($this->employee);
        
        $response = $this->post('/logout');
        
        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    /**
     * Test employee dashboard shows employee-specific data
     */
    public function test_employee_dashboard_shows_employee_data(): void
    {
        $this->actingAs($this->employee);
        
        $response = $this->get('/employee/dashboard');
        $response->assertStatus(200);
        
        // Check that the response contains employee data
        $response->assertInertia(fn ($page) => 
            $page->component('Employee/Dashboard')
                ->has('user')
                ->has('stats')
                ->has('myActivities')
                ->has('myPenalties')
                ->has('myIdleSessions')
        );
    }
}
