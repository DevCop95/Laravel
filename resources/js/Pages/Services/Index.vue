<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import Modal from '@/Components/Modal.vue';
import AuditInfo from '@/Components/AuditInfo.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    services: Array,
});

const selectedServiceId = ref(null);
const showCreateModal = ref(false);

const sanitizeRecordText = (value) => value.replace(/[^\p{L}\p{N} ]/gu, '').replace(/\s+/g, ' ').trimStart();

const sanitizeField = (form, field) => {
    form[field] = sanitizeRecordText(form[field] ?? '');
};

const createServiceForm = useForm({
    name: '',
    service_type: 'Consulta general',
    price: '',
    description: '',
});

const serviceForms = Object.fromEntries(
    props.services.map((service) => [
        service.id,
        useForm({
            name: service.name,
            service_type: service.service_type,
            price: service.price,
            description: service.description ?? '',
        }),
    ]),
);

const columns = [
    { key: 'name', label: 'Servicio', sortable: true },
    { key: 'service_type', label: 'Tipo', sortable: true },
    { key: 'price', label: 'Costo', format: 'currency', sortable: true },
    { key: 'description', label: 'Descripcion' },
    { key: 'actions', label: 'Acciones', sortable: false },
];

const searchFields = ['name', 'service_type', 'description'];

const formatCurrency = (value) =>
    new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', maximumFractionDigits: 0 }).format(Number(value || 0));

const selectedService = computed(() => props.services.find((service) => service.id === selectedServiceId.value) ?? null);

const openCreateModal = () => {
    createServiceForm.reset();
    createServiceForm.service_type = 'Consulta general';
    showCreateModal.value = true;
};

const closeCreateModal = () => {
    showCreateModal.value = false;
};

const createService = () => {
    createServiceForm.post(route('services.store'), {
        preserveScroll: true,
        onSuccess: () => {
            createServiceForm.reset();
            closeCreateModal();
        },
    });
};

const openServiceModal = (serviceId) => {
    selectedServiceId.value = serviceId;
};

const closeServiceModal = () => {
    selectedServiceId.value = null;
};

const saveService = (serviceId) => {
    serviceForms[serviceId].patch(route('services.update', serviceId), {
        preserveScroll: true,
        onSuccess: closeServiceModal,
    });
};
</script>

