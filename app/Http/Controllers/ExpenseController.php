<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\ExpenseApprovalHistory;
use App\Enums\ExpenseStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Expense::with(['category', 'user'])
            ->where('user_id', $user->id);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('expense_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('expense_date', '<=', $request->to_date);
        }

        $expenses = $query->orderByDesc('expense_date')->paginate(15);

        return view('expenses.index', compact('expenses'));
    }

    public function create()
    {
        $categories = ExpenseCategory::where('is_active', true)->get();

        return view('expenses.create', compact('categories'));
    }

    public function store(\App\Http\Requests\StoreExpenseRequest $request)
    {
        $data = $request->validated();

        $data['user_id'] = Auth::id();
        $data['status'] = ExpenseStatus::PENDING;

        if ($request->hasFile('receipt')) {
            $path = $request->file('receipt')->store('receipts', 'local');
            $data['receipt_path'] = $path;
        }

        if ($request->filled('latitude') && $request->filled('longitude')) {
            $data['latitude'] = $request->latitude;
            $data['longitude'] = $request->longitude;
            $data['location_accuracy'] = $request->location_accuracy;
            $data['location_captured_at'] = $request->filled('location_captured_at')
                ? $request->location_captured_at
                : now();
        }

        $expense = Expense::create($data);

        ExpenseApprovalHistory::create([
            'expense_id' => $expense->id,
            'actioned_by' => Auth::id(),
            'action' => \App\Enums\ApprovalAction::SUBMITTED,
            'previous_status' => null,
            'new_status' => ExpenseStatus::PENDING,
        ]);

        return redirect()->route('expenses.index')->with('success', 'Expense submitted successfully.');
    }

    public function show(Expense $expense)
    {
        $this->authorize('view', $expense);

        $expense->load(['category', 'user', 'approvalHistories.actionedBy']);

        return view('expenses.show', compact('expense'));
    }

    public function edit(Expense $expense)
    {
        $this->authorize('update', $expense);

        $categories = ExpenseCategory::where('is_active', true)->get();

        return view('expenses.edit', compact('expense', 'categories'));
    }

    public function update(\App\Http\Requests\UpdateExpenseRequest $request, Expense $expense)
    {
        $this->authorize('update', $expense);

        $data = $request->validated();

        if ($request->hasFile('receipt')) {
            if ($expense->receipt_path) {
                Storage::disk('local')->delete($expense->receipt_path);
            }

            $data['receipt_path'] = $request->file('receipt')->store('receipts', 'local');
        }

        if ($request->filled('latitude') && $request->filled('longitude')) {
            $data['latitude'] = $request->latitude;
            $data['longitude'] = $request->longitude;
            $data['location_accuracy'] = $request->location_accuracy;
            $data['location_captured_at'] = $request->filled('location_captured_at')
                ? $request->location_captured_at
                : now();
        }

        $expense->update($data);

        return redirect()->route('expenses.show', $expense)->with('success', 'Expense updated successfully.');
    }

    public function destroy(Expense $expense)
    {
        $this->authorize('delete', $expense);

        if ($expense->receipt_path) {
            Storage::disk('local')->delete($expense->receipt_path);
        }

        $expense->delete();

        return redirect()->route('expenses.index')->with('success', 'Expense deleted successfully.');
    }

    public function receipt(Expense $expense)
    {
        $this->authorize('view', $expense);

        if (!$expense->receipt_path || !Storage::disk('local')->exists($expense->receipt_path)) {
            abort(404);
        }

        return Storage::disk('local')->response($expense->receipt_path);
    }
}
