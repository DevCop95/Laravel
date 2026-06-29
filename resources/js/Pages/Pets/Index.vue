<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import Modal from '@/Components/Modal.vue';
import AuditInfo from '@/Components/AuditInfo.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    pets: Array,
    owners: Array,
    countryOptions: Array,
    veterinarians: Array,
});

const selectedPetId = ref(null);
const showCreateModal = ref(false);

const sanitizeRecordText = (value) => value.replace(/[^\p{L}\p{N} ]/gu, '').replace(/\s+/g, ' ').trimStart();

const sanitizeField = (form, field) => {
    form[field] = sanitizeRecordText(form[field] ?? '');
};

const createPetForm = useForm({
    name: '',
    species: 'Perro',
    breed: '',
    birth_date: '',
    veterinarian_id: '',
    photo: null,
    owner_name: '',
    owner_email: '',
    owner_phone_country_code: '+57',
    owner_phone: '',
    owner_address: '',
});

const petForms = Object.fromEntries(
    props.pets.map((pet) => [
        pet.id,
        useForm({
            name: pet.name,
            species: pet.species,
            breed: pet.breed ?? '',
            birth_date: pet.birth_date ?? '',
            veterinarian_id: pet.veterinarian_id ?? '',
            photo: null,
            owner_name: pet.owner_name ?? '',
            owner_email: pet.owner_email ?? '',
            owner_phone_country_code: pet.owner_phone_country_code ?? '+57',
            owner_phone: pet.owner_phone ?? '',
            owner_address: pet.owner_address ?? '',
        }),
    ]),
);

const ownerForms = Object.fromEntries(
    props.owners.map((owner) => [
        owner.id,
        useForm({
            name: owner.name,
            email: owner.email,
            phone_country_code: owner.phone_country_code || '+57',
            phone: owner.phone || '',
            address: owner.address || '',
        }),
    ]),
);

const petColumns = [
    { key: 'name', label: 'Paciente', sortable: true },
    { key: 'species', label: 'Especie', sortable: true },
    { key: 'owner_name', label: 'Responsable', sortable: true },
    { key: 'veterinarian_name', label: 'Veterinario', sortable: true },
    { key: 'owner_phone', label: 'Contacto' },
    { key: 'actions', label: 'Acciones', sortable: false },
];

const petSearchFields = ['name', 'species', 'breed', 'owner_name', 'owner_email', 'owner_phone', 'veterinarian_name'];

const selectedPet = computed(() => props.pets.find((pet) => pet.id === selectedPetId.value) ?? null);

const openCreateModal = () => {
    createPetForm.reset();
    createPetForm.species = 'Perro';
    createPetForm.owner_phone_country_code = '+57';
    showCreateModal.value = true;
};

const closeCreateModal = () => {
    showCreateModal.value = false;
};

const createPet = () => {
    createPetForm.post(route('pets.store'), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            createPetForm.reset();
            closeCreateModal();
        },
    });
};

const openPetModal = (petId) => {
    selectedPetId.value = petId;
};

const closePetModal = () => {
    selectedPetId.value = null;
};

const savePet = (petId) => {
    petForms[petId].post(route('pets.update', petId), {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: closePetModal,
    });
};

