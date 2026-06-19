<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Veterinarian;

class VeterinarianPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Veterinarian $veterinarian): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        return $veterinarian->id === $user->veterinarian?->id;
    }

    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function update(User $user, Veterinarian $veterinarian): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        return $veterinarian->id === $user->veterinarian?->id;
    }

    public function delete(User $user): bool
    {
        return $user->role === 'admin';
    }
}
