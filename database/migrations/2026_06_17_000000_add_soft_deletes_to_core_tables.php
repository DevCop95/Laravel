<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('owners', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('pets', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('veterinarians', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('services', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('owners', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('pets', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('veterinarians', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('services', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
