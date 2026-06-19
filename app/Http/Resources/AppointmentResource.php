<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'pet_id' => $this->pet_id,
            'veterinarian_id' => $this->veterinarian_id,
            'service_id' => $this->service_id,
            'service_price' => (float) $this->service_price,
            'appointment_date' => optional($this->appointment_date)->toISOString(),
            'appointment_date_input' => optional($this->appointment_date)->format('Y-m-d\TH:i'),
            'reason' => $this->reason,
            'status' => $this->status,
            'doctor_notes' => $this->doctor_notes,
            'service_finished_at' => optional($this->service_finished_at)->toISOString(),
            'public_token' => $this->public_token,
        ];
    }
}
