<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { useFormatting } from '@/composables/useFormatting';

const props = defineProps({
    logs: Array,
    pagination: Object,
    modelLabels: Object,
});

const { formatDate } = useFormatting();

const actionLabels = {
    created: 'Creo',
    updated: 'Edito',
    deleted: 'Elimino',
};

const actionColors = {
    created: 'bg-[#dce6d1] text-[#24543f]',
    updated: 'bg-[#dce7ef] text-[#36556e]',
    deleted: 'bg-[#f3ded7] text-[#8a4d3a]',
};

const modelColors = {
    'App\\Models\\Appointment': 'bg-[#efe3c8] text-[#6e5b42]',
    'App\\Models\\Pet': 'bg-[#dce6d1] text-[#24543f]',
    'App\\Models\\Owner': 'bg-[#dce7ef] text-[#36556e]',
    'App\\Models\\Veterinarian': 'bg-[#f3ded7] text-[#8a4d3a]',
    'App\\Models\\Service': 'bg-[#efe3c8] text-[#6e5b42]',
    'App\\Models\\InventoryItem': 'bg-[#dce7ef] text-[#36556e]',
};
</script>

<template>
    <Head title="Auditoria" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-2">
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-[var(--clinic-muted)]">Auditoria</p>
                <h2 class="text-3xl font-black leading-tight text-[var(--clinic-text)]">Historial de cambios</h2>
                <p class="max-w-3xl text-sm leading-6 text-[var(--clinic-muted)]">
                    Registro completo de todas las acciones realizadas en el sistema. Quien hizo que y cuando.
                </p>
            </div>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-7xl space-y-8 sm:px-6 lg:px-8">
                <section class="rounded-[2rem] border border-[var(--clinic-border)] bg-white/90 p-6 shadow-[0_28px_90px_-55px_color-mix(in_srgb,var(--clinic-primary-dark)_35%,transparent)]">
                    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--clinic-muted)]">Registro</p>
                            <h3 class="mt-2 text-2xl font-black text-[var(--clinic-text)]">Actividad del sistema</h3>
                        </div>
                        <span class="rounded-full bg-[var(--clinic-highlight)] px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-[var(--clinic-primary)]">{{ pagination.total }} registros</span>
                    </div>

                    <div class="mt-6 space-y-3">
                        <div v-for="log in logs" :key="log.id" class="rounded-[1.4rem] border border-[var(--clinic-border)] bg-[var(--clinic-panel-bg)] p-4 transition-all duration-150 hover:shadow-[0_4px_15px_-4px_color-mix(in_srgb,var(--clinic-primary)_15%,transparent)]">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                <div class="flex items-start gap-3">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl" :class="actionColors[log.action] || 'bg-gray-100 text-gray-600'">
                                        <svg v-if="log.action === 'created'" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <line x1="12" x2="12" y1="5" y2="19" /><line x1="5" x2="19" y1="12" y2="12" />
                                        </svg>
                                        <svg v-else-if="log.action === 'updated'" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z" />
                                        </svg>
                                        <svg v-else class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M3 6h18" /><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6" />
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="font-bold text-[var(--clinic-text)]">{{ log.user_name || 'Sistema' }}</span>
                                            <span class="text-sm text-[var(--clinic-muted)]">{{ actionLabels[log.action] || log.action }}</span>
                                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-[11px] font-semibold" :class="modelColors[log.auditable_type] || 'bg-gray-100 text-gray-600'">
                                                {{ modelLabels[log.auditable_type] || log.auditable_type }}
                                            </span>
                                            <span class="text-sm font-semibold text-[var(--clinic-primary)]">#{{ log.auditable_id }}</span>
                                        </div>
                                        <p v-if="log.description" class="mt-1 text-sm text-[var(--clinic-muted)]">{{ log.description }}</p>
                                        <div v-if="log.changes && Object.keys(log.changes).length" class="mt-2 flex flex-wrap gap-1.5">
                                            <span v-for="(value, key) in log.changes" :key="key" class="inline-flex items-center gap-1 rounded-full bg-[color-mix(in_srgb,var(--clinic-primary)_5%,white)] px-2.5 py-0.5 text-[11px] text-[var(--clinic-muted)]">
                                                <span class="font-semibold text-[var(--clinic-text)]">{{ key }}</span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <span class="shrink-0 text-xs text-[var(--clinic-muted)]">{{ formatDate(log.created_at) }}</span>
                            </div>
                        </div>

                        <div v-if="!logs.length" class="rounded-[1.6rem] border border-dashed border-[var(--clinic-border)] bg-[var(--clinic-panel-bg)] p-10 text-center">
                            <p class="text-sm text-[var(--clinic-muted)]">No hay registros de actividad.</p>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
