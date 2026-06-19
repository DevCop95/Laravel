<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAppointmentRequest;
use App\Http\Requests\UpdateAppointmentRequest;
use App\Models\Appointment;
use App\Services\AppointmentService;
use App\Traits\Auditable;

class AppointmentController extends Controller
{
    use Auditable;

    public function __construct(
        private readonly AppointmentService $appointmentService,
    ) {}

    public function store(StoreAppointmentRequest $request)
    {
        $this->authorize('create', Appointment::class);

        $appointment = $this->appointmentService->create($request->validated());
        $this->logAudit($appointment, 'created', 'Cita creada');

        return back();
    }

    public function update(UpdateAppointmentRequest $request, Appointment $appointment)
    {
        $this->authorize('update', $appointment);

        $appointment = $this->appointmentService->update($appointment, $request->validated());
        $this->logAudit($appointment, 'updated', 'Cita editada', $appointment->getChanges());

        return back();
    }

    public function destroy(Appointment $appointment)
    {
        $this->authorize('delete', $appointment);

        $this->appointmentService->destroy($appointment);
        $this->logAudit($appointment, 'deleted', 'Cita eliminada');

        return back();
    }
}
