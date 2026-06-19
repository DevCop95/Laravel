<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('owners', 'phone_country_code')) {
            return;
        }

        Schema::table('owners', function (Blueprint $table) {
            $table->string('phone_country_code', 8)->default('+57')->after('email');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('owners', 'phone_country_code')) {
            return;
        }

        Schema::table('owners', function (Blueprint $table) {
            $table->dropColumn('phone_country_code');
        });
    }
};
