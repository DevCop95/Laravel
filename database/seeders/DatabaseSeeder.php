<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\ClinicSetting;
use App\Models\Owner;
use App\Models\Pet;
use App\Models\Service;
use App\Models\User;
use App\Models\Veterinarian;
use App\Support\VeterinarianCredentials;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedClinic();
        $this->seedAdmin();
        $veterinarians = $this->seedVeterinarians();
        $owners = $this->seedOwners();
        $pets = $this->seedPets($owners, $veterinarians);
        $services = $this->seedServices();
        $this->seedAppointments($pets, $services, $veterinarians);
    }

    protected function seedClinic(): void
    {
        $settings = ClinicSetting::query()->first() ?? new ClinicSetting();
        $settings->name = $settings->name ?: 'Mi Veterinaria';
        $settings->save();
    }

    protected function seedAdmin(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@vet.com'],
            [
                'name' => 'Administrador',
                'password' => 'password',
                'role' => 'admin',
            ],
        );
    }

    protected function seedVeterinarians(): array
    {
        $records = [
            ['name' => 'Laura Gomez', 'email' => 'laura@greencol.com', 'phone' => '3001112233', 'specialty' => 'Medicina general'],
            ['name' => 'Carlos Medina', 'email' => 'carlos@greencol.com', 'phone' => '3002223344', 'specialty' => 'Cirugia'],
            ['name' => 'Valentina Rojas', 'email' => 'valentina@greencol.com', 'phone' => '3003334455', 'specialty' => 'Dermatologia'],
            ['name' => 'Andres Leon', 'email' => 'andres@greencol.com', 'phone' => '3004445566', 'specialty' => 'Urgencias'],
            ['name' => 'Paula Torres', 'email' => 'paula@greencol.com', 'phone' => '3005556677', 'specialty' => 'Hospitalizacion'],
        ];

        $veterinarians = [];

        foreach ($records as $record) {
            $defaultPassword = VeterinarianCredentials::defaultPassword($record['name'], $record['specialty']);

            $user = User::updateOrCreate(
                ['email' => $record['email']],
                [
                    'name' => $record['name'],
                    'password' => $defaultPassword,
                    'role' => 'veterinarian',
                ],
            );

            $veterinarians[$record['email']] = Veterinarian::updateOrCreate(
                ['email' => $record['email']],
                [
                    'user_id' => $user->id,
                    'name' => $record['name'],
                    'phone' => $record['phone'],
                    'specialty' => $record['specialty'],
                    'default_password' => $defaultPassword,
                ],
            );
        }

        return $veterinarians;
    }

    protected function seedOwners(): array
    {
        $records = [
            ['name' => 'Juan Perez', 'email' => 'juan.perez@example.com', 'phone' => '3101001001', 'address' => 'Calle 10 15 20'],
            ['name' => 'Maria Garcia', 'email' => 'maria.garcia@example.com', 'phone' => '3101001002', 'address' => 'Carrera 22 14 09'],
            ['name' => 'Luisa Herrera', 'email' => 'luisa.herrera@example.com', 'phone' => '3101001003', 'address' => 'Avenida 30 18 44'],
            ['name' => 'Pedro Martinez', 'email' => 'pedro.martinez@example.com', 'phone' => '3101001004', 'address' => 'Calle 45 19 11'],
            ['name' => 'Camila Ramirez', 'email' => 'camila.ramirez@example.com', 'phone' => '3101001005', 'address' => 'Carrera 12 08 30'],
            ['name' => 'Sofia Castro', 'email' => 'sofia.castro@example.com', 'phone' => '3101001006', 'address' => 'Transversal 16 11 27'],
        ];

        $owners = [];

        foreach ($records as $record) {
            $owners[$record['email']] = Owner::updateOrCreate(
                ['email' => $record['email']],
                [
                    'name' => $record['name'],
                    'phone_country_code' => '+57',
                    'phone' => $record['phone'],
                    'address' => $record['address'],
                ],
            );
        }

        return $owners;
    }

    protected function seedPets(array $owners, array $veterinarians): array
    {
        $records = [
            ['name' => 'Nala', 'species' => 'Perro', 'breed' => 'Labrador', 'birth_date' => '2021-03-15', 'owner' => 'juan.perez@example.com', 'vet' => 'laura@greencol.com'],
            ['name' => 'Milo', 'species' => 'Gato', 'breed' => 'Criollo', 'birth_date' => '2020-08-09', 'owner' => 'maria.garcia@example.com', 'vet' => 'valentina@greencol.com'],
            ['name' => 'Toby', 'species' => 'Perro', 'breed' => 'Beagle', 'birth_date' => '2019-01-21', 'owner' => 'luisa.herrera@example.com', 'vet' => 'carlos@greencol.com'],
            ['name' => 'Luna', 'species' => 'Gato', 'breed' => 'Siames', 'birth_date' => '2022-02-12', 'owner' => 'pedro.martinez@example.com', 'vet' => 'laura@greencol.com'],
            ['name' => 'Max', 'species' => 'Perro', 'breed' => 'Golden', 'birth_date' => '2018-11-30', 'owner' => 'camila.ramirez@example.com', 'vet' => 'andres@greencol.com'],
            ['name' => 'Coco', 'species' => 'Conejo', 'breed' => 'Mini lop', 'birth_date' => '2023-05-07', 'owner' => 'sofia.castro@example.com', 'vet' => 'paula@greencol.com'],
            ['name' => 'Kira', 'species' => 'Perro', 'breed' => 'Pastor', 'birth_date' => '2020-06-18', 'owner' => 'juan.perez@example.com', 'vet' => 'andres@greencol.com'],
            ['name' => 'Simba', 'species' => 'Gato', 'breed' => 'Persa', 'birth_date' => '2019-09-25', 'owner' => 'maria.garcia@example.com', 'vet' => 'valentina@greencol.com'],
            ['name' => 'Bruno', 'species' => 'Perro', 'breed' => 'Bulldog', 'birth_date' => '2021-12-05', 'owner' => 'camila.ramirez@example.com', 'vet' => 'carlos@greencol.com'],
        ];

        $pets = [];

        foreach ($records as $record) {
            $owner = $owners[$record['owner']];
            $veterinarian = $veterinarians[$record['vet']];

            $pets[$record['name']] = Pet::updateOrCreate(
                [
                    'name' => $record['name'],
                    'owner_id' => $owner->id,
                ],
                [
                    'species' => $record['species'],
                    'breed' => $record['breed'],
                    'birth_date' => $record['birth_date'],
                    'veterinarian_id' => $veterinarian->id,
                ],
            );
        }

        return $pets;
    }

    protected function seedServices(): array
    {
        $records = [
            ['name' => 'Consulta general', 'service_type' => 'Consulta', 'price' => 80000, 'description' => 'Revision clinica general y plan de manejo inicial'],
            ['name' => 'Vacunacion', 'service_type' => 'Prevencion', 'price' => 95000, 'description' => 'Aplicacion de vacunas segun esquema recomendado'],
            ['name' => 'Cirugia menor', 'service_type' => 'Cirugia', 'price' => 320000, 'description' => 'Procedimientos ambulatorios de baja complejidad'],
            ['name' => 'Control dermatologico', 'service_type' => 'Especialidad', 'price' => 110000, 'description' => 'Evaluacion de piel, pelaje y tratamiento'],
            ['name' => 'Urgencia', 'service_type' => 'Urgencias', 'price' => 180000, 'description' => 'Atencion prioritaria de casos agudos'],
            ['name' => 'Hospitalizacion diaria', 'service_type' => 'Hospitalizacion', 'price' => 210000, 'description' => 'Monitoreo y manejo intrahospitalario por jornada'],
        ];

        $services = [];

        foreach ($records as $record) {
            $services[$record['name']] = Service::updateOrCreate(
                ['name' => $record['name']],
                [
                    'service_type' => $record['service_type'],
                    'price' => $record['price'],
                    'description' => $record['description'],
                ],
            );
        }

        return $services;
    }

    protected function seedAppointments(array $pets, array $services, array $veterinarians): void
    {
        $records = [
            ['pet' => 'Nala', 'service' => 'Consulta general', 'vet' => 'laura@greencol.com', 'date' => now()->addDay()->setTime(9, 0), 'reason' => 'Chequeo general', 'status' => 'scheduled'],
            ['pet' => 'Milo', 'service' => 'Control dermatologico', 'vet' => 'valentina@greencol.com', 'date' => now()->addDay()->setTime(11, 0), 'reason' => 'Picazon persistente', 'status' => 'scheduled'],
            ['pet' => 'Toby', 'service' => 'Cirugia menor', 'vet' => 'carlos@greencol.com', 'date' => now()->addDays(2)->setTime(8, 30), 'reason' => 'Retiro de masa pequena', 'status' => 'scheduled'],
            ['pet' => 'Luna', 'service' => 'Vacunacion', 'vet' => 'laura@greencol.com', 'date' => now()->addDays(3)->setTime(15, 0), 'reason' => 'Refuerzo anual', 'status' => 'scheduled'],
            ['pet' => 'Max', 'service' => 'Urgencia', 'vet' => 'andres@greencol.com', 'date' => now()->subHours(4), 'reason' => 'Dolor abdominal', 'status' => 'in_progress'],
            ['pet' => 'Coco', 'service' => 'Hospitalizacion diaria', 'vet' => 'paula@greencol.com', 'date' => now()->subDay()->setTime(10, 0), 'reason' => 'Observacion post procedimiento', 'status' => 'in_progress'],
            ['pet' => 'Kira', 'service' => 'Consulta general', 'vet' => 'andres@greencol.com', 'date' => now()->subDays(2)->setTime(14, 0), 'reason' => 'Revision osteoarticular', 'status' => 'completed', 'notes' => 'Paciente estable y con analgesia formulada'],
            ['pet' => 'Simba', 'service' => 'Control dermatologico', 'vet' => 'valentina@greencol.com', 'date' => now()->subDays(3)->setTime(16, 0), 'reason' => 'Control de alergia alimentaria', 'status' => 'completed', 'notes' => 'Mejoria visible y dieta ajustada'],
            ['pet' => 'Bruno', 'service' => 'Vacunacion', 'vet' => 'carlos@greencol.com', 'date' => now()->subDays(1)->setTime(9, 30), 'reason' => 'Plan preventivo completo', 'status' => 'cancelled'],
            ['pet' => 'Nala', 'service' => 'Vacunacion', 'vet' => 'laura@greencol.com', 'date' => now()->addDays(5)->setTime(10, 30), 'reason' => 'Esquema de refuerzo', 'status' => 'scheduled'],
            ['pet' => 'Luna', 'service' => 'Consulta general', 'vet' => 'laura@greencol.com', 'date' => now()->addDays(6)->setTime(13, 15), 'reason' => 'Control nutricional', 'status' => 'scheduled'],
            ['pet' => 'Max', 'service' => 'Hospitalizacion diaria', 'vet' => 'paula@greencol.com', 'date' => now()->addDays(1)->setTime(7, 45), 'reason' => 'Seguimiento posterior a urgencia', 'status' => 'scheduled'],
        ];

        foreach ($records as $record) {
            $pet = $pets[$record['pet']];
            $service = $services[$record['service']];
            $veterinarian = $veterinarians[$record['vet']];
            $isCompleted = $record['status'] === 'completed';

            Appointment::updateOrCreate(
                [
                    'pet_id' => $pet->id,
                    'appointment_date' => $record['date'],
                ],
                [
                    'veterinarian_id' => $veterinarian->id,
                    'service_id' => $service->id,
                    'service_price' => $service->price,
                    'reason' => $record['reason'],
                    'status' => $record['status'],
                    'public_token' => Str::uuid()->toString(),
                    'doctor_notes' => $record['notes'] ?? null,
                    'service_finished_at' => $isCompleted ? $record['date']->copy()->addHour() : null,
                ],
            );
        }
    }
}
