<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Head, Link } from '@inertiajs/vue3';
import { useFormatting } from '@/composables/useFormatting';

const props = defineProps({
    pet: Object,
    appointments: Array,
});

const { formatCurrency, formatDate } = useFormatting();
</script>

<template>
    <Head :title="`Historial - ${pet.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-2">
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-[var(--clinic-muted)]">Historial clinico</p>
                <h2 class="text-3xl font-black leading-tight text-[var(--clinic-text)]">{{ pet.name }}</h2>
                <p class="max-w-3xl text-sm leading-6 text-[var(--clinic-muted)]">
                    Evolucion temporal del paciente. Todas las citas registradas en orden cronologico.
                </p>
            </div>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-7xl space-y-8 sm:px-6 lg:px-8">
                <section class="rounded-[2rem] border border-[var(--clinic-border)] bg-white/90 p-6 shadow-[0_28px_90px_-55px_color-mix(in_srgb,var(--clinic-primary-dark)_35%,transparent)]">
                    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                        <div class="flex items-center gap-4">
                            <img v-if="pet.photo_path" :src="'/storage/' + pet.photo_path" class="h-16 w-16 rounded-2xl object-cover" />
                            <div v-else class="flex h-16 w-16 items-center justify-center rounded-2xl bg-[color-mix(in_srgb,var(--clinic-primary)_8%,white)]">
                                <svg class="h-7 w-7 text-[color-mix(in_srgb,var(--clinic-primary)_40%,white)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--clinic-muted)]">Historial clinico</p>
                                <h2 class="mt-1 text-3xl font-black leading-tight text-[var(--clinic-text)]">{{ pet.name }}</h2>
                                <p class="mt-1 max-w-3xl text-sm leading-6 text-[var(--clinic-muted)]">
                                    Evolucion temporal del paciente. Todas las citas registradas en orden cronologico.
                                </p>
                            </div>
                        </div>
                        <Link :href="route('pets.index')" class="inline-flex items-center gap-2 rounded-full border border-[var(--clinic-border)] px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-[var(--clinic-primary)] transition-all duration-150 hover:bg-[color-mix(in_srgb,var(--clinic-primary)_5%,white)]">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M19 12H5" /><path d="m12 19-7-7 7-7" />
                            </svg>
                            Volver a pacientes
                        </Link>
                    </div>

                    <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <div class="rounded-[1.4rem] border border-[var(--clinic-border)] bg-[var(--clinic-panel-bg)] p-4">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-[var(--clinic-muted)]">Especie</p>
                            <p class="mt-1.5 text-sm font-bold text-[var(--clinic-text)]">{{ pet.species }}</p>
                        </div>
                        <div class="rounded-[1.4rem] border border-[var(--clinic-border)] bg-[var(--clinic-panel-bg)] p-4">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-[var(--clinic-muted)]">Raza</p>
                            <p class="mt-1.5 text-sm font-bold text-[var(--clinic-text)]">{{ pet.breed || 'No registrada' }}</p>
                        </div>
                        <div class="rounded-[1.4rem] border border-[var(--clinic-border)] bg-[var(--clinic-panel-bg)] p-4">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-[var(--clinic-muted)]">Responsable</p>
                            <p class="mt-1.5 text-sm font-bold text-[var(--clinic-text)]">{{ pet.owner?.name || 'Sin asignar' }}</p>
                            <p class="mt-0.5 text-xs text-[var(--clinic-muted)]">{{ pet.owner?.email }}</p>
                        </div>
                        <div class="rounded-[1.4rem] border border-[var(--clinic-border)] bg-[var(--clinic-panel-bg)] p-4">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-[var(--clinic-muted)]">Veterinario</p>
                            <p class="mt-1.5 text-sm font-bold text-[var(--clinic-text)]">{{ pet.veterinarian?.name || 'Sin asignar' }}</p>
                        </div>
                    </div>

                    <div class="mt-4 rounded-[1.4rem] border border-[var(--clinic-highlight)] bg-[var(--clinic-highlight)]/50 px-5 py-3">
                        <p class="text-xs font-semibold text-[var(--clinic-primary)]">{{ pet.appointments_count }} cita{{ pet.appointments_count !== 1 ? 's' : '' }} registrada{{ pet.appointments_count !== 1 ? 's' : '' }}</p>
                    </div>
                </section>

                <section class="rounded-[2rem] border border-[var(--clinic-border)] bg-white/90 p-6 shadow-[0_28px_90px_-55px_color-mix(in_srgb,var(--clinic-primary-dark)_35%,transparent)]">
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--clinic-muted)]">Linea de tiempo</p>
                    <h3 class="mt-2 text-2xl font-black text-[var(--clinic-text)]">Evolucion clinica</h3>

                    <div v-if="appointments.length" class="mt-6 relative ml-4 border-l-2 border-[var(--clinic-border)] pl-8 space-y-8">
                        <div v-for="appointment in appointments" :key="appointment.id" class="relative">
                            <div class="absolute -left-10 top-1 h-4 w-4 rounded-full border-2 border-[var(--clinic-primary)] bg-white" :class="{ 'bg-[var(--clinic-primary)]': appointment.status === 'completed' }" />
                            <div class="rounded-[1.6rem] border border-[var(--clinic-border)] bg-[var(--clinic-panel-bg)] p-5 transition-all duration-200 hover:shadow-[0_8px_25px_-8px_color-mix(in_srgb,var(--clinic-primary)_20%,transparent)]">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                    <div>
                                        <div class="flex items-center gap-3">
                                            <p class="font-bold text-[var(--clinic-text)]">{{ appointment.service?.name || 'Servicio' }}</p>
                                            <StatusBadge :status="appointment.status" :label="appointment.status_label" />
                                        </div>
                                        <p class="mt-1 text-sm text-[var(--clinic-muted)]">{{ appointment.service?.service_type }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-semibold text-[var(--clinic-primary)]">{{ formatCurrency(appointment.service_price) }}</p>
                                        <p class="mt-0.5 text-xs text-[var(--clinic-muted)]">{{ formatDate(appointment.appointment_date) }}</p>
                                    </div>
                                </div>

                                <div v-if="appointment.reason" class="mt-3 rounded-xl bg-[color-mix(in_srgb,var(--clinic-primary)_4%,white)] px-4 py-2.5">
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.12em] text-[var(--clinic-muted)]">Motivo</p>
                                    <p class="mt-1 text-sm text-[var(--clinic-text)]">{{ appointment.reason }}</p>
                                </div>

                                <div v-if="appointment.doctor_notes" class="mt-3 rounded-xl bg-[color-mix(in_srgb,var(--clinic-primary)_4%,white)] px-4 py-2.5">
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.12em] text-[var(--clinic-muted)]">Notas del doctor</p>
                                    <p class="mt-1 text-sm leading-6 text-[var(--clinic-text)]">{{ appointment.doctor_notes }}</p>
                                </div>

                                <div class="mt-3 flex items-center gap-2 text-xs text-[var(--clinic-muted)]">
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" /><circle cx="12" cy="7" r="4" />
                                    </svg>
                                    <span>{{ appointment.veterinarian?.name || 'Sin asignar' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-else class="mt-6 rounded-[1.6rem] border border-dashed border-[var(--clinic-border)] bg-[var(--clinic-panel-bg)] p-10 text-center">
                        <div class="flex flex-col items-center gap-3">
                            <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-[color-mix(in_srgb,var(--clinic-primary)_8%,white)]">
                                <svg class="h-6 w-6 text-[color-mix(in_srgb,var(--clinic-primary)_40%,white)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <rect width="18" height="18" x="3" y="4" rx="2" ry="2" /><line x1="16" x2="16" y1="2" y2="6" /><line x1="8" x2="8" y1="2" y2="6" /><line x1="3" x2="21" y1="10" y2="10" />
                                </svg>
                            </div>
                            <p class="text-sm text-[var(--clinic-muted)]">Sin citas registradas para este paciente.</p>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
