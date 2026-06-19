<script setup>
import { computed, watch } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Toast from '@/Components/Toast.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { useToast } from '@/composables/useToast';

const page = usePage();
const toast = useToast();
const palette = computed(() => page.props.clinic.palette.colors);

const themeStyle = computed(() => ({
    '--clinic-shell-bg': palette.value.shell_bg,
    '--clinic-shell-surface': palette.value.shell_surface,
    '--clinic-panel-bg': palette.value.panel_bg,
    '--clinic-primary': palette.value.primary,
    '--clinic-primary-dark': palette.value.primary_dark,
    '--clinic-accent': palette.value.accent,
    '--clinic-highlight': palette.value.highlight,
    '--clinic-text': palette.value.text,
    '--clinic-muted': palette.value.muted,
    '--clinic-border': palette.value.border,
    '--clinic-hero-from': palette.value.hero_from,
    '--clinic-hero-via': palette.value.hero_via,
    '--clinic-hero-to': palette.value.hero_to,
}));

watch(
    () => page.props.flash?.status,
    (msg) => {
        if (msg) toast.success(msg);
    },
    { immediate: true },
);
</script>

<template>
    <div :style="themeStyle" class="min-h-screen overflow-hidden bg-[var(--clinic-shell-bg)] text-[var(--clinic-text)]">
        <div class="grid min-h-screen min-[900px]:grid-cols-[0.95fr_1.05fr]">
            <!-- Form Panel -->
            <div class="relative flex items-center justify-center bg-[linear-gradient(180deg,color-mix(in_srgb,var(--clinic-panel-bg)_98%,transparent)_0%,color-mix(in_srgb,var(--clinic-shell-bg)_96%,transparent)_100%)] px-6 py-10 lg:px-12">
                <!-- Floating orbs -->
                <div class="absolute left-10 top-10 h-32 w-32 rounded-full bg-[color-mix(in_srgb,var(--clinic-accent)_35%,white)] blur-3xl animate-pulse" style="animation-duration: 4s;" />
                <div class="absolute bottom-12 left-24 h-40 w-40 rounded-full bg-[color-mix(in_srgb,var(--clinic-highlight)_80%,white)] blur-3xl animate-pulse" style="animation-duration: 6s; animation-delay: 1s;" />
                <div class="absolute right-20 top-1/3 h-24 w-24 rounded-full bg-[color-mix(in_srgb,var(--clinic-primary)_20%,white)] blur-2xl animate-pulse" style="animation-duration: 5s; animation-delay: 2s;" />

                <div class="relative z-10 w-full max-w-md">
                    <Link href="/" class="mb-10 inline-flex items-center gap-4 group">
                        <div class="flex h-20 w-20 items-center justify-center rounded-[1.8rem] border border-[var(--clinic-border)] bg-white p-4 shadow-[0_24px_70px_-40px_color-mix(in_srgb,var(--clinic-text)_45%,transparent)] transition-all duration-300 group-hover:shadow-[0_24px_70px_-30px_color-mix(in_srgb,var(--clinic-primary)_55%,transparent)] group-hover:border-[color-mix(in_srgb,var(--clinic-primary)_40%,white)]">
                            <template v-if="$page.props.clinic.logo_path">
                                <img :src="'/storage/' + $page.props.clinic.logo_path" class="h-full w-auto" />
                            </template>
                            <template v-else>
                                <ApplicationLogo class="h-full w-full" />
                            </template>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.34em] text-[var(--clinic-muted)]">Veterinaria</p>
                            <h1 class="text-4xl font-black text-[var(--clinic-text)] transition-colors group-hover:text-[var(--clinic-primary-dark)]">{{ $page.props.clinic.name }}</h1>
                        </div>
                    </Link>

                    <slot />
                </div>
            </div>

            <!-- Hero Panel -->
            <div class="relative hidden overflow-hidden bg-[radial-gradient(circle_at_top,color-mix(in_srgb,var(--clinic-accent)_16%,transparent),transparent_24%),linear-gradient(160deg,var(--clinic-hero-from)_0%,var(--clinic-hero-via)_48%,var(--clinic-hero-to)_100%)] px-14 py-14 text-white min-[900px]:flex min-[900px]:flex-col min-[900px]:justify-center">
                <!-- Animated background patterns -->
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_22%_22%,_rgba(255,255,255,0.14),_transparent_18%),radial-gradient(circle_at_78%_20%,_rgba(239,227,200,0.12),_transparent_16%),radial-gradient(circle_at_72%_80%,_rgba(220,230,209,0.14),_transparent_18%)]" />

                <!-- Floating geometric shapes -->
                <div class="absolute right-16 top-20 h-16 w-16 rounded-2xl border border-white/10 bg-white/5 backdrop-blur-sm rotate-12 animate-pulse" style="animation-duration: 7s;" />
                <div class="absolute bottom-32 right-32 h-10 w-10 rounded-full border border-white/10 bg-white/5 backdrop-blur-sm animate-pulse" style="animation-duration: 5s; animation-delay: 1.5s;" />
                <div class="absolute left-16 bottom-48 h-12 w-12 rounded-xl border border-white/10 bg-white/5 backdrop-blur-sm -rotate-12 animate-pulse" style="animation-duration: 6s; animation-delay: 0.5s;" />
                <div class="absolute right-48 top-1/2 h-8 w-8 rounded-lg border border-white/8 bg-white/4 backdrop-blur-sm rotate-45 animate-pulse" style="animation-duration: 8s; animation-delay: 2s;" />

                <div class="relative z-10 mx-auto w-full max-w-[32rem]">
                    <p class="text-xs font-semibold uppercase tracking-[0.34em] text-[#dbe8d4]">Vista previa del panel</p>
                    <h2 class="mt-5 max-w-xl text-5xl font-black leading-[0.98] tracking-tight">
                        Registrar nueva cita y entrar al flujo correcto.
                    </h2>

                    <div class="mt-10 rounded-[2.5rem] border border-white/12 bg-[linear-gradient(180deg,_rgba(255,255,255,0.14),_rgba(255,255,255,0.08))] p-7 shadow-[0_40px_120px_-50px_rgba(0,0,0,0.78)] backdrop-blur-md">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-[#dbe8d4]">Nueva cita</p>
                                <h3 class="mt-3 text-4xl font-black text-white">Registrar servicio</h3>
                            </div>
                            <div class="flex h-16 w-16 items-center justify-center rounded-[1.4rem] border border-white/12 bg-white/10">
                                <template v-if="$page.props.clinic.logo_path">
                                    <img :src="'/storage/' + $page.props.clinic.logo_path" class="h-10 w-10 object-contain" />
                                </template>
                                <template v-else>
                                    <ApplicationLogo class="h-10 w-10" />
                                </template>
                            </div>
                        </div>

                        <div class="mt-8 space-y-4">
                            <div class="rounded-[1.35rem] border border-white/14 bg-white/10 px-5 py-4 text-lg font-medium text-white/95">
                                Paciente Especie Responsable
                            </div>
                            <div class="rounded-[1.35rem] border border-white/14 bg-white/10 px-5 py-4 text-lg font-medium text-white/95">
                                Profesional Especialidad
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <div class="rounded-[1.35rem] border border-white/14 bg-white/10 px-5 py-4 text-lg font-medium text-white/95">
                                    Servicio programado
                                </div>
                                <div class="rounded-[1.35rem] border border-white/14 bg-white/10 px-5 py-4 text-lg font-medium text-white/95">
                                    Fecha y hora
                                </div>
                            </div>

                            <div class="rounded-[1.7rem] border border-white/14 bg-white/10 px-5 py-5 text-lg text-white/80">
                                Motivo del servicio
                            </div>
                        </div>

                        <div class="mt-7">
                            <div class="inline-flex w-full items-center justify-center rounded-full bg-[#f6f2e8] px-6 py-4 text-sm font-semibold uppercase tracking-[0.28em] text-[#24543f] shadow-[0_24px_50px_-25px_rgba(246,242,232,0.6)]">
                                Crear cita
                            </div>
                        </div>
                    </div>

                    <!-- Footer branding -->
                    <div class="mt-12 flex items-center gap-3 text-sm text-white/40">
                        <div class="h-px flex-1 bg-white/10" />
                        <span class="text-xs font-medium uppercase tracking-[0.2em]">{{ $page.props.clinic.name }}</span>
                        <div class="h-px flex-1 bg-white/10" />
                    </div>
                </div>
            </div>
        </div>
        <Toast />
    </div>
</template>
