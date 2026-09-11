<?php

namespace App\Models;

use App\Enums\ApprovalAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpenseApprovalHistory extends Model
{
    public $timestamps = true;

    protected $fillable = [
        'expense_id',
        'actioned_by',
        'action',
        'previous_status',
        'new_status',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'action' => ApprovalAction::class,
        ];
    }

    public function expense(): BelongsTo
    {
        return $this->belongsTo(Expense::class);
    }

    public function actionedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actioned_by');
    }
}
