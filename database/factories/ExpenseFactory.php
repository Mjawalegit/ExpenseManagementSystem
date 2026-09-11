<?php

namespace Database\Factories;

use App\Enums\ExpenseStatus;
use App\Models\ExpenseCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Expense>
 */
class ExpenseFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'category_id' => ExpenseCategory::factory(),
            'expense_date' => fake()->dateTimeThisYear(),
            'amount' => fake()->randomFloat(2, 100, 10000),
            'description' => fake()->sentence(),
            'receipt_path' => fake()->optional(0.7)->filePath(),
            'status' => fake()->randomElement([ExpenseStatus::PENDING, ExpenseStatus::APPROVED, ExpenseStatus::REJECTED]),
            'latitude' => fake()->optional()->latitude(),
            'longitude' => fake()->optional()->longitude(),
            'location_accuracy' => fake()->optional()->randomFloat(2, 1, 100),
            'location_captured_at' => fake()->optional()->dateTimeThisYear(),
            'rejection_reason' => fake()->optional()->sentence(),
        ];
    }
}
