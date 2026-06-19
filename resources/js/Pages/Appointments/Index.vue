<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import Modal from '@/Components/Modal.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import AuditInfo from '@/Components/AuditInfo.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    stats: Object,
    recentAppointments: Array,
    appointments: Array,
    pets: Array,
    veterinarians: Array,
    services: Array,
    appointmentStatuses: Array,
});

const createAppointmentForm = useForm({
    pet_id: '',
    veterinarian_id: '',
    service_id: '',
    service_price: '',
    appointment_date: '',
    reason: '',
    status: 'scheduled',
    doctor_notes: '',
});

const appointmentForms = Object.fromEntries(
    props.appointments.map((appointment) => [
        appointment.id,
        useForm({
            pet_id: appointment.pet_id,
            veterinarian_id: appointment.veterinarian_id ?? '',
            service_id: appointment.service_id ?? '',
            service_price: appointment.service_price ?? '',
            appointment_date: appointment.appointment_date_input ?? '',
            reason: appointment.reason,
            status: appointment.status,
            doctor_notes: appointment.doctor_notes ?? '',
        }),
    ]),
);

const statusFilter = ref('all');
const vetFilter = ref('all');
const dateFrom = ref('');
const dateTo = ref('');
const selectedAppointmentId = ref(null);
const showCreateAppointmentModal = ref(false);

const selectedAppointment = computed(() =>
    props.appointments.find((appointment) => appointment.id === selectedAppointmentId.value) ?? null,
);

const filteredAppointments = computed(() => {
    let result = props.appointments;

    if (statusFilter.value !== 'all') {
        result = result.filter((a) => a.status === statusFilter.value);
    }

    if (vetFilter.value !== 'all') {
        result = result.filter((a) => String(a.veterinarian_id) === vetFilter.value);
    }

    if (dateFrom.value) {
        result = result.filter((a) => a.appointment_date_input?.slice(0, 10) >= dateFrom.value);
    }

    if (dateTo.value) {
        result = result.filter((a) => a.appointment_date_input?.slice(0, 10) <= dateTo.value);
    }

    return result;
});

const columns = [
    { key: 'pet.name', label: 'Paciente', sortable: true },
    { key: 'veterinarian.name', label: 'Veterinario', sortable: true },
    { key: 'service.name', label: 'Servicio' },
    { key: 'appointment_date', label: 'Fecha', format: 'datetime', sortable: true },
    { key: 'status_label', label: 'Estado', sortable: true },
    { key: 'service_price', label: 'Costo', format: 'currency', sortable: true },
    { key: 'actions', label: 'Acciones', sortable: false },
];

const searchFields = [
    'pet.name',
    'pet.species',
    'pet.owner.name',
    'veterinarian.name',
    'service.name',
    'reason',
    'status_label',
];

const selectedPet = computed(() => props.pets.find((pet) => pet.id === createAppointmentForm.pet_id) ?? null);

watch(
    () => createAppointmentForm.pet_id,
    () => {
        createAppointmentForm.veterinarian_id = selectedPet.value?.veterinarian_id ?? '';
    },
    { immediate: false },
);

const syncServicePrice = (form) => {
    const service = props.services.find((item) => item.id === form.service_id);
    form.service_price = service?.price ?? '';
};

const openAppointmentModal = (appointmentId) => {
    selectedAppointmentId.value = appointmentId;
};

const closeAppointmentModal = () => {
    selectedAppointmentId.value = null;
};

const openCreateAppointmentModal = () => {
    showCreateAppointmentModal.value = true;
};

const closeCreateAppointmentModal = () => {
    showCreateAppointmentModal.value = false;
};

const createAppointment = () => {
    createAppointmentForm.post(route('appointments.store'), {
        preserveScroll: true,
        onSuccess: () => {
            createAppointmentForm.reset();
            createAppointmentForm.status = 'scheduled';
            closeCreateAppointmentModal();
        },
    });
};

