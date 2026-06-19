<script setup>
const model = defineModel({ type: String });
defineProps({ placeholder: String, maxWidth: { type: String, default: 'max-w-md' } });
</script>

<template>
    <input :value="model" type="text" :placeholder="placeholder" :class="['block w-full rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-shell-surface)] px-4 py-3 text-sm text-[var(--clinic-text)] shadow-sm', maxWidth]" @input="model = $event.target.value" />
</template>
