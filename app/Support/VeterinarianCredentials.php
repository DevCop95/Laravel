<?php

namespace App\Support;

use Illuminate\Support\Str;

class VeterinarianCredentials
{
    public static function defaultPassword(string $name, ?string $specialty = null): string
    {
        return Str::random(12).rand(10, 99);
    }

    protected static function specialtySegment(string $value): string
    {
        $words = self::normalizedWords($value);

        if ($words === []) {
            return 'Gen';
        }

        return Str::ucfirst(Str::lower(Str::substr($words[0], 0, 3)));
    }

    protected static function firstNameSegment(string $value): string
    {
        $words = self::normalizedWords($value);
        $titles = ['dr', 'dra', 'doctor', 'doctora'];

        $usableWords = array_values(array_filter(
            $words,
            fn (string $word) => ! in_array(Str::lower($word), $titles, true),
        ));

        $firstWord = $usableWords[0] ?? $words[0] ?? 'Vet';

        return Str::title(Str::lower($firstWord));
    }

    protected static function normalizedWords(string $value): array
    {
        $ascii = Str::ascii($value);
        $clean = preg_replace('/[^A-Za-z0-9 ]+/', ' ', $ascii) ?? '';
        $collapsed = preg_replace('/\s+/', ' ', trim($clean)) ?? '';

        if ($collapsed === '') {
            return [];
        }

        return array_values(array_filter(explode(' ', $collapsed)));
    }
}
