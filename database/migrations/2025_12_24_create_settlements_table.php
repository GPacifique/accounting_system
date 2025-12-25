<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('settlement_period_id')->constrained('settlement_periods')->cascadeOnDelete();
            $table->foreignId('member_id')->constrained('group_members')->cascadeOnDelete();
            $table->decimal('original_savings', 15, 2)->default(0); // Amount member deposited
            $table->decimal('interest_earned', 15, 2)->default(0); // Interest from loans to others
            $table->decimal('penalties_applied', 15, 2)->default(0); // Total penalties
            $table->decimal('penalties_waived', 15, 2)->default(0); // Waived penalties
            $table->decimal('total_due', 15, 2)->default(0); // savings + interest + (penalties - waived)
            $table->decimal('amount_paid', 15, 2)->default(0); // Amount already paid
            $table->decimal('amount_pending', 15, 2)->default(0); // Amount still owed
            $table->enum('status', ['pending', 'partial', 'paid', 'overdue'])->default('pending');
            $table->date('payment_date')->nullable(); // When fully paid
            $table->date('due_date')->nullable(); // Settlement due date
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('settlement_period_id');
            $table->index('member_id');
            $table->index('status');
            $table->unique(['settlement_period_id', 'member_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settlements');
    }
};
