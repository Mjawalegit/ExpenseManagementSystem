<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ManagerExpenseController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AdminCategoryController;
use App\Http\Controllers\AdminExpenseController;
use App\Http\Controllers\ApprovalHistoryController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::middleware('role:employee')->prefix('expenses')->name('expenses.')->group(function () {
        Route::get('/', [ExpenseController::class, 'index'])->name('index');
        Route::get('/create', [ExpenseController::class, 'create'])->name('create');
        Route::post('/', [ExpenseController::class, 'store'])->name('store');
        Route::get('/{expense}', [ExpenseController::class, 'show'])->name('show');
        Route::get('/{expense}/edit', [ExpenseController::class, 'edit'])->name('edit');
        Route::put('/{expense}', [ExpenseController::class, 'update'])->name('update');
        Route::delete('/{expense}', [ExpenseController::class, 'destroy'])->name('destroy');
        Route::get('/{expense}/receipt', [ExpenseController::class, 'receipt'])->name('receipt');
    });

    Route::middleware('role:manager')->prefix('manager')->name('manager.')->group(function () {
        Route::prefix('expenses')->name('expenses.')->group(function () {
            Route::get('/', [ManagerExpenseController::class, 'index'])->name('index');
            Route::get('/{expense}', [ManagerExpenseController::class, 'show'])->name('show');
            Route::post('/{expense}/approve', [ManagerExpenseController::class, 'approve'])->name('approve');
            Route::post('/{expense}/reject', [ManagerExpenseController::class, 'reject'])->name('reject');
        });
    });

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [AdminUserController::class, 'index'])->name('index');
            Route::get('/create', [AdminUserController::class, 'create'])->name('create');
            Route::post('/', [AdminUserController::class, 'store'])->name('store');
            Route::get('/{user}/edit', [AdminUserController::class, 'edit'])->name('edit');
            Route::put('/{user}', [AdminUserController::class, 'update'])->name('update');
            Route::delete('/{user}', [AdminUserController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('categories')->name('categories.')->group(function () {
            Route::get('/', [AdminCategoryController::class, 'index'])->name('index');
            Route::get('/create', [AdminCategoryController::class, 'create'])->name('create');
            Route::post('/', [AdminCategoryController::class, 'store'])->name('store');
            Route::get('/{category}/edit', [AdminCategoryController::class, 'edit'])->name('edit');
            Route::put('/{category}', [AdminCategoryController::class, 'update'])->name('update');
            Route::delete('/{category}', [AdminCategoryController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('expenses')->name('expenses.')->group(function () {
            Route::get('/', [AdminExpenseController::class, 'index'])->name('index');
            Route::get('/{expense}', [AdminExpenseController::class, 'show'])->name('show');
        });

        Route::prefix('approval-history')->name('approval-history.')->group(function () {
            Route::get('/', [ApprovalHistoryController::class, 'index'])->name('index');
        });
    });
});
