<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOwnerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => StorePetRequest::registrationTextRules(),
            'email' => ['required', 'email', 'max:255', 'unique:owners,email,NULL,id'],
            'phone_country_code' => ['required', 'string', 'max:8'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],
        ];
    }
}
