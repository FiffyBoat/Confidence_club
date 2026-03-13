<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('donations')) {
            return;
        }

        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys=off');
            DB::statement('ALTER TABLE donations RENAME TO donations_old');

            Schema::create('donations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('special_contribution_id')->nullable()
                    ->constrained('contributions')
                    ->nullOnDelete();
                $table->string('special_contribution_purpose', 255)->nullable();
                $table->decimal('donated_amount', 12, 2);
                $table->decimal('remaining_amount', 12, 2);
                $table->string('donation_purpose', 255)->nullable();
                $table->string('remaining_use', 255)->nullable();
                $table->date('donation_date');
                $table->foreignId('recorded_by')->constrained('users');
                $table->timestamps();
            });

            DB::statement('
                INSERT INTO donations (
                    id,
                    special_contribution_id,
                    donated_amount,
                    remaining_amount,
                    donation_purpose,
                    remaining_use,
                    donation_date,
                    recorded_by,
                    created_at,
                    updated_at
                )
                SELECT
                    id,
                    special_contribution_id,
                    donated_amount,
                    remaining_amount,
                    donation_purpose,
                    remaining_use,
                    donation_date,
                    recorded_by,
                    created_at,
                    updated_at
                FROM donations_old
            ');

            DB::statement('
                UPDATE donations
                SET special_contribution_purpose = (
                    SELECT description FROM contributions
                    WHERE contributions.id = donations.special_contribution_id
                )
                WHERE special_contribution_id IS NOT NULL
            ');

            DB::statement('DROP TABLE donations_old');
            DB::statement('PRAGMA foreign_keys=on');

            return;
        }

        Schema::table('donations', function (Blueprint $table) {
            $table->string('special_contribution_purpose', 255)->nullable()->after('special_contribution_id');
            $table->dropUnique(['special_contribution_id']);
            $table->foreignId('special_contribution_id')->nullable()->change();
        });

        DB::statement('
            UPDATE donations d
            JOIN contributions c ON c.id = d.special_contribution_id
            SET d.special_contribution_purpose = c.description
            WHERE d.special_contribution_id IS NOT NULL
        ');
    }

    public function down(): void
    {
        if (! Schema::hasTable('donations')) {
            return;
        }

        $driver = DB::getDriverName();
        if ($driver === 'sqlite') {
            return;
        }

        Schema::table('donations', function (Blueprint $table) {
            $table->dropColumn('special_contribution_purpose');
            $table->foreignId('special_contribution_id')->nullable(false)->change();
            $table->unique('special_contribution_id');
        });
    }
};
