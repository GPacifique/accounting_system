<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('group_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('groups')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('role', ['admin', 'treasurer', 'member'])->default('member');
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->decimal('current_savings', 15, 2)->default(0);
            $table->decimal('total_contributed', 15, 2)->default(0);
            $table->decimal('total_withdrawn', 15, 2)->default(0);
            $table->decimal('total_borrowed', 15, 2)->default(0);
            $table->decimal('total_repaid', 15, 2)->default(0);
            $table->decimal('outstanding_loans', 15, 2)->default(0);
            $table->date('joined_at');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['group_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_members');
    }
};
