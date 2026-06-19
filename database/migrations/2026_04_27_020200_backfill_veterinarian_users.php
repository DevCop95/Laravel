<?php

use App\Models\User;
use App\Models\Veterinarian;
use App\Support\VeterinarianCredentials;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Veterinarian::query()
            ->whereNull('user_id')
            ->whereNotNull('email')
            ->get()
            ->each(function (Veterinarian $veterinarian) {
                $user = User::firstOrCreate(
                    ['email' => $veterinarian->email],
                    [
                        'name' => $veterinarian->name,
                        'password' => VeterinarianCredentials::defaultPassword($veterinarian->name, $veterinarian->specialty),
                        'role' => 'veterinarian',
                    ],
                );

                if ($user->role !== 'veterinarian') {
                    return;
                }

                $veterinarian->update(['user_id' => $user->id]);
            });
    }

    public function down(): void
    {
        // No-op to preserve linked users in existing environments.
    }
};
