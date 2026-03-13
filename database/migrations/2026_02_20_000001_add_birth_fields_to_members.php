<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->unsignedTinyInteger('birth_month')->nullable()->after('join_date');
            $table->unsignedTinyInteger('birth_day')->nullable()->after('birth_month');
            $table->index(['birth_month', 'birth_day']);
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropIndex(['birth_month', 'birth_day']);
            $table->dropColumn(['birth_month', 'birth_day']);
        });
    }
};
