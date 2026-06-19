<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resumen de Servicio - {{ $clinic?->name ?? 'Clinica Veterinaria' }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; color: #1a1a1a; padding: 40px; }
        .container { max-width: 700px; margin: 0 auto; }
        .header { border-bottom: 3px solid #24543f; padding-bottom: 20px; margin-bottom: 30px; }
        .clinic-name { font-size: 28px; font-weight: 900; color: #24543f; }
        .clinic-subtitle { font-size: 12px; text-transform: uppercase; letter-spacing: 0.2em; color: #6b856e; margin-top: 4px; }
        .title { font-size: 18px; font-weight: 700; margin: 20px 0; color: #333; }
        .section { margin-bottom: 24px; }
        .section-title { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.15em; color: #6b856e; margin-bottom: 12px; border-bottom: 1px solid #e3e8dc; padding-bottom: 6px; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .field { margin-bottom: 12px; }
        .field-label { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.1em; color: #6b856e; }
        .field-value { font-size: 14px; font-weight: 500; margin-top: 4px; color: #1a1a1a; }
        .status { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.1em; }
        .status-completed { background: #dce6d1; color: #24543f; }
        .status-in_progress { background: #dce7ef; color: #36556e; }
        .status-scheduled { background: #efe3c8; color: #6e5b42; }
        .status-cancelled { background: #f3ded7; color: #8a4d3a; }
        .notes { background: #f8faf5; border: 1px solid #e3e8dc; border-radius: 12px; padding: 16px; font-size: 14px; line-height: 1.7; }
        .footer { margin-top: 40px; border-top: 1px solid #e3e8dc; padding-top: 16px; font-size: 11px; color: #6b856e; text-align: center; }
        .signature-area { margin-top: 40px; display: grid; grid-template-columns: 1fr 1fr; gap: 40px; }
        .signature-line { border-top: 1px solid #1a1a1a; padding-top: 8px; font-size: 12px; color: #666; text-align: center; }
        @media print {
            body { padding: 20px; }
            .no-print { display: none !important; }
        }
        .print-btn { position: fixed; bottom: 30px; right: 30px; background: #24543f; color: white; border: none; padding: 12px 24px; border-radius: 12px; font-size: 14px; font-weight: 600; cursor: pointer; box-shadow: 0 8px 24px rgba(36,84,63,0.3); }
        .print-btn:hover { background: #1d4634; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            @if($clinic?->logo_path)
                <img src="{{ asset('storage/' . $clinic->logo_path) }}" style="height: 50px; margin-bottom: 12px;" />
            @endif
            <div class="clinic-name">{{ $clinic?->name ?? 'Clinica Veterinaria' }}</div>
            <div class="clinic-subtitle">Resumen de servicio veterinario</div>
        </div>

        <div class="title">Servicio #{{ $appointment->id }}</div>

        <div class="section">
            <div class="section-title">Informacion del paciente</div>
            <div class="grid">
                <div class="field">
                    <div class="field-label">Paciente</div>
                    <div class="field-value">{{ $appointment->pet?->name }}</div>
                </div>
                <div class="field">
                    <div class="field-label">Especie / Raza</div>
                    <div class="field-value">{{ $appointment->pet?->species }} {{ $appointment->pet?->breed ? '/ ' . $appointment->pet->breed : '' }}</div>
                </div>
                <div class="field">
                    <div class="field-label">Responsable</div>
                    <div class="field-value">{{ $appointment->pet?->owner?->name }}</div>
                </div>
                <div class="field">
                    <div class="field-label">Contacto</div>
                    <div class="field-value">{{ $appointment->pet?->owner?->phone_country_code }} {{ $appointment->pet?->owner?->phone }}</div>
                </div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Detalles del servicio</div>
            <div class="grid">
                <div class="field">
                    <div class="field-label">Servicio</div>
                    <div class="field-value">{{ $appointment->service?->name }}</div>
                </div>
                <div class="field">
                    <div class="field-label">Tipo</div>
                    <div class="field-value">{{ $appointment->service?->service_type }}</div>
                </div>
                <div class="field">
                    <div class="field-label">Fecha</div>
                    <div class="field-value">{{ $appointment->appointment_date?->format('d/m/Y H:i') }}</div>
                </div>
                <div class="field">
                    <div class="field-label">Estado</div>
                    <div class="field-value">
                        <span class="status status-{{ $appointment->status }}">{{ $tracking['status_label'] }}</span>
                    </div>
                </div>
                <div class="field">
                    <div class="field-label">Veterinario</div>
                    <div class="field-value">{{ $appointment->veterinarian?->name }}</div>
                </div>
                <div class="field">
                    <div class="field-label">Costo</div>
                    <div class="field-value">${{ number_format($appointment->service_price, 0, ',', '.') }} COP</div>
                </div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Motivo de consulta</div>
            <div class="notes">{{ $appointment->reason }}</div>
        </div>

        @if($appointment->doctor_notes)
        <div class="section">
            <div class="section-title">Notas del doctor</div>
            <div class="notes">{{ $appointment->doctor_notes }}</div>
        </div>
        @endif

        @if($appointment->service_finished_at)
        <div class="section">
            <div class="field">
                <div class="field-label">Fecha de finalizacion</div>
                <div class="field-value">{{ $appointment->service_finished_at->format('d/m/Y H:i') }}</div>
            </div>
        </div>
        @endif

        <div class="signature-area">
            <div class="signature-line">Firma del veterinario</div>
            <div class="signature-line">Firma del responsable</div>
        </div>

        <div class="footer">
            Generado el {{ now()->format('d/m/Y H:i') }} &mdash; {{ $clinic?->name ?? 'Clinica Veterinaria' }}
        </div>
    </div>

    <button class="print-btn no-print" onclick="window.print()">
        Imprimir / Guardar PDF
    </button>
</body>
</html>
