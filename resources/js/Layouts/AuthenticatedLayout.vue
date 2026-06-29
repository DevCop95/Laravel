<script setup>
import { computed, ref, watch } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import Toast from '@/Components/Toast.vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { useToast } from '@/composables/useToast';

const showingNavigationDropdown = ref(false);
const page = usePage();
const toast = useToast();

const palette = computed(() => page.props.clinic.palette.colors);

const demo = computed(() => page.props.demo ?? { enabled: false, readonly: false });

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
}));

watch(
    () => page.props.flash?.status,
    (msg) => {
        if (msg) toast.success(msg);
    },
    { immediate: true },
);

watch(
    () => page.props.flash?.error,
    (msg) => {
        if (msg) toast.error(msg);
    },
    { immediate: true },
);

router.on('finish', () => {
    const errors = Object.keys(page.props.errors || {});
    if (errors.length) {
        toast.error('Revisa los campos del formulario.');
    }
});
</script>

<template>
    <div>
        <div :style="themeStyle" class="min-h-screen bg-[var(--clinic-shell-bg)] text-[var(--clinic-text)]">
            <!-- Banner de modo demostracion -->
            <div
                v-if="demo.enabled"
                class="flex items-center justify-center gap-2 bg-[var(--clinic-primary-dark)] px-4 py-1.5 text-center text-xs font-semibold uppercase tracking-[0.18em] text-[var(--clinic-highlight)]"
            >
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10" />
                    <line x1="12" x2="12" y1="8" y2="12" />
                    <line x1="12" x2="12.01" y1="16" y2="16" />
                </svg>
                <span>Entorno de demostracion</span>
                <span v-if="demo.readonly" class="rounded-full bg-[color-mix(in_srgb,var(--clinic-highlight)_22%,transparent)] px-2 py-0.5 normal-case tracking-normal">
                    solo lectura
                </span>
            </div>
            <div class="absolute inset-x-0 top-0 -z-10 h-[24rem] bg-[linear-gradient(180deg,color-mix(in_srgb,var(--clinic-primary)_10%,transparent)_0%,transparent_100%)]" />
            <nav class="sticky top-0 z-30 border-b border-[var(--clinic-border)] bg-[color-mix(in_srgb,var(--clinic-shell-surface)_90%,transparent)] backdrop-blur">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16 justify-between">
                        <div class="flex">
                            <div class="flex shrink-0 items-center">
                                <Link :href="route('dashboard')" class="flex items-center gap-3">
                                    <template v-if="$page.props.clinic.logo_path">
                                        <img :src="'/storage/' + $page.props.clinic.logo_path" class="block h-10 w-auto" />
                                    </template>
                                    <template v-else>
                                        <ApplicationLogo class="block h-10 w-10" />
                                    </template>
                                    <div class="hidden md:block">
                                        <p class="text-[10px] font-semibold uppercase tracking-[0.24em] text-[var(--clinic-muted)]">Veterinaria</p>
                                        <span class="font-black text-xl text-[var(--clinic-text)]">{{ $page.props.clinic.name }}</span>
                                    </div>
                                </Link>
                            </div>

                            <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                                <NavLink
                                    :href="route('dashboard')"
                                    :active="route().current('dashboard')"
                                >
                                    Dashboard
                                </NavLink>
                                <NavLink
                                    :href="route('appointments.index')"
                                    :active="route().current('appointments.*')"
                                >
                                    Citas
                                </NavLink>
                                <NavLink
                                    :href="route('pets.index')"
                                    :active="route().current('pets.*')"
                                >
                                    Pacientes
                                </NavLink>
                                <NavLink
                                    v-if="$page.props.auth.user?.role !== 'veterinarian'"
                                    :href="route('veterinarians.index')"
                                    :active="route().current('veterinarians.*')"
                                >
                                    Equipo clinico
                                </NavLink>
                                <NavLink
                                    v-if="$page.props.auth.user?.role !== 'veterinarian'"
                                    :href="route('services.index')"
                                    :active="route().current('services.*')"
                                >
                                    Servicios
                                </NavLink>
                                <NavLink
                                    :href="route('owners.index')"
                                    :active="route().current('owners.*')"
                                >
                                    Responsables
                                </NavLink>
                                <NavLink
                                    v-if="$page.props.auth.user?.role !== 'veterinarian'"
                                    :href="route('audit.index')"
                                    :active="route().current('audit.*')"
                                >
                                    Auditoria
                                </NavLink>
                                <NavLink
                                    v-if="$page.props.auth.user?.role !== 'veterinarian'"
                                    :href="route('inventory.index')"
                                    :active="route().current('inventory.*')"
                                >
                                    Inventario
                                </NavLink>
                            </div>
                        </div>

                        <div class="hidden sm:ms-6 sm:flex sm:items-center">
                            <div class="relative ms-3">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                class="inline-flex items-center rounded-full border border-[var(--clinic-border)] bg-white px-4 py-2 text-sm font-medium leading-4 text-[var(--clinic-muted)] transition duration-150 ease-in-out hover:border-[var(--clinic-accent)] hover:text-[var(--clinic-text)] focus:outline-none"
                                            >
                                                {{ $page.props.auth.user.name }}

                                                <svg
                                                    class="-me-0.5 ms-2 h-4 w-4"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <DropdownLink :href="route('profile.edit')">
                                            Perfil
                                        </DropdownLink>
                                        <DropdownLink v-if="$page.props.auth.user?.role !== 'veterinarian'" :href="route('settings.edit')">
                                            Configuracion Clinica
                                        </DropdownLink>
                                        <DropdownLink
                                            :href="route('logout')"
                                            method="post"
                                            as="button"
                                        >
                                            Cerrar sesion
                                        </DropdownLink>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <div class="-me-2 flex items-center sm:hidden">
                            <button
                                @click="
                                    showingNavigationDropdown =
                                        !showingNavigationDropdown
                                "
                                class="inline-flex items-center justify-center rounded-xl p-2 text-[var(--clinic-muted)] transition duration-150 ease-in-out hover:bg-white hover:text-[var(--clinic-text)] focus:bg-white focus:text-[var(--clinic-text)] focus:outline-none"
                            >
                                <svg
                                    class="h-6 w-6"
                                    stroke="currentColor"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        :class="{
                                            hidden: showingNavigationDropdown,
                                            'inline-flex':
                                                !showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{
                                            hidden: !showingNavigationDropdown,
                                            'inline-flex':
                                                showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div
                    :class="{
                        block: showingNavigationDropdown,
                        hidden: !showingNavigationDropdown,
                    }"
                    class="sm:hidden"
                >
                    <div class="space-y-1 pb-3 pt-2">
                        <ResponsiveNavLink
                            :href="route('dashboard')"
                            :active="route().current('dashboard')"
                        >
                            Dashboard
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            :href="route('appointments.index')"
                            :active="route().current('appointments.*')"
                        >
                            Citas
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            :href="route('pets.index')"
                            :active="route().current('pets.*')"
                        >
                            Pacientes
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            v-if="$page.props.auth.user?.role !== 'veterinarian'"
                            :href="route('veterinarians.index')"
                            :active="route().current('veterinarians.*')"
                        >
                            Equipo clinico
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            v-if="$page.props.auth.user?.role !== 'veterinarian'"
                            :href="route('services.index')"
                            :active="route().current('services.*')"
                        >
                            Servicios
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            :href="route('owners.index')"
                            :active="route().current('owners.*')"
                        >
                            Responsables
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            v-if="$page.props.auth.user?.role !== 'veterinarian'"
                            :href="route('audit.index')"
                            :active="route().current('audit.*')"
                        >
                            Auditoria
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            v-if="$page.props.auth.user?.role !== 'veterinarian'"
                            :href="route('inventory.index')"
                            :active="route().current('inventory.*')"
                        >
                            Inventario
                        </ResponsiveNavLink>
                    </div>

                    <div class="border-t border-[var(--clinic-border)] pb-1 pt-4">
                        <div class="px-4">
                            <div class="text-base font-medium text-[var(--clinic-text)]">
                                {{ $page.props.auth.user.name }}
                            </div>
                            <div class="text-sm font-medium text-[var(--clinic-muted)]">
                                {{ $page.props.auth.user.email }}
                            </div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink :href="route('profile.edit')">
                                Perfil
                            </ResponsiveNavLink>
                            <ResponsiveNavLink v-if="$page.props.auth.user?.role !== 'veterinarian'" :href="route('settings.edit')">
                                Configuracion Clinica
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                :href="route('logout')"
                                method="post"
                                as="button"
                            >
                                Cerrar sesion
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            <header
                class="border-b border-[var(--clinic-border)] bg-[color-mix(in_srgb,var(--clinic-shell-surface)_80%,transparent)] backdrop-blur"
                v-if="$slots.header"
            >
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <main class="overflow-x-auto">
                <slot />
            </main>
        </div>
        <Toast />
    </div>
</template>
