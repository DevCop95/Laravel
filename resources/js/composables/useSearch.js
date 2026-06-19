import { computed, ref } from 'vue';

/** @param {import('vue').Ref<Array>} items @param {string[]} searchFields */
export function useSearch(items, searchFields) {
    const search = ref('');

    const filteredItems = computed(() => {
        const term = search.value.trim().toLowerCase();
        if (!term) return items.value;

        return items.value.filter((item) =>
            searchFields.some((field) => {
                const value = field.split('.').reduce((obj, key) => obj?.[key], item);
                return value != null && String(value).toLowerCase().includes(term);
            }),
        );
    });

    return { search, filteredItems };
}
