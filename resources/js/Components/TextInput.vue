<script setup>
import { onMounted, ref } from 'vue';

const model = defineModel({
    type: String,
    required: true,
});

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        class="rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-panel-bg)] px-4 py-3 text-sm text-[var(--clinic-text)] shadow-sm transition duration-200 placeholder:text-[var(--clinic-muted)] focus:border-[var(--clinic-accent)] focus:ring-[var(--clinic-accent)]"
        v-model="model"
        ref="input"
    />
</template>
