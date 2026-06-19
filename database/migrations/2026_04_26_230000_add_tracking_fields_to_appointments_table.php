<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('appointments', 'public_token')) {
            return;
        }

        Schema::table('appointments', function (Blueprint $table) {
            $table->string('public_token')->nullable()->unique()->after('status');
            $table->text('doctor_notes')->nullable()->after('public_token');
            $table->timestamp('service_finished_at')->nullable()->after('doctor_notes');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('appointments', 'public_token')) {
            return;
        }

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn(['public_token', 'doctor_notes', 'service_finished_at']);
        });
    }
};
