<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const showPassword = ref(false);
const focusedField = ref(null);
const touched = ref({
    email: false,
    password: false,
});

const markTouched = (field) => {
    touched.value[field] = true;
};

const emailError = computed(() => {
    if (!touched.value.email) return '';
    if (!form.email.trim()) return 'Ingresa tu correo.';
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailPattern.test(form.email)) return 'Escribe un correo valido.';
    return '';
});

const passwordError = computed(() => {
    if (!touched.value.password) return '';
    if (!form.password.trim()) return 'Ingresa tu contrasena.';
    return '';
});

const authError = computed(() => {
    if (emailError.value || passwordError.value) return '';
    return form.errors.email || form.errors.password || '';
});

const greeting = computed(() => {
    const hour = new Date().getHours();
    if (hour < 12) return 'Buenos dias';
    if (hour < 18) return 'Buenas tardes';
    return 'Buenas noches';
});

const submit = () => {
    touched.value.email = true;
    touched.value.password = true;
    if (emailError.value || passwordError.value) return;
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};

const fillDemo = () => {
    form.email = 'admin@vet.com';
    form.password = 'password';
    touched.value.email = true;
    touched.value.password = true;
};
</script>

<template>
    <GuestLayout>
        <Head title="Ingresar" />

        <div class="space-y-8">
            <!-- Header -->
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-[var(--clinic-muted)]">{{ greeting }}</p>
                <h2 class="mt-3 text-5xl font-black leading-none tracking-tight text-[var(--clinic-text)]">Ingresar</h2>
                <p class="mt-3 max-w-sm text-base leading-7 text-[color-mix(in_srgb,var(--clinic-text)_65%,white)]">
                    Accede al panel de administracion de la clinica.
                </p>
            </div>

            <!-- Status message -->
            <Transition
                enter-active-class="transition duration-300 ease-out"
                enter-from-class="opacity-0 -translate-y-2 scale-[0.98]"
                enter-to-class="opacity-100 translate-y-0 scale-100"
                leave-active-class="transition duration-200 ease-in"
                leave-from-class="opacity-100 translate-y-0 scale-100"
                leave-to-class="opacity-0 -translate-y-2 scale-[0.98]"
            >
                <div
                    v-if="status"
                    class="rounded-[1.3rem] border px-4 py-3 text-sm font-medium"
                    style="border-color: color-mix(in srgb, var(--clinic-primary) 20%, white); background-color: color-mix(in srgb, var(--clinic-highlight) 84%, white); color: var(--clinic-primary-dark);"
                >
                    {{ status }}
                </div>
            </Transition>

            <!-- Form -->
            <form class="space-y-5" @submit.prevent="submit">
                <!-- Email -->
                <div class="space-y-2">
                    <InputLabel for="email" value="Correo electronico" />
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                            <svg class="h-5 w-5 text-[color-mix(in_srgb,var(--clinic-muted)_70%,white)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <rect width="20" height="16" x="2" y="4" rx="2" />
                                <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7" />
                            </svg>
                        </div>
                        <input
                            id="email"
                            type="email"
                            class="block w-full rounded-2xl border bg-white py-4 pl-12 pr-4 text-base transition-all duration-200 placeholder:text-[color-mix(in_srgb,var(--clinic-muted)_70%,white)] focus:outline-none focus:ring-2"
                            :class="[
                                emailError ? 'border-[#e8a99a] focus:ring-[#e8a99a]/30' : 'border-[color-mix(in_srgb,var(--clinic-border)_92%,white)] focus:border-[var(--clinic-primary)] focus:ring-[color-mix(in_srgb,var(--clinic-primary)_20%,white)]',
                                focusedField === 'email' && !emailError ? 'border-[var(--clinic-primary)] shadow-[0_0_0_3px_color-mix(in_srgb,var(--clinic-primary)_10%,white)]' : ''
                            ]"
                            style="color: var(--clinic-text);"
                            v-model="form.email"
                            required
                            autofocus
                            autocomplete="username"
                            placeholder="tu@correo.com"
                            @blur="markTouched('email'); focusedField = null"
                            @focus="focusedField = 'email'"
                        />
                    </div>
                    <Transition
                        enter-active-class="transition duration-200 ease-out"
                        enter-from-class="opacity-0 -translate-y-1"
                        enter-to-class="opacity-100 translate-y-0"
                        leave-active-class="transition duration-150 ease-in"
                        leave-from-class="opacity-100 translate-y-0"
                        leave-to-class="opacity-0 -translate-y-1"
                    >
                        <p v-if="emailError" class="flex items-center gap-1.5 text-sm font-medium text-[#a14d38]">
                            <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10" />
                                <line x1="12" x2="12" y1="8" y2="12" />
                                <line x1="12" x2="12.01" y1="16" y2="16" />
                            </svg>
                            {{ emailError }}
                        </p>
                    </Transition>
                </div>

                <!-- Password -->
                <div class="space-y-2">
                    <div class="flex items-center justify-between gap-4">
                        <InputLabel for="password" value="Contrasena" />
                        <Link
                            v-if="canResetPassword"
                            :href="route('password.request')"
                            class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--clinic-muted)] transition hover:text-[var(--clinic-primary-dark)]"
                        >
                            Recuperar acceso
                        </Link>
                    </div>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                            <svg class="h-5 w-5 text-[color-mix(in_srgb,var(--clinic-muted)_70%,white)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <rect width="18" height="11" x="3" y="11" rx="2" ry="2" />
                                <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                            </svg>
                        </div>
                        <input
                            id="password"
                            :type="showPassword ? 'text' : 'password'"
                            class="block w-full rounded-2xl border bg-white py-4 pl-12 pr-14 text-base transition-all duration-200 placeholder:text-[color-mix(in_srgb,var(--clinic-muted)_70%,white)] focus:outline-none focus:ring-2"
                            :class="[
                                passwordError ? 'border-[#e8a99a] focus:ring-[#e8a99a]/30' : 'border-[color-mix(in_srgb,var(--clinic-border)_92%,white)] focus:border-[var(--clinic-primary)] focus:ring-[color-mix(in_srgb,var(--clinic-primary)_20%,white)]',
                                focusedField === 'password' && !passwordError ? 'border-[var(--clinic-primary)] shadow-[0_0_0_3px_color-mix(in_srgb,var(--clinic-primary)_10%,white)]' : ''
                            ]"
                            style="color: var(--clinic-text);"
                            v-model="form.password"
                            required
                            autocomplete="current-password"
                            placeholder="Escribe tu contrasena"
                            @blur="markTouched('password'); focusedField = null"
                            @focus="focusedField = 'password'"
                        />
                        <button
                            type="button"
                            class="absolute inset-y-0 right-0 inline-flex w-14 items-center justify-center text-[var(--clinic-muted)] transition hover:text-[var(--clinic-primary-dark)]"
                            :aria-label="showPassword ? 'Ocultar contrasena' : 'Mostrar contrasena'"
                            @click="showPassword = !showPassword"
                        >
                            <svg v-if="showPassword" viewBox="0 0 24 24" class="h-5 w-5 fill-none stroke-current" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 3l18 18" />
                                <path d="M10.58 10.58A2 2 0 0012 14a2 2 0 001.42-.58" />
                                <path d="M9.88 5.09A9.77 9.77 0 0112 4.8c4.93 0 8.29 3.2 9.5 7.2a11.42 11.42 0 01-4.04 5.56" />
                                <path d="M6.61 6.61A11.36 11.36 0 002.5 12c1.21 4 4.57 7.2 9.5 7.2 1.72 0 3.28-.39 4.66-1.05" />
                            </svg>
                            <svg v-else viewBox="0 0 24 24" class="h-5 w-5 fill-none stroke-current" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M2.5 12c1.21-4 4.57-7.2 9.5-7.2s8.29 3.2 9.5 7.2c-1.21 4-4.57 7.2-9.5 7.2S3.71 16 2.5 12Z" />
                                <circle cx="12" cy="12" r="2.6" />
                            </svg>
                        </button>
                    </div>
                    <Transition
                        enter-active-class="transition duration-200 ease-out"
                        enter-from-class="opacity-0 -translate-y-1"
                        enter-to-class="opacity-100 translate-y-0"
                        leave-active-class="transition duration-150 ease-in"
                        leave-from-class="opacity-100 translate-y-0"
                        leave-to-class="opacity-0 -translate-y-1"
                    >
                        <p v-if="passwordError" class="flex items-center gap-1.5 text-sm font-medium text-[#a14d38]">
                            <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10" />
                                <line x1="12" x2="12" y1="8" y2="12" />
                                <line x1="12" x2="12.01" y1="16" y2="16" />
                            </svg>
                            {{ passwordError }}
                        </p>
                    </Transition>
                </div>

                <!-- Auth error -->
                <Transition
                    enter-active-class="transition duration-300 ease-out"
                    enter-from-class="opacity-0 -translate-y-2 scale-[0.98]"
                    enter-to-class="opacity-100 translate-y-0 scale-100"
                    leave-active-class="transition duration-200 ease-in"
                    leave-from-class="opacity-100 translate-y-0 scale-100"
                    leave-to-class="opacity-0 -translate-y-2 scale-[0.98]"
                >
                    <div v-if="authError" class="flex items-start gap-3 rounded-[1.3rem] border border-[#efcfc4] bg-[#fff5f1] px-4 py-3.5 text-sm font-medium text-[#a14d38]">
                        <svg class="mt-0.5 h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                            <line x1="12" x2="12" y1="9" y2="13" />
                            <line x1="12" x2="12.01" y1="17" y2="17" />
                        </svg>
                        <span>{{ authError }}</span>
                    </div>
                </Transition>

                <!-- Remember me -->
                <label
                    class="flex cursor-pointer items-center gap-3 rounded-[1.3rem] border border-[color-mix(in_srgb,var(--clinic-border)_90%,white)] bg-[color-mix(in_srgb,var(--clinic-shell-surface)_94%,white)] px-4 py-3.5 transition-all duration-200 hover:border-[color-mix(in_srgb,var(--clinic-primary)_30%,white)]"
                >
                    <div class="relative flex items-center">
                        <input
                            v-model="form.remember"
                            type="checkbox"
                            class="peer h-4.5 w-4.5 rounded border-[1.5px] text-[var(--clinic-primary-dark)] transition-colors focus:ring-2 focus:ring-[color-mix(in_srgb,var(--clinic-primary)_20%,white)] focus:outline-none"
                            style="border-color: color-mix(in srgb, var(--clinic-primary) 35%, white);"
                        />
                        <svg class="pointer-events-none absolute left-0 top-0 h-4.5 w-4.5 -rotate-90 scale-0 text-white transition-transform peer-checked:rotate-0 peer-checked:scale-100" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12" />
                        </svg>
                    </div>
                    <span class="text-sm text-[color-mix(in_srgb,var(--clinic-text)_72%,white)]">Mantener sesion activa</span>
                </label>

                <!-- Submit -->
                <PrimaryButton
                    class="w-full rounded-2xl px-6 py-4 text-sm font-semibold tracking-[0.24em] shadow-[0_24px_50px_-25px_color-mix(in_srgb,var(--clinic-primary-dark)_60%,transparent)] transition-all duration-200 hover:shadow-[0_24px_50px_-20px_color-mix(in_srgb,var(--clinic-primary-dark)_70%,transparent)] hover:brightness-110 focus:brightness-110 focus:outline-none focus:ring-2 focus:ring-[color-mix(in_srgb,var(--clinic-primary)_40%,white)] focus:ring-offset-2 active:brightness-90 active:scale-[0.99]"
                    style="background: var(--clinic-primary-dark); color: var(--clinic-highlight);"
                    :class="{ 'opacity-50': form.processing }"
                    :disabled="form.processing"
                >
                    <span v-if="form.processing" class="inline-flex items-center gap-3">
                        <svg class="h-4 w-4 animate-spin fill-none stroke-current" viewBox="0 0 24 24" stroke-width="2">
                            <circle cx="12" cy="12" r="9" class="opacity-25" />
                            <path d="M21 12a9 9 0 0 0-9-9" stroke-linecap="round" class="opacity-100" />
                        </svg>
                        Validando credenciales
                    </span>
                    <span v-else class="inline-flex items-center gap-3">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4" />
                            <polyline points="10 17 15 12 10 7" />
                            <line x1="15" x2="3" y1="12" y2="12" />
                        </svg>
                        Ingresar al panel
                    </span>
                </PrimaryButton>

                <!-- Demo login -->
                <button
                    type="button"
                    class="w-full rounded-2xl border border-dashed border-[color-mix(in_srgb,var(--clinic-border)_80%,white)] bg-transparent px-6 py-3 text-xs font-semibold uppercase tracking-[0.22em] text-[var(--clinic-muted)] transition-all duration-200 hover:border-[color-mix(in_srgb,var(--clinic-primary)_40%,white)] hover:bg-[color-mix(in_srgb,var(--clinic-primary)_5%,white)] hover:text-[var(--clinic-primary-dark)]"
                    @click="fillDemo"
                >
                    Usar credenciales de demo
                </button>
            </form>

            <!-- Footer -->
            <div class="pt-4">
                <div class="flex items-center gap-3 text-xs text-[color-mix(in_srgb,var(--clinic-muted)_60%,white)]">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                    </svg>
                    <span>Sesion protegida con encriptacion</span>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>
