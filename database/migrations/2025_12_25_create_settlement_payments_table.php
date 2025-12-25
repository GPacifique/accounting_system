<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settlement_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('settlement_id')->constrained('settlements')->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->date('payment_date');
            $table->string('payment_method'); // cash, bank_transfer, check, etc
            $table->string('reference')->nullable(); // Receipt/check number
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('recorded_by'); // User ID who recorded payment
            $table->timestamps();

            $table->index('settlement_id');
            $table->index('payment_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settlement_payments');
    }
};
