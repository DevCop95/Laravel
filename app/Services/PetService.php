<?php

namespace App\Services;

use App\Models\Owner;
use App\Models\Pet;

class PetService
{
    public function create(array $validated): Pet
    {
        $owner = Owner::create([
            'name' => $validated['owner_name'],
            'email' => $validated['owner_email'],
            'phone_country_code' => $validated['owner_phone_country_code'],
            'phone' => $validated['owner_phone'],
            'address' => $validated['owner_address'],
        ]);

        return Pet::create([
            'name' => $validated['name'],
            'species' => $validated['species'],
            'breed' => $validated['breed'],
            'birth_date' => $validated['birth_date'],
            'owner_id' => $owner->id,
            'veterinarian_id' => $validated['veterinarian_id'],
        ]);
    }

    public function update(Pet $pet, array $validated): Pet
    {
        $pet->update([
            'name' => $validated['name'],
            'species' => $validated['species'],
            'breed' => $validated['breed'],
            'birth_date' => $validated['birth_date'],
            'veterinarian_id' => $validated['veterinarian_id'],
        ]);

        $pet->owner()->update([
            'name' => $validated['owner_name'],
            'email' => $validated['owner_email'],
            'phone_country_code' => $validated['owner_phone_country_code'],
            'phone' => $validated['owner_phone'],
            'address' => $validated['owner_address'],
        ]);

        return $pet;
    }

    public function destroy(Pet $pet): bool
    {
        return $pet->delete();
    }

    public function mapForArray(Pet $pet, ?array $audit = null): array
    {
        $pet->loadMissing(['owner', 'veterinarian']);

        return [
            'id' => $pet->id,
            'name' => $pet->name,
            'species' => $pet->species,
            'breed' => $pet->breed,
            'birth_date' => $pet->birth_date,
            'photo_path' => $pet->photo_path,
            'owner_id' => $pet->owner_id,
            'owner_name' => $pet->owner?->name,
            'owner_phone' => $pet->owner?->phone,
            'owner_phone_country_code' => $pet->owner?->phone_country_code ?? '+57',
            'owner_email' => $pet->owner?->email,
            'owner_address' => $pet->owner?->address,
            'veterinarian_id' => $pet->veterinarian_id,
            'veterinarian_name' => $pet->veterinarian?->name,
            'audit' => $audit,
        ];
    }
}
