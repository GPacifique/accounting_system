<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('savings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('groups')->cascadeOnDelete();
            $table->foreignId('member_id')->constrained('group_members')->cascadeOnDelete();
            $table->decimal('current_balance', 15, 2)->default(0);
            $table->decimal('total_deposits', 15, 2)->default(0);
            $table->decimal('total_withdrawals', 15, 2)->default(0);
            $table->decimal('interest_earned', 15, 2)->default(0);
            $table->date('last_deposit_date')->nullable();
            $table->date('last_withdrawal_date')->nullable();
            $table->timestamps();

            $table->unique(['group_id', 'member_id']);
            $table->index('group_id');
            $table->index('member_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('savings');
    }
};
