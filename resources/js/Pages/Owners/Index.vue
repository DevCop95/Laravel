<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import Modal from '@/Components/Modal.vue';
import AuditInfo from '@/Components/AuditInfo.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    owners: Array,
    countryOptions: Array,
});

const selectedOwnerId = ref(null);
const showCreateModal = ref(false);

const sanitizeRecordText = (value) => value.replace(/[^\p{L}\p{N} ]/gu, '').replace(/\s+/g, ' ').trimStart();

const sanitizeField = (form, field) => {
    form[field] = sanitizeRecordText(form[field] ?? '');
};

const createOwnerForm = useForm({
    name: '',
    email: '',
    phone_country_code: '+57',
    phone: '',
    address: '',
});

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

const columns = [
    { key: 'name', label: 'Nombre', sortable: true },
    { key: 'email', label: 'Correo', sortable: true },
    { key: 'phone', label: 'Telefono' },
    { key: 'address', label: 'Direccion' },
    { key: 'pets', label: 'Mascotas' },
    { key: 'actions', label: 'Acciones', sortable: false },
];

const searchFields = ['name', 'email', 'phone', 'address'];

const selectedOwner = computed(() => props.owners.find((owner) => owner.id === selectedOwnerId.value) ?? null);

const openCreateModal = () => {
    createOwnerForm.reset();
    createOwnerForm.phone_country_code = '+57';
    showCreateModal.value = true;
};

const closeCreateModal = () => {
    showCreateModal.value = false;
};

const createOwner = () => {
    createOwnerForm.post(route('owners.store'), {
        preserveScroll: true,
        onSuccess: () => {
            createOwnerForm.reset();
            closeCreateModal();
        },
    });
};

const openOwnerModal = (ownerId) => {
    selectedOwnerId.value = ownerId;
};

const closeOwnerModal = () => {
    selectedOwnerId.value = null;
};

const saveOwner = (ownerId) => {
    ownerForms[ownerId].patch(route('owners.update', ownerId), {
        preserveScroll: true,
        onSuccess: closeOwnerModal,
    });
};

