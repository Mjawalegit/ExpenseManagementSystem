<?php

namespace App\Models;

use App\Enums\Role;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Casts\Attribute;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'role' => Role::class,
        ];
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function approvalHistories()
    {
        return $this->hasMany(ExpenseApprovalHistory::class, 'actioned_by');
    }

    public function isAdmin(): bool
    {
        return $this->role === Role::ADMIN;
    }

    public function isManager(): bool
    {
        return $this->role === Role::MANAGER;
    }

    public function isEmployee(): bool
    {
        return $this->role === Role::EMPLOYEE;
    }

    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }
}
