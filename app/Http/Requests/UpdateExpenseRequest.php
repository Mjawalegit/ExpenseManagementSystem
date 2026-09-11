<?php

namespace App\Http\Requests;

use App\Models\Expense;
use Illuminate\Foundation\Http\FormRequest;

class UpdateExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        $expense = $this->route('expense');

        if (!$expense instanceof Expense) {
            return false;
        }

        $user = $this->user();

        return $user->isAdmin() || ($user->isEmployee() && $user->id === $expense->user_id && $expense->status === 'pending');
    }

    public function rules(): array
    {
        $isReceiptRequired = $this->input('amount') > 1000;

        return [
            'expense_date' => ['required', 'date', 'before_or_equal:today'],
            'category_id' => ['required', 'integer', 'exists:expense_categories,id'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'description' => ['required', 'string', 'max:2000'],
            'receipt' => [
                $isReceiptRequired ? 'required' : 'nullable',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:5120',
            ],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'location_accuracy' => ['nullable', 'numeric', 'min:0'],
            'location_captured_at' => ['nullable', 'date'],
        ];
    }
}
