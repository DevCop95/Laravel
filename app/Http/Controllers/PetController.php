<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePetRequest;
use App\Http\Requests\UpdatePetRequest;
use App\Models\Pet;
use App\Services\PetService;
use App\Traits\Auditable;

class PetController extends Controller
{
    use Auditable;

    public function __construct(
        private readonly PetService $petService,
    ) {}

    public function store(StorePetRequest $request)
    {
        $this->authorize('create', Pet::class);

        $data = $request->validated();

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('pets', 'public');
        }

        $pet = $this->petService->create($data);
        $this->logAudit($pet, 'created', 'Paciente creado');

        return back();
    }

    public function update(UpdatePetRequest $request, Pet $pet)
    {
        $this->authorize('update', $pet);

        $data = $request->validated();

        if ($request->hasFile('photo')) {
            if ($pet->photo_path) {
                \Storage::disk('public')->delete($pet->photo_path);
            }
            $data['photo_path'] = $request->file('photo')->store('pets', 'public');
        }

        $pet = $this->petService->update($pet, $data);
        $this->logAudit($pet, 'updated', 'Paciente editado', $pet->getChanges());

        return back();
    }

    public function destroy(Pet $pet)
    {
        $this->authorize('delete', $pet);

        $this->petService->destroy($pet);
        $this->logAudit($pet, 'deleted', 'Paciente eliminado');

        return back();
    }
}
