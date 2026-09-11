<?php

namespace App\Providers;

use App\Models\Expense;
use App\Policies\ExpensePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Expense::class => ExpensePolicy::class,
    ];

    public function boot(): void
    {
        //
    }
}
