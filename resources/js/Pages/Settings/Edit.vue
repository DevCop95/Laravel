<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    settings: Object,
    palettes: Array,
});

const form = useForm({
    name: props.settings.name,
    palette_key: props.settings.palette_key,
    logo: null,
});

const sanitizeRecordText = (value) => value.replace(/[^\p{L}\p{N} ]/gu, '').replace(/\s+/g, ' ').trimStart();

const selectedPalette = computed(() =>
    props.palettes.find((palette) => palette.key === form.palette_key) ?? props.palettes[0],
);

const submit = () => {
    form.post(route('settings.update'), {
        forceFormData: true,
    });
};
</script>

<template>
    <Head title="Configuracion de marca" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-[#6b856e]">Identidad visual</p>
                <h2 class="mt-2 text-3xl font-black leading-tight text-[#173729]">
                    Configuracion de la veterinaria
                </h2>
            </div>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="rounded-[2rem] border border-[var(--clinic-border)] bg-white/90 p-6 shadow-[0_28px_80px_-56px_color-mix(in_srgb,var(--clinic-primary-dark)_35%,transparent)] backdrop-blur sm:p-8">
                    <form @submit.prevent="submit" class="mt-6 space-y-6">
                        <div>
                            <InputLabel for="name" value="Nombre de la veterinaria" />
                            <TextInput
                                id="name"
                                type="text"
                                class="mt-2 block w-full"
                                v-model="form.name"
                                @input="form.name = sanitizeRecordText(form.name)"
                                required
                                autofocus
                            />
                            <InputError class="mt-2" :message="form.errors.name" />
                        </div>

                        <div>
                            <InputLabel for="logo" value="Logo de la clinica" />
                            <div v-if="settings.logo_path" class="mb-4">
                                <img :src="'/storage/' + settings.logo_path" alt="Logo actual" class="h-20 w-auto rounded-2xl border border-[#dde3d6] bg-[#f8faf5] p-2" />
                            </div>
                            <input
                                id="logo"
                                type="file"
                                class="mt-2 block w-full rounded-2xl border border-[#d7ddcf] bg-[#fdfcf8] px-4 py-3 text-sm text-[#496255] file:mr-4 file:rounded-full file:border-0 file:bg-[#24543f] file:px-4 file:py-2 file:text-xs file:font-semibold file:uppercase file:tracking-[0.18em] file:text-[#f6f2e8] hover:file:bg-[#1d4634]"
                                @input="form.logo = $event.target.files[0]"
                            />
                            <InputError class="mt-2" :message="form.errors.logo" />
                        </div>

                        <div>
                            <div class="flex items-end justify-between gap-4">
                                <div>
                                    <InputLabel for="palette_key" value="Paleta de colores" />
                                    <p class="mt-2 text-sm text-[#5d7365]">Selecciona una paleta lista para aplicar automaticamente en accesos, barra principal y botones.</p>
                                </div>
                                <div class="rounded-full border border-[#dde3d6] bg-[#f8faf5] px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-[#496255]">
                                    {{ selectedPalette.name }}
                                </div>
                            </div>

                            <div class="mt-5 grid gap-4 lg:grid-cols-2">
                                <button
                                    v-for="palette in props.palettes"
                                    :key="palette.key"
                                    type="button"
                                    class="rounded-[1.6rem] border p-4 text-left transition"
                                    :class="palette.key === form.palette_key ? 'border-[#24543f] bg-[#f8faf5] shadow-[0_20px_50px_-40px_rgba(23,55,41,0.9)]' : 'border-[#dde3d6] bg-white hover:border-[#b8c8b2]'"
                                    @click="form.palette_key = palette.key"
                                >
                                    <div class="flex items-start justify-between gap-4">
                                        <div>
                                            <p class="text-sm font-bold text-[#173729]">{{ palette.name }}</p>
                                            <p class="mt-2 text-sm leading-6 text-[#5d7365]">{{ palette.description }}</p>
                                        </div>
                                        <span v-if="palette.key === form.palette_key" class="rounded-full bg-[#24543f] px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.16em] text-[#f6f2e8]">Activa</span>
                                    </div>

                                    <div class="mt-4 flex gap-2">
                                        <span
                                            v-for="tone in [palette.colors.primary, palette.colors.primary_dark, palette.colors.accent, palette.colors.highlight]"
                                            :key="tone"
                                            class="h-10 flex-1 rounded-xl border border-white/30"
                                            :style="{ backgroundColor: tone }"
                                        />
                                    </div>
                                </button>
                            </div>

                            <InputError class="mt-2" :message="form.errors.palette_key" />
                        </div>

                        <div class="rounded-[1.8rem] border border-[#dde3d6] bg-white p-5">
                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[#6b856e]">Vista previa</p>
                            <div class="mt-4 overflow-hidden rounded-[1.8rem] border" :style="{ borderColor: selectedPalette.colors.border }">
                                <div
                                    class="px-5 py-6 text-white"
                                    :style="{ background: `linear-gradient(135deg, ${selectedPalette.colors.hero_from} 0%, ${selectedPalette.colors.hero_via} 55%, ${selectedPalette.colors.hero_to} 100%)` }"
                                >
                                    <p class="text-xs font-semibold uppercase tracking-[0.24em] opacity-80">Panel clinico</p>
                                    <h3 class="mt-3 text-2xl font-black">{{ form.name || 'Clinica Veterinaria' }}</h3>
                                </div>
                                <div class="grid gap-4 p-5 md:grid-cols-[1.1fr_0.9fr]" :style="{ backgroundColor: selectedPalette.colors.shell_surface }">
                                    <div class="rounded-[1.3rem] border p-4" :style="{ borderColor: selectedPalette.colors.border, backgroundColor: selectedPalette.colors.panel_bg, color: selectedPalette.colors.text }">
                                        <p class="text-sm font-bold">Citas y pacientes</p>
                                        <p class="mt-2 text-sm" :style="{ color: selectedPalette.colors.muted }">La paleta se aplica en login, navegacion principal y botones clave.</p>
                                    </div>
                                    <div class="flex items-center justify-center rounded-[1.3rem] px-4 py-5 text-sm font-semibold uppercase tracking-[0.18em]" :style="{ backgroundColor: selectedPalette.colors.primary, color: selectedPalette.colors.highlight }">
                                        Guardar cambios
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            <PrimaryButton :disabled="form.processing">Guardar Cambios</PrimaryButton>
                            <Transition
                                enter-active-class="transition ease-in-out"
                                enter-from-class="opacity-0"
                                leave-active-class="transition ease-in-out"
                                leave-to-class="opacity-0"
                            >
                                <p v-if="form.recentlySuccessful" class="text-sm text-[#5d7365]">Guardado.</p>
                            </Transition>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
