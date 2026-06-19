<script setup>
import { useToast } from '@/composables/useToast';

const { toasts, remove } = useToast();

const typeStyles = {
    success: {
        bg: 'bg-[#dce6d1]',
        border: 'border-[#b8cfa8]',
        text: 'text-[#24543f]',
        icon: 'M22 11.08V12a10 10 0 1 1-5.93-9.14 M22 4L12 14.01l-3-3',
    },
    error: {
        bg: 'bg-[#f3ded7]',
        border: 'border-[#e8b8a8]',
        text: 'text-[#8a4d3a]',
        icon: 'M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z M12 9v4 M12 17h.01',
    },
    info: {
        bg: 'bg-[#dce7ef]',
        border: 'border-[#b8c8d8]',
        text: 'text-[#36556e]',
        icon: 'M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z M12 16v-4 M12 8h.01',
    },
    warning: {
        bg: 'bg-[#efe3c8]',
        border: 'border-[#dcc8a0]',
        text: 'text-[#6e5b42]',
        icon: 'M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z M12 9v4 M12 17h.01',
    },
};
</script>

<template>
    <div class="pointer-events-none fixed inset-x-0 top-0 z-[100] flex flex-col items-center gap-3 px-4 pt-6">
        <Transition
            v-for="toast in toasts"
            :key="toast.id"
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0 -translate-y-4 scale-95"
            enter-to-class="opacity-100 translate-y-0 scale-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100 translate-y-0 scale-100"
            leave-to-class="opacity-0 -translate-y-4 scale-95"
        >
            <div
                v-if="toast.visible"
                class="pointer-events-auto flex w-full max-w-md items-center gap-3 rounded-2xl border px-5 py-4 shadow-[0_16px_40px_-10px_rgba(0,0,0,0.15)] backdrop-blur-sm"
                :class="[typeStyles[toast.type].bg, typeStyles[toast.type].border]"
            >
                <svg class="h-5 w-5 shrink-0" :class="typeStyles[toast.type].text" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path :d="typeStyles[toast.type].icon" />
                </svg>
                <p class="flex-1 text-sm font-medium" :class="typeStyles[toast.type].text">{{ toast.message }}</p>
                <button
                    type="button"
                    class="shrink-0 rounded-lg p-1 transition hover:bg-black/5"
                    :class="typeStyles[toast.type].text"
                    @click="remove(toast.id)"
                >
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" x2="6" y1="6" y2="18" />
                        <line x1="6" x2="18" y1="6" y2="18" />
                    </svg>
                </button>
            </div>
        </Transition>
    </div>
</template>
