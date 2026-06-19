<?php

namespace App\Http\Requests;

use App\Models\Veterinarian;
use Illuminate\Foundation\Http\FormRequest;

class StoreVeterinarianRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => StorePetRequest::registrationTextRules(),
            'email' => ['required', 'email', 'max:255', 'unique:veterinarians,email,NULL,id', 'unique:users,email,NULL,id'],
            'phone' => ['nullable', 'string', 'max:30'],
            'specialty' => StorePetRequest::registrationTextRules(false),
        ];
    }
}
