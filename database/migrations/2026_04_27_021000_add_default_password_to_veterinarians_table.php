<?php

use App\Models\User;
use App\Models\Veterinarian;
use App\Support\VeterinarianCredentials;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('veterinarians', function (Blueprint $table) {
            if (! Schema::hasColumn('veterinarians', 'default_password')) {
                $table->string('default_password')->nullable()->after('specialty');
            }
        });

        Veterinarian::query()
            ->with('user')
            ->get()
            ->each(function (Veterinarian $veterinarian) {
                $generatedPassword = VeterinarianCredentials::defaultPassword(
                    $veterinarian->name,
                    $veterinarian->specialty,
                );

                $user = $veterinarian->user;

                if ($user instanceof User && Hash::check('Vet123456!', $user->password)) {
                    $user->update(['password' => $generatedPassword]);
                    $veterinarian->update(['default_password' => $generatedPassword]);

                    return;
                }

                if (! $veterinarian->default_password) {
                    $veterinarian->update([
                        'default_password' => $user ? null : $generatedPassword,
                    ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('veterinarians', function (Blueprint $table) {
            if (Schema::hasColumn('veterinarians', 'default_password')) {
                $table->dropColumn('default_password');
            }
        });
    }
};
