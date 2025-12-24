<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->enum('type', ['itsinda', 'ikimina', 'association', 'cooperative'])->default('itsinda')->after('name');
            $table->integer('member_numbers')->default(0)->after('type');
        });
    }

    public function down(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->dropColumn(['type', 'member_numbers']);
        });
    }
};
