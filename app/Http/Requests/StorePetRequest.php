<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => self::registrationTextRules(),
            'species' => self::registrationTextRules(),
            'breed' => self::registrationTextRules(false),
            'birth_date' => ['nullable', 'date'],
            'veterinarian_id' => ['nullable', 'exists:veterinarians,id'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'owner_name' => self::registrationTextRules(),
            'owner_email' => ['required', 'email', 'max:255', 'unique:owners,email,NULL,id'],
            'owner_phone_country_code' => ['required', 'string', 'max:8'],
            'owner_phone' => ['nullable', 'string', 'max:30'],
            'owner_address' => ['nullable', 'string', 'max:255'],
        ];
    }

    public static function registrationTextRules(bool $required = true): array
    {
        return array_values(array_filter([
            $required ? 'required' : 'nullable',
            'string',
            'max:255',
            'regex:/^[\pL\pN ]+$/u',
        ]));
    }
}
