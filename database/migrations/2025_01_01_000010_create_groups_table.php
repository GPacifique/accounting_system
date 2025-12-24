<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->decimal('total_savings', 15, 2)->default(0);
            $table->decimal('total_loans_issued', 15, 2)->default(0);
            $table->decimal('total_interest_earned', 15, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('groups');
    }
};