const saveOwner = (ownerId) => {
    ownerForms[ownerId].patch(route('owners.update', ownerId), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Pacientes" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-2">
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-[var(--clinic-muted)]">Pacientes</p>
                <h2 class="text-3xl font-black leading-tight text-[var(--clinic-text)]">Mascotas y responsables</h2>
                <p class="max-w-3xl text-sm leading-6 text-[var(--clinic-muted)]">
                    Cada paciente ahora puede tener un veterinario responsable visible desde la tabla y editable en modal.
                </p>
            </div>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-7xl space-y-8 sm:px-6 lg:px-8">
                <!-- Pets Table -->
                <section class="rounded-[2rem] border border-[var(--clinic-border)] bg-[var(--clinic-panel-bg)]/95 p-6 shadow-[0_28px_90px_-55px_color-mix(in_srgb,var(--clinic-primary)_45%,transparent)]">
                    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--clinic-muted)]">Base clinica</p>
                            <h3 class="mt-2 text-2xl font-black text-[var(--clinic-text)]">Pacientes registrados</h3>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="rounded-full bg-[var(--clinic-highlight)] px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-[var(--clinic-primary)]">{{ props.pets.length }} en base</span>
                            <button type="button" class="inline-flex items-center gap-2 rounded-full bg-[var(--clinic-primary)] px-5 py-3 text-xs font-semibold uppercase tracking-[0.22em] text-[var(--clinic-highlight)] transition-all duration-200 hover:bg-[var(--clinic-primary-dark)]" @click="openCreateModal">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="12" x2="12" y1="5" y2="19" /><line x1="5" x2="19" y1="12" y2="12" />
                                </svg>
                                Crear paciente
                            </button>
                        </div>
                    </div>

                    <div class="mt-6">
                        <DataTable
                            :columns="petColumns"
                            :rows="props.pets"
                            :search-fields="petSearchFields"
                            search-placeholder="Filtrar por mascota, responsable o veterinario"
                            empty-message="No hay pacientes registrados."
                            :per-page="8"
                        >
                            <template #empty-action>
                                <button type="button" class="inline-flex items-center gap-2 rounded-full bg-[var(--clinic-primary)] px-5 py-2.5 text-xs font-semibold uppercase tracking-[0.22em] text-[var(--clinic-highlight)] transition-all duration-200 hover:bg-[var(--clinic-primary-dark)]" @click="openCreateModal">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="12" x2="12" y1="5" y2="19" /><line x1="5" x2="19" y1="12" y2="12" />
                                    </svg>
                                    Crear primer paciente
                                </button>
                            </template>
                            <template #cell-name="{ row }">
                                <div class="flex items-center gap-3">
                                    <img v-if="row.photo_path" :src="'/storage/' + row.photo_path" class="h-10 w-10 rounded-xl object-cover" />
                                    <div v-else class="flex h-10 w-10 items-center justify-center rounded-xl bg-[color-mix(in_srgb,var(--clinic-primary)_8%,white)]">
                                        <svg class="h-5 w-5 text-[color-mix(in_srgb,var(--clinic-primary)_40%,white)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-bold text-[var(--clinic-text)]">{{ row.name }}</p>
                                        <p class="mt-0.5 text-xs text-[var(--clinic-muted)]">{{ row.breed || 'Sin raza registrada' }}</p>
                                    </div>
                                </div>
                            </template>
                            <template #cell-owner_name="{ row }">
                                <div>
                                    <p class="font-medium text-[var(--clinic-text)]">{{ row.owner_name }}</p>
                                    <p class="mt-0.5 text-xs text-[var(--clinic-muted)]">{{ row.owner_email }}</p>
                                </div>
                            </template>
                            <template #cell-veterinarian_name="{ row }">
                                <span class="text-[var(--clinic-muted)]">{{ row.veterinarian_name || 'Sin asignar' }}</span>
                            </template>
                            <template #cell-owner_phone="{ row }">
                                <span class="text-[var(--clinic-muted)]">{{ row.owner_phone_country_code }} {{ row.owner_phone || 'Sin telefono' }}</span>
                            </template>
                            <template #cell-actions="{ row }">
                                <div class="flex flex-wrap gap-2">
                                    <Link :href="route('pets.history', row.id)" class="inline-flex items-center gap-1.5 rounded-full border border-[var(--clinic-border)] px-3.5 py-1.5 text-[11px] font-semibold uppercase tracking-[0.14em] text-[var(--clinic-primary)] transition-all duration-150 hover:border-[var(--clinic-primary)] hover:bg-[color-mix(in_srgb,var(--clinic-primary)_5%,white)]">
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10" /><polyline points="12 6 12 12 16 14" />
                                        </svg>
                                        Historial
                                    </Link>
                                    <button type="button" class="inline-flex items-center gap-1.5 rounded-full border border-[var(--clinic-border)] bg-[var(--clinic-highlight)] px-3.5 py-1.5 text-[11px] font-semibold uppercase tracking-[0.14em] text-[var(--clinic-primary)] transition-all duration-150 hover:border-[var(--clinic-primary)] hover:bg-[color-mix(in_srgb,var(--clinic-primary)_8%,white)]" @click="openPetModal(row.id)">
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z" />
                                        </svg>
                                        Editar
                                    </button>
                                </div>
                            </template>
                        </DataTable>
                    </div>
                </section>

                <!-- Owners Table -->
                <section class="rounded-[2rem] border border-[var(--clinic-border)] bg-[var(--clinic-shell-surface)] p-6 shadow-[0_28px_90px_-55px_color-mix(in_srgb,var(--clinic-primary)_45%,transparent)]">
                    <div class="flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--clinic-muted)]">Responsables</p>
                            <h3 class="mt-2 text-2xl font-black text-[var(--clinic-text)]">Directorio de contacto</h3>
                        </div>
                    </div>

                    <div class="mt-6 overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="border-b border-[var(--clinic-border)] text-left text-xs font-semibold uppercase tracking-[0.18em] text-[var(--clinic-muted)]">
                                    <th class="px-4 py-3">Nombre</th>
                                    <th class="px-4 py-3">Correo</th>
                                    <th class="px-4 py-3">Telefono</th>
                                    <th class="px-4 py-3">Direccion</th>
                                    <th class="px-4 py-3">Mascotas</th>
                                    <th class="px-4 py-3">Accion</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="owner in props.owners" :key="owner.id" class="border-b border-[var(--clinic-border)]/60 transition-colors duration-150 hover:bg-[color-mix(in_srgb,var(--clinic-primary)_3%,white)]">
                                    <td class="px-4 py-4"><input v-model="ownerForms[owner.id].name" type="text" class="block w-full rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-panel-bg)] px-3 py-2 text-sm text-[var(--clinic-text)] shadow-sm transition-all duration-200 focus:border-[var(--clinic-primary)] focus:outline-none focus:ring-2 focus:ring-[color-mix(in_srgb,var(--clinic-primary)_15%,white)]" @input="sanitizeField(ownerForms[owner.id], 'name')" /></td>
                                    <td class="px-4 py-4"><input v-model="ownerForms[owner.id].email" type="email" class="block w-full rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-panel-bg)] px-3 py-2 text-sm text-[var(--clinic-text)] shadow-sm transition-all duration-200 focus:border-[var(--clinic-primary)] focus:outline-none focus:ring-2 focus:ring-[color-mix(in_srgb,var(--clinic-primary)_15%,white)]" /></td>
                                    <td class="px-4 py-4">
                                        <div class="grid gap-2 md:grid-cols-[120px_1fr]">
                                            <select v-model="ownerForms[owner.id].phone_country_code" class="block w-full rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-panel-bg)] px-3 py-2 text-sm text-[var(--clinic-text)] shadow-sm transition-all duration-200 focus:border-[var(--clinic-primary)] focus:outline-none focus:ring-2 focus:ring-[color-mix(in_srgb,var(--clinic-primary)_15%,white)]">
                                                <option v-for="country in props.countryOptions" :key="country.code" :value="country.code">{{ country.code }}</option>
                                            </select>
                                            <input v-model="ownerForms[owner.id].phone" type="text" class="block w-full rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-panel-bg)] px-3 py-2 text-sm text-[var(--clinic-text)] shadow-sm transition-all duration-200 focus:border-[var(--clinic-primary)] focus:outline-none focus:ring-2 focus:ring-[color-mix(in_srgb,var(--clinic-primary)_15%,white)]" />
                                        </div>
                                    </td>
                                    <td class="px-4 py-4"><input v-model="ownerForms[owner.id].address" type="text" class="block w-full rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-panel-bg)] px-3 py-2 text-sm text-[var(--clinic-text)] shadow-sm transition-all duration-200 focus:border-[var(--clinic-primary)] focus:outline-none focus:ring-2 focus:ring-[color-mix(in_srgb,var(--clinic-primary)_15%,white)]" /></td>
                                    <td class="px-4 py-4">
                                        <div class="flex flex-wrap gap-2">
                                            <span v-for="pet in owner.pets" :key="pet.id" class="rounded-full bg-[var(--clinic-highlight)] px-3 py-1 text-xs font-semibold text-[var(--clinic-primary)]">{{ pet.name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4">
                                        <button type="button" class="inline-flex items-center gap-1.5 rounded-full border border-[var(--clinic-primary)] bg-[var(--clinic-primary)] px-3.5 py-1.5 text-[11px] font-semibold uppercase tracking-[0.14em] text-[var(--clinic-highlight)] transition-all duration-150 hover:bg-[var(--clinic-primary-dark)]" @click="saveOwner(owner.id)">
                                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" />
                                                <polyline points="17 21 17 13 7 13 7 21" />
                                                <polyline points="7 3 7 8 15 8" />
                                            </svg>
                                            Guardar
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </div>

        <!-- Create Pet Modal -->
        <Modal :show="showCreateModal" max-width="2xl" @close="closeCreateModal">
            <div class="p-6 sm:p-8">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--clinic-muted)]">Nuevo registro</p>
                        <h3 class="mt-2 text-2xl font-black text-[var(--clinic-text)]">Crear paciente</h3>
                    </div>
                    <button type="button" class="inline-flex items-center gap-2 rounded-full border border-[var(--clinic-border)] px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-[var(--clinic-muted)] transition-all duration-150 hover:border-[var(--clinic-primary)] hover:text-[var(--clinic-primary)]" @click="closeCreateModal">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" x2="6" y1="6" y2="18" /><line x1="6" x2="18" y1="6" y2="18" />
                        </svg>
                        Cerrar
                    </button>
                </div>

                <form class="mt-6 grid gap-4 md:grid-cols-2" @submit.prevent="createPet">
                    <input v-model="createPetForm.name" type="text" placeholder="Paciente" class="block w-full rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-shell-surface)] px-4 py-3 text-sm text-[var(--clinic-text)] shadow-sm transition-all duration-200 focus:border-[var(--clinic-primary)] focus:outline-none focus:ring-2 focus:ring-[color-mix(in_srgb,var(--clinic-primary)_15%,white)]" @input="sanitizeField(createPetForm, 'name')" />
                    <input v-model="createPetForm.species" type="text" placeholder="Especie" class="block w-full rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-shell-surface)] px-4 py-3 text-sm text-[var(--clinic-text)] shadow-sm transition-all duration-200 focus:border-[var(--clinic-primary)] focus:outline-none focus:ring-2 focus:ring-[color-mix(in_srgb,var(--clinic-primary)_15%,white)]" @input="sanitizeField(createPetForm, 'species')" />
                    <input v-model="createPetForm.breed" type="text" placeholder="Raza" class="block w-full rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-shell-surface)] px-4 py-3 text-sm text-[var(--clinic-text)] shadow-sm transition-all duration-200 focus:border-[var(--clinic-primary)] focus:outline-none focus:ring-2 focus:ring-[color-mix(in_srgb,var(--clinic-primary)_15%,white)]" @input="sanitizeField(createPetForm, 'breed')" />
                    <input v-model="createPetForm.birth_date" type="date" class="block w-full rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-shell-surface)] px-4 py-3 text-sm text-[var(--clinic-text)] shadow-sm transition-all duration-200 focus:border-[var(--clinic-primary)] focus:outline-none focus:ring-2 focus:ring-[color-mix(in_srgb,var(--clinic-primary)_15%,white)]" />
                    <select v-model="createPetForm.veterinarian_id" class="block w-full rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-shell-surface)] px-4 py-3 text-sm text-[var(--clinic-text)] shadow-sm transition-all duration-200 focus:border-[var(--clinic-primary)] focus:outline-none focus:ring-2 focus:ring-[color-mix(in_srgb,var(--clinic-primary)_15%,white)]">
                        <option value="">Sin veterinario responsable</option>
                        <option v-for="veterinarian in props.veterinarians" :key="veterinarian.id" :value="veterinarian.id">{{ veterinarian.name }} {{ veterinarian.specialty || 'General' }}</option>
                    </select>
                    <label class="flex w-full cursor-pointer items-center justify-center gap-2 rounded-2xl border border-dashed border-[var(--clinic-border)] bg-[var(--clinic-shell-surface)] px-4 py-3 text-sm text-[var(--clinic-muted)] transition-all duration-200 hover:border-[var(--clinic-primary)] hover:text-[var(--clinic-primary)]">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" /><polyline points="17 8 12 3 7 8" /><line x1="12" x2="12" y1="3" y2="15" />
                        </svg>
                        <span>{{ createPetForm.photo ? createPetForm.photo.name : 'Foto del paciente' }}</span>
                        <input type="file" accept="image/*" class="hidden" @change="createPetForm.photo = $event.target.files[0]" />
                    </label>
                    <input v-model="createPetForm.owner_name" type="text" placeholder="Responsable" class="block w-full rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-shell-surface)] px-4 py-3 text-sm text-[var(--clinic-text)] shadow-sm transition-all duration-200 focus:border-[var(--clinic-primary)] focus:outline-none focus:ring-2 focus:ring-[color-mix(in_srgb,var(--clinic-primary)_15%,white)]" @input="sanitizeField(createPetForm, 'owner_name')" />
                    <input v-model="createPetForm.owner_email" type="email" placeholder="Correo" class="block w-full rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-shell-surface)] px-4 py-3 text-sm text-[var(--clinic-text)] shadow-sm transition-all duration-200 focus:border-[var(--clinic-primary)] focus:outline-none focus:ring-2 focus:ring-[color-mix(in_srgb,var(--clinic-primary)_15%,white)]" />
                    <div class="grid gap-2 md:grid-cols-[140px_1fr]">
                        <select v-model="createPetForm.owner_phone_country_code" class="block w-full rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-shell-surface)] px-4 py-3 text-sm text-[var(--clinic-text)] shadow-sm transition-all duration-200 focus:border-[var(--clinic-primary)] focus:outline-none focus:ring-2 focus:ring-[color-mix(in_srgb,var(--clinic-primary)_15%,white)]">
                            <option v-for="country in props.countryOptions" :key="country.code" :value="country.code">{{ country.label }}</option>
                        </select>
                        <input v-model="createPetForm.owner_phone" type="text" placeholder="Celular" class="block w-full rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-shell-surface)] px-4 py-3 text-sm text-[var(--clinic-text)] shadow-sm transition-all duration-200 focus:border-[var(--clinic-primary)] focus:outline-none focus:ring-2 focus:ring-[color-mix(in_srgb,var(--clinic-primary)_15%,white)]" />
                    </div>
                    <input v-model="createPetForm.owner_address" type="text" placeholder="Direccion" class="md:col-span-2 block w-full rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-shell-surface)] px-4 py-3 text-sm text-[var(--clinic-text)] shadow-sm transition-all duration-200 focus:border-[var(--clinic-primary)] focus:outline-none focus:ring-2 focus:ring-[color-mix(in_srgb,var(--clinic-primary)_15%,white)]" />

                    <div class="md:col-span-2 flex flex-wrap justify-end gap-3">
                        <button type="button" class="inline-flex items-center gap-2 rounded-full border border-[var(--clinic-border)] px-5 py-3 text-xs font-semibold uppercase tracking-[0.22em] text-[var(--clinic-muted)] transition-all duration-150 hover:border-[var(--clinic-primary)] hover:text-[var(--clinic-primary)]" @click="closeCreateModal">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="18" x2="6" y1="6" y2="18" /><line x1="6" x2="18" y1="6" y2="18" />
                            </svg>
                            Cerrar
                        </button>
                        <button type="submit" class="inline-flex items-center gap-2 rounded-full bg-[var(--clinic-primary)] px-5 py-3 text-xs font-semibold uppercase tracking-[0.22em] text-[var(--clinic-highlight)] transition-all duration-200 hover:bg-[var(--clinic-primary-dark)]">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="12" x2="12" y1="5" y2="19" /><line x1="5" x2="19" y1="12" y2="12" />
                            </svg>
                            Crear paciente
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Edit Pet Modal -->
        <Modal :show="!!selectedPet" max-width="2xl" @close="closePetModal">
            <div v-if="selectedPet" class="p-6 sm:p-8">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--clinic-muted)]">Editar paciente</p>
                        <h3 class="mt-2 text-2xl font-black text-[var(--clinic-text)]">{{ selectedPet.name }}</h3>
                    </div>
                    <button type="button" class="inline-flex items-center gap-2 rounded-full border border-[var(--clinic-border)] px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-[var(--clinic-muted)] transition-all duration-150 hover:border-[var(--clinic-primary)] hover:text-[var(--clinic-primary)]" @click="closePetModal">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" x2="6" y1="6" y2="18" /><line x1="6" x2="18" y1="6" y2="18" />
                        </svg>
                        Cerrar
                    </button>
                </div>

                <div class="mt-6 grid gap-4 md:grid-cols-2">
                    <input v-model="petForms[selectedPet.id].name" type="text" placeholder="Paciente" class="block w-full rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-shell-surface)] px-4 py-3 text-sm text-[var(--clinic-text)] shadow-sm transition-all duration-200 focus:border-[var(--clinic-primary)] focus:outline-none focus:ring-2 focus:ring-[color-mix(in_srgb,var(--clinic-primary)_15%,white)]" @input="sanitizeField(petForms[selectedPet.id], 'name')" />
                    <input v-model="petForms[selectedPet.id].species" type="text" placeholder="Especie" class="block w-full rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-shell-surface)] px-4 py-3 text-sm text-[var(--clinic-text)] shadow-sm transition-all duration-200 focus:border-[var(--clinic-primary)] focus:outline-none focus:ring-2 focus:ring-[color-mix(in_srgb,var(--clinic-primary)_15%,white)]" @input="sanitizeField(petForms[selectedPet.id], 'species')" />
                    <input v-model="petForms[selectedPet.id].breed" type="text" placeholder="Raza" class="block w-full rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-shell-surface)] px-4 py-3 text-sm text-[var(--clinic-text)] shadow-sm transition-all duration-200 focus:border-[var(--clinic-primary)] focus:outline-none focus:ring-2 focus:ring-[color-mix(in_srgb,var(--clinic-primary)_15%,white)]" @input="sanitizeField(petForms[selectedPet.id], 'breed')" />
                    <input v-model="petForms[selectedPet.id].birth_date" type="date" class="block w-full rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-shell-surface)] px-4 py-3 text-sm text-[var(--clinic-text)] shadow-sm transition-all duration-200 focus:border-[var(--clinic-primary)] focus:outline-none focus:ring-2 focus:ring-[color-mix(in_srgb,var(--clinic-primary)_15%,white)]" />
                    <select v-model="petForms[selectedPet.id].veterinarian_id" class="block w-full rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-shell-surface)] px-4 py-3 text-sm text-[var(--clinic-text)] shadow-sm transition-all duration-200 focus:border-[var(--clinic-primary)] focus:outline-none focus:ring-2 focus:ring-[color-mix(in_srgb,var(--clinic-primary)_15%,white)]">
                        <option value="">Sin veterinario responsable</option>
                        <option v-for="veterinarian in props.veterinarians" :key="veterinarian.id" :value="veterinarian.id">{{ veterinarian.name }} - {{ veterinarian.specialty || 'General' }}</option>
                    </select>
                    <div class="flex items-center gap-4 md:col-span-2">
                        <img v-if="selectedPet.photo_path" :src="'/storage/' + selectedPet.photo_path" class="h-16 w-16 rounded-xl object-cover" />
                        <label class="flex flex-1 cursor-pointer items-center justify-center gap-2 rounded-2xl border border-dashed border-[var(--clinic-border)] bg-[var(--clinic-shell-surface)] px-4 py-3 text-sm text-[var(--clinic-muted)] transition-all duration-200 hover:border-[var(--clinic-primary)] hover:text-[var(--clinic-primary)]">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" /><polyline points="17 8 12 3 7 8" /><line x1="12" x2="12" y1="3" y2="15" />
                            </svg>
                            <span>{{ petForms[selectedPet.id].photo ? petForms[selectedPet.id].photo.name : 'Cambiar foto' }}</span>
                            <input type="file" accept="image/*" class="hidden" @change="petForms[selectedPet.id].photo = $event.target.files[0]" />
                        </label>
                    </div>
                    <input v-model="petForms[selectedPet.id].owner_name" type="text" placeholder="Responsable" class="block w-full rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-shell-surface)] px-4 py-3 text-sm text-[var(--clinic-text)] shadow-sm transition-all duration-200 focus:border-[var(--clinic-primary)] focus:outline-none focus:ring-2 focus:ring-[color-mix(in_srgb,var(--clinic-primary)_15%,white)]" @input="sanitizeField(petForms[selectedPet.id], 'owner_name')" />
                    <input v-model="petForms[selectedPet.id].owner_email" type="email" placeholder="Correo" class="block w-full rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-shell-surface)] px-4 py-3 text-sm text-[var(--clinic-text)] shadow-sm transition-all duration-200 focus:border-[var(--clinic-primary)] focus:outline-none focus:ring-2 focus:ring-[color-mix(in_srgb,var(--clinic-primary)_15%,white)]" />
                    <div class="grid gap-2 md:grid-cols-[180px_1fr]">
                        <select v-model="petForms[selectedPet.id].owner_phone_country_code" class="block w-full rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-shell-surface)] px-4 py-3 text-sm text-[var(--clinic-text)] shadow-sm transition-all duration-200 focus:border-[var(--clinic-primary)] focus:outline-none focus:ring-2 focus:ring-[color-mix(in_srgb,var(--clinic-primary)_15%,white)]">
                            <option v-for="country in props.countryOptions" :key="country.code" :value="country.code">{{ country.label }}</option>
                        </select>
                        <input v-model="petForms[selectedPet.id].owner_phone" type="text" placeholder="Celular" class="block w-full rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-shell-surface)] px-4 py-3 text-sm text-[var(--clinic-text)] shadow-sm transition-all duration-200 focus:border-[var(--clinic-primary)] focus:outline-none focus:ring-2 focus:ring-[color-mix(in_srgb,var(--clinic-primary)_15%,white)]" />
                    </div>
                </div>

                <input v-model="petForms[selectedPet.id].owner_address" type="text" placeholder="Direccion" class="mt-4 block w-full rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-shell-surface)] px-4 py-3 text-sm text-[var(--clinic-text)] shadow-sm transition-all duration-200 focus:border-[var(--clinic-primary)] focus:outline-none focus:ring-2 focus:ring-[color-mix(in_srgb,var(--clinic-primary)_15%,white)]" />

                <AuditInfo :audit="selectedPet.audit" />

                <div class="mt-6 flex flex-wrap justify-end gap-3">
                    <button type="button" class="inline-flex items-center gap-2 rounded-full border border-[var(--clinic-border)] px-5 py-3 text-xs font-semibold uppercase tracking-[0.22em] text-[var(--clinic-muted)] transition-all duration-150 hover:border-[var(--clinic-primary)] hover:text-[var(--clinic-primary)]" @click="closePetModal">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" x2="6" y1="6" y2="18" /><line x1="6" x2="18" y1="6" y2="18" />
                        </svg>
                        Cerrar
                    </button>
                    <button type="button" class="inline-flex items-center gap-2 rounded-full bg-[var(--clinic-primary)] px-5 py-3 text-xs font-semibold uppercase tracking-[0.22em] text-[var(--clinic-highlight)] transition-all duration-200 hover:bg-[var(--clinic-primary-dark)]" @click="savePet(selectedPet.id)">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" />
                            <polyline points="17 21 17 13 7 13 7 21" /><polyline points="7 3 7 8 15 8" />
                        </svg>
                        Guardar
                    </button>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
