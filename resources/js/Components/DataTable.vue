<script setup>
import { computed, ref } from 'vue';
import { useFormatting } from '@/composables/useFormatting';

const props = defineProps({
    columns: { type: Array, required: true },
    rows: { type: Array, required: true },
    perPage: { type: Number, default: 10 },
    emptyMessage: { type: String, default: 'No hay registros para mostrar.' },
    searchable: { type: Boolean, default: true },
    searchPlaceholder: { type: String, default: 'Buscar...' },
    searchFields: { type: Array, default: () => [] },
});

const emit = defineEmits(['row-click']);

const { formatDate, formatCurrency } = useFormatting();

const search = ref('');
const sortKey = ref('');
const sortDir = ref('asc');
const currentPage = ref(1);

const filteredRows = computed(() => {
    let rows = [...props.rows];

    if (search.value && props.searchFields.length) {
        const term = search.value.trim().toLowerCase();
        rows = rows.filter(row =>
            props.searchFields.some(field => {
                const value = field.split('.').reduce((obj, key) => obj?.[key], row);
                return value != null && String(value).toLowerCase().includes(term);
            }),
        );
    }

    if (sortKey.value) {
        rows.sort((a, b) => {
            const aVal = sortKey.value.split('.').reduce((obj, key) => obj?.[key], a);
            const bVal = sortKey.value.split('.').reduce((obj, key) => obj?.[key], b);
            if (aVal == null) return 1;
            if (bVal == null) return -1;
            const cmp = String(aVal).localeCompare(String(bVal), 'es', { numeric: true });
            return sortDir.value === 'asc' ? cmp : -cmp;
        });
    }

    return rows;
});

const totalPages = computed(() => Math.max(1, Math.ceil(filteredRows.value.length / props.perPage)));

const paginatedRows = computed(() => {
    const start = (currentPage.value - 1) * props.perPage;
    return filteredRows.value.slice(start, start + props.perPage);
});

const visiblePages = computed(() => {
    const pages = [];
    const total = totalPages.value;
    const current = currentPage.value;
    let start = Math.max(1, current - 2);
    let end = Math.min(total, start + 4);
    start = Math.max(1, end - 4);
    for (let i = start; i <= end; i++) pages.push(i);
    return pages;
});

const toggleSort = (key) => {
    if (sortKey.value === key) {
        sortDir.value = sortDir.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortKey.value = key;
        sortDir.value = 'asc';
    }
};

const goToPage = (page) => {
    currentPage.value = Math.max(1, Math.min(page, totalPages.value));
};

const resolveValue = (row, key) => {
    return key.split('.').reduce((obj, k) => obj?.[k], row);
};

const formatCell = (row, column) => {
    const value = resolveValue(row, column.key);
    if (column.format === 'currency') return formatCurrency(value);
    if (column.format === 'date') return formatDate(value);
    if (column.format === 'datetime') return formatDate(value);
    return value;
};
</script>

