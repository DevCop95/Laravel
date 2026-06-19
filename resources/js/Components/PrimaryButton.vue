<template>
    <button
        class="inline-flex items-center justify-center rounded-full border border-transparent bg-[var(--clinic-primary)] px-5 py-3 text-xs font-semibold uppercase tracking-[0.28em] text-[var(--clinic-highlight)] shadow-[0_18px_45px_-24px_color-mix(in_srgb,var(--clinic-primary)_65%,transparent)] transition duration-200 ease-out hover:bg-[var(--clinic-primary-dark)] focus:bg-[var(--clinic-primary-dark)] focus:outline-none focus:ring-2 focus:ring-[var(--clinic-accent)] focus:ring-offset-2 active:bg-[var(--clinic-text)] disabled:cursor-not-allowed disabled:opacity-60"
    >
        <slot />
    </button>
</template>
