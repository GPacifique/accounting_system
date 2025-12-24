<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loan_charges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained('loans')->cascadeOnDelete();
            $table->integer('month_number'); // Which month of the loan
            $table->decimal('charge_amount', 15, 2); // Monthly charge/interest
            $table->date('due_date');
            $table->enum('status', ['pending', 'paid', 'overdue', 'waived'])->default('pending');
            $table->decimal('amount_paid', 15, 2)->default(0);
            $table->date('paid_at')->nullable();
            $table->text('payment_notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('loan_id');
            $table->index('due_date');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_charges');
    }
};
