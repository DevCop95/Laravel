<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\AuditLog;
use App\Models\Owner;
use App\Models\Pet;
use App\Models\Service;
use App\Models\Veterinarian;
use App\Services\AppointmentService;
use App\Services\PetService;
use Illuminate\Support\Collection;
use Inertia\Inertia;
use Inertia\Response;

class VeterinaryController extends Controller
{
    public function __construct(
        private readonly AppointmentService $appointmentService,
        private readonly PetService $petService,
    ) {}

    public function index(): Response
    {
        $payload = $this->sharedPayload();

        return Inertia::render('Dashboard', [
            'stats' => $payload['stats'],
            'recentAppointments' => $payload['recentAppointments'],
            'appointments' => $payload['appointments'],
            'pets' => $payload['pets'],
            'veterinarians' => $payload['veterinarians'],
            'services' => $payload['services'],
            'appointmentStatuses' => $payload['appointmentStatuses'],
        ]);
    }

    public function pets(): Response
    {
        $payload = $this->sharedPayload();

        return Inertia::render('Pets/Index', [
            'pets' => $payload['pets'],
            'owners' => $payload['owners'],
            'countryOptions' => $payload['countryOptions'],
            'veterinarians' => $payload['veterinarians'],
        ]);
    }

    public function appointments(): Response
    {
        $payload = $this->sharedPayload();

        return Inertia::render('Appointments/Index', [
            'stats' => $payload['stats'],
            'recentAppointments' => $payload['recentAppointments'],
            'appointments' => $payload['appointments'],
            'pets' => $payload['pets'],
            'veterinarians' => $payload['veterinarians'],
            'services' => $payload['services'],
            'appointmentStatuses' => $payload['appointmentStatuses'],
        ]);
    }

    public function veterinarians(): Response
    {
        abort_unless(auth()->user()?->role === 'admin', 403);

        $payload = $this->sharedPayload();

        return Inertia::render('Veterinarians/Index', [
            'veterinarians' => $payload['veterinarians'],
        ]);
    }

    public function services(): Response
    {
        abort_unless(auth()->user()?->role === 'admin', 403);

        $payload = $this->sharedPayload();

        return Inertia::render('Services/Index', [
            'services' => $payload['services'],
        ]);
    }

    public function owners(): Response
    {
        $payload = $this->sharedPayload();

        return Inertia::render('Owners/Index', [
            'owners' => $payload['owners'],
            'countryOptions' => $payload['countryOptions'],
        ]);
    }

    public function petHistory(Pet $pet): Response
    {
        $this->authorize('view', $pet);

        $pet->load(['owner', 'veterinarian', 'appointments.service', 'appointments.veterinarian'])
            ->loadCount('appointments');

        $appointments = $pet->appointments()
            ->with(['service', 'veterinarian'])
            ->orderBy('appointment_date', 'desc')
            ->get()
            ->map(fn (Appointment $a) => $this->appointmentService->mapForArray($a));

        return Inertia::render('Pets/History', [
            'pet' => [
                'id' => $pet->id,
                'name' => $pet->name,
                'species' => $pet->species,
                'breed' => $pet->breed,
                'birth_date' => $pet->birth_date,
                'photo_path' => $pet->photo_path,
                'owner' => [
                    'id' => $pet->owner?->id,
                    'name' => $pet->owner?->name,
                    'email' => $pet->owner?->email,
                    'phone' => $pet->owner?->phone,
                    'phone_country_code' => $pet->owner?->phone_country_code,
                ],
                'veterinarian' => [
                    'id' => $pet->veterinarian?->id,
                    'name' => $pet->veterinarian?->name,
                ],
                'appointments_count' => $pet->appointments_count,
            ],
            'appointments' => $appointments,
        ]);
    }

    public function showTracking(string $token): Response
    {
        $appointment = Appointment::with(['pet.owner', 'veterinarian', 'service'])
            ->where('public_token', $token)
            ->firstOrFail();

        if ($appointment->expires_at && $appointment->expires_at->isPast()) {
            abort(410, 'Este enlace de seguimiento ha expirado.');
        }

        return Inertia::render('Public/Tracking', [
            'clinicName' => \App\Models\ClinicSetting::first()?->name ?? 'Mi Veterinaria',
            'tracking' => $this->appointmentService->mapTracking($appointment),
        ]);
    }

    public function trackingData(string $token)
    {
        $appointment = Appointment::with(['pet.owner', 'veterinarian', 'service'])
            ->where('public_token', $token)
            ->firstOrFail();

        if ($appointment->expires_at && $appointment->expires_at->isPast()) {
            return response()->json(['error' => 'Este enlace de seguimiento ha expirado.'], 410);
        }

        return response()->json($this->appointmentService->mapTracking($appointment));
    }

