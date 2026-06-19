<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DataTable from '@/Components/DataTable.vue';
import Modal from '@/Components/Modal.vue';
import StatsCard from '@/Components/StatsCard.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    items: Array,
    categories: Array,
    stats: Object,
});

const selectedItemId = ref(null);
const showDeleteConfirm = ref(false);
const deleteItemId = ref(null);

const sanitizeRecordText = (value) => value.replace(/[^\p{L}\p{N} ]/gu, '').replace(/\s+/g, ' ').trimStart();

const sanitizeField = (form, field) => {
    form[field] = sanitizeRecordText(form[field] ?? '');
};

const createItemForm = useForm({
    name: '',
    category: 'Medicamento',
    sku: '',
    quantity: 0,
    min_quantity: 0,
    unit_price: '',
    unit: 'unidad',
    supplier: '',
    notes: '',
});

const itemForms = Object.fromEntries(
    props.items.map((item) => [
        item.id,
        useForm({
            name: item.name,
            category: item.category,
            sku: item.sku ?? '',
            quantity: item.quantity,
            min_quantity: item.min_quantity,
            unit_price: item.unit_price,
            unit: item.unit,
            supplier: item.supplier ?? '',
            notes: item.notes ?? '',
        }),
    ]),
);

const columns = [
    { key: 'name', label: 'Articulo', sortable: true },
    { key: 'category', label: 'Categoria', sortable: true },
    { key: 'quantity', label: 'Stock', sortable: true },
    { key: 'unit_price', label: 'Precio', format: 'currency', sortable: true },
    { key: 'total_value', label: 'Valor', format: 'currency', sortable: true },
    { key: 'supplier', label: 'Proveedor', sortable: true },
    { key: 'actions', label: 'Acciones', sortable: false },
];

const searchFields = ['name', 'category', 'sku', 'supplier', 'notes'];

const formatCurrency = (value) =>
    new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', maximumFractionDigits: 0 }).format(Number(value || 0));

const selectedItem = () => props.items.find((item) => item.id === selectedItemId.value) ?? null;

const createItem = () => {
    createItemForm.post(route('inventory.store'), {
        preserveScroll: true,
        onSuccess: () => createItemForm.reset(),
    });
};

const openItemModal = (itemId) => {
    selectedItemId.value = itemId;
};

const closeItemModal = () => {
    selectedItemId.value = null;
};

const saveItem = (itemId) => {
    itemForms[itemId].patch(route('inventory.update', itemId), {
        preserveScroll: true,
        onSuccess: closeItemModal,
    });
};

const confirmDelete = (itemId) => {
    deleteItemId.value = itemId;
    showDeleteConfirm.value = true;
};

const cancelDelete = () => {
    deleteItemId.value = null;
    showDeleteConfirm.value = false;
};

const executeDelete = () => {
    if (!deleteItemId.value) return;
    router.delete(route('inventory.destroy', deleteItemId.value), {
        preserveScroll: true,
        onSuccess: cancelDelete,
    });
};
</script>

