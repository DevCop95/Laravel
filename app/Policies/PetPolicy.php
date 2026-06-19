<?php

namespace App\Policies;

use App\Models\Pet;
use App\Models\User;

class PetPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Pet $pet): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        return $pet->veterinarian_id === $user->veterinarian?->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Pet $pet): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        return $pet->veterinarian_id === $user->veterinarian?->id;
    }

    public function delete(User $user, Pet $pet): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        return $pet->veterinarian_id === $user->veterinarian?->id;
    }
}