    public function printSummary(string $token)
    {
        $appointment = Appointment::with(['pet.owner', 'veterinarian', 'service'])
            ->where('public_token', $token)
            ->firstOrFail();

        $clinic = \App\Models\ClinicSetting::first();

        return response()->view('printable-summary', [
            'appointment' => $appointment,
            'clinic' => $clinic,
            'tracking' => $this->appointmentService->mapTracking($appointment),
        ]);
    }

    public function exportAppointmentsCsv()
    {
        $appointments = Appointment::with(['pet.owner', 'veterinarian', 'service'])
            ->orderBy('appointment_date')
            ->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="citas_'.date('Y-m-d').'.csv"',
        ];

        // Neutraliza la inyeccion de formulas CSV: un valor que empiece por
        // = + - @ (o tab/CR) se ejecutaria como formula en Excel/Sheets.
        $csvSafe = static function ($value): string {
            $value = (string) $value;

            if ($value !== '' && in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
                return "'".$value;
            }

            return $value;
        };

        $callback = function () use ($appointments, $csvSafe) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Paciente', 'Especie', 'Responsable', 'Veterinario', 'Servicio', 'Fecha', 'Estado', 'Costo', 'Notas']);

            foreach ($appointments as $a) {
                fputcsv($file, [
                    $csvSafe($a->pet?->name),
                    $csvSafe($a->pet?->species),
                    $csvSafe($a->pet?->owner?->name),
                    $csvSafe($a->veterinarian?->name),
                    $csvSafe($a->service?->name),
                    $a->appointment_date?->format('Y-m-d H:i'),
                    $this->appointmentService->statusLabel($a->status),
                    $a->service_price,
                    $csvSafe($a->doctor_notes),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function auditLog()
    {
        abort_unless(auth()->user()?->role === 'admin', 403);

        $logs = \App\Models\AuditLog::query()
            ->latest()
            ->paginate(30);

        $modelLabels = [
            'App\\Models\\Appointment' => 'Cita',
            'App\\Models\\Pet' => 'Paciente',
            'App\\Models\\Owner' => 'Responsable',
            'App\\Models\\Veterinarian' => 'Veterinario',
            'App\\Models\\Service' => 'Servicio',
            'App\\Models\\InventoryItem' => 'Inventario',
        ];

        return Inertia::render('Audit/Index', [
            'logs' => $logs->items(),
            'pagination' => [
                'current_page' => $logs->currentPage(),
                'last_page' => $logs->lastPage(),
                'per_page' => $logs->perPage(),
                'total' => $logs->total(),
            ],
            'modelLabels' => $modelLabels,
        ]);
    }

    protected function sharedPayload(): array
    {
        $user = auth()->user();
        $isVeterinarian = $user?->role === 'veterinarian';
        $veterinarianId = $user?->veterinarian?->id;

        $appointmentsQuery = Appointment::with(['pet.owner', 'veterinarian', 'service'])
            ->orderBy('appointment_date');

        $petsQuery = Pet::with(['owner', 'veterinarian'])
            ->orderBy('name');

        if ($isVeterinarian) {
            if ($veterinarianId) {
                $appointmentsQuery->where('veterinarian_id', $veterinarianId);
                $petsQuery->where('veterinarian_id', $veterinarianId);
            } else {
                $appointmentsQuery->whereRaw('1 = 0');
                $petsQuery->whereRaw('1 = 0');
            }
        }

        $appointments = $appointmentsQuery->get();
        $pets = $petsQuery->get();
        $veterinarians = $isVeterinarian
            ? Veterinarian::when($veterinarianId, fn ($query) => $query->whereKey($veterinarianId), fn ($query) => $query->whereRaw('1 = 0'))->get()
            : Veterinarian::orderBy('name')->get();
        $services = Service::orderBy('name')->get();

        $appointmentAuditMap = $this->latestAuditMap(Appointment::class, $appointments->pluck('id'));
        $petAuditMap = $this->latestAuditMap(Pet::class, $pets->pluck('id'));
        $veterinarianAuditMap = $this->latestAuditMap(Veterinarian::class, $veterinarians->pluck('id'));
        $serviceAuditMap = $this->latestAuditMap(Service::class, $services->pluck('id'));

        $ownersQuery = Owner::with(['pets' => fn ($q) => $q->orderBy('name')->select(['id', 'name', 'species', 'owner_id'])])
            ->orderBy('name')
            ->when($isVeterinarian && $pets->isNotEmpty(), fn ($query) => $query->whereIn('id', $pets->pluck('owner_id')->filter()->unique()))
            ->get(['id', 'name', 'email', 'phone_country_code', 'phone', 'address']);

        $ownerAuditMap = $this->latestAuditMap(Owner::class, $ownersQuery->pluck('id'));

        return [
            'stats' => [
                'owners' => $isVeterinarian ? Owner::whereIn('id', $pets->pluck('owner_id')->filter())->count() : Owner::count(),
                'pets' => $pets->count(),
                'appointments' => $appointments->where('appointment_date', '>=', now())->count(),
                'services' => $services->count(),
                'veterinarians' => $veterinarians->count(),
            ],
            'recentAppointments' => $appointments
                ->filter(fn (Appointment $appointment) => $appointment->appointment_date >= now())
                ->take(5)
                ->values()
                ->map(fn (Appointment $appointment) => $this->appointmentService->mapForArray($appointment, $appointmentAuditMap[$appointment->id] ?? null)),
            'appointments' => $appointments->map(fn (Appointment $appointment) => $this->appointmentService->mapForArray($appointment, $appointmentAuditMap[$appointment->id] ?? null)),
            'pets' => $pets->map(fn (Pet $pet) => $this->petService->mapForArray($pet, $petAuditMap[$pet->id] ?? null)),
            'owners' => $ownersQuery
                ->map(fn (Owner $owner) => [
                    'id' => $owner->id,
                    'name' => $owner->name,
                    'email' => $owner->email,
                    'phone_country_code' => $owner->phone_country_code ?: '+57',
                    'phone' => $owner->phone,
                    'address' => $owner->address,
                    'pets' => $owner->pets->map(fn (Pet $pet) => [
                        'id' => $pet->id,
                        'name' => $pet->name,
                        'species' => $pet->species,
                    ]),
                    'audit' => $ownerAuditMap[$owner->id] ?? null,
                ]),
            'veterinarians' => $veterinarians
                ->map(fn (Veterinarian $veterinarian) => [
                    'id' => $veterinarian->id,
                    'user_id' => $veterinarian->user_id,
                    'name' => $veterinarian->name,
                    'email' => $veterinarian->email,
                    'phone' => $veterinarian->phone,
                    'specialty' => $veterinarian->specialty,
                    'default_password' => null,
                    'audit' => $veterinarianAuditMap[$veterinarian->id] ?? null,
                ]),
            'services' => $services
                ->map(fn (Service $service) => [
                    'id' => $service->id,
                    'name' => $service->name,
                    'service_type' => $service->service_type,
                    'price' => (float) $service->price,
                    'description' => $service->description,
                    'audit' => $serviceAuditMap[$service->id] ?? null,
                ]),
            'countryOptions' => $this->countryOptions(),
            'appointmentStatuses' => $this->appointmentStatuses(),
        ];
    }

    protected function latestAuditMap(string $modelClass, Collection $ids): array
    {
        if ($ids->isEmpty()) {
            return [];
        }

        return AuditLog::query()
            ->where('auditable_type', $modelClass)
            ->whereIn('auditable_id', $ids->all())
            ->latest()
            ->get()
            ->groupBy('auditable_id')
            ->map(fn (Collection $logs) => $this->formatAuditSummary($logs->first()))
            ->all();
    }

    protected function formatAuditSummary(?AuditLog $log): ?array
    {
        if (! $log) {
            return null;
        }

        return [
            'action' => $log->action,
            'action_label' => match ($log->action) {
                'created' => 'Creado',
                'updated' => 'Editado',
                'deleted' => 'Eliminado',
                default => ucfirst($log->action),
            },
            'user_name' => $log->user_name ?? 'Sistema',
            'description' => $log->description,
            'created_at' => $log->created_at?->toISOString(),
        ];
    }

    protected function countryOptions(): array
    {
        return [
            ['code' => '+57', 'label' => 'Colombia (+57)'],
            ['code' => '+31', 'label' => 'Paises Bajos (+31)'],
            ['code' => '+34', 'label' => 'Espana (+34)'],
            ['code' => '+51', 'label' => 'Peru (+51)'],
            ['code' => '+52', 'label' => 'Mexico (+52)'],
            ['code' => '+54', 'label' => 'Argentina (+54)'],
            ['code' => '+56', 'label' => 'Chile (+56)'],
            ['code' => '+1', 'label' => 'Estados Unidos (+1)'],
        ];
    }

    protected function appointmentStatuses(): array
    {
        return [
            ['value' => 'scheduled', 'label' => 'Programada'],
            ['value' => 'in_progress', 'label' => 'En progreso'],
            ['value' => 'completed', 'label' => 'Finalizada'],
            ['value' => 'cancelled', 'label' => 'Cancelada'],
        ];
    }
}
