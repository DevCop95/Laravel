<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVeterinarianRequest;
use App\Http\Requests\UpdateVeterinarianRequest;
use App\Models\Veterinarian;
use App\Services\VeterinarianService;
use App\Traits\Auditable;
use Illuminate\Support\Facades\Gate;

class VeterinarianController extends Controller
{
    use Auditable;

    public function __construct(
        private readonly VeterinarianService $veterinarianService,
    ) {}

    public function store(StoreVeterinarianRequest $request)
    {
        Gate::authorize('admin');

        $veterinarian = $this->veterinarianService->create($request->validated());
        $this->logAudit($veterinarian, 'created', 'Veterinario creado');

        return back()->with('status', "Veterinario creado. Usuario: {$veterinarian->email} | Clave inicial ver credenciales.");
    }

    public function update(UpdateVeterinarianRequest $request, Veterinarian $veterinarian)
    {
        Gate::authorize('admin');

        $veterinarian = $this->veterinarianService->update($veterinarian, $request->validated());
        $this->logAudit($veterinarian, 'updated', 'Veterinario editado', $veterinarian->getChanges());

        return back();
    }

    public function destroy(Veterinarian $veterinarian)
    {
        Gate::authorize('admin');

        $this->veterinarianService->destroy($veterinarian);
        $this->logAudit($veterinarian, 'deleted', 'Veterinario eliminado');

        return back();
    }
}
