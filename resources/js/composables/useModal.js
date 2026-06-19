import { computed, ref } from 'vue';

export function useModal() {
    const selectedId = ref(null);
    const isOpen = computed(() => selectedId.value !== null);
    const open = (id) => { selectedId.value = id; };
    const close = () => { selectedId.value = null; };

    return { selectedId, isOpen, open, close };
}
