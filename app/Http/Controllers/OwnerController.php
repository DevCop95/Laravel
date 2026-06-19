<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOwnerRequest;
use App\Http\Requests\UpdateOwnerRequest;
use App\Models\Owner;
use App\Traits\Auditable;

class OwnerController extends Controller
{
    use Auditable;

    public function store(StoreOwnerRequest $request)
    {
        $this->authorize('create', Owner::class);

        $owner = Owner::create($request->validated());
        $this->logAudit($owner, 'created', 'Responsable creado');

        return back();
    }

    public function update(UpdateOwnerRequest $request, Owner $owner)
    {
        $this->authorize('update', $owner);

        $owner->update($request->validated());
        $this->logAudit($owner, 'updated', 'Responsable editado', $owner->getChanges());

        return back();
    }

    public function destroy(Owner $owner)
    {
        $this->authorize('delete', $owner);

        $owner->delete();
        $this->logAudit($owner, 'deleted', 'Responsable eliminado');

        return back();
    }
}
