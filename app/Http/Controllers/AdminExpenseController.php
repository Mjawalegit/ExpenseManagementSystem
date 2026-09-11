<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\User;
use App\Models\ExpenseCategory;
use App\Enums\ExpenseStatus;
use Illuminate\Http\Request;

class AdminExpenseController extends Controller
{
    public function index(Request $request)
    {
        $query = Expense::with(['user', 'category']);

        if ($request->filled('employee_id')) {
            $query->where('user_id', $request->employee_id);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('expense_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('expense_date', '<=', $request->to_date);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('description', 'like', '%' . $request->search . '%')
                    ->orWhereHas('user', function ($q2) use ($request) {
                        $q2->where('name', 'like', '%' . $request->search . '%');
                    });
            });
        }

        $expenses = $query->orderByDesc('expense_date')->paginate(15);
        $employees = User::whereIn('role', ['employee', 'manager'])->get();
        $categories = ExpenseCategory::all();

        return view('admin.expenses.index', compact('expenses', 'employees', 'categories'));
    }

    public function show(Expense $expense)
    {
        $this->authorize('view', $expense);

        $expense->load(['user', 'category', 'approvalHistories.actionedBy']);

        return view('admin.expenses.show', compact('expense'));
    }
}
