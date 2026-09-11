<?php

namespace App\Models;

use App\Enums\ExpenseStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Expense extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'category_id',
        'expense_date',
        'amount',
        'description',
        'receipt_path',
        'status',
        'latitude',
        'longitude',
        'location_accuracy',
        'location_captured_at',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'expense_date' => 'date',
            'amount' => 'decimal:2',
            'location_accuracy' => 'decimal:2',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'location_captured_at' => 'datetime',
            'status' => ExpenseStatus::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class);
    }

    public function approvalHistories(): HasMany
    {
        return $this->hasMany(ExpenseApprovalHistory::class);
    }
}
