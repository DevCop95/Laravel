<?php

namespace App\Http\Requests;

use App\Models\Veterinarian;
use Illuminate\Foundation\Http\FormRequest;

class UpdateVeterinarianRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $veterinarianId = $this->route('veterinarian')?->id ?? 'NULL';
        $userId = optional(Veterinarian::find($veterinarianId))->user_id ?? 'NULL';

        return [
            'name' => StorePetRequest::registrationTextRules(),
            'email' => ['required', 'email', 'max:255', 'unique:veterinarians,email,'.$veterinarianId.',id', 'unique:users,email,'.$userId.',id'],
            'phone' => ['nullable', 'string', 'max:30'],
            'specialty' => StorePetRequest::registrationTextRules(false),
        ];
    }
}
