<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    active: {
        type: Boolean,
    },
});

const classes = computed(() =>
    props.active
        ? 'block w-full rounded-2xl border-l-2 border-[var(--clinic-primary)] bg-white/80 px-4 py-3 text-start text-base font-semibold text-[var(--clinic-text)] transition duration-150 ease-in-out'
        : 'block w-full rounded-2xl border-l-2 border-transparent px-4 py-3 text-start text-base font-medium text-[var(--clinic-muted)] hover:bg-white/70 hover:text-[var(--clinic-text)] focus:outline-none transition duration-150 ease-in-out',
);
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
