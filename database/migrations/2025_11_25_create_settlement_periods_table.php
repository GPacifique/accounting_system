<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settlement_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('groups')->cascadeOnDelete();
            $table->string('period_name'); // e.g., "Q1 2025", "Jan-Mar 2025"
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['active', 'closed', 'finalized'])->default('active');
            $table->decimal('total_savings_target', 15, 2)->nullable();
            $table->decimal('total_savings_collected', 15, 2)->default(0);
            $table->decimal('total_interest_earned', 15, 2)->default(0);
            $table->decimal('total_penalties_applied', 15, 2)->default(0);
            $table->decimal('total_settlement_amount', 15, 2)->default(0);
            $table->timestamp('finalized_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('group_id');
            $table->index('status');
            $table->unique(['group_id', 'period_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settlement_periods');
    }
};
