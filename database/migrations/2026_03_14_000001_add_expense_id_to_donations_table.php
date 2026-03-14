<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('donations') || Schema::hasColumn('donations', 'expense_id')) {
            return;
        }

        Schema::table('donations', function (Blueprint $table) {
            $table->unsignedBigInteger('expense_id')->nullable()->after('special_contribution_id');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('donations') || ! Schema::hasColumn('donations', 'expense_id')) {
            return;
        }

        Schema::table('donations', function (Blueprint $table) {
            $table->dropColumn('expense_id');
        });
    }
};
