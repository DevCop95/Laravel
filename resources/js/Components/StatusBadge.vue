<script setup>
import { useFormatting } from '@/composables/useFormatting';

defineProps({ status: String, label: String });

const { statusBadgeClass } = useFormatting();
</script>

<template>
    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em]" :class="statusBadgeClass(status)">{{ label }}</span>
</template>
