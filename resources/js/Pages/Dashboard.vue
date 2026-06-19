<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ChartCard from '@/Components/ChartCard.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { useFormatting } from '@/composables/useFormatting';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    stats: Object,
    recentAppointments: Array,
    appointments: Array,
});

const { formatCurrency, formatDate } = useFormatting();

const activeAppointments = computed(() =>
    props.appointments.filter((appointment) => appointment.status !== 'completed' && appointment.status !== 'cancelled'),
);

const completedAppointments = computed(() =>
    props.appointments.filter((appointment) => appointment.status === 'completed').length,
);

const scheduledToday = computed(() => {
    const today = new Date().toISOString().slice(0, 10);
    return props.appointments.filter((appointment) => appointment.appointment_date_input?.slice(0, 10) === today).length;
});

const revenueInProgress = computed(() =>
    activeAppointments.value.reduce((sum, appointment) => sum + Number(appointment.service_price || 0), 0),
);

const totalRevenue = computed(() =>
    props.appointments
        .filter((a) => a.status === 'completed')
        .reduce((sum, a) => sum + Number(a.service_price || 0), 0),
);

const statusChartData = computed(() => {
    const counts = { scheduled: 0, in_progress: 0, completed: 0, cancelled: 0 };
    props.appointments.forEach((a) => { counts[a.status] = (counts[a.status] || 0) + 1; });
    return {
        labels: ['Programadas', 'En progreso', 'Finalizadas', 'Canceladas'],
        datasets: [{
            data: [counts.scheduled, counts.in_progress, counts.completed, counts.cancelled],
            backgroundColor: ['#efe3c8', '#dce7ef', '#dce6d1', '#f3ded7'],
            borderColor: ['#6e5b42', '#36556e', '#24543f', '#8a4d3a'],
            borderWidth: 2,
            borderRadius: 8,
        }],
    };
});

const revenueChartData = computed(() => {
    const byService = {};
    props.appointments
        .filter((a) => a.status === 'completed')
        .forEach((a) => {
            const name = a.service?.name || 'Otro';
            byService[name] = (byService[name] || 0) + Number(a.service_price || 0);
        });
    const sorted = Object.entries(byService).sort((a, b) => b[1] - a[1]).slice(0, 5);
    return {
        labels: sorted.map(([k]) => k),
        datasets: [{
            data: sorted.map(([, v]) => v),
            backgroundColor: ['#24543f', '#2c664f', '#3a7d5e', '#4a9470', '#5aab82'],
            borderRadius: 8,
        }],
    };
});

const recentByDayChartData = computed(() => {
    const days = {};
    const now = new Date();
    for (let i = 6; i >= 0; i--) {
        const d = new Date(now);
        d.setDate(d.getDate() - i);
        const key = d.toISOString().slice(0, 10);
        days[key] = 0;
    }
    props.appointments.forEach((a) => {
        const key = a.appointment_date_input?.slice(0, 10);
        if (key && key in days) days[key]++;
    });
    const labels = Object.keys(days).map((k) => {
        const d = new Date(k + 'T12:00:00');
        return d.toLocaleDateString('es-CO', { weekday: 'short', day: 'numeric' });
    });
    return {
        labels,
        datasets: [{
            label: 'Citas',
            data: Object.values(days),
            borderColor: '#24543f',
            backgroundColor: 'rgba(36,84,63,0.1)',
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#24543f',
            pointRadius: 4,
            pointHoverRadius: 6,
        }],
    };
});

const todayAppointments = computed(() => {
    const today = new Date().toISOString().slice(0, 10);
    return props.appointments
        .filter((a) => a.appointment_date_input?.slice(0, 10) === today)
        .sort((a, b) => a.appointment_date?.localeCompare(b.appointment_date));
});

const vetStatsData = computed(() => {
    const byVet = {};
    props.appointments
        .filter((a) => a.status === 'completed')
        .forEach((a) => {
            const name = a.veterinarian?.name || 'Sin asignar';
            if (!byVet[name]) byVet[name] = { revenue: 0, count: 0 };
            byVet[name].revenue += Number(a.service_price || 0);
            byVet[name].count++;
        });
    const sorted = Object.entries(byVet).sort((a, b) => b[1].revenue - a[1].revenue).slice(0, 6);
    return {
        labels: sorted.map(([k]) => k),
        datasets: [{
            label: 'Ingresos',
            data: sorted.map(([, v]) => v.revenue),
            backgroundColor: ['#24543f', '#2c664f', '#3a7d5e', '#4a9470', '#5aab82', '#6bc494'],
            borderRadius: 8,
        }],
    };
});

</script>

