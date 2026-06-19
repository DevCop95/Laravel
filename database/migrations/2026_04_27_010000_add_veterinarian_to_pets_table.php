<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('pets', 'veterinarian_id')) {
            return;
        }

        Schema::table('pets', function (Blueprint $table) {
            $table->foreignId('veterinarian_id')->nullable()->after('owner_id')->constrained()->nullOnDelete();
        });

        DB::statement('
            UPDATE pets
            SET veterinarian_id = (
                SELECT appointments.veterinarian_id
                FROM appointments
                WHERE appointments.pet_id = pets.id
                  AND appointments.veterinarian_id IS NOT NULL
                ORDER BY appointments.appointment_date ASC
                LIMIT 1
            )
            WHERE veterinarian_id IS NULL
        ');
    }

    public function down(): void
    {
        if (! Schema::hasColumn('pets', 'veterinarian_id')) {
            return;
        }

        Schema::table('pets', function (Blueprint $table) {
            $table->dropConstrainedForeignId('veterinarian_id');
        });
    }
};
