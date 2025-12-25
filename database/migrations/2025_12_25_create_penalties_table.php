<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penalties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained('group_members')->cascadeOnDelete();
            $table->foreignId('group_id')->constrained('groups')->cascadeOnDelete();
            $table->foreignId('loan_id')->nullable()->constrained('loans')->cascadeOnDelete();
            $table->string('type'); // late_payment, violation, default, other
            $table->decimal('amount', 15, 2);
            $table->text('reason'); // Detailed reason for penalty
            $table->boolean('waived')->default(false);
            $table->text('waived_reason')->nullable();
            $table->timestamp('applied_at');
            $table->timestamp('waived_at')->nullable();
            $table->unsignedBigInteger('waived_by')->nullable(); // User ID who waived
            $table->timestamps();

            $table->index('member_id');
            $table->index('group_id');
            $table->index('loan_id');
            $table->index('type');
            $table->index('waived');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penalties');
    }
};
