<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $employee;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->employee = User::factory()->employee()->create([
            'email' => 'employee@example.com',
            'password' => Hash::make('password'),
        ]);
    }

    public function test_admin_can_view_users(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('admin.users.index'));

        $response->assertStatus(200);
    }

    public function test_admin_can_create_user(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('admin.users.store'), [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'employee',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com',
            'role' => 'employee',
        ]);
    }

    public function test_admin_cannot_create_user_with_duplicate_email(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('admin.users.store'), [
            'name' => 'Duplicate User',
            'email' => 'employee@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'employee',
            'is_active' => true,
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_employee_cannot_access_admin_pages(): void
    {
        $this->actingAs($this->employee);

        $response = $this->get(route('admin.users.index'));

        $response->assertStatus(403);
    }
}
