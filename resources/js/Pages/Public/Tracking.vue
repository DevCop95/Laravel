<script setup>
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useFormatting } from '@/composables/useFormatting';
import { usePolling } from '@/composables/usePolling';

const props = defineProps({
    clinicName: String,
    tracking: Object,
});

const { formatDateLong, statusBadgeClass, formatCurrency } = useFormatting();

const currentTracking = ref(props.tracking);

const refreshTracking = async () => {
    try {
        const response = await fetch(`${window.location.pathname}/data`, {
            headers: {
                Accept: 'application/json',
            },
        });

        if (!response.ok) {
            return;
        }

        currentTracking.value = await response.json();
    } catch {
        // Keep the last successful state if polling fails.
    }
};

usePolling(refreshTracking, 15000);
</script>

<template>
    <Head :title="`Seguimiento - ${clinicName}`" />

    <div class="min-h-screen bg-[#f5efe6] px-6 py-10 text-[#173729]">
        <div class="mx-auto max-w-5xl">
            <div class="overflow-hidden rounded-[2.2rem] border border-[#dbe2d4] bg-white/92 shadow-[0_30px_90px_-55px_rgba(23,55,41,0.95)]">
                <div class="bg-[linear-gradient(135deg,_rgba(36,84,63,0.98)_0%,_rgba(85,125,100,0.92)_100%)] px-8 py-10 text-[#f6f2e8]">
                    <p class="text-xs font-semibold uppercase tracking-[0.28em] text-[#dbe8d4]">Seguimiento temporal del caso</p>
                    <h1 class="mt-3 text-4xl font-black">{{ clinicName }}</h1>
                    <p class="mt-4 max-w-2xl text-sm leading-7 text-[#eef4ea]">
                        Este enlace muestra solo la evolucion del servicio actual y se actualiza automaticamente con las notas del equipo veterinario.
                    </p>
                </div>

                <div class="grid gap-6 px-8 py-8 lg:grid-cols-[1.1fr_0.9fr]">
                    <section class="space-y-6">
                        <div class="rounded-[1.8rem] border border-[#e3e8dc] bg-[#fafbf8] p-6">
                            <div class="flex flex-wrap items-start justify-between gap-4">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-[#6b856e]">Paciente</p>
                                    <h2 class="mt-2 text-3xl font-black text-[#173729]">
                                        {{ currentTracking.pet_name }}
                                    </h2>
                                    <p class="mt-2 text-sm text-[#5d7365]">
                                        {{ currentTracking.species }} · Responsable: {{ currentTracking.owner_name }}
                                    </p>
                                </div>
                                <span class="rounded-full px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em]" :class="statusBadgeClass(currentTracking.status)">
                                    {{ currentTracking.status_label }}
                                </span>
                            </div>

                            <div class="mt-6 grid gap-4 md:grid-cols-2">
                                <div class="rounded-[1.4rem] bg-white p-4">
                                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[#6b856e]">Fecha del servicio</p>
                                    <p class="mt-3 text-sm font-medium text-[#173729]">{{ formatDateLong(currentTracking.appointment_date) }}</p>
                                </div>
                                <div class="rounded-[1.4rem] bg-white p-4">
                                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[#6b856e]">Ultima actualizacion</p>
                                    <p class="mt-3 text-sm font-medium text-[#173729]">{{ formatDateLong(currentTracking.updated_at) }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-[1.8rem] border border-[#e3e8dc] bg-white p-6">
                            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-[#6b856e]">Motivo de consulta</p>
                            <p class="mt-4 text-base leading-7 text-[#395143]">{{ currentTracking.reason }}</p>
                            <div class="mt-5 grid gap-4 md:grid-cols-2">
                                <div class="rounded-[1.4rem] bg-[#fafbf8] p-4">
                                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[#6b856e]">Servicio</p>
                                    <p class="mt-3 text-sm font-medium text-[#173729]">{{ currentTracking.service_name || 'Sin servicio asignado' }}</p>
                                </div>
                                <div class="rounded-[1.4rem] bg-[#fafbf8] p-4">
                                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[#6b856e]">Veterinario</p>
                                    <p class="mt-3 text-sm font-medium text-[#173729]">{{ currentTracking.veterinarian_name || 'Sin profesional asignado' }}</p>
                                </div>
                            </div>
                        </div>
                    </section>

                    <aside class="space-y-6">
                        <div class="rounded-[1.8rem] border border-[#dbe2d4] bg-white p-6">
                            <a
                                :href="route('tracking.print', $page.params.token)"
                                target="_blank"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-[#24543f] px-5 py-3.5 text-sm font-semibold text-white transition-all duration-200 hover:bg-[#1d4634] hover:shadow-[0_8px_20px_-6px_rgba(36,84,63,0.4)]"
                            >
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="6 9 6 2 18 2 18 9" />
                                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" />
                                    <rect width="12" height="8" x="6" y="14" />
                                </svg>
                                Imprimir resumen
                            </a>
                        </div>

                        <div class="rounded-[1.8rem] border border-[#dbe2d4] bg-[#f8faf5] p-6">
                            <div class="flex items-center justify-between gap-3">
                                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-[#6b856e]">Notas del doctor</p>
                                <span class="inline-flex h-3 w-3 rounded-full bg-[#7ea27f] shadow-[0_0_0_8px_rgba(126,162,127,0.14)]" />
                            </div>
                            <div class="mt-4 rounded-[1.4rem] bg-white p-5">
                                <p class="text-sm leading-7 text-[#395143]">
                                    {{ currentTracking.doctor_notes || 'Aun no hay una actualizacion escrita por el equipo veterinario.' }}
                                </p>
                            </div>
                        </div>

                        <div class="rounded-[1.8rem] border border-[#dbe2d4] bg-white p-6">
                            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-[#6b856e]">Estado del servicio</p>
                            <div class="mt-4 space-y-3">
                                <div class="rounded-[1.4rem] bg-[#fafbf8] p-4">
                                    <p class="text-sm font-semibold text-[#173729]">{{ currentTracking.status_label }}</p>
                                    <p class="mt-2 text-sm text-[#5d7365]">
                                        El equipo puede actualizar esta informacion mientras el caso avanza.
                                    </p>
                                </div>
                                <div class="rounded-[1.4rem] bg-[#fafbf8] p-4">
                                    <p class="text-sm font-semibold text-[#173729]">Servicio finalizado</p>
                                    <p class="mt-2 text-sm text-[#5d7365]">
                                        {{ formatDateLong(currentTracking.service_finished_at) }}
                                    </p>
                                </div>
                                <div class="rounded-[1.4rem] bg-[#fafbf8] p-4">
                                    <p class="text-sm font-semibold text-[#173729]">Costo del servicio</p>
                                    <p class="mt-2 text-sm text-[#5d7365]">
                                        {{ formatCurrency(currentTracking.service_price) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </aside>
                </div>
            </div>
        </div>
    </div>
</template>
