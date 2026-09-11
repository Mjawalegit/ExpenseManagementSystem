<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $manager;
    private User $employee;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->manager = User::factory()->manager()->create([
            'email' => 'manager@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->employee = User::factory()->employee()->create([
            'email' => 'employee@example.com',
            'password' => Hash::make('password'),
        ]);

        $category = ExpenseCategory::create([
            'name' => 'Travel',
            'description' => 'Travel expenses',
            'is_active' => true,
        ]);

        Expense::factory()->count(3)->create([
            'user_id' => $this->employee->id,
            'category_id' => $category->id,
        ]);
    }

    public function test_admin_can_view_dashboard(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('stats');
    }

    public function test_employee_can_view_dashboard(): void
    {
        $this->actingAs($this->employee);

        $response = $this->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('stats');
    }

    public function test_manager_can_view_dashboard(): void
    {
        $this->actingAs($this->manager);

        $response = $this->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('stats');
    }
}
