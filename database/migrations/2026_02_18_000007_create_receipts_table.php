<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receipts', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_number', 40)->unique();
            $table->foreignId('member_id')->nullable()->constrained()->nullOnDelete();
            $table->string('reference_type', 40);
            $table->unsignedBigInteger('reference_id');
            $table->decimal('amount', 12, 2);
            $table->foreignId('generated_by')->constrained('users');
            $table->string('pdf_path');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receipts');
    }
};