<template>
    <Head title="Servicios" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-2">
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-[var(--clinic-muted)]">Servicios</p>
                <h2 class="text-3xl font-black leading-tight text-[var(--clinic-text)]">Catalogo clinico y costos</h2>
                <p class="max-w-3xl text-sm leading-6 text-[var(--clinic-muted)]">La tabla muestra un resumen corto y la descripcion completa vive en el popup de edicion.</p>
            </div>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-7xl space-y-8 sm:px-6 lg:px-8">
                <section class="rounded-[2rem] border border-[var(--clinic-border)] bg-[var(--clinic-panel-bg)]/95 p-6 shadow-[0_28px_90px_-55px_color-mix(in_srgb,var(--clinic-primary)_45%,transparent)]">
                    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--clinic-muted)]">Catalogo activo</p>
                            <h3 class="mt-2 text-2xl font-black text-[var(--clinic-text)]">Servicios configurados</h3>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="rounded-full bg-[var(--clinic-highlight)] px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-[var(--clinic-primary)]">{{ props.services.length }} en base</span>
                            <button type="button" class="inline-flex items-center gap-2 rounded-full bg-[var(--clinic-primary)] px-5 py-3 text-xs font-semibold uppercase tracking-[0.22em] text-[var(--clinic-highlight)] transition-all duration-200 hover:bg-[var(--clinic-primary-dark)]" @click="openCreateModal">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="12" x2="12" y1="5" y2="19" /><line x1="5" x2="19" y1="12" y2="12" />
                                </svg>
                                Crear servicio
                            </button>
                        </div>
                    </div>

                    <div class="mt-6">
                        <DataTable
                            :columns="columns"
                            :rows="props.services"
                            :search-fields="searchFields"
                            search-placeholder="Filtrar por nombre, tipo o descripcion"
                            empty-message="No hay servicios configurados."
                            :per-page="8"
                        >
                            <template #cell-name="{ row }">
                                <span class="font-semibold text-[var(--clinic-text)]">{{ row.name }}</span>
                            </template>
                            <template #cell-description="{ row }">
                                <span class="text-[var(--clinic-muted)]">{{ row.description ? `${row.description.slice(0, 70)}${row.description.length > 70 ? '...' : ''}` : 'Sin descripcion' }}</span>
                            </template>
                            <template #cell-actions="{ row }">
                                <button type="button" class="inline-flex items-center gap-1.5 rounded-full border border-[var(--clinic-border)] bg-[var(--clinic-highlight)] px-3.5 py-1.5 text-[11px] font-semibold uppercase tracking-[0.14em] text-[var(--clinic-primary)] transition-all duration-150 hover:border-[var(--clinic-primary)] hover:bg-[color-mix(in_srgb,var(--clinic-primary)_8%,white)]" @click="openServiceModal(row.id)">
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

        <!-- Create Service Modal -->
        <Modal :show="showCreateModal" max-width="2xl" @close="closeCreateModal">
            <div class="p-6 sm:p-8">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--clinic-muted)]">Nuevo servicio</p>
                        <h3 class="mt-2 text-2xl font-black text-[var(--clinic-text)]">Agregar al catalogo</h3>
                    </div>
                    <button type="button" class="inline-flex items-center gap-2 rounded-full border border-[var(--clinic-border)] px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-[var(--clinic-muted)] transition-all duration-150 hover:border-[var(--clinic-primary)] hover:text-[var(--clinic-primary)]" @click="closeCreateModal">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" x2="6" y1="6" y2="18" /><line x1="6" x2="18" y1="6" y2="18" />
                        </svg>
                        Cerrar
                    </button>
                </div>

                <form class="mt-6 space-y-4" @submit.prevent="createService">
                    <div class="grid gap-4 md:grid-cols-2">
                        <input v-model="createServiceForm.name" type="text" placeholder="Nombre del servicio" class="block w-full rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-shell-surface)] px-4 py-3 text-sm text-[var(--clinic-text)] shadow-sm transition-all duration-200 focus:border-[var(--clinic-primary)] focus:outline-none focus:ring-2 focus:ring-[color-mix(in_srgb,var(--clinic-primary)_15%,white)]" @input="sanitizeField(createServiceForm, 'name')" />
                        <input v-model="createServiceForm.service_type" type="text" placeholder="Tipo de servicio" class="block w-full rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-shell-surface)] px-4 py-3 text-sm text-[var(--clinic-text)] shadow-sm transition-all duration-200 focus:border-[var(--clinic-primary)] focus:outline-none focus:ring-2 focus:ring-[color-mix(in_srgb,var(--clinic-primary)_15%,white)]" @input="sanitizeField(createServiceForm, 'service_type')" />
                        <input v-model="createServiceForm.price" type="number" min="0" step="0.01" placeholder="Costo" class="block w-full rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-shell-surface)] px-4 py-3 text-sm text-[var(--clinic-text)] shadow-sm transition-all duration-200 focus:border-[var(--clinic-primary)] focus:outline-none focus:ring-2 focus:ring-[color-mix(in_srgb,var(--clinic-primary)_15%,white)]" />
                        <div class="flex items-center rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-shell-surface)] px-4 py-3 text-sm font-semibold text-[var(--clinic-primary)]">{{ formatCurrency(createServiceForm.price) }}</div>
                    </div>
                    <textarea v-model="createServiceForm.description" rows="3" class="block w-full rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-shell-surface)] px-4 py-3 text-sm text-[var(--clinic-text)] shadow-sm transition-all duration-200 focus:border-[var(--clinic-primary)] focus:outline-none focus:ring-2 focus:ring-[color-mix(in_srgb,var(--clinic-primary)_15%,white)]" placeholder="Descripcion" />

                    <div class="flex flex-wrap justify-end gap-3">
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
                            Crear servicio
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Edit Service Modal -->
        <Modal :show="!!selectedService" max-width="2xl" @close="closeServiceModal">
            <div v-if="selectedService" class="p-6 sm:p-8">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--clinic-muted)]">Editar servicio</p>
                        <h3 class="mt-2 text-2xl font-black text-[var(--clinic-text)]">{{ selectedService.name }}</h3>
                    </div>
                    <button type="button" class="inline-flex items-center gap-2 rounded-full border border-[var(--clinic-border)] px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-[var(--clinic-muted)] transition-all duration-150 hover:border-[var(--clinic-primary)] hover:text-[var(--clinic-primary)]" @click="closeServiceModal">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" x2="6" y1="6" y2="18" /><line x1="6" x2="18" y1="6" y2="18" />
                        </svg>
                        Cerrar
                    </button>
                </div>

                <div class="mt-6 grid gap-4 md:grid-cols-2">
                    <input v-model="serviceForms[selectedService.id].name" type="text" placeholder="Nombre del servicio" class="block w-full rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-shell-surface)] px-4 py-3 text-sm text-[var(--clinic-text)] shadow-sm transition-all duration-200 focus:border-[var(--clinic-primary)] focus:outline-none focus:ring-2 focus:ring-[color-mix(in_srgb,var(--clinic-primary)_15%,white)]" @input="sanitizeField(serviceForms[selectedService.id], 'name')" />
                    <input v-model="serviceForms[selectedService.id].service_type" type="text" placeholder="Tipo de servicio" class="block w-full rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-shell-surface)] px-4 py-3 text-sm text-[var(--clinic-text)] shadow-sm transition-all duration-200 focus:border-[var(--clinic-primary)] focus:outline-none focus:ring-2 focus:ring-[color-mix(in_srgb,var(--clinic-primary)_15%,white)]" @input="sanitizeField(serviceForms[selectedService.id], 'service_type')" />
                    <input v-model="serviceForms[selectedService.id].price" type="number" min="0" step="0.01" placeholder="Costo" class="block w-full rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-shell-surface)] px-4 py-3 text-sm text-[var(--clinic-text)] shadow-sm transition-all duration-200 focus:border-[var(--clinic-primary)] focus:outline-none focus:ring-2 focus:ring-[color-mix(in_srgb,var(--clinic-primary)_15%,white)]" />
                    <div class="flex items-center rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-shell-surface)] px-4 py-3 text-sm font-semibold text-[var(--clinic-primary)]">{{ formatCurrency(serviceForms[selectedService.id].price) }}</div>
                </div>

                <textarea v-model="serviceForms[selectedService.id].description" rows="6" class="mt-4 block w-full rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-shell-surface)] px-4 py-3 text-sm text-[var(--clinic-text)] shadow-sm transition-all duration-200 focus:border-[var(--clinic-primary)] focus:outline-none focus:ring-2 focus:ring-[color-mix(in_srgb,var(--clinic-primary)_15%,white)]" placeholder="Descripcion completa" />

                <AuditInfo :audit="selectedService.audit" />

                <div class="mt-6 flex flex-wrap justify-end gap-3">
                    <button type="button" class="inline-flex items-center gap-2 rounded-full border border-[var(--clinic-border)] px-5 py-3 text-xs font-semibold uppercase tracking-[0.22em] text-[var(--clinic-muted)] transition-all duration-150 hover:border-[var(--clinic-primary)] hover:text-[var(--clinic-primary)]" @click="closeServiceModal">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" x2="6" y1="6" y2="18" /><line x1="6" x2="18" y1="6" y2="18" />
                        </svg>
                        Cerrar
                    </button>
                    <button type="button" class="inline-flex items-center gap-2 rounded-full bg-[var(--clinic-primary)] px-5 py-3 text-xs font-semibold uppercase tracking-[0.22em] text-[var(--clinic-highlight)] transition-all duration-200 hover:bg-[var(--clinic-primary-dark)]" @click="saveService(selectedService.id)">
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
