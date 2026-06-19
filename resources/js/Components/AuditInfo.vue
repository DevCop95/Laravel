<script setup>
defineProps({ audit: Object });
</script>

<template>
    <div v-if="audit" class="mt-4 rounded-[1.4rem] border border-[var(--clinic-border)] bg-[var(--clinic-shell-surface)] px-4 py-4 text-sm text-[var(--clinic-muted)]">
        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[var(--clinic-muted)]">Actividad</p>
        <p class="mt-2">{{ audit.action_label }} por {{ audit.user_name }}</p>
    </div>
</template>
