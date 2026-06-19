<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Models\Service;
use App\Traits\Auditable;
use Illuminate\Support\Facades\Gate;

class ServiceController extends Controller
{
    use Auditable;

    public function store(StoreServiceRequest $request)
    {
        Gate::authorize('admin');

        $service = Service::create($request->validated());
        $this->logAudit($service, 'created', 'Servicio creado');

        return back();
    }

    public function update(UpdateServiceRequest $request, Service $service)
    {
        Gate::authorize('admin');

        $service->update($request->validated());
        $this->logAudit($service, 'updated', 'Servicio editado', $service->getChanges());

        return back();
    }

    public function destroy(Service $service)
    {
        Gate::authorize('admin');

        $service->delete();
        $this->logAudit($service, 'deleted', 'Servicio eliminado');

        return back();
    }
}