<template>
    <Head title="Inventario" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-2">
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-[var(--clinic-muted)]">Inventario</p>
                <h2 class="text-3xl font-black leading-tight text-[var(--clinic-text)]">Control de suministros clinicos</h2>
                <p class="max-w-3xl text-sm leading-6 text-[var(--clinic-muted)]">Gestiona medicamentos, insumos y materiales. Los articulos bajo stock minimo se marcan automaticamente.</p>
            </div>
        </template>

        <div class="py-10">
            <div class="mx-auto max-w-7xl space-y-8 sm:px-6 lg:px-8">
                <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <StatsCard label="Articulos" :value="stats.total_items" />
                    <StatsCard label="Valor total" :value="formatCurrency(stats.total_value)" />
                    <StatsCard label="Bajo stock" :value="stats.low_stock" :accent="stats.low_stock > 0" />
                    <StatsCard label="Categorias" :value="stats.categories" />
                </section>

                <section class="rounded-[2rem] border border-[var(--clinic-border)] bg-[var(--clinic-panel-bg)]/95 p-6 shadow-[0_28px_90px_-55px_color-mix(in_srgb,var(--clinic-primary)_45%,transparent)]">
                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--clinic-muted)]">Nuevo articulo</p>
                    <h3 class="mt-2 text-2xl font-black text-[var(--clinic-text)]">Agregar al inventario</h3>

                    <form class="mt-6 grid gap-4 xl:grid-cols-4" @submit.prevent="createItem">
                        <input v-model="createItemForm.name" type="text" placeholder="Nombre del articulo" class="block w-full rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-shell-surface)] px-4 py-3 text-sm text-[var(--clinic-text)] shadow-sm transition-all duration-200 focus:border-[var(--clinic-primary)] focus:outline-none focus:ring-2 focus:ring-[color-mix(in_srgb,var(--clinic-primary)_15%,white)]" @input="sanitizeField(createItemForm, 'name')" />
                        <input v-model="createItemForm.category" type="text" placeholder="Categoria" class="block w-full rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-shell-surface)] px-4 py-3 text-sm text-[var(--clinic-text)] shadow-sm transition-all duration-200 focus:border-[var(--clinic-primary)] focus:outline-none focus:ring-2 focus:ring-[color-mix(in_srgb,var(--clinic-primary)_15%,white)]" @input="sanitizeField(createItemForm, 'category')" />
                        <input v-model="createItemForm.sku" type="text" placeholder="SKU (opcional)" class="block w-full rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-shell-surface)] px-4 py-3 text-sm text-[var(--clinic-text)] shadow-sm transition-all duration-200 focus:border-[var(--clinic-primary)] focus:outline-none focus:ring-2 focus:ring-[color-mix(in_srgb,var(--clinic-primary)_15%,white)]" />
                        <input v-model="createItemForm.unit_price" type="number" min="0" step="0.01" placeholder="Precio unitario" class="block w-full rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-shell-surface)] px-4 py-3 text-sm text-[var(--clinic-text)] shadow-sm transition-all duration-200 focus:border-[var(--clinic-primary)] focus:outline-none focus:ring-2 focus:ring-[color-mix(in_srgb,var(--clinic-primary)_15%,white)]" />
                        <input v-model.number="createItemForm.quantity" type="number" min="0" placeholder="Cantidad" class="block w-full rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-shell-surface)] px-4 py-3 text-sm text-[var(--clinic-text)] shadow-sm transition-all duration-200 focus:border-[var(--clinic-primary)] focus:outline-none focus:ring-2 focus:ring-[color-mix(in_srgb,var(--clinic-primary)_15%,white)]" />
                        <input v-model.number="createItemForm.min_quantity" type="number" min="0" placeholder="Stock minimo" class="block w-full rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-shell-surface)] px-4 py-3 text-sm text-[var(--clinic-text)] shadow-sm transition-all duration-200 focus:border-[var(--clinic-primary)] focus:outline-none focus:ring-2 focus:ring-[color-mix(in_srgb,var(--clinic-primary)_15%,white)]" />
                        <input v-model="createItemForm.unit" type="text" placeholder="Unidad" class="block w-full rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-shell-surface)] px-4 py-3 text-sm text-[var(--clinic-text)] shadow-sm transition-all duration-200 focus:border-[var(--clinic-primary)] focus:outline-none focus:ring-2 focus:ring-[color-mix(in_srgb,var(--clinic-primary)_15%,white)]" />
                        <input v-model="createItemForm.supplier" type="text" placeholder="Proveedor" class="block w-full rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-shell-surface)] px-4 py-3 text-sm text-[var(--clinic-text)] shadow-sm transition-all duration-200 focus:border-[var(--clinic-primary)] focus:outline-none focus:ring-2 focus:ring-[color-mix(in_srgb,var(--clinic-primary)_15%,white)]" @input="sanitizeField(createItemForm, 'supplier')" />
                        <input v-model="createItemForm.notes" type="text" placeholder="Notas" class="xl:col-span-3 block w-full rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-shell-surface)] px-4 py-3 text-sm text-[var(--clinic-text)] shadow-sm transition-all duration-200 focus:border-[var(--clinic-primary)] focus:outline-none focus:ring-2 focus:ring-[color-mix(in_srgb,var(--clinic-primary)_15%,white)]" />
                        <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-full border border-[var(--clinic-primary)] bg-[var(--clinic-primary)] px-6 py-3 text-xs font-semibold uppercase tracking-[0.28em] text-[var(--clinic-highlight)] transition-all duration-200 hover:bg-[var(--clinic-primary-dark)]">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="12" x2="12" y1="5" y2="19" />
                                <line x1="5" x2="19" y1="12" y2="12" />
                            </svg>
                            Agregar
                        </button>
                    </form>
                </section>

                <section class="rounded-[2rem] border border-[var(--clinic-border)] bg-[var(--clinic-panel-bg)]/95 p-6 shadow-[0_28px_90px_-55px_color-mix(in_srgb,var(--clinic-primary)_45%,transparent)]">
                    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--clinic-muted)]">Inventario activo</p>
                            <h3 class="mt-2 text-2xl font-black text-[var(--clinic-text)]">Articulos registrados</h3>
                        </div>
                    </div>

                    <div class="mt-6">
                        <DataTable
                            :columns="columns"
                            :rows="props.items"
                            :search-fields="searchFields"
                            search-placeholder="Filtrar por nombre, categoria o proveedor"
                            empty-message="No hay articulos en el inventario."
                            :per-page="10"
                        >
                            <template #cell-name="{ row }">
                                <div>
                                    <p class="font-bold text-[var(--clinic-text)]">{{ row.name }}</p>
                                    <p class="mt-0.5 text-xs text-[var(--clinic-muted)]">{{ row.sku || 'Sin SKU' }} &middot; {{ row.unit }}</p>
                                </div>
                            </template>
                            <template #cell-quantity="{ row }">
                                <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold" :class="row.is_low_stock ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700'">
                                    <svg v-if="row.is_low_stock" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                                        <line x1="12" x2="12" y1="9" y2="13" />
                                        <line x1="12" x2="12.01" y1="17" y2="17" />
                                    </svg>
                                    <svg v-else class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                                        <polyline points="22 4 12 14.01 9 11.01" />
                                    </svg>
                                    {{ row.quantity }} / {{ row.min_quantity }}
                                </span>
                            </template>
                            <template #cell-supplier="{ row }">
                                <span class="text-[var(--clinic-muted)]">{{ row.supplier || 'Sin proveedor' }}</span>
                            </template>
                            <template #cell-actions="{ row }">
                                <div class="flex gap-2">
                                    <button type="button" class="inline-flex items-center gap-1.5 rounded-full border border-[var(--clinic-border)] bg-[var(--clinic-highlight)] px-3.5 py-1.5 text-[11px] font-semibold uppercase tracking-[0.14em] text-[var(--clinic-primary)] transition-all duration-150 hover:border-[var(--clinic-primary)] hover:bg-[color-mix(in_srgb,var(--clinic-primary)_8%,white)]" @click="openItemModal(row.id)">
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z" />
                                        </svg>
                                        Editar
                                    </button>
                                    <button type="button" class="inline-flex items-center justify-center rounded-full border border-red-300 bg-red-50 p-1.5 text-red-600 transition-all duration-150 hover:bg-red-100" @click="confirmDelete(row.id)">
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="3 6 5 6 21 6" />
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                                        </svg>
                                    </button>
                                </div>
                            </template>
                        </DataTable>
                    </div>
                </section>
            </div>
        </div>

        <Modal :show="!!selectedItem()" max-width="2xl" @close="closeItemModal">
            <div v-if="selectedItem()" class="p-6 sm:p-8">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--clinic-muted)]">Editar articulo</p>
                        <h3 class="mt-2 text-2xl font-black text-[var(--clinic-text)]">{{ selectedItem().name }}</h3>
                    </div>
                    <button type="button" class="inline-flex items-center gap-2 rounded-full border border-[var(--clinic-border)] px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-[var(--clinic-muted)] transition-all duration-150 hover:border-[var(--clinic-primary)] hover:text-[var(--clinic-primary)]" @click="closeItemModal">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" x2="6" y1="6" y2="18" />
                            <line x1="6" x2="18" y1="6" y2="18" />
                        </svg>
                        Cerrar
                    </button>
                </div>

                <div class="mt-6 grid gap-4 md:grid-cols-2">
                    <input v-model="itemForms[selectedItem().id].name" type="text" placeholder="Nombre del articulo" class="block w-full rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-shell-surface)] px-4 py-3 text-sm text-[var(--clinic-text)] shadow-sm transition-all duration-200 focus:border-[var(--clinic-primary)] focus:outline-none focus:ring-2 focus:ring-[color-mix(in_srgb,var(--clinic-primary)_15%,white)]" @input="sanitizeField(itemForms[selectedItem().id], 'name')" />
                    <input v-model="itemForms[selectedItem().id].category" type="text" placeholder="Categoria" class="block w-full rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-shell-surface)] px-4 py-3 text-sm text-[var(--clinic-text)] shadow-sm transition-all duration-200 focus:border-[var(--clinic-primary)] focus:outline-none focus:ring-2 focus:ring-[color-mix(in_srgb,var(--clinic-primary)_15%,white)]" @input="sanitizeField(itemForms[selectedItem().id], 'category')" />
                    <input v-model="itemForms[selectedItem().id].sku" type="text" placeholder="SKU" class="block w-full rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-shell-surface)] px-4 py-3 text-sm text-[var(--clinic-text)] shadow-sm transition-all duration-200 focus:border-[var(--clinic-primary)] focus:outline-none focus:ring-2 focus:ring-[color-mix(in_srgb,var(--clinic-primary)_15%,white)]" />
                    <input v-model="itemForms[selectedItem().id].unit" type="text" placeholder="Unidad" class="block w-full rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-shell-surface)] px-4 py-3 text-sm text-[var(--clinic-text)] shadow-sm transition-all duration-200 focus:border-[var(--clinic-primary)] focus:outline-none focus:ring-2 focus:ring-[color-mix(in_srgb,var(--clinic-primary)_15%,white)]" />
                    <input v-model.number="itemForms[selectedItem().id].quantity" type="number" min="0" placeholder="Cantidad" class="block w-full rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-shell-surface)] px-4 py-3 text-sm text-[var(--clinic-text)] shadow-sm transition-all duration-200 focus:border-[var(--clinic-primary)] focus:outline-none focus:ring-2 focus:ring-[color-mix(in_srgb,var(--clinic-primary)_15%,white)]" />
                    <input v-model.number="itemForms[selectedItem().id].min_quantity" type="number" min="0" placeholder="Stock minimo" class="block w-full rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-shell-surface)] px-4 py-3 text-sm text-[var(--clinic-text)] shadow-sm transition-all duration-200 focus:border-[var(--clinic-primary)] focus:outline-none focus:ring-2 focus:ring-[color-mix(in_srgb,var(--clinic-primary)_15%,white)]" />
                    <input v-model="itemForms[selectedItem().id].unit_price" type="number" min="0" step="0.01" placeholder="Precio unitario" class="block w-full rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-shell-surface)] px-4 py-3 text-sm text-[var(--clinic-text)] shadow-sm transition-all duration-200 focus:border-[var(--clinic-primary)] focus:outline-none focus:ring-2 focus:ring-[color-mix(in_srgb,var(--clinic-primary)_15%,white)]" />
                    <div class="flex items-center rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-shell-surface)] px-4 py-3 text-sm font-semibold text-[var(--clinic-primary)]">{{ formatCurrency(itemForms[selectedItem().id].unit_price) }}</div>
                    <input v-model="itemForms[selectedItem().id].supplier" type="text" placeholder="Proveedor" class="md:col-span-2 block w-full rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-shell-surface)] px-4 py-3 text-sm text-[var(--clinic-text)] shadow-sm transition-all duration-200 focus:border-[var(--clinic-primary)] focus:outline-none focus:ring-2 focus:ring-[color-mix(in_srgb,var(--clinic-primary)_15%,white)]" @input="sanitizeField(itemForms[selectedItem().id], 'supplier')" />
                </div>

                <textarea v-model="itemForms[selectedItem().id].notes" rows="3" class="mt-4 block w-full rounded-2xl border border-[var(--clinic-border)] bg-[var(--clinic-shell-surface)] px-4 py-3 text-sm text-[var(--clinic-text)] shadow-sm transition-all duration-200 focus:border-[var(--clinic-primary)] focus:outline-none focus:ring-2 focus:ring-[color-mix(in_srgb,var(--clinic-primary)_15%,white)]" placeholder="Notas" />

                <div class="mt-6 flex flex-wrap justify-end gap-3">
                    <button type="button" class="inline-flex items-center gap-2 rounded-full border border-[var(--clinic-border)] px-5 py-3 text-xs font-semibold uppercase tracking-[0.22em] text-[var(--clinic-muted)] transition-all duration-150 hover:border-[var(--clinic-primary)] hover:text-[var(--clinic-primary)]" @click="closeItemModal">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" x2="6" y1="6" y2="18" />
                            <line x1="6" x2="18" y1="6" y2="18" />
                        </svg>
                        Cerrar
                    </button>
                    <button type="button" class="inline-flex items-center gap-2 rounded-full bg-[var(--clinic-primary)] px-5 py-3 text-xs font-semibold uppercase tracking-[0.22em] text-[var(--clinic-highlight)] transition-all duration-200 hover:bg-[var(--clinic-primary-dark)]" @click="saveItem(selectedItem().id)">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" />
                            <polyline points="17 21 17 13 7 13 7 21" />
                            <polyline points="7 3 7 8 15 8" />
                        </svg>
                        Guardar
                    </button>
                </div>
            </div>
        </Modal>

        <Modal :show="showDeleteConfirm" max-width="md" @close="cancelDelete">
            <div class="p-6 sm:p-8">
                <div class="flex items-start gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-red-100">
                        <svg class="h-6 w-6 text-red-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                            <line x1="12" x2="12" y1="9" y2="13" />
                            <line x1="12" x2="12.01" y1="17" y2="17" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-[var(--clinic-muted)]">Confirmar eliminacion</p>
                        <h3 class="mt-2 text-xl font-black text-[var(--clinic-text)]">Eliminar articulo del inventario</h3>
                        <p class="mt-2 text-sm text-[var(--clinic-muted)]">Esta accion no se puede deshacer. El articulo sera eliminado permanentemente.</p>
                    </div>
                </div>
                <div class="mt-6 flex flex-wrap justify-end gap-3">
                    <button type="button" class="inline-flex items-center gap-2 rounded-full border border-[var(--clinic-border)] px-5 py-3 text-xs font-semibold uppercase tracking-[0.22em] text-[var(--clinic-muted)] transition-all duration-150 hover:border-[var(--clinic-primary)] hover:text-[var(--clinic-primary)]" @click="cancelDelete">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" x2="6" y1="6" y2="18" />
                            <line x1="6" x2="18" y1="6" y2="18" />
                        </svg>
                        Cancelar
                    </button>
                    <button type="button" class="inline-flex items-center gap-2 rounded-full border border-red-600 bg-red-600 px-5 py-3 text-xs font-semibold uppercase tracking-[0.22em] text-white transition-all duration-150 hover:bg-red-700" @click="executeDelete">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="3 6 5 6 21 6" />
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                        </svg>
                        Eliminar
                    </button>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
