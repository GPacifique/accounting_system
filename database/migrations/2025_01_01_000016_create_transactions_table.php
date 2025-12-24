<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('groups')->cascadeOnDelete();
            $table->foreignId('member_id')->constrained('group_members')->cascadeOnDelete();
            $table->enum('type', ['deposit', 'withdrawal', 'loan_disburse', 'loan_payment', 'interest', 'charge', 'fee']);
            $table->decimal('amount', 15, 2);
            $table->decimal('balance_after', 15, 2); // Account balance after transaction
            $table->text('description')->nullable();
            $table->string('reference')->nullable(); // Reference to loan ID or other entity
            $table->nullableMorphs('transactionable'); // For polymorphic relations
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->date('transaction_date');
            $table->timestamps();

            $table->index('group_id');
            $table->index('member_id');
            $table->index('transaction_date');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
