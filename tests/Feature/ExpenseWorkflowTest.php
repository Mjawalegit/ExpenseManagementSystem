<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ExpenseWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $manager;
    private User $employee;
    private User $employeeB;
    private ExpenseCategory $category;

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

        $this->employeeB = User::factory()->employee()->create([
            'email' => 'employeeb@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->category = ExpenseCategory::create([
            'name' => 'Travel',
            'description' => 'Travel expenses',
            'is_active' => true,
        ]);
    }

    public function test_login_works(): void
    {
        $response = $this->post('/login', [
            'email' => 'employee@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
    }

    public function test_unauthenticated_user_cannot_access_protected_routes(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect(route('login'));
    }

    public function test_employee_cannot_access_another_employee_expense(): void
    {
        $expense = Expense::factory()->create([
            'user_id' => $this->employee->id,
            'category_id' => $this->category->id,
            'status' => 'pending',
        ]);

        $this->actingAs($this->employeeB);

        $response = $this->get(route('expenses.show', $expense));

        $response->assertStatus(403);
    }

    public function test_employee_cannot_modify_approved_expense(): void
    {
        $expense = Expense::factory()->create([
            'user_id' => $this->employee->id,
            'category_id' => $this->category->id,
            'status' => 'approved',
        ]);

        $this->actingAs($this->employee);

        $response = $this->put(route('expenses.update', $expense), [
            'expense_date' => now()->subDays(2)->format('Y-m-d'),
            'category_id' => $this->category->id,
            'amount' => 500,
            'description' => 'Updated description',
        ]);

        $response->assertStatus(403);
    }

    public function test_employee_cannot_modify_rejected_expense(): void
    {
        $expense = Expense::factory()->create([
            'user_id' => $this->employee->id,
            'category_id' => $this->category->id,
            'status' => 'rejected',
        ]);

        $this->actingAs($this->employee);

        $response = $this->put(route('expenses.update', $expense), [
            'expense_date' => now()->subDays(2)->format('Y-m-d'),
            'category_id' => $this->category->id,
            'amount' => 500,
            'description' => 'Updated description',
        ]);

        $response->assertStatus(403);
    }

    public function test_manager_cannot_approve_own_expense(): void
    {
        $expense = Expense::factory()->create([
            'user_id' => $this->manager->id,
            'category_id' => $this->category->id,
            'status' => 'pending',
        ]);

        $this->actingAs($this->manager);

        $response = $this->post(route('manager.expenses.approve', $expense));

        $response->assertStatus(403);
    }

    public function test_manager_can_approve_employee_expense(): void
    {
        $expense = Expense::factory()->create([
            'user_id' => $this->employee->id,
            'category_id' => $this->category->id,
            'status' => 'pending',
        ]);

        $this->actingAs($this->manager);

        $response = $this->post(route('manager.expenses.approve', $expense));

        $response->assertRedirect(route('manager.expenses.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('expenses', [
            'id' => $expense->id,
            'status' => 'approved',
        ]);
    }

    public function test_manager_can_reject_employee_expense_with_reason(): void
    {
        $expense = Expense::factory()->create([
            'user_id' => $this->employee->id,
            'category_id' => $this->category->id,
            'status' => 'pending',
        ]);

        $this->actingAs($this->manager);

        $response = $this->post(route('manager.expenses.reject', $expense), [
            'rejection_reason' => 'Invalid expense',
        ]);

        $response->assertRedirect(route('manager.expenses.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('expenses', [
            'id' => $expense->id,
            'status' => 'rejected',
            'rejection_reason' => 'Invalid expense',
        ]);
    }

    public function test_manager_rejection_requires_reason(): void
    {
        $expense = Expense::factory()->create([
            'user_id' => $this->employee->id,
            'category_id' => $this->category->id,
            'status' => 'pending',
        ]);

        $this->actingAs($this->manager);

        $response = $this->post(route('manager.expenses.reject', $expense), [
            'rejection_reason' => '',
        ]);

        $response->assertSessionHasErrors('rejection_reason');
    }

    public function test_admin_can_view_all_expenses(): void
    {
        Expense::factory()->count(3)->create([
            'user_id' => $this->employee->id,
            'category_id' => $this->category->id,
        ]);

        $this->actingAs($this->admin);

        $response = $this->get(route('admin.expenses.index'));

        $response->assertStatus(200);
    }

    public function test_employee_can_create_expense(): void
    {
        $this->actingAs($this->employee);

        $response = $this->post(route('expenses.store'), [
            'expense_date' => now()->subDays(2)->format('Y-m-d'),
            'category_id' => $this->category->id,
            'amount' => 500,
            'description' => 'Test expense',
        ]);

        $response->assertRedirect(route('expenses.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('expenses', [
            'user_id' => $this->employee->id,
            'description' => 'Test expense',
            'status' => 'pending',
        ]);
    }

    public function test_amount_must_be_greater_than_zero(): void
    {
        $this->actingAs($this->employee);

        $response = $this->post(route('expenses.store'), [
            'expense_date' => now()->subDays(2)->format('Y-m-d'),
            'category_id' => $this->category->id,
            'amount' => 0,
            'description' => 'Test expense',
        ]);

        $response->assertSessionHasErrors('amount');
    }

    public function test_future_date_is_rejected(): void
    {
        $this->actingAs($this->employee);

        $response = $this->post(route('expenses.store'), [
            'expense_date' => now()->addDay()->format('Y-m-d'),
            'category_id' => $this->category->id,
            'amount' => 500,
            'description' => 'Test expense',
        ]);

        $response->assertSessionHasErrors('expense_date');
    }

    public function test_receipt_required_above_thousand(): void
    {
        $this->actingAs($this->employee);

        $response = $this->post(route('expenses.store'), [
            'expense_date' => now()->subDays(2)->format('Y-m-d'),
            'category_id' => $this->category->id,
            'amount' => 1500,
            'description' => 'Test expense',
        ]);

        $response->assertSessionHasErrors('receipt');
    }

    public function test_valid_receipt_is_accepted(): void
    {
        $this->actingAs($this->employee);

        $response = $this->post(route('expenses.store'), [
            'expense_date' => now()->subDays(2)->format('Y-m-d'),
            'category_id' => $this->category->id,
            'amount' => 1500,
            'description' => 'Test expense',
            'receipt' => UploadedFile::fake()->image('receipt.jpg', 100, 100),
        ]);

        $response->assertRedirect(route('expenses.index'));
        $response->assertSessionHas('success');
    }

    public function test_invalid_receipt_type_is_rejected(): void
    {
        $this->actingAs($this->employee);

        $response = $this->post(route('expenses.store'), [
            'expense_date' => now()->subDays(2)->format('Y-m-d'),
            'category_id' => $this->category->id,
            'amount' => 1500,
            'description' => 'Test expense',
            'receipt' => UploadedFile::fake()->create('receipt.exe', 100),
        ]);

        $response->assertSessionHasErrors('receipt');
    }
}
