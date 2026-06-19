<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AppointmentConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Appointment $appointment,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Confirmacion de cita - ' . $this->appointment->pet?->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlString: $this->buildHtml(),
        );
    }

    private function buildHtml(): string
    {
        $pet = $this->appointment->pet;
        $owner = $pet?->owner;
        $vet = $this->appointment->veterinarian;
        $service = $this->appointment->service;
        $clinicName = $this->appointment->clinicSetting?->name ?? 'Clinica Veterinaria';

        return <<<HTML
        <!DOCTYPE html>
        <html>
        <head><meta charset="UTF-8"></head>
        <body style="font-family: 'Segoe UI', system-ui, sans-serif; background: #f5f5f5; padding: 40px; margin: 0;">
        <div style="max-width: 600px; margin: 0 auto; background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
            <div style="background: linear-gradient(135deg, #24543f 0%, #2c664f 100%); padding: 32px; color: white;">
                <p style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.2em; opacity: 0.8; margin: 0;">{$clinicName}</p>
                <h1 style="font-size: 22px; font-weight: 800; margin: 8px 0 0;">Cita confirmada</h1>
            </div>
            <div style="padding: 32px;">
                <p style="font-size: 14px; color: #333; line-height: 1.6;">Hola {$owner?->name},</p>
                <p style="font-size: 14px; color: #333; line-height: 1.6;">Tu cita para <strong>{$pet?->name}</strong> ha sido registrada exitosamente.</p>

                <div style="background: #f8faf5; border: 1px solid #e3e8dc; border-radius: 12px; padding: 20px; margin: 24px 0;">
                    <table style="width: 100%; font-size: 13px; border-collapse: collapse;">
                        <tr><td style="padding: 6px 0; color: #6b856e; font-weight: 600;">Servicio</td><td style="padding: 6px 0; color: #1a1a1a;">{$service?->name}</td></tr>
                        <tr><td style="padding: 6px 0; color: #6b856e; font-weight: 600;">Fecha</td><td style="padding: 6px 0; color: #1a1a1a;">{$this->appointment->appointment_date?->format('d/m/Y H:i')}</td></tr>
                        <tr><td style="padding: 6px 0; color: #6b856e; font-weight: 600;">Veterinario</td><td style="padding: 6px 0; color: #1a1a1a;">{$vet?->name}</td></tr>
                        <tr><td style="padding: 6px 0; color: #6b856e; font-weight: 600;">Costo</td><td style="padding: 6px 0; color: #1a1a1a;">\${$this->appointment->service_price} COP</td></tr>
                    </table>
                </div>

                <p style="font-size: 13px; color: #666; line-height: 1.6;">Si necesitas cambiar la fecha o cancelar, contacta directamente a la clinica.</p>

                <div style="border-top: 1px solid #e3e8dc; margin-top: 24px; padding-top: 16px; text-align: center;">
                    <p style="font-size: 11px; color: #999;">{$clinicName}</p>
                </div>
            </div>
        </div>
        </body>
        </html>
        HTML;
    }
}
