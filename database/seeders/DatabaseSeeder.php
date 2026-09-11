<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\ExpenseCategory;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::factory()->admin()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        User::factory()->manager()->create([
            'name' => 'Manager User',
            'email' => 'manager@example.com',
            'password' => bcrypt('password'),
        ]);

        User::factory()->employee()->create([
            'name' => 'Employee User',
            'email' => 'employee@example.com',
            'password' => bcrypt('password'),
        ]);

        $categories = [
            ['name' => 'Travel', 'description' => 'Travel expenses including flights, trains, etc.'],
            ['name' => 'Food', 'description' => 'Meals and food-related expenses.'],
            ['name' => 'Office Supplies', 'description' => 'Stationery and office materials.'],
            ['name' => 'Accommodation', 'description' => 'Hotel and lodging expenses.'],
            ['name' => 'Transport', 'description' => 'Local transportation costs.'],
            ['name' => 'Other', 'description' => 'Miscellaneous expenses.'],
        ];

        foreach ($categories as $category) {
            ExpenseCategory::create($category);
        }
    }
}
