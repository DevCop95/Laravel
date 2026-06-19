<?php

namespace App\Http\Requests;

use App\Http\Requests\StorePetRequest;
use App\Support\PhoneValidator;
use Illuminate\Foundation\Http\FormRequest;

class UpdateOwnerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $ownerId = $this->route('owner')?->id ?? 'NULL';

        return [
            'name' => StorePetRequest::registrationTextRules(),
            'email' => ['required', 'email', 'max:255', 'unique:owners,email,'.$ownerId.',id'],
            'phone_country_code' => ['required', 'string', 'max:8'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $phone = $this->input('phone');
            $countryCode = $this->input('phone_country_code');

            if ($phone && $countryCode) {
                $error = PhoneValidator::validate($phone, $countryCode);
                if ($error) {
                    $validator->errors()->add('phone', $error);
                }
            }
        });
    }
}