const deleteOwner = (ownerId) => {
    if (!confirm('Eliminar este responsable? Las mascotas asociadas quedaran sin responsable.')) return;
    useForm().delete(route('owners.destroy', ownerId), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Responsables" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-2">
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-[var(--clinic-muted)]">Responsables</p>
                <h2 class="text-3xl font-black leading-tight text-[var(--clinic-text)]">Directorio de contacto</h2>
                <p class="max-w-3xl text-sm leading-6 text-[var(--clinic-muted)]">
                    Gestion completa de responsables de mascotas. Crear, editar y eliminar desde un solo lugar.
                </p>
            </div>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-7xl space-y-8 sm:px-6 lg:px-8">
                <section class="rounded-[2rem] border border-[var(--clinic-border)] bg-[var(--clinic-panel-bg)]/95 p-6 shadow-[0_28px_90px_-55px_color-mix(in_srgb,var(--clinic-primary)_45%,transparent)]">
                    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--clinic-muted)]">Directorio</p>
                            <h3 class="mt-2 text-2xl font-black text-[var(--clinic-text)]">Responsables registrados</h3>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="rounded-full bg-[var(--clinic-highlight)] px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-[var(--clinic-primary)]">{{ props.owners.length }} en base</span>
                            <button type="button" class="inline-flex items-center gap-2 rounded-full bg-[var(--clinic-primary)] px-5 py-3 text-xs font-semibold uppercase tracking-[0.22em] text-[var(--clinic-highlight)] transition-all duration-200 hover:bg-[var(--clinic-primary-dark)]" @click="openCreateModal">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="12" x2="12" y1="5" y2="19" /><line x1="5" x2="19" y1="12" y2="12" />
                                </svg>
                                Crear responsable
                            </button>
                        </div>
                    </div>

                    <div class="mt-6">
                        <DataTable
                            :columns="columns"
                            :rows="props.owners"
                            :search-fields="searchFields"
                            search-placeholder="Filtrar por nombre, correo o telefono"
                            empty-message="No hay responsables registrados."
                            :per-page="8"
                        >
                            <template #cell-phone="{ row }">
                                <span class="text-[var(--clinic-muted)]">{{ row.phone_country_code }} {{ row.phone || 'Sin telefono' }}</span>
                            </template>
                            <template #cell-pets="{ row }">
                                <div class="flex flex-wrap gap-1.5">
                                    <span v-for="pet in row.pets" :key="pet.id" class="rounded-full bg-[var(--clinic-highlight)] px-2.5 py-0.5 text-[11px] font-semibold text-[var(--clinic-primary)]">{{ pet.name }}</span>
                                    <span v-if="!row.pets?.length" class="text-xs text-[var(--clinic-muted)]">Sin mascotas</span>
                                </div>
                            </template>
                            <template #cell-actions="{ row }">
                                <div class="flex flex-wrap gap-2">
                                    <button type="button" class="inline-flex items-center gap-1.5 rounded-full border border-[var(--clinic-border)] bg-[var(--clinic-highlight)] px-3.5 py-1.5 text-[11px] font-semibold uppercase tracking-[0.14em] text-[var(--clinic-primary)] transition-all duration-150 hover:border-[var(--clinic-primary)] hover:bg-[color-mix(in_srgb,var(--clinic-primary)_8%,white)]" @click="openOwnerModal(row.id)">
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z" />
                                        </svg>
                                        Editar
                                    </button>
                                    <button type="button" class="inline-flex items-center gap-1.5 rounded-full border border-[#f3ded7] bg-[#f3ded7] px-3.5 py-1.5 text-[11px] font-semibold uppercase tracking-[0.14em] text-[#8a4d3a] transition-all duration-150 hover:bg-[#e8cec4]" @click="deleteOwner(row.id)">
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M3 6h18" /><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6" /><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2" />
                                        </svg>
                                        Eliminar
                                    </button>
                                </div>
                            </template>
                        </DataTable>
                    </div>
                </section>
            </div>
        </div>

        <!-- Create Owner Modal -->
        <Modal :show="showCreateModal" max-width="2xl" @close="closeCreateModal">
            <div class="p-6 sm:p-8">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--clinic-muted)]">Nuevo registro</p>
                        <h3 class="mt-2 text-2xl font-black text-[var(--clinic-text)]">Crear responsable</h3>
                    </div>
                    <button type="button" class="inline-flex items-center gap-2 rounded-full border border-[var(--clinic-border)] px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-[var(--clinic-muted)] transition-all duration-150 hover:border-[var(--clinic-primary)] hover:text-[var(--clinic-primary)]" @click="closeCreateModal">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" x2="6" y1="6" y2="18" /><line x1="6" x2="18" y1="6" y2="18" />
                        </svg>
                        Cerrar
                    </button>
                </div>

                <form class="mt-6 grid gap-4 md:grid-cols-2" @submit.prevent="createOwner">
                    <input v-model="createOwnerForm.name" type="text" placeholder="Nombre completo" class="block w-full rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-shell-surface)] px-4 py-3 text-sm text-[var(--clinic-text)] shadow-sm transition-all duration-200 focus:border-[var(--clinic-primary)] focus:outline-none focus:ring-2 focus:ring-[color-mix(in_srgb,var(--clinic-primary)_15%,white)]" @input="sanitizeField(createOwnerForm, 'name')" />
                    <input v-model="createOwnerForm.email" type="email" placeholder="Correo" class="block w-full rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-shell-surface)] px-4 py-3 text-sm text-[var(--clinic-text)] shadow-sm transition-all duration-200 focus:border-[var(--clinic-primary)] focus:outline-none focus:ring-2 focus:ring-[color-mix(in_srgb,var(--clinic-primary)_15%,white)]" />
                    <div class="grid gap-2 md:grid-cols-[140px_1fr]">
                        <select v-model="createOwnerForm.phone_country_code" class="block w-full rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-shell-surface)] px-4 py-3 text-sm text-[var(--clinic-text)] shadow-sm transition-all duration-200 focus:border-[var(--clinic-primary)] focus:outline-none focus:ring-2 focus:ring-[color-mix(in_srgb,var(--clinic-primary)_15%,white)]">
                            <option v-for="country in props.countryOptions" :key="country.code" :value="country.code">{{ country.label }}</option>
                        </select>
                        <input v-model="createOwnerForm.phone" type="text" placeholder="Celular" class="block w-full rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-shell-surface)] px-4 py-3 text-sm text-[var(--clinic-text)] shadow-sm transition-all duration-200 focus:border-[var(--clinic-primary)] focus:outline-none focus:ring-2 focus:ring-[color-mix(in_srgb,var(--clinic-primary)_15%,white)]" />
                    </div>
                    <input v-model="createOwnerForm.address" type="text" placeholder="Direccion" class="md:col-span-2 block w-full rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-shell-surface)] px-4 py-3 text-sm text-[var(--clinic-text)] shadow-sm transition-all duration-200 focus:border-[var(--clinic-primary)] focus:outline-none focus:ring-2 focus:ring-[color-mix(in_srgb,var(--clinic-primary)_15%,white)]" />

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
                            Crear responsable
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Edit Owner Modal -->
        <Modal :show="!!selectedOwner" max-width="2xl" @close="closeOwnerModal">
            <div v-if="selectedOwner" class="p-6 sm:p-8">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--clinic-muted)]">Editar responsable</p>
                        <h3 class="mt-2 text-2xl font-black text-[var(--clinic-text)]">{{ selectedOwner.name }}</h3>
                    </div>
                    <button type="button" class="inline-flex items-center gap-2 rounded-full border border-[var(--clinic-border)] px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-[var(--clinic-muted)] transition-all duration-150 hover:border-[var(--clinic-primary)] hover:text-[var(--clinic-primary)]" @click="closeOwnerModal">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" x2="6" y1="6" y2="18" /><line x1="6" x2="18" y1="6" y2="18" />
                        </svg>
                        Cerrar
                    </button>
                </div>

                <div class="mt-6 grid gap-4 md:grid-cols-2">
                    <input v-model="ownerForms[selectedOwner.id].name" type="text" placeholder="Nombre completo" class="block w-full rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-shell-surface)] px-4 py-3 text-sm text-[var(--clinic-text)] shadow-sm transition-all duration-200 focus:border-[var(--clinic-primary)] focus:outline-none focus:ring-2 focus:ring-[color-mix(in_srgb,var(--clinic-primary)_15%,white)]" @input="sanitizeField(ownerForms[selectedOwner.id], 'name')" />
                    <input v-model="ownerForms[selectedOwner.id].email" type="email" placeholder="Correo" class="block w-full rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-shell-surface)] px-4 py-3 text-sm text-[var(--clinic-text)] shadow-sm transition-all duration-200 focus:border-[var(--clinic-primary)] focus:outline-none focus:ring-2 focus:ring-[color-mix(in_srgb,var(--clinic-primary)_15%,white)]" />
                    <div class="grid gap-2 md:grid-cols-[140px_1fr]">
                        <select v-model="ownerForms[selectedOwner.id].phone_country_code" class="block w-full rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-shell-surface)] px-4 py-3 text-sm text-[var(--clinic-text)] shadow-sm transition-all duration-200 focus:border-[var(--clinic-primary)] focus:outline-none focus:ring-2 focus:ring-[color-mix(in_srgb,var(--clinic-primary)_15%,white)]">
                            <option v-for="country in props.countryOptions" :key="country.code" :value="country.code">{{ country.label }}</option>
                        </select>
                        <input v-model="ownerForms[selectedOwner.id].phone" type="text" placeholder="Celular" class="block w-full rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-shell-surface)] px-4 py-3 text-sm text-[var(--clinic-text)] shadow-sm transition-all duration-200 focus:border-[var(--clinic-primary)] focus:outline-none focus:ring-2 focus:ring-[color-mix(in_srgb,var(--clinic-primary)_15%,white)]" />
                    </div>
                    <input v-model="ownerForms[selectedOwner.id].address" type="text" placeholder="Direccion" class="md:col-span-2 block w-full rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-shell-surface)] px-4 py-3 text-sm text-[var(--clinic-text)] shadow-sm transition-all duration-200 focus:border-[var(--clinic-primary)] focus:outline-none focus:ring-2 focus:ring-[color-mix(in_srgb,var(--clinic-primary)_15%,white)]" />
                </div>

                <AuditInfo :audit="selectedOwner.audit" />

                <div class="mt-6 flex flex-wrap justify-end gap-3">
                    <button type="button" class="inline-flex items-center gap-2 rounded-full border border-[var(--clinic-border)] px-5 py-3 text-xs font-semibold uppercase tracking-[0.22em] text-[var(--clinic-muted)] transition-all duration-150 hover:border-[var(--clinic-primary)] hover:text-[var(--clinic-primary)]" @click="closeOwnerModal">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" x2="6" y1="6" y2="18" /><line x1="6" x2="18" y1="6" y2="18" />
                        </svg>
                        Cerrar
                    </button>
                    <button type="button" class="inline-flex items-center gap-2 rounded-full bg-[var(--clinic-primary)] px-5 py-3 text-xs font-semibold uppercase tracking-[0.22em] text-[var(--clinic-highlight)] transition-all duration-200 hover:bg-[var(--clinic-primary-dark)]" @click="saveOwner(selectedOwner.id)">
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
