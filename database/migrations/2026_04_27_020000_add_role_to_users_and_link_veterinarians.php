<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'role')) {
            DB::statement("ALTER TABLE users ADD COLUMN role VARCHAR DEFAULT 'admin'");
        }

        if (! Schema::hasColumn('veterinarians', 'user_id')) {
            DB::statement('ALTER TABLE veterinarians ADD COLUMN user_id INTEGER');
        }
    }

    public function down(): void
    {
        // No-op on SQLite to avoid destructive table rebuilds in existing local data.
    }
};
