<script setup>
defineProps({ label: String, value: [String, Number], muted: String, accent: Boolean });
</script>

<template>
    <div class="rounded-[2rem] border border-[var(--clinic-border)] bg-[var(--clinic-panel-bg)]/95 p-5 shadow-[0_28px_90px_-55px_color-mix(in_srgb,var(--clinic-primary)_45%,transparent)]">
        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--clinic-muted)]">{{ label }}</p>
        <p class="mt-2 text-3xl font-black" :class="accent ? 'text-red-500' : 'text-[var(--clinic-text)]'">{{ value }}</p>
        <p v-if="muted" class="mt-1 text-sm text-[var(--clinic-muted)]">{{ muted }}</p>
    </div>
</template>
