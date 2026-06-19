<?php

use App\Models\User;
use App\Models\Veterinarian;
use App\Support\VeterinarianCredentials;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        Veterinarian::query()
            ->with('user')
            ->get()
            ->each(function (Veterinarian $veterinarian) {
                $newPassword = VeterinarianCredentials::defaultPassword(
                    $veterinarian->name,
                    $veterinarian->specialty,
                );

                $currentPassword = $veterinarian->default_password;

                if (! $currentPassword || $currentPassword === $newPassword) {
                    $veterinarian->update(['default_password' => $newPassword]);

                    return;
                }

                $user = $veterinarian->user;

                if ($user instanceof User && Hash::check($currentPassword, $user->password)) {
                    $user->update(['password' => $newPassword]);
                    $veterinarian->update(['default_password' => $newPassword]);
                }
            });
    }

    public function down(): void
    {
        // No-op. We do not restore the previous longer generated passwords.
    }
};
