<?php

namespace App\Support;

class PhoneValidator
{
    private static array $patterns = [
        '+57' => ['pattern' => '/^[3][0-9]{9}$/', 'example' => '3101234567', 'label' => 'Colombia (10 digitos, empieza en 3)'],
        '+52' => ['pattern' => '/^[1-9][0-9]{9}$/', 'example' => '5512345678', 'label' => 'Mexico (10 digitos)'],
        '+54' => ['pattern' => '/^[1-9][0-9]{9,10}$/', 'example' => '11123456789', 'label' => 'Argentina (10-11 digitos)'],
        '+56' => ['pattern' => '/^[9][0-9]{8}$/', 'example' => '912345678', 'label' => 'Chile (9 digitos, empieza en 9)'],
        '+51' => ['pattern' => '/^[9][0-9]{8}$/', 'example' => '912345678', 'label' => 'Peru (9 digitos, empieza en 9)'],
        '+1'  => ['pattern' => '/^[2-9][0-9]{9}$/', 'example' => '2025551234', 'label' => 'EE.UU./Canada (10 digitos)'],
        '+34' => ['pattern' => '/^[6-9][0-9]{8}$/', 'example' => '612345678', 'label' => 'Espana (9 digitos, empieza en 6-9)'],
        '+31' => ['pattern' => '/^[6][0-9]{8}$/', 'example' => '612345678', 'label' => 'Paises Bajos (9 digitos, empieza en 6)'],
    ];

    public static function validate(string $phone, string $countryCode): ?string
    {
        $phone = preg_replace('/\D+/', '', $phone);
        $countryCode = preg_replace('/\D+/', '', $countryCode);
        $fullCode = '+' . $countryCode;

        if (! isset(self::$patterns[$fullCode])) {
            return strlen($phone) >= 6 && strlen($phone) <= 15 ? null : 'Telefono invalido para el pais seleccionado';
        }

        $rule = self::$patterns[$fullCode];

        if (preg_match($rule['pattern'], $phone)) {
            return null;
        }

        return "Telefono invalido. {$rule['label']}. Ejemplo: {$rule['example']}";
    }

    public static function getExample(string $countryCode): string
    {
        $fullCode = '+' . preg_replace('/\D+/', '', $countryCode);

        return self::$patterns[$fullCode]['example'] ?? '1234567890';
    }

    public static function getLabel(string $countryCode): string
    {
        $fullCode = '+' . preg_replace('/\D+/', '', $countryCode);

        return self::$patterns[$fullCode]['label'] ?? 'Formato: 6-15 digitos';
    }
}
