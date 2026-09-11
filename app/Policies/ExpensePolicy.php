<?php

namespace App\Policies;

use App\Models\Expense;
use App\Models\User;
use App\Enums\ExpenseStatus;
use Illuminate\Auth\Access\Response;

class ExpensePolicy
{
    public function view(User $user, Expense $expense): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isManager()) {
            return $expense->status === ExpenseStatus::PENDING;
        }

        return $user->isEmployee() && $user->id === $expense->user_id;
    }

    public function update(User $user, Expense $expense): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isEmployee() && $user->id === $expense->user_id && $expense->status === ExpenseStatus::PENDING) {
            return true;
        }

        return false;
    }

    public function delete(User $user, Expense $expense): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isEmployee() && $user->id === $expense->user_id && $expense->status === ExpenseStatus::PENDING) {
            return true;
        }

        return false;
    }

    public function approve(User $user, Expense $expense): bool
    {
        if ($user->isAdmin() || $user->isManager()) {
            return $expense->status === ExpenseStatus::PENDING && $user->id !== $expense->user_id;
        }

        return false;
    }

    public function reject(User $user, Expense $expense): bool
    {
        if ($user->isAdmin() || $user->isManager()) {
            return $expense->status === ExpenseStatus::PENDING && $user->id !== $expense->user_id;
        }

        return false;
    }
}
