<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('appointments', 'service_id')) {
            return;
        }

        Schema::table('appointments', function (Blueprint $table) {
            $table->foreignId('veterinarian_id')->nullable()->after('pet_id')->constrained()->nullOnDelete();
            $table->foreignId('service_id')->nullable()->after('veterinarian_id')->constrained()->nullOnDelete();
            $table->decimal('service_price', 10, 2)->default(0)->after('service_id');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('appointments', 'service_id')) {
            return;
        }

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('veterinarian_id');
            $table->dropConstrainedForeignId('service_id');
            $table->dropColumn('service_price');
        });
    }
};
