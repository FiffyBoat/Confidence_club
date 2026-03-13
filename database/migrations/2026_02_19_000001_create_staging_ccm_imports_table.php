<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staging_ccm_imports', function (Blueprint $table) {
            $table->id();
            $table->string('member_no', 20)->nullable();
            $table->string('membership_id', 40)->nullable();
            $table->string('full_name', 150)->nullable();
            $table->string('phone', 30)->nullable();
            $table->decimal('admission_fee', 12, 2)->nullable();
            $table->decimal('professor_donation', 12, 2)->nullable();
            $table->decimal('lawyer_donation', 12, 2)->nullable();
            $table->decimal('extra_levies', 12, 2)->nullable();
            $table->string('extra_notes', 255)->nullable();
            $table->text('payment_received_raw')->nullable();
            $table->string('notes', 255)->nullable();
            $table->json('dues')->nullable();
            $table->json('raw_row')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staging_ccm_imports');
    }
};
