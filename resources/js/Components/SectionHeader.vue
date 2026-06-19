<script setup>
defineProps({ eyebrow: String, title: String, description: String });
</script>

<template>
    <div>
        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--clinic-muted)]">{{ eyebrow }}</p>
        <h3 class="mt-2 text-2xl font-black text-[var(--clinic-text)]">{{ title }}</h3>
        <p v-if="description" class="mt-3 text-sm leading-6 text-[var(--clinic-muted)]">{{ description }}</p>
    </div>
</template>
