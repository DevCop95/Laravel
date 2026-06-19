<?php

use App\Models\ClinicSetting;
use App\Models\Owner;
use App\Models\Pet;
use App\Models\Service;
use App\Models\User;
use App\Models\Veterinarian;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        User::query()
            ->where('role', 'admin')
            ->where('email', '!=', 'admin@greencol.com')
            ->update(['email' => 'admin@greencol.com']);

        Veterinarian::query()->get()->each(function (Veterinarian $veterinarian) {
            $cleanName = $this->sanitizePersonName($veterinarian->name);
            $cleanSpecialty = $this->sanitizeWords($veterinarian->specialty);

            $veterinarian->update([
                'name' => $cleanName ?: $veterinarian->name,
                'specialty' => $cleanSpecialty ?: $veterinarian->specialty,
            ]);

            $veterinarian->user?->update([
                'name' => $cleanName ?: $veterinarian->user->name,
            ]);
        });

        Owner::query()->get()->each(function (Owner $owner) {
            $owner->update([
                'name' => $this->sanitizeWords($owner->name) ?: $owner->name,
            ]);
        });

        Pet::query()->get()->each(function (Pet $pet) {
            $pet->update([
                'name' => $this->sanitizeWords($pet->name) ?: $pet->name,
                'species' => $this->sanitizeWords($pet->species) ?: $pet->species,
                'breed' => $this->sanitizeWords($pet->breed) ?: $pet->breed,
            ]);
        });

        Service::query()->get()->each(function (Service $service) {
            $service->update([
                'name' => $this->sanitizeWords($service->name) ?: $service->name,
                'service_type' => $this->sanitizeWords($service->service_type) ?: $service->service_type,
            ]);
        });

        ClinicSetting::query()->get()->each(function (ClinicSetting $setting) {
            $setting->update([
                'name' => $this->sanitizeWords($setting->name) ?: $setting->name,
            ]);
        });
    }

    public function down(): void
    {
        // No-op.
    }

    private function sanitizeWords(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $ascii = Str::ascii($value);
        $clean = preg_replace('/[^A-Za-z0-9 ]+/', ' ', $ascii) ?? '';
        $collapsed = preg_replace('/\s+/', ' ', trim($clean)) ?? '';

        return $collapsed !== '' ? $collapsed : null;
    }

    private function sanitizePersonName(?string $value): ?string
    {
        $clean = $this->sanitizeWords($value);

        if (! $clean) {
            return null;
        }

        $words = array_values(array_filter(explode(' ', $clean)));
        $titles = ['dr', 'dra', 'doctor', 'doctora'];
        $usableWords = array_values(array_filter(
            $words,
            fn (string $word) => ! in_array(Str::lower($word), $titles, true),
        ));

        return implode(' ', $usableWords ?: $words);
    }
};
