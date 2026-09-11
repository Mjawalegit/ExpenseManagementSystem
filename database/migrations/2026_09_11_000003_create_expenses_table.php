<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('expense_categories')->cascadeOnDelete();
            $table->date('expense_date');
            $table->decimal('amount', 10, 2);
            $table->text('description');
            $table->string('receipt_path')->nullable();
            $table->string('status')->default('pending');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->decimal('location_accuracy', 10, 2)->nullable();
            $table->dateTime('location_captured_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('category_id');
            $table->index('status');
            $table->index('expense_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
