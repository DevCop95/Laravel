<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pet_id' => ['required', 'exists:pets,id'],
            'veterinarian_id' => ['required', 'exists:veterinarians,id'],
            'service_id' => ['required', 'exists:services,id'],
            'appointment_date' => ['required', 'date'],
            'reason' => ['required', 'string', 'max:1000'],
            'status' => ['required', 'in:scheduled,in_progress,completed,cancelled'],
            'doctor_notes' => ['nullable', 'string', 'max:4000'],
            'service_price' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
