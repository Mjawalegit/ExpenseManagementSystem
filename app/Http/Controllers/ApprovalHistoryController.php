<?php

namespace App\Http\Controllers;

use App\Models\ExpenseApprovalHistory;
use Illuminate\Http\Request;

class ApprovalHistoryController extends Controller
{
    public function index(Request $request)
    {
        $query = ExpenseApprovalHistory::with(['expense.user', 'expense.category', 'actionedBy']);

        if ($request->filled('expense_id')) {
            $query->where('expense_id', $request->expense_id);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $histories = $query->orderByDesc('created_at')->paginate(15);

        return view('admin.approval-history.index', compact('histories'));
    }
}
