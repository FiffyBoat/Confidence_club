<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('donations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('special_contribution_id')
                ->constrained('contributions')
                ->cascadeOnDelete();
            $table->decimal('donated_amount', 12, 2);
            $table->decimal('remaining_amount', 12, 2);
            $table->string('donation_purpose', 255)->nullable();
            $table->string('remaining_use', 255)->nullable();
            $table->date('donation_date');
            $table->foreignId('recorded_by')->constrained('users');
            $table->timestamps();

            $table->unique('special_contribution_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donations');
    }
};
