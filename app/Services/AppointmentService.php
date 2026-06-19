<?php

namespace App\Services;

use App\Mail\AppointmentConfirmation;
use App\Mail\ServiceSummary;
use App\Models\Appointment;
use App\Models\ClinicSetting;
use App\Models\Service;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AppointmentService
{
    private ?string $clinicName = null;

    private function getClinicName(): string
    {
        return $this->clinicName ??= Cache::remember('clinic_name', 3600, fn () => ClinicSetting::first()?->name ?? 'Mi Veterinaria');
    }

    public function create(array $validated): Appointment
    {
        $validated['public_token'] = Str::uuid()->toString();
        $validated['service_finished_at'] = $validated['status'] === 'completed' ? now() : null;
        $validated['expires_at'] = now()->addDays(7);
        $validated['service_price'] = $this->resolveServicePrice($validated['service_id'], $validated['service_price'] ?? null);

        $appointment = Appointment::create($validated);

        $this->sendConfirmationEmail($appointment);

        return $appointment;
    }

    public function update(Appointment $appointment, array $validated): Appointment
    {
        $wasCompleted = $appointment->status === 'completed';
        $validated['public_token'] = $appointment->public_token ?: Str::uuid()->toString();
        $validated['service_finished_at'] = $validated['status'] === 'completed'
            ? ($appointment->service_finished_at ?? now())
            : null;
        $validated['service_price'] = $this->resolveServicePrice($validated['service_id'], $validated['service_price'] ?? null);

        $appointment->update($validated);

        if (! $wasCompleted && $validated['status'] === 'completed') {
            $this->sendSummaryEmail($appointment);
        }

        return $appointment;
    }

    public function destroy(Appointment $appointment): bool
    {
        return $appointment->delete();
    }

    private function sendConfirmationEmail(Appointment $appointment): void
    {
        try {
            $email = $appointment->pet?->owner?->email;
            if ($email) {
                Mail::to($email)->send(new AppointmentConfirmation($appointment));
            }
        } catch (\Throwable) {}
    }

    private function sendSummaryEmail(Appointment $appointment): void
    {
        try {
            $email = $appointment->pet?->owner?->email;
            if ($email) {
                Mail::to($email)->send(new ServiceSummary($appointment));
            }
        } catch (\Throwable) {}
    }

    public function resolveServicePrice(int $serviceId, mixed $manualPrice): float
    {
        if ($manualPrice !== null && $manualPrice !== '') {
            return (float) $manualPrice;
        }

        return (float) (Service::find($serviceId)?->price ?? 0);
    }

    public function buildWhatsAppUrl(?string $phone, string $trackingUrl, Appointment $appointment): ?string
    {
        if (! $phone) {
            return null;
        }

        $sanitizedPhone = preg_replace('/\D+/', '', $phone);

        if (! $sanitizedPhone) {
            return null;
        }

        $countryCode = preg_replace('/\D+/', '', $appointment->pet?->owner?->phone_country_code ?: '+57');
        $fullPhone = preg_replace('/^0+/', '', $sanitizedPhone);
        $fullPhone = str_starts_with($fullPhone, $countryCode) ? $fullPhone : $countryCode.$fullPhone;

        $message = rawurlencode(
            'Hola, el servicio de '.$appointment->pet?->name.' ha sido actualizado en '
            .$this->getClinicName().'. '
            ."Puedes revisar el seguimiento aqui: {$trackingUrl}"
        );

        return "https://wa.me/{$fullPhone}?text={$message}";
    }

    public function statusLabel(string $status): string
    {
        return match ($status) {
            'scheduled' => 'Programada',
            'in_progress' => 'En progreso',
            'completed' => 'Finalizada',
            'cancelled' => 'Cancelada',
            default => ucfirst($status),
        };
    }

    public function mapForArray(Appointment $appointment, ?array $audit = null): array
    {
        $appointment->loadMissing(['pet.owner', 'veterinarian', 'service']);

        $owner = $appointment->pet?->owner;
        $publicToken = $appointment->public_token ?: Str::uuid()->toString();
        $trackingUrl = route('tracking.show', $publicToken);

        return [
            'id' => $appointment->id,
            'pet_id' => $appointment->pet_id,
            'veterinarian_id' => $appointment->veterinarian_id,
            'service_id' => $appointment->service_id,
            'service_price' => (float) $appointment->service_price,
            'appointment_date' => $appointment->appointment_date?->toISOString(),
            'appointment_date_input' => $appointment->appointment_date?->format('Y-m-d\TH:i'),
            'reason' => $appointment->reason,
            'status' => $appointment->status,
            'status_label' => $this->statusLabel($appointment->status),
            'doctor_notes' => $appointment->doctor_notes,
            'service_finished_at' => $appointment->service_finished_at?->toISOString(),
            'tracking_url' => $trackingUrl,
            'whatsapp_url' => $this->buildWhatsAppUrl($owner?->phone, $trackingUrl, $appointment),
            'pet' => [
                'name' => $appointment->pet?->name,
                'species' => $appointment->pet?->species,
                'breed' => $appointment->pet?->breed,
                'owner' => [
                    'name' => $owner?->name,
                    'email' => $owner?->email,
                    'phone_country_code' => $owner?->phone_country_code ?: '+57',
                    'phone' => $owner?->phone,
                ],
            ],
            'service' => [
                'name' => $appointment->service?->name,
                'service_type' => $appointment->service?->service_type,
            ],
            'veterinarian' => [
                'name' => $appointment->veterinarian?->name,
                'specialty' => $appointment->veterinarian?->specialty,
            ],
            'audit' => $audit,
        ];
    }

    public function mapTracking(Appointment $appointment): array
    {
        $appointment->loadMissing(['pet.owner', 'veterinarian', 'service']);

        return [
            'pet_name' => $appointment->pet?->name,
            'species' => $appointment->pet?->species,
            'owner_name' => $appointment->pet?->owner?->name,
            'reason' => $appointment->reason,
            'status' => $appointment->status,
            'status_label' => $this->statusLabel($appointment->status),
            'doctor_notes' => $appointment->doctor_notes,
            'appointment_date' => $appointment->appointment_date?->toISOString(),
            'service_finished_at' => $appointment->service_finished_at?->toISOString(),
            'updated_at' => $appointment->updated_at?->toISOString(),
            'service_name' => $appointment->service?->name,
            'service_price' => (float) $appointment->service_price,
            'veterinarian_name' => $appointment->veterinarian?->name,
        ];
    }
}
