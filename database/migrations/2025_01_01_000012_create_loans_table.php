<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('groups')->cascadeOnDelete();
            $table->foreignId('member_id')->constrained('group_members')->cascadeOnDelete();
            $table->decimal('principal_amount', 15, 2);
            $table->decimal('monthly_charge', 15, 2); // Fixed monthly charge/interest
            $table->decimal('remaining_balance', 15, 2);
            $table->integer('duration_months'); // Loan duration in months
            $table->integer('months_paid', 0); // How many months paid
            $table->decimal('total_charged', 15, 2)->default(0); // Total charges/interest paid
            $table->decimal('total_principal_paid', 15, 2)->default(0);
            $table->date('issued_at');
            $table->date('maturity_date'); // Expected completion date
            $table->date('paid_off_at')->nullable(); // Actual completion date
            $table->enum('status', ['pending', 'approved', 'active', 'completed', 'defaulted'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('group_id');
            $table->index('member_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
