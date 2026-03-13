<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->decimal('principal', 12, 2);
            $table->decimal('interest_rate', 6, 2);
            $table->decimal('total_payable', 12, 2);
            $table->decimal('balance', 12, 2);
            $table->date('issue_date');
            $table->date('due_date');
            $table->string('status', 20)->default('active');
            $table->foreignId('recorded_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
