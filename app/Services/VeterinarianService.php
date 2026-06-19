<?php

namespace App\Services;

use App\Models\User;
use App\Models\Veterinarian;
use App\Support\VeterinarianCredentials;

class VeterinarianService
{
    public function create(array $validated): Veterinarian
    {
        $defaultPassword = VeterinarianCredentials::defaultPassword($validated['name'], $validated['specialty'] ?? null);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $defaultPassword,
            'role' => 'veterinarian',
        ]);

        $veterinarian = Veterinarian::create([
            ...$validated,
            'user_id' => $user->id,
        ]);

        return $veterinarian;
    }

    public function update(Veterinarian $veterinarian, array $validated): Veterinarian
    {
        $veterinarian->update($validated);

        if (! $veterinarian->user) {
            $defaultPassword = VeterinarianCredentials::defaultPassword($validated['name'], $validated['specialty'] ?? null);
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $defaultPassword,
                'role' => 'veterinarian',
            ]);

            $veterinarian->update([
                'user_id' => $user->id,
            ]);
        } else {
            $veterinarian->user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
            ]);
        }

        return $veterinarian;
    }

    public function destroy(Veterinarian $veterinarian): bool
    {
        return $veterinarian->delete();
    }
}
