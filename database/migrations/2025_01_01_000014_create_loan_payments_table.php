<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loan_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained('loans')->cascadeOnDelete();
            $table->decimal('principal_paid', 15, 2)->default(0); // Amount toward principal
            $table->decimal('charges_paid', 15, 2)->default(0); // Amount toward charges
            $table->decimal('total_paid', 15, 2); // Total payment
            $table->date('payment_date');
            $table->string('payment_method')->nullable(); // Cash, bank transfer, etc
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->index('loan_id');
            $table->index('payment_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_payments');
    }
};
