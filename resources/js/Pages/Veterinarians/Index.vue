<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import Modal from '@/Components/Modal.vue';
import AuditInfo from '@/Components/AuditInfo.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    veterinarians: Array,
});

const selectedVeterinarianId = ref(null);
const showCreateModal = ref(false);

const sanitizeRecordText = (value) => value.replace(/[^\p{L}\p{N} ]/gu, '').replace(/\s+/g, ' ').trimStart();

const sanitizeField = (form, field) => {
    form[field] = sanitizeRecordText(form[field] ?? '');
};

const createVeterinarianForm = useForm({
    name: '',
    email: '',
    phone: '',
    specialty: '',
});

const veterinarianForms = Object.fromEntries(
    props.veterinarians.map((veterinarian) => [
        veterinarian.id,
        useForm({
            name: veterinarian.name,
            email: veterinarian.email ?? '',
            phone: veterinarian.phone ?? '',
            specialty: veterinarian.specialty ?? '',
        }),
    ]),
);

const columns = [
    { key: 'name', label: 'Nombre', sortable: true },
    { key: 'specialty', label: 'Especialidad', sortable: true },
    { key: 'email', label: 'Correo', sortable: true },
    { key: 'phone', label: 'Telefono' },
    { key: 'default_password', label: 'Clave inicial' },
    { key: 'actions', label: 'Acciones', sortable: false },
];

const searchFields = ['name', 'email', 'phone', 'specialty'];

const selectedVeterinarian = computed(() => props.veterinarians.find((veterinarian) => veterinarian.id === selectedVeterinarianId.value) ?? null);

const openCreateModal = () => {
    createVeterinarianForm.reset();
    showCreateModal.value = true;
};

const closeCreateModal = () => {
    showCreateModal.value = false;
};

const createVeterinarian = () => {
    createVeterinarianForm.post(route('veterinarians.store'), {
        preserveScroll: true,
        onSuccess: () => {
            createVeterinarianForm.reset();
            closeCreateModal();
        },
    });
};

const openVeterinarianModal = (veterinarianId) => {
    selectedVeterinarianId.value = veterinarianId;
};

const closeVeterinarianModal = () => {
    selectedVeterinarianId.value = null;
};

const saveVeterinarian = (veterinarianId) => {
    veterinarianForms[veterinarianId].patch(route('veterinarians.update', veterinarianId), {
        preserveScroll: true,
        onSuccess: closeVeterinarianModal,
    });
};
</script>

