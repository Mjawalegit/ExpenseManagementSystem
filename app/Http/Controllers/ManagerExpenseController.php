<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseApprovalHistory;
use App\Models\User;
use App\Enums\ExpenseStatus;
use App\Enums\ApprovalAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ManagerExpenseController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Expense::with(['user', 'category'])
            ->where('status', ExpenseStatus::PENDING)
            ->where('user_id', '!=', $user->id);

        if ($request->filled('employee_id')) {
            $query->where('user_id', $request->employee_id);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('expense_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('expense_date', '<=', $request->to_date);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $expenses = $query->orderByDesc('expense_date')->paginate(15);
        $employees = User::whereIn('role', ['employee', 'manager'])->get();

        return view('manager.expenses.index', compact('expenses', 'employees'));
    }

    public function show(Expense $expense)
    {
        $this->authorize('view', $expense);

        $expense->load(['user', 'category', 'approvalHistories.actionedBy']);

        return view('manager.expenses.show', compact('expense'));
    }

    public function approve(Expense $expense)
    {
        $this->authorize('approve', $expense);

        DB::transaction(function () use ($expense) {
            $expense->update([
                'status' => ExpenseStatus::APPROVED,
                'rejection_reason' => null,
            ]);

            ExpenseApprovalHistory::create([
                'expense_id' => $expense->id,
                'actioned_by' => Auth::id(),
                'action' => ApprovalAction::APPROVED,
                'previous_status' => ExpenseStatus::PENDING,
                'new_status' => ExpenseStatus::APPROVED,
            ]);
        });

        return redirect()->route('manager.expenses.index')->with('success', 'Expense approved successfully.');
    }

    public function reject(\App\Http\Requests\RejectExpenseRequest $request, Expense $expense)
    {
        $this->authorize('reject', $expense);

        $data = $request->validated();

        DB::transaction(function () use ($expense, $data) {
            $expense->update([
                'status' => ExpenseStatus::REJECTED,
                'rejection_reason' => $data['rejection_reason'],
            ]);

            ExpenseApprovalHistory::create([
                'expense_id' => $expense->id,
                'actioned_by' => Auth::id(),
                'action' => ApprovalAction::REJECTED,
                'previous_status' => ExpenseStatus::PENDING,
                'new_status' => ExpenseStatus::REJECTED,
                'rejection_reason' => $data['rejection_reason'],
            ]);
        });

        return redirect()->route('manager.expenses.index')->with('success', 'Expense rejected successfully.');
    }
}