const saveAppointment = (appointmentId) => {
    appointmentForms[appointmentId].patch(route('appointments.update', appointmentId), {
        preserveScroll: true,
        onSuccess: closeAppointmentModal,
    });
};

const completeAndShare = (appointment) => {
    const form = appointmentForms[appointment.id];
    form.status = 'completed';

    form.patch(route('appointments.update', appointment.id), {
        preserveScroll: true,
        onSuccess: () => {
            closeAppointmentModal();
            if (appointment.whatsapp_url) {
                window.open(appointment.whatsapp_url, '_blank', 'noopener,noreferrer');
            }
        },
    });
};
</script>

<template>
    <Head title="Citas" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-2">
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-[var(--clinic-muted)]">Citas</p>
                <h2 class="text-3xl font-black leading-tight text-[var(--clinic-text)]">Programadas, en progreso, finalizadas y canceladas</h2>
                <p class="max-w-3xl text-sm leading-6 text-[var(--clinic-muted)]">
                    Modulo dedicado para consultar toda la agenda, filtrar por estado y editar cada caso desde su popup.
                </p>
            </div>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-7xl space-y-8 sm:px-6 lg:px-8">
                <section class="rounded-[2rem] border border-[var(--clinic-border)] bg-white/90 p-6 shadow-[0_28px_90px_-55px_color-mix(in_srgb,var(--clinic-primary-dark)_35%,transparent)]">
                    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--clinic-muted)]">Tabla de citas</p>
                            <h3 class="mt-2 text-2xl font-black text-[var(--clinic-text)]">Agenda completa</h3>
                        </div>
                        <div class="flex flex-wrap gap-3">
                            <select v-model="statusFilter" class="block w-full min-w-[200px] rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-panel-bg)] px-4 py-3 text-sm text-[var(--clinic-text)] shadow-sm transition-all duration-200 focus:border-[var(--clinic-primary)] focus:outline-none focus:ring-2 focus:ring-[color-mix(in_srgb,var(--clinic-primary)_15%,white)]">
                                <option value="all">Todos los estados</option>
                                <option v-for="status in props.appointmentStatuses" :key="status.value" :value="status.value">{{ status.label }}</option>
                            </select>
                            <select v-model="vetFilter" class="block w-full min-w-[200px] rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-panel-bg)] px-4 py-3 text-sm text-[var(--clinic-text)] shadow-sm transition-all duration-200 focus:border-[var(--clinic-primary)] focus:outline-none focus:ring-2 focus:ring-[color-mix(in_srgb,var(--clinic-primary)_15%,white)]">
                                <option value="all">Todos los veterinarios</option>
                                <option v-for="vet in props.veterinarians" :key="vet.id" :value="String(vet.id)">{{ vet.name }}</option>
                            </select>
                            <input v-model="dateFrom" type="date" placeholder="Desde" class="block w-full min-w-[170px] rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-panel-bg)] px-4 py-3 text-sm text-[var(--clinic-text)] shadow-sm transition-all duration-200 focus:border-[var(--clinic-primary)] focus:outline-none focus:ring-2 focus:ring-[color-mix(in_srgb,var(--clinic-primary)_15%,white)]" />
                            <input v-model="dateTo" type="date" placeholder="Hasta" class="block w-full min-w-[170px] rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-panel-bg)] px-4 py-3 text-sm text-[var(--clinic-text)] shadow-sm transition-all duration-200 focus:border-[var(--clinic-primary)] focus:outline-none focus:ring-2 focus:ring-[color-mix(in_srgb,var(--clinic-primary)_15%,white)]" />
                            <button v-if="statusFilter !== 'all' || vetFilter !== 'all' || dateFrom || dateTo" type="button" class="inline-flex items-center gap-1.5 rounded-full border border-[var(--clinic-border)] px-4 py-2 text-xs font-semibold uppercase tracking-[0.14em] text-[var(--clinic-muted)] transition-all duration-150 hover:border-[var(--clinic-primary)] hover:text-[var(--clinic-primary)]" @click="statusFilter = 'all'; vetFilter = 'all'; dateFrom = ''; dateTo = ''">
                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="18" x2="6" y1="6" y2="18" /><line x1="6" x2="18" y1="6" y2="18" />
                                </svg>
                                Limpiar
                            </button>
                            <button type="button" class="inline-flex items-center gap-2 rounded-full border border-[var(--clinic-border)] px-4 py-2 text-xs font-semibold uppercase tracking-[0.14em] text-[var(--clinic-muted)] transition-all duration-150 hover:border-[var(--clinic-primary)] hover:text-[var(--clinic-primary)]" @click="window.location.href = route('appointments.export')">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" /><polyline points="7 10 12 15 17 10" /><line x1="12" x2="12" y1="15" y2="3" />
                                </svg>
                                CSV
                            </button>
                            <button type="button" class="inline-flex items-center gap-2 rounded-full bg-[var(--clinic-primary)] px-5 py-3 text-xs font-semibold uppercase tracking-[0.22em] text-[var(--clinic-highlight)] transition-all duration-200 hover:bg-[var(--clinic-primary-dark)] hover:shadow-[0_8px_20px_-6px_color-mix(in_srgb,var(--clinic-primary)_50%,transparent)]" @click="openCreateAppointmentModal">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect width="18" height="18" x="3" y="4" rx="2" ry="2" />
                                    <line x1="16" x2="16" y1="2" y2="6" />
                                    <line x1="8" x2="8" y1="2" y2="6" />
                                    <line x1="3" x2="21" y1="10" y2="10" />
                                    <line x1="12" x2="12" y1="14" y2="18" />
                                    <line x1="10" x2="14" y1="16" y2="16" />
                                </svg>
                                <span>Registrar cita</span>
                            </button>
                        </div>
                    </div>

                    <div class="mt-6">
                        <DataTable
                            :columns="columns"
                            :rows="filteredAppointments"
                            :search-fields="searchFields"
                            search-placeholder="Buscar por mascota, responsable, veterinario o servicio"
                            empty-message="No hay citas que coincidan con ese filtro."
                            :per-page="8"
                        >
                            <template #cell-pet.name="{ row }">
                                <div>
                                    <p class="font-bold text-[var(--clinic-text)]">{{ row.pet.name }}</p>
                                    <p class="mt-0.5 text-xs text-[var(--clinic-muted)]">{{ row.pet.species }} {{ row.pet.owner.name }}</p>
                                </div>
                            </template>
                            <template #cell-veterinarian.name="{ row }">
                                <span class="text-[var(--clinic-text)]">{{ row.veterinarian.name || 'Sin asignar' }}</span>
                            </template>
                            <template #cell-service.name="{ row }">
                                <div>
                                    <p class="font-medium text-[var(--clinic-text)]">{{ row.service.name }}</p>
                                    <p class="mt-0.5 text-xs text-[var(--clinic-muted)]">{{ row.service.service_type }}</p>
                                </div>
                            </template>
                            <template #cell-status_label="{ row }">
                                <StatusBadge :status="row.status" :label="row.status_label" />
                            </template>
                            <template #cell-actions="{ row }">
                                <div class="flex flex-wrap gap-2">
                                    <button type="button" class="inline-flex items-center gap-1.5 rounded-full border border-[var(--clinic-border)] px-3.5 py-1.5 text-[11px] font-semibold uppercase tracking-[0.14em] text-[var(--clinic-primary)] transition-all duration-150 hover:border-[var(--clinic-primary)] hover:bg-[color-mix(in_srgb,var(--clinic-primary)_5%,white)]" @click="openAppointmentModal(row.id)">
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z" />
                                        </svg>
                                        Editar
                                    </button>
                                    <button v-if="row.whatsapp_url && row.status !== 'completed'" type="button" class="inline-flex items-center gap-1.5 rounded-full bg-[#dce6d1] px-3.5 py-1.5 text-[11px] font-semibold uppercase tracking-[0.14em] text-[#24543f] transition-all duration-150 hover:bg-[#c8d9bc]" @click="completeAndShare(row)">
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                                            <polyline points="22 4 12 14.01 9 11.01" />
                                        </svg>
                                        Finalizar
                                    </button>
                                </div>
                            </template>
                        </DataTable>
                    </div>
                </section>
            </div>
        </div>

        <Modal :show="showCreateAppointmentModal" max-width="2xl" @close="closeCreateAppointmentModal">
            <div class="p-6 sm:p-8">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--clinic-muted)]">Nueva cita</p>
                        <h3 class="mt-2 text-2xl font-black text-[var(--clinic-text)]">Registrar servicio</h3>
                    </div>
                    <button type="button" class="inline-flex items-center gap-2 rounded-full border border-[var(--clinic-border)] px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-[var(--clinic-muted)] transition-all duration-150 hover:border-[var(--clinic-primary)] hover:text-[var(--clinic-primary)]" @click="closeCreateAppointmentModal">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" x2="6" y1="6" y2="18" />
                            <line x1="6" x2="18" y1="6" y2="18" />
                        </svg>
                        Cerrar
                    </button>
                </div>

                <form class="mt-6 space-y-4" @submit.prevent="createAppointment">
                    <select v-model="createAppointmentForm.pet_id" class="block w-full rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-panel-bg)] px-4 py-3 text-sm text-[var(--clinic-text)] shadow-sm transition-all duration-200 focus:border-[var(--clinic-primary)] focus:outline-none focus:ring-2 focus:ring-[color-mix(in_srgb,var(--clinic-primary)_15%,white)]">
                        <option value="">Selecciona un paciente</option>
                        <option v-for="pet in props.pets" :key="pet.id" :value="pet.id">{{ pet.name }} {{ pet.species }} {{ pet.owner_name }}</option>
                    </select>
                    <select v-model="createAppointmentForm.veterinarian_id" class="block w-full rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-panel-bg)] px-4 py-3 text-sm text-[var(--clinic-text)] shadow-sm transition-all duration-200 focus:border-[var(--clinic-primary)] focus:outline-none focus:ring-2 focus:ring-[color-mix(in_srgb,var(--clinic-primary)_15%,white)]">
                        <option value="">Selecciona un veterinario</option>
                        <option v-for="veterinarian in props.veterinarians" :key="veterinarian.id" :value="veterinarian.id">{{ veterinarian.name }} {{ veterinarian.specialty || 'General' }}</option>
                    </select>
                    <div class="grid gap-4 md:grid-cols-2">
                        <select v-model="createAppointmentForm.service_id" class="block w-full rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-panel-bg)] px-4 py-3 text-sm text-[var(--clinic-text)] shadow-sm transition-all duration-200 focus:border-[var(--clinic-primary)] focus:outline-none focus:ring-2 focus:ring-[color-mix(in_srgb,var(--clinic-primary)_15%,white)]" @change="syncServicePrice(createAppointmentForm)">
                            <option value="">Selecciona un servicio</option>
                            <option v-for="service in props.services" :key="service.id" :value="service.id">{{ service.name }} {{ service.service_type }}</option>
                        </select>
                        <input v-model="createAppointmentForm.appointment_date" type="datetime-local" class="block w-full rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-panel-bg)] px-4 py-3 text-sm text-[var(--clinic-text)] shadow-sm transition-all duration-200 focus:border-[var(--clinic-primary)] focus:outline-none focus:ring-2 focus:ring-[color-mix(in_srgb,var(--clinic-primary)_15%,white)]" />
                    </div>
                    <textarea v-model="createAppointmentForm.reason" rows="4" class="block w-full rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-panel-bg)] px-4 py-3 text-sm text-[var(--clinic-text)] shadow-sm transition-all duration-200 focus:border-[var(--clinic-primary)] focus:outline-none focus:ring-2 focus:ring-[color-mix(in_srgb,var(--clinic-primary)_15%,white)]" placeholder="Motivo del servicio" />

                    <div class="flex flex-wrap justify-end gap-3">
                        <button type="button" class="inline-flex items-center gap-2 rounded-full border border-[var(--clinic-border)] px-5 py-3 text-xs font-semibold uppercase tracking-[0.22em] text-[var(--clinic-muted)] transition-all duration-150 hover:border-[var(--clinic-primary)] hover:text-[var(--clinic-primary)]" @click="closeCreateAppointmentModal">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="18" x2="6" y1="6" y2="18" />
                                <line x1="6" x2="18" y1="6" y2="18" />
                            </svg>
                            Cerrar
                        </button>
                        <button type="submit" class="inline-flex items-center gap-2 rounded-full bg-[var(--clinic-primary)] px-5 py-3 text-xs font-semibold uppercase tracking-[0.22em] text-[var(--clinic-highlight)] transition-all duration-200 hover:bg-[var(--clinic-primary-dark)]">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="12" x2="12" y1="5" y2="19" />
                                <line x1="5" x2="19" y1="12" y2="12" />
                            </svg>
                            Crear cita
                        </button>
                    </div>
                </form>
            </div>
        </Modal>

        <Modal :show="!!selectedAppointment" max-width="2xl" @close="closeAppointmentModal">
            <div v-if="selectedAppointment" class="p-6 sm:p-8">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--clinic-muted)]">Editar cita</p>
                        <h3 class="mt-2 text-2xl font-black text-[var(--clinic-text)]">{{ selectedAppointment.pet.name }}</h3>
                        <p class="mt-2 text-sm text-[var(--clinic-muted)]">{{ selectedAppointment.pet.owner.name }} {{ selectedAppointment.pet.species }}</p>
                    </div>
                    <button type="button" class="inline-flex items-center gap-2 rounded-full border border-[var(--clinic-border)] px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-[var(--clinic-muted)] transition-all duration-150 hover:border-[var(--clinic-primary)] hover:text-[var(--clinic-primary)]" @click="closeAppointmentModal">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" x2="6" y1="6" y2="18" />
                            <line x1="6" x2="18" y1="6" y2="18" />
                        </svg>
                        Cerrar
                    </button>
                </div>

                <div class="mt-6 grid gap-4 md:grid-cols-2">
                    <select v-model="appointmentForms[selectedAppointment.id].veterinarian_id" class="block w-full rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-panel-bg)] px-4 py-3 text-sm text-[var(--clinic-text)] shadow-sm transition-all duration-200 focus:border-[var(--clinic-primary)] focus:outline-none focus:ring-2 focus:ring-[color-mix(in_srgb,var(--clinic-primary)_15%,white)]">
                        <option v-for="veterinarian in props.veterinarians" :key="veterinarian.id" :value="veterinarian.id">{{ veterinarian.name }} {{ veterinarian.specialty || 'General' }}</option>
                    </select>
                    <select v-model="appointmentForms[selectedAppointment.id].service_id" class="block w-full rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-panel-bg)] px-4 py-3 text-sm text-[var(--clinic-text)] shadow-sm transition-all duration-200 focus:border-[var(--clinic-primary)] focus:outline-none focus:ring-2 focus:ring-[color-mix(in_srgb,var(--clinic-primary)_15%,white)]" @change="syncServicePrice(appointmentForms[selectedAppointment.id])">
                        <option v-for="service in props.services" :key="service.id" :value="service.id">{{ service.name }} {{ service.service_type }}</option>
                    </select>
                    <input v-model="appointmentForms[selectedAppointment.id].appointment_date" type="datetime-local" class="block w-full rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-panel-bg)] px-4 py-3 text-sm text-[var(--clinic-text)] shadow-sm transition-all duration-200 focus:border-[var(--clinic-primary)] focus:outline-none focus:ring-2 focus:ring-[color-mix(in_srgb,var(--clinic-primary)_15%,white)]" />
                    <input v-model="appointmentForms[selectedAppointment.id].service_price" type="number" min="0" step="0.01" class="block w-full rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-panel-bg)] px-4 py-3 text-sm text-[var(--clinic-text)] shadow-sm transition-all duration-200 focus:border-[var(--clinic-primary)] focus:outline-none focus:ring-2 focus:ring-[color-mix(in_srgb,var(--clinic-primary)_15%,white)]" />
                    <select v-model="appointmentForms[selectedAppointment.id].status" class="block w-full rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-panel-bg)] px-4 py-3 text-sm text-[var(--clinic-text)] shadow-sm transition-all duration-200 focus:border-[var(--clinic-primary)] focus:outline-none focus:ring-2 focus:ring-[color-mix(in_srgb,var(--clinic-primary)_15%,white)]">
                        <option v-for="status in props.appointmentStatuses" :key="status.value" :value="status.value">{{ status.label }}</option>
                    </select>
                    <a :href="selectedAppointment.tracking_url" target="_blank" class="flex items-center gap-2 rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-highlight)] px-4 py-3 text-sm text-[var(--clinic-primary)] underline break-all transition-all duration-150 hover:bg-[color-mix(in_srgb,var(--clinic-primary)_8%,white)]">
                        <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71" />
                            <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71" />
                        </svg>
                        {{ selectedAppointment.tracking_url }}
                    </a>
                </div>

                <textarea v-model="appointmentForms[selectedAppointment.id].reason" rows="4" class="mt-4 block w-full rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-panel-bg)] px-4 py-3 text-sm text-[var(--clinic-text)] shadow-sm transition-all duration-200 focus:border-[var(--clinic-primary)] focus:outline-none focus:ring-2 focus:ring-[color-mix(in_srgb,var(--clinic-primary)_15%,white)]" placeholder="Motivo" />
                <textarea v-model="appointmentForms[selectedAppointment.id].doctor_notes" rows="5" class="mt-4 block w-full rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-panel-bg)] px-4 py-3 text-sm text-[var(--clinic-text)] shadow-sm transition-all duration-200 focus:border-[var(--clinic-primary)] focus:outline-none focus:ring-2 focus:ring-[color-mix(in_srgb,var(--clinic-primary)_15%,white)]" placeholder="Notas del doctor" />

                <AuditInfo :audit="selectedAppointment.audit" />

                <div class="mt-6 flex flex-wrap justify-end gap-3">
                    <button type="button" class="inline-flex items-center gap-2 rounded-full border border-[var(--clinic-border)] px-5 py-3 text-xs font-semibold uppercase tracking-[0.22em] text-[var(--clinic-muted)] transition-all duration-150 hover:border-[var(--clinic-primary)] hover:text-[var(--clinic-primary)]" @click="closeAppointmentModal">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" x2="6" y1="6" y2="18" />
                            <line x1="6" x2="18" y1="6" y2="18" />
                        </svg>
                        Cerrar
                    </button>
                    <button type="button" class="inline-flex items-center gap-2 rounded-full bg-[var(--clinic-primary)] px-5 py-3 text-xs font-semibold uppercase tracking-[0.22em] text-[var(--clinic-highlight)] transition-all duration-200 hover:bg-[var(--clinic-primary-dark)]" @click="saveAppointment(selectedAppointment.id)">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" />
                            <polyline points="17 21 17 13 7 13 7 21" />
                            <polyline points="7 3 7 8 15 8" />
                        </svg>
                        Guardar
                    </button>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
