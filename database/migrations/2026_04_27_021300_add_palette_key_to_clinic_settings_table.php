<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clinic_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('clinic_settings', 'palette_key')) {
                $table->string('palette_key')->default('green-col')->after('logo_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('clinic_settings', function (Blueprint $table) {
            if (Schema::hasColumn('clinic_settings', 'palette_key')) {
                $table->dropColumn('palette_key');
            }
        });
    }
};