<template>
    <Head title="Equipo clinico" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-2">
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-[var(--clinic-muted)]">Equipo clinico</p>
                <h2 class="text-3xl font-black leading-tight text-[var(--clinic-text)]">Veterinarios y veterinarias</h2>
                <p class="max-w-3xl text-sm leading-6 text-[var(--clinic-muted)]">Lista compacta, modal para editar y menos ruido visual en pantalla.</p>
            </div>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-7xl space-y-8 sm:px-6 lg:px-8">
                <section class="rounded-[2rem] border border-[var(--clinic-border)] bg-[var(--clinic-panel-bg)]/95 p-6 shadow-[0_28px_90px_-55px_color-mix(in_srgb,var(--clinic-primary)_45%,transparent)]">
                    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--clinic-muted)]">Personal activo</p>
                            <h3 class="mt-2 text-2xl font-black text-[var(--clinic-text)]">Equipo disponible para agenda</h3>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="rounded-full bg-[var(--clinic-highlight)] px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-[var(--clinic-primary)]">{{ props.veterinarians.length }} en base</span>
                            <button type="button" class="inline-flex items-center gap-2 rounded-full bg-[var(--clinic-primary)] px-5 py-3 text-xs font-semibold uppercase tracking-[0.22em] text-[var(--clinic-highlight)] transition-all duration-200 hover:bg-[var(--clinic-primary-dark)]" @click="openCreateModal">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="12" x2="12" y1="5" y2="19" /><line x1="5" x2="19" y1="12" y2="12" />
                                </svg>
                                Crear veterinario
                            </button>
                        </div>
                    </div>

                    <div class="mt-6">
                        <DataTable
                            :columns="columns"
                            :rows="props.veterinarians"
                            :search-fields="searchFields"
                            search-placeholder="Filtrar por nombre, especialidad o correo"
                            empty-message="No hay veterinarios registrados."
                            :per-page="8"
                        >
                            <template #cell-specialty="{ row }">
                                <span class="text-[var(--clinic-muted)]">{{ row.specialty || 'General' }}</span>
                            </template>
                            <template #cell-email="{ row }">
                                <span class="text-[var(--clinic-muted)]">{{ row.email || 'Sin correo' }}</span>
                            </template>
                            <template #cell-phone="{ row }">
                                <span class="text-[var(--clinic-muted)]">{{ row.phone || 'Sin telefono' }}</span>
                            </template>
                            <template #cell-default_password="{ row }">
                                <span class="text-[var(--clinic-muted)]">{{ row.default_password || 'Ya actualizada' }}</span>
                            </template>
                            <template #cell-actions="{ row }">
                                <button type="button" class="inline-flex items-center gap-1.5 rounded-full border border-[var(--clinic-border)] bg-[var(--clinic-highlight)] px-3.5 py-1.5 text-[11px] font-semibold uppercase tracking-[0.14em] text-[var(--clinic-primary)] transition-all duration-150 hover:border-[var(--clinic-primary)] hover:bg-[color-mix(in_srgb,var(--clinic-primary)_8%,white)]" @click="openVeterinarianModal(row.id)">
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z" />
                                    </svg>
                                    Editar
                                </button>
                            </template>
                        </DataTable>
                    </div>
                </section>
            </div>
        </div>

        <!-- Create Veterinarian Modal -->
        <Modal :show="showCreateModal" max-width="2xl" @close="closeCreateModal">
            <div class="p-6 sm:p-8">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--clinic-muted)]">Nuevo profesional</p>
                        <h3 class="mt-2 text-2xl font-black text-[var(--clinic-text)]">Registrar integrante del equipo</h3>
                        <p class="mt-2 text-sm text-[var(--clinic-muted)]">Cada veterinario se crea tambien como usuario. La clave inicial se genera con su nombre y especialidad.</p>
                    </div>
                    <button type="button" class="inline-flex items-center gap-2 rounded-full border border-[var(--clinic-border)] px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-[var(--clinic-muted)] transition-all duration-150 hover:border-[var(--clinic-primary)] hover:text-[var(--clinic-primary)]" @click="closeCreateModal">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" x2="6" y1="6" y2="18" /><line x1="6" x2="18" y1="6" y2="18" />
                        </svg>
                        Cerrar
                    </button>
                </div>

                <form class="mt-6 grid gap-4 md:grid-cols-2" @submit.prevent="createVeterinarian">
                    <input v-model="createVeterinarianForm.name" type="text" placeholder="Nombre completo" class="block w-full rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-shell-surface)] px-4 py-3 text-sm text-[var(--clinic-text)] shadow-sm transition-all duration-200 focus:border-[var(--clinic-primary)] focus:outline-none focus:ring-2 focus:ring-[color-mix(in_srgb,var(--clinic-primary)_15%,white)]" @input="sanitizeField(createVeterinarianForm, 'name')" />
                    <input v-model="createVeterinarianForm.specialty" type="text" placeholder="Especialidad" class="block w-full rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-shell-surface)] px-4 py-3 text-sm text-[var(--clinic-text)] shadow-sm transition-all duration-200 focus:border-[var(--clinic-primary)] focus:outline-none focus:ring-2 focus:ring-[color-mix(in_srgb,var(--clinic-primary)_15%,white)]" @input="sanitizeField(createVeterinarianForm, 'specialty')" />
                    <input v-model="createVeterinarianForm.email" type="email" placeholder="Correo" class="block w-full rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-shell-surface)] px-4 py-3 text-sm text-[var(--clinic-text)] shadow-sm transition-all duration-200 focus:border-[var(--clinic-primary)] focus:outline-none focus:ring-2 focus:ring-[color-mix(in_srgb,var(--clinic-primary)_15%,white)]" />
                    <input v-model="createVeterinarianForm.phone" type="text" placeholder="Celular" class="block w-full rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-shell-surface)] px-4 py-3 text-sm text-[var(--clinic-text)] shadow-sm transition-all duration-200 focus:border-[var(--clinic-primary)] focus:outline-none focus:ring-2 focus:ring-[color-mix(in_srgb,var(--clinic-primary)_15%,white)]" />

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
                            Crear veterinario
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Edit Veterinarian Modal -->
        <Modal :show="!!selectedVeterinarian" max-width="2xl" @close="closeVeterinarianModal">
            <div v-if="selectedVeterinarian" class="p-6 sm:p-8">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--clinic-muted)]">Editar profesional</p>
                        <h3 class="mt-2 text-2xl font-black text-[var(--clinic-text)]">{{ selectedVeterinarian.name }}</h3>
                    </div>
                    <button type="button" class="inline-flex items-center gap-2 rounded-full border border-[var(--clinic-border)] px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-[var(--clinic-muted)] transition-all duration-150 hover:border-[var(--clinic-primary)] hover:text-[var(--clinic-primary)]" @click="closeVeterinarianModal">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" x2="6" y1="6" y2="18" /><line x1="6" x2="18" y1="6" y2="18" />
                        </svg>
                        Cerrar
                    </button>
                </div>

                <div class="mt-6 grid gap-4 md:grid-cols-2">
                    <input v-model="veterinarianForms[selectedVeterinarian.id].name" type="text" placeholder="Nombre completo" class="block w-full rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-shell-surface)] px-4 py-3 text-sm text-[var(--clinic-text)] shadow-sm transition-all duration-200 focus:border-[var(--clinic-primary)] focus:outline-none focus:ring-2 focus:ring-[color-mix(in_srgb,var(--clinic-primary)_15%,white)]" @input="sanitizeField(veterinarianForms[selectedVeterinarian.id], 'name')" />
                    <input v-model="veterinarianForms[selectedVeterinarian.id].specialty" type="text" placeholder="Especialidad" class="block w-full rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-shell-surface)] px-4 py-3 text-sm text-[var(--clinic-text)] shadow-sm transition-all duration-200 focus:border-[var(--clinic-primary)] focus:outline-none focus:ring-2 focus:ring-[color-mix(in_srgb,var(--clinic-primary)_15%,white)]" @input="sanitizeField(veterinarianForms[selectedVeterinarian.id], 'specialty')" />
                    <input v-model="veterinarianForms[selectedVeterinarian.id].email" type="email" placeholder="Correo" class="block w-full rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-shell-surface)] px-4 py-3 text-sm text-[var(--clinic-text)] shadow-sm transition-all duration-200 focus:border-[var(--clinic-primary)] focus:outline-none focus:ring-2 focus:ring-[color-mix(in_srgb,var(--clinic-primary)_15%,white)]" />
                    <input v-model="veterinarianForms[selectedVeterinarian.id].phone" type="text" placeholder="Celular" class="block w-full rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-shell-surface)] px-4 py-3 text-sm text-[var(--clinic-text)] shadow-sm transition-all duration-200 focus:border-[var(--clinic-primary)] focus:outline-none focus:ring-2 focus:ring-[color-mix(in_srgb,var(--clinic-primary)_15%,white)]" />
                </div>

                <div class="mt-4 rounded-[1.4rem] border border-[var(--clinic-border)] bg-[var(--clinic-shell-surface)] px-4 py-4 text-sm text-[var(--clinic-muted)]">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--clinic-muted)]">Usuario vinculado</p>
                    <p class="mt-2">{{ selectedVeterinarian.email }}</p>
                    <p class="mt-2"><span class="font-semibold text-[var(--clinic-text)]">Clave inicial:</span> {{ selectedVeterinarian.default_password || 'Ya actualizada' }}</p>
                </div>

                <AuditInfo :audit="selectedVeterinarian.audit" />

                <div class="mt-6 flex flex-wrap justify-end gap-3">
                    <button type="button" class="inline-flex items-center gap-2 rounded-full border border-[var(--clinic-border)] px-5 py-3 text-xs font-semibold uppercase tracking-[0.22em] text-[var(--clinic-muted)] transition-all duration-150 hover:border-[var(--clinic-primary)] hover:text-[var(--clinic-primary)]" @click="closeVeterinarianModal">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" x2="6" y1="6" y2="18" /><line x1="6" x2="18" y1="6" y2="18" />
                        </svg>
                        Cerrar
                    </button>
                    <button type="button" class="inline-flex items-center gap-2 rounded-full bg-[var(--clinic-primary)] px-5 py-3 text-xs font-semibold uppercase tracking-[0.22em] text-[var(--clinic-highlight)] transition-all duration-200 hover:bg-[var(--clinic-primary-dark)]" @click="saveVeterinarian(selectedVeterinarian.id)">
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
