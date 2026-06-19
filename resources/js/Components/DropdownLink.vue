<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    href: {
        type: String,
        required: true,
    },
});
</script>

<template>
    <Link
        :href="href"
        class="block w-full rounded-xl px-4 py-2 text-start text-sm leading-5 text-[var(--clinic-muted)] transition duration-150 ease-in-out hover:bg-[var(--clinic-highlight)] hover:text-[var(--clinic-text)] focus:bg-[var(--clinic-highlight)] focus:outline-none"
    >
        <slot />
    </Link>
</template>