<template>
    <Head title="Agenda" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-2">
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-[var(--clinic-muted)]">Dashboard clinico</p>
                <h2 class="text-3xl font-black leading-tight text-[var(--clinic-text)]">Vista general de {{ $page.props.clinic.name }}</h2>
                <p class="max-w-3xl text-sm leading-6 text-[var(--clinic-muted)]">
                    Resumen operativo, analytics y edicion detallada en ventanas modales.
                </p>
            </div>
        </template>

        <div class="py-10 overflow-x-auto">
            <div class="mx-auto max-w-7xl min-w-0 space-y-8 sm:px-6 lg:px-8">
                <!-- Hero Stats -->
                <section class="overflow-hidden rounded-[2rem] border border-[var(--clinic-border)] px-6 py-8 text-[var(--clinic-highlight)] shadow-[0_32px_90px_-50px_color-mix(in_srgb,var(--clinic-primary-dark)_65%,transparent)] sm:px-8" :style="{ background: `linear-gradient(135deg, ${$page.props.clinic.palette.colors.hero_from} 0%, ${$page.props.clinic.palette.colors.hero_via} 54%, ${$page.props.clinic.palette.colors.hero_to} 100%)` }">
                    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4 overflow-x-auto">
                        <div class="rounded-[1.6rem] border border-white/15 bg-white/10 p-5 xl:col-span-2">
                            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-white/75">Centro de control</p>
                            <h3 class="mt-3 text-3xl font-black leading-tight">Resumen general antes de entrar a operar.</h3>
                            <p class="mt-4 max-w-2xl text-sm leading-7 text-white/80">
                                Dashboard para leer carga, proximas citas y capacidad. La gestion completa vive en el modulo de citas y en los CRUD dedicados.
                            </p>
                            <div class="mt-5 flex flex-wrap gap-3 text-xs font-semibold uppercase tracking-[0.18em]">
                                <Link :href="route('appointments.index')" class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-4 py-2"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg><span>Citas</span></Link>
                                <Link :href="route('pets.index')" class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-4 py-2"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg><span>Pacientes</span></Link>
                                <Link :href="route('veterinarians.index')" class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-4 py-2"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg><span>Equipo clinico</span></Link>
                                <Link :href="route('services.index')" class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-4 py-2"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 5H2v7l6.29 6.29c.94.94 2.48.94 3.42 0l3.58-3.58c.94-.94.94-2.48 0-3.42L9 5Z"/><path d="M6 9.01V9"/><path d="m15 5 6.3 6.3a2.4 2.4 0 0 1 0 3.4L17 19"/></svg><span>Servicios</span></Link>
                            </div>
                        </div>
                        <div class="rounded-[1.6rem] border border-white/15 bg-white/10 p-5">
                            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-white/75">Casos activos</p>
                            <p class="mt-3 text-4xl font-black">{{ activeAppointments.length }}</p>
                            <p class="mt-2 text-sm text-white/80">Pacientes en seguimiento o programados.</p>
                        </div>
                        <div class="rounded-[1.6rem] border border-white/15 p-5 text-[var(--clinic-text)]" :style="{ backgroundColor: 'var(--clinic-highlight)' }">
                            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-[var(--clinic-muted)]">Agenda de hoy</p>
                            <p class="mt-3 text-4xl font-black">{{ scheduledToday }}</p>
                            <p class="mt-2 text-sm text-[var(--clinic-muted)]">Citas con fecha de hoy.</p>
                        </div>
                        <div class="rounded-[1.6rem] border border-white/15 p-5 text-[var(--clinic-highlight)]" :style="{ backgroundColor: 'color-mix(in srgb, var(--clinic-primary) 76%, transparent)' }">
                            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-white/75">Finalizadas</p>
                            <p class="mt-3 text-4xl font-black">{{ completedAppointments }}</p>
                            <p class="mt-2 text-sm text-white/80">Servicios cerrados y listos para compartir.</p>
                        </div>
                        <div class="rounded-[1.6rem] border border-white/15 bg-white/10 p-5 text-[var(--clinic-highlight)]">
                            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-white/75">Monto en curso</p>
                            <p class="mt-3 text-2xl font-black">{{ formatCurrency(revenueInProgress) }}</p>
                            <p class="mt-2 text-sm text-white/80">Valor acumulado en agenda activa.</p>
                        </div>
                        <div class="rounded-[1.6rem] border border-white/15 bg-white/10 p-5 md:col-span-2">
                            <p class="text-xs font-semibold uppercase tracking-[0.22em] text-white/75">Capacidad configurada</p>
                            <p class="mt-3 text-lg font-black">{{ props.stats.pets }} pacientes, {{ props.stats.veterinarians }} profesionales y {{ props.stats.services }} servicios.</p>
                        </div>
                    </div>
                </section>

                <!-- Analytics Charts -->
                <section class="grid gap-6 lg:grid-cols-3 overflow-x-auto">
                    <div class="rounded-[2rem] border border-[var(--clinic-border)] bg-white/90 p-6 shadow-[0_28px_90px_-55px_color-mix(in_srgb,var(--clinic-primary-dark)_35%,transparent)]">
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--clinic-muted)]">Distribucion</p>
                        <h3 class="mt-2 text-xl font-black text-[var(--clinic-text)]">Citas por estado</h3>
                        <div class="mt-5">
                            <ChartCard type="doughnut" :data="statusChartData" :height="200" :options="{ plugins: { legend: { display: true, position: 'bottom', labels: { boxWidth: 12, padding: 12, font: { size: 11 } } } }, cutout: '65%' }" />
                        </div>
                    </div>

                    <div class="rounded-[2rem] border border-[var(--clinic-border)] bg-white/90 p-6 shadow-[0_28px_90px_-55px_color-mix(in_srgb,var(--clinic-primary-dark)_35%,transparent)]">
                        <div class="flex items-end justify-between gap-2">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--clinic-muted)]">Ingresos</p>
                                <h3 class="mt-2 text-xl font-black text-[var(--clinic-text)]">Por servicio</h3>
                            </div>
                            <span class="rounded-full bg-[var(--clinic-highlight)] px-3 py-1 text-xs font-semibold text-[var(--clinic-primary)]">{{ formatCurrency(totalRevenue) }}</span>
                        </div>
                        <div class="mt-5">
                            <ChartCard type="bar" :data="revenueChartData" :height="200" />
                        </div>
                    </div>

                    <div class="rounded-[2rem] border border-[var(--clinic-border)] bg-white/90 p-6 shadow-[0_28px_90px_-55px_color-mix(in_srgb,var(--clinic-primary-dark)_35%,transparent)]">
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--clinic-muted)]">Tendencia</p>
                        <h3 class="mt-2 text-xl font-black text-[var(--clinic-text)]">Citas ultimos 7 dias</h3>
                        <div class="mt-5">
                            <ChartCard type="line" :data="recentByDayChartData" :height="200" />
                        </div>
                    </div>
                </section>

                <!-- Today's Agenda -->
                <section class="rounded-[2rem] border border-[var(--clinic-border)] bg-white/90 p-6 shadow-[0_28px_90px_-55px_color-mix(in_srgb,var(--clinic-primary-dark)_35%,transparent)]">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--clinic-muted)]">Agenda del dia</p>
                            <h3 class="mt-2 text-2xl font-black text-[var(--clinic-text)]">Citas de hoy</h3>
                        </div>
                        <span class="rounded-full bg-[var(--clinic-highlight)] px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-[var(--clinic-primary)]">{{ todayAppointments.length }} cita{{ todayAppointments.length !== 1 ? 's' : '' }}</span>
                    </div>

                    <div v-if="todayAppointments.length" class="mt-5 space-y-3">
                        <div v-for="appointment in todayAppointments" :key="appointment.id" class="flex items-center gap-4 rounded-[1.4rem] border border-[var(--clinic-border)] bg-[var(--clinic-panel-bg)] p-4 transition-all duration-150 hover:shadow-[0_4px_15px_-4px_color-mix(in_srgb,var(--clinic-primary)_15%,transparent)]">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-[color-mix(in_srgb,var(--clinic-primary)_8%,white)] text-[var(--clinic-primary)]">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10" /><polyline points="12 6 12 12 16 14" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <p class="font-bold text-[var(--clinic-text)] truncate">{{ appointment.pet?.name }}</p>
                                    <StatusBadge :status="appointment.status" :label="appointment.status_label" />
                                </div>
                                <p class="mt-0.5 text-sm text-[var(--clinic-muted)] truncate">{{ appointment.service?.name }} con {{ appointment.veterinarian?.name || 'Sin asignar' }}</p>
                            </div>
                            <div class="text-right shrink-0">
                                <p class="text-sm font-semibold text-[var(--clinic-primary)]">{{ formatCurrency(appointment.service_price) }}</p>
                                <p class="mt-0.5 text-xs text-[var(--clinic-muted)]">{{ new Date(appointment.appointment_date).toLocaleTimeString('es-CO', { hour: '2-digit', minute: '2-digit' }) }}</p>
                            </div>
                        </div>
                    </div>

                    <div v-else class="mt-5 rounded-[1.6rem] border border-dashed border-[var(--clinic-border)] bg-[var(--clinic-panel-bg)] p-8 text-center">
                        <p class="text-sm text-[var(--clinic-muted)]">Sin citas programadas para hoy.</p>
                    </div>
                </section>

                <!-- Vet Stats Chart -->
                <section v-if="Object.keys(vetStatsData.labels).length" class="rounded-[2rem] border border-[var(--clinic-border)] bg-white/90 p-6 shadow-[0_28px_90px_-55px_color-mix(in_srgb,var(--clinic-primary-dark)_35%,transparent)]">
                    <div class="flex items-end justify-between gap-2">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--clinic-muted)]">Rendimiento</p>
                            <h3 class="mt-2 text-xl font-black text-[var(--clinic-text)]">Ingresos por veterinario</h3>
                        </div>
                    </div>
                    <div class="mt-5">
                        <ChartCard type="bar" :data="vetStatsData" :height="220" />
                    </div>
                </section>

                <!-- Bottom Grid -->
                <section class="grid gap-6 xl:grid-cols-[1fr_1fr] overflow-x-auto">
                    <div class="space-y-6">
                        <section class="rounded-[2rem] border border-[var(--clinic-border)] bg-white/90 p-6 shadow-[0_28px_90px_-55px_color-mix(in_srgb,var(--clinic-primary-dark)_35%,transparent)]">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--clinic-muted)]">Modulo de citas</p>
                                    <h3 class="mt-2 text-2xl font-black text-[var(--clinic-text)]">Ir a gestion completa</h3>
                                </div>
                            </div>

                            <p class="mt-3 text-sm leading-6 text-[var(--clinic-muted)]">
                                Consulta programadas, en progreso, finalizadas y canceladas con filtros, tabla y modales en un solo lugar.
                            </p>

                            <Link :href="route('appointments.index')" class="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-full bg-[var(--clinic-primary)] px-6 py-3 text-xs font-semibold uppercase tracking-[0.28em] text-[var(--clinic-highlight)] transition hover:bg-[var(--clinic-primary-dark)]">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                                <span>Ver citas</span>
                            </Link>
                        </section>

                        <section class="rounded-[2rem] border border-[var(--clinic-border)] bg-white/90 p-6 shadow-[0_28px_90px_-55px_color-mix(in_srgb,var(--clinic-primary-dark)_35%,transparent)]">
                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--clinic-muted)]">Proximas citas</p>
                            <div class="mt-5 space-y-3">
                                <div v-for="appointment in props.recentAppointments" :key="appointment.id" class="rounded-[1.4rem] border border-[var(--clinic-border)] bg-[var(--clinic-panel-bg)] p-4">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <p class="font-bold text-[var(--clinic-text)]">{{ appointment.pet.name }}</p>
                                            <p class="text-sm text-[var(--clinic-muted)]">{{ appointment.service.name }} con {{ appointment.veterinarian.name || 'Sin asignar' }}</p>
                                        </div>
                                        <StatusBadge :status="appointment.status" :label="appointment.status_label" />
                                    </div>
                                    <p class="mt-3 text-sm text-[var(--clinic-muted)]">{{ formatDate(appointment.appointment_date) }}</p>
                                </div>
                            </div>
                        </section>
                    </div>
                    <section class="rounded-[2rem] border border-[var(--clinic-border)] bg-white/90 p-6 shadow-[0_28px_90px_-55px_color-mix(in_srgb,var(--clinic-primary-dark)_35%,transparent)]">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--clinic-muted)]">Casos activos</p>
                                <h3 class="mt-2 text-2xl font-black text-[var(--clinic-text)]">Resumen rapido</h3>
                            </div>
                            <Link :href="route('appointments.index')" class="inline-flex items-center gap-2 rounded-full border border-[var(--clinic-border)] px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-[var(--clinic-primary)]">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 16 16 12 12 8"/><line x1="8" x2="16" y1="12" y2="12"/></svg>
                                <span>Abrir citas</span>
                            </Link>
                        </div>

                        <div class="mt-6 space-y-4">
                            <div v-for="appointment in activeAppointments.slice(0, 6)" :key="appointment.id" class="rounded-[1.5rem] border border-[var(--clinic-border)] bg-[var(--clinic-panel-bg)] p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="font-bold text-[var(--clinic-text)]">{{ appointment.pet.name }}</p>
                                        <p class="text-sm text-[var(--clinic-muted)]">{{ appointment.service.name }} con {{ appointment.veterinarian.name || 'Sin asignar' }}</p>
                                    </div>
                                    <p class="text-sm font-semibold text-[var(--clinic-muted)]">{{ formatCurrency(appointment.service_price) }}</p>
                                </div>
                                <p class="mt-3 text-sm text-[var(--clinic-muted)]">{{ formatDate(appointment.appointment_date) }}</p>
                            </div>
                        </div>
                    </section>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
