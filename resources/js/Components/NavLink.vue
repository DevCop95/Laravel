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
        ? 'inline-flex items-center border-b-2 border-[var(--clinic-primary)] px-2 pb-[1.15rem] pt-[1.3rem] text-sm font-semibold leading-5 text-[var(--clinic-text)] transition duration-150 ease-in-out'
        : 'inline-flex items-center border-b-2 border-transparent px-2 pb-[1.15rem] pt-[1.3rem] text-sm font-medium leading-5 text-[var(--clinic-muted)] hover:text-[var(--clinic-text)] focus:outline-none transition duration-150 ease-in-out',
);
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