<template>
    <div class="space-y-4">
        <!-- Search -->
        <div v-if="searchable && searchFields.length" class="flex items-center gap-3">
            <div class="relative flex-1">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                    <svg class="h-4.5 w-4.5 text-[color-mix(in_srgb,var(--clinic-muted)_60%,white)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8" />
                        <line x1="21" x2="16.65" y1="21" y2="16.65" />
                    </svg>
                </div>
                <input
                    v-model="search"
                    type="text"
                    :placeholder="searchPlaceholder"
                    class="block w-full rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-panel-bg)] py-3 pl-11 pr-4 text-sm text-[var(--clinic-text)] shadow-sm transition-all duration-200 placeholder:text-[color-mix(in_srgb,var(--clinic-muted)_60%,white)] focus:border-[var(--clinic-primary)] focus:outline-none focus:ring-2 focus:ring-[color-mix(in_srgb,var(--clinic-primary)_15%,white)]"
                />
            </div>
            <span class="shrink-0 text-xs font-medium text-[var(--clinic-muted)]">
                {{ filteredRows.length }} resultado{{ filteredRows.length !== 1 ? 's' : '' }}
            </span>
        </div>

        <!-- Desktop Table -->
        <div class="hidden overflow-hidden rounded-[1.8rem] border border-[var(--clinic-border)] bg-[var(--clinic-panel-bg)] shadow-[0_16px_50px_-20px_color-mix(in_srgb,var(--clinic-primary)_20%,transparent)] lg:block">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-[var(--clinic-border)]">
                            <th
                                v-for="col in columns"
                                :key="col.key"
                                class="px-5 py-4 text-left text-[11px] font-semibold uppercase tracking-[0.16em] text-[var(--clinic-muted)] transition-colors hover:bg-[color-mix(in_srgb,var(--clinic-primary)_5%,white)]"
                                :class="{ 'cursor-pointer select-none': col.sortable !== false }"
                                @click="col.sortable !== false && toggleSort(col.key)"
                            >
                                <div class="flex items-center gap-2">
                                    <span>{{ col.label }}</span>
                                    <span v-if="sortKey === col.key" class="inline-flex">
                                        <svg v-if="sortDir === 'asc'" class="h-3.5 w-3.5 text-[var(--clinic-primary)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M18 15l-6-6-6 6" />
                                        </svg>
                                        <svg v-else class="h-3.5 w-3.5 text-[var(--clinic-primary)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M6 9l6 6 6-6" />
                                        </svg>
                                    </span>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="(row, idx) in paginatedRows"
                            :key="row.id ?? idx"
                            class="border-b border-[var(--clinic-border)]/50 transition-colors duration-150 hover:bg-[color-mix(in_srgb,var(--clinic-primary)_3%,white)]"
                            :class="{ 'bg-[color-mix(in_srgb,var(--clinic-primary)_2%,white)]': idx % 2 === 1 }"
                        >
                            <td
                                v-for="col in columns"
                                :key="col.key"
                                class="px-5 py-4 align-middle"
                                :class="col.cellClass"
                            >
                                <slot :name="'cell-' + col.key" :row="row" :value="resolveValue(row, col.key)">
                                    <span :class="col.textClass ?? 'text-[var(--clinic-text)]'" v-html="formatCell(row, col)" />
                                </slot>
                            </td>
                        </tr>
                        <tr v-if="paginatedRows.length === 0">
                            <td :colspan="columns.length" class="px-5 py-16 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-[color-mix(in_srgb,var(--clinic-primary)_8%,white)]">
                                        <svg class="h-6 w-6 text-[color-mix(in_srgb,var(--clinic-primary)_40%,white)]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                    </div>
                                    <p class="text-sm text-[var(--clinic-muted)]">{{ emptyMessage }}</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Mobile Cards -->
        <div class="space-y-3 lg:hidden">
            <div
                v-for="(row, idx) in paginatedRows"
                :key="row.id ?? idx"
                class="rounded-[1.6rem] border border-[var(--clinic-border)] bg-[var(--clinic-panel-bg)] p-5 shadow-[0_8px_30px_-12px_color-mix(in_srgb,var(--clinic-primary)_15%,transparent)] transition-all duration-200 hover:shadow-[0_12px_40px_-10px_color-mix(in_srgb,var(--clinic-primary)_25%,transparent)]"
            >
                <div class="space-y-3">
                    <div v-for="col in columns.filter(c => c.key !== 'actions')" :key="col.key" class="flex items-start justify-between gap-3">
                        <span class="shrink-0 text-[11px] font-semibold uppercase tracking-[0.14em] text-[var(--clinic-muted)]">{{ col.label }}</span>
                        <slot :name="'cell-' + col.key" :row="row" :value="resolveValue(row, col.key)" class="text-right">
                            <span :class="col.textClass ?? 'text-right text-sm font-medium text-[var(--clinic-text)]'" v-html="formatCell(row, col)" />
                        </slot>
                    </div>
                    <slot name="row-actions" :row="row" />
                </div>
            </div>
            <div v-if="paginatedRows.length === 0" class="rounded-[1.6rem] border border-dashed border-[var(--clinic-border)] bg-[var(--clinic-panel-bg)] p-10 text-center">
                <p class="text-sm text-[var(--clinic-muted)]">{{ emptyMessage }}</p>
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="totalPages > 1" class="flex items-center justify-between gap-4 rounded-[1.4rem] border border-[var(--clinic-border)] bg-[var(--clinic-panel-bg)] px-5 py-3">
            <p class="text-xs text-[var(--clinic-muted)]">
                Pagina {{ currentPage }} de {{ totalPages }}
            </p>
            <div class="flex items-center gap-1.5">
                <button
                    type="button"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-[var(--clinic-border)] text-[var(--clinic-muted)] transition-all duration-150 hover:border-[var(--clinic-primary)] hover:text-[var(--clinic-primary)] disabled:cursor-not-allowed disabled:opacity-30"
                    :disabled="currentPage === 1"
                    @click="goToPage(currentPage - 1)"
                >
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M15 18l-6-6 6-6" />
                    </svg>
                </button>
                <button
                    v-for="page in visiblePages"
                    :key="page"
                    type="button"
                    class="inline-flex h-9 min-w-9 items-center justify-center rounded-xl px-2 text-xs font-semibold transition-all duration-150"
                    :class="page === currentPage
                        ? 'bg-[var(--clinic-primary)] text-[var(--clinic-highlight)] shadow-[0_4px_12px_-3px_color-mix(in_srgb,var(--clinic-primary)_50%,transparent)]'
                        : 'border border-[var(--clinic-border)] text-[var(--clinic-muted)] hover:border-[var(--clinic-primary)] hover:text-[var(--clinic-primary)]'"
                    @click="goToPage(page)"
                >
                    {{ page }}
                </button>
                <button
                    type="button"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-[var(--clinic-border)] text-[var(--clinic-muted)] transition-all duration-150 hover:border-[var(--clinic-primary)] hover:text-[var(--clinic-primary)] disabled:cursor-not-allowed disabled:opacity-30"
                    :disabled="currentPage === totalPages"
                    @click="goToPage(currentPage + 1)"
                >
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 18l6-6-6-6" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
</template>
