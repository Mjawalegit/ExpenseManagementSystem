<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\User;
use App\Enums\ExpenseStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $stats = [];
        $now = Carbon::now();

        if ($user->isAdmin()) {
            $stats = [
                'total_expenses' => Expense::count(),
                'pending_expenses' => Expense::where('status', ExpenseStatus::PENDING)->count(),
                'approved_expenses' => Expense::where('status', ExpenseStatus::APPROVED)->count(),
                'rejected_expenses' => Expense::where('status', ExpenseStatus::REJECTED)->count(),
                'current_month_expenses' => Expense::whereBetween('expense_date', [$now->startOfMonth(), $now->endOfMonth()])
                    ->sum('amount'),
            ];
        } elseif ($user->isManager()) {
            $stats = [
                'total_expenses' => Expense::where('status', '!=', 'pending')->orWhereHas('approvalHistories', function ($q) use ($user) {
                    $q->where('actioned_by', $user->id);
                })->count(),
                'pending_expenses' => Expense::where('status', ExpenseStatus::PENDING)->count(),
                'approved_expenses' => Expense::where('status', ExpenseStatus::APPROVED)->count(),
                'rejected_expenses' => Expense::where('status', ExpenseStatus::REJECTED)->count(),
                'current_month_expenses' => Expense::whereBetween('expense_date', [$now->startOfMonth(), $now->endOfMonth()])
                    ->sum('amount'),
            ];
        } else {
            $stats = [
                'total_expenses' => $user->expenses()->count(),
                'pending_expenses' => $user->expenses()->where('status', ExpenseStatus::PENDING)->count(),
                'approved_expenses' => $user->expenses()->where('status', ExpenseStatus::APPROVED)->count(),
                'rejected_expenses' => $user->expenses()->where('status', ExpenseStatus::REJECTED)->count(),
                'current_month_expenses' => $user->expenses()
                    ->whereBetween('expense_date', [$now->startOfMonth(), $now->endOfMonth()])
                    ->sum('amount'),
            ];
        }

        $startOfYear = $now->copy()->startOfYear();
        $monthlyData = [];

        for ($i = 1; $i <= 12; $i++) {
            $monthStart = $startOfYear->copy()->addMonths($i - 1)->startOfMonth();
            $monthEnd = $monthStart->copy()->endOfMonth();

            $total = Expense::whereBetween('expense_date', [$monthStart, $monthEnd])
                ->sum('amount');

            $monthlyData[] = [
                'month' => $monthStart->format('M'),
                'total' => (float) $total,
            ];
        }

        return view('dashboard.index', compact('stats', 'monthlyData', 'user'));
    }
}
