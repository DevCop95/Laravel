<script setup>
defineProps({ message: { type: String, default: 'No hay registros que coincidan con ese filtro.' } });
</script>

<template>
    <p class="mt-5 rounded-2xl border border-dashed border-[var(--clinic-border)] bg-[var(--clinic-highlight)] px-4 py-5 text-sm text-[var(--clinic-muted)]">{{ message }}</p>
</template>
