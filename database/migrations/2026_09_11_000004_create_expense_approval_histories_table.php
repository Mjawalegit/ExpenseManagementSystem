<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expense_approval_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expense_id')->constrained()->cascadeOnDelete();
            $table->foreignId('actioned_by')->constrained('users')->cascadeOnDelete();
            $table->string('action');
            $table->string('previous_status')->nullable();
            $table->string('new_status')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->index('expense_id');
            $table->index('actioned_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_approval_histories');
    }
};
