<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ServiceSummary extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Appointment $appointment,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Resumen de servicio - ' . $this->appointment->pet?->name,
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
        $trackingUrl = route('tracking.show', $this->appointment->public_token);
        $printUrl = route('tracking.print', $this->appointment->public_token);

        return <<<HTML
        <!DOCTYPE html>
        <html>
        <head><meta charset="UTF-8"></head>
        <body style="font-family: 'Segoe UI', system-ui, sans-serif; background: #f5f5f5; padding: 40px; margin: 0;">
        <div style="max-width: 600px; margin: 0 auto; background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">
            <div style="background: linear-gradient(135deg, #24543f 0%, #2c664f 100%); padding: 32px; color: white;">
                <p style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.2em; opacity: 0.8; margin: 0;">{$clinicName}</p>
                <h1 style="font-size: 22px; font-weight: 800; margin: 8px 0 0;">Servicio finalizado</h1>
            </div>
            <div style="padding: 32px;">
                <p style="font-size: 14px; color: #333; line-height: 1.6;">Hola {$owner?->name},</p>
                <p style="font-size: 14px; color: #333; line-height: 1.6;">El servicio de <strong>{$pet?->name}</strong> ha sido completado. Aqui tienes el resumen:</p>

                <div style="background: #f8faf5; border: 1px solid #e3e8dc; border-radius: 12px; padding: 20px; margin: 24px 0;">
                    <table style="width: 100%; font-size: 13px; border-collapse: collapse;">
                        <tr><td style="padding: 6px 0; color: #6b856e; font-weight: 600;">Paciente</td><td style="padding: 6px 0; color: #1a1a1a;">{$pet?->name} ({$pet?->species})</td></tr>
                        <tr><td style="padding: 6px 0; color: #6b856e; font-weight: 600;">Servicio</td><td style="padding: 6px 0; color: #1a1a1a;">{$service?->name}</td></tr>
                        <tr><td style="padding: 6px 0; color: #6b856e; font-weight: 600;">Fecha</td><td style="padding: 6px 0; color: #1a1a1a;">{$this->appointment->appointment_date?->format('d/m/Y H:i')}</td></tr>
                        <tr><td style="padding: 6px 0; color: #6b856e; font-weight: 600;">Veterinario</td><td style="padding: 6px 0; color: #1a1a1a;">{$vet?->name}</td></tr>
                        <tr><td style="padding: 6px 0; color: #6b856e; font-weight: 600;">Costo</td><td style="padding: 6px 0; color: #1a1a1a; font-weight: 700;">\${$this->appointment->service_price} COP</td></tr>
                    </table>
                </div>

                {$this->appointment->doctor_notes ? '
                <div style="margin: 24px 0;">
                    <p style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.15em; color: #6b856e; font-weight: 600; margin-bottom: 8px;">Notas del doctor</p>
                    <div style="background: white; border: 1px solid #e3e8dc; border-radius: 12px; padding: 16px; font-size: 13px; line-height: 1.7; color: #333;">' . nl2br(e($this->appointment->doctor_notes)) . '</div>
                </div>' : ''}

                <div style="text-align: center; margin: 24px 0;">
                    <a href="{$trackingUrl}" style="display: inline-block; background: #24543f; color: white; text-decoration: none; padding: 12px 28px; border-radius: 12px; font-size: 13px; font-weight: 600;">Ver seguimiento en linea</a>
                </div>

                <div style="text-align: center; margin: 16px 0;">
                    <a href="{$printUrl}" style="display: inline-block; color: #24543f; text-decoration: none; font-size: 12px; font-weight: 600; border-bottom: 1px solid #24543f;">Descargar resumen en PDF</a>
                </div>

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
