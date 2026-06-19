<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $ownerId = $this->route('pet')?->owner_id ?? 'NULL';

        return [
            'name' => StorePetRequest::registrationTextRules(),
            'species' => StorePetRequest::registrationTextRules(),
            'breed' => StorePetRequest::registrationTextRules(false),
            'birth_date' => ['nullable', 'date'],
            'veterinarian_id' => ['nullable', 'exists:veterinarians,id'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'owner_name' => StorePetRequest::registrationTextRules(),
            'owner_email' => ['required', 'email', 'max:255', 'unique:owners,email,'.$ownerId.',id'],
            'owner_phone_country_code' => ['required', 'string', 'max:8'],
            'owner_phone' => ['nullable', 'string', 'max:30'],
            'owner_address' => ['nullable', 'string', 'max:255'],
        ];
    }
}
