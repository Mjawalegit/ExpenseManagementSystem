<?php

namespace Database\Factories;

use App\Models\ExpenseCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ExpenseCategory>
 */
class ExpenseCategoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement(['Travel', 'Food', 'Office Supplies', 'Accommodation', 'Transport', 'Other']),
            'description' => fake()->sentence(),
            'is_active' => true,
        ];
    }
}
