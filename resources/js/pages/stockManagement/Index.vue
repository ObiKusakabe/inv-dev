<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import stockManagement from '@/routes/stockManagement';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Manajemen Stok',
        href: stockManagement.index().url,
    },
];

import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Badge } from '@/components/ui/badge';
import { toast } from 'vue-sonner';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
    DialogFooter,
} from '@/components/ui/dialog';
import {
    DropdownMenu,
    DropdownMenuCheckboxItem,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { valueUpdater } from '@/lib/utils';
import type {
    ColumnDef,
    ColumnFiltersState,
    ExpandedState,
    SortingState,
    VisibilityState,
} from '@tanstack/vue-table';
import {
    FlexRender,
    getCoreRowModel,
    getExpandedRowModel,
    getFilteredRowModel,
    getPaginationRowModel,
    getSortedRowModel,
    useVueTable,
} from '@tanstack/vue-table';
import { ArrowUpDown, ChevronDown, Package, Plus, Minus, History } from 'lucide-vue-next';
import { h, ref, computed } from 'vue';

// Types
type StockRow = {
    id: number;
    product_id: number;
    branch_id: number;
    stock: number;
    min_stock: number;
    product: {
        id: number;
        name: string;
        image: string | null;
    };
    branch: {
        id: number;
        name: string;
        code: string;
    };
};

type Branch = {
    id: number;
    name: string;
    code: string;
};

// Props
const props = defineProps<{
    stocks: StockRow[];
    branches: Branch[];
    currentBranchId: number | null;
    filters: {
        branch_id?: number | null;
    };
}>();

// Filter state
const selectedBranch = ref<number | null>(props.filters.branch_id || null);

function applyFilter() {
    router.get(
        stockManagement.index().url,
        { branch_id: selectedBranch.value },
        { preserveScroll: true }
    );
}

// Dialog state for stock update
const isUpdateDialogOpen = ref(false);
const selectedStock = ref<StockRow | null>(null);
const stockAdjustment = ref(0);
const stockReason = ref('');

function openUpdateDialog(row: StockRow) {
    selectedStock.value = row;
    stockAdjustment.value = 0;
    stockReason.value = '';
    isUpdateDialogOpen.value = true;
}

function adjustStock(amount: number) {
    stockAdjustment.value += amount;
}

function submitStockUpdate() {
    if (!selectedStock.value || stockAdjustment.value === 0) return;
    
    const toastId = toast.loading('Memperbarui stok...')
    
    router.post(
        stockManagement.update(selectedStock.value.id).url,
        {
            adjustment: stockAdjustment.value,
            reason: stockReason.value,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                toast.dismiss(toastId)
                setTimeout(() => {
                    toast.success('Stok berhasil diperbarui')
                }, 100)
                isUpdateDialogOpen.value = false;
                selectedStock.value = null;
            },
            onError: (errors) => {
                toast.dismiss(toastId)
                const errorMessage = typeof errors === 'object' ? Object.values(errors).flat().join(', ') : 'Terjadi kesalahan'
                setTimeout(() => {
                    toast.error('Gagal memperbarui stok', { description: errorMessage })
                }, 100)
            }
        }
    );
}

// Table columns
const columns: ColumnDef<StockRow>[] = [
    {
        id: 'select',
        header: ({ table }) =>
            h(Checkbox, {
                modelValue:
                    table.getIsAllPageRowsSelected() ||
                    (table.getIsSomePageRowsSelected() && 'indeterminate'),
                'onUpdate:modelValue': (value) =>
                    table.toggleAllPageRowsSelected(!!value),
                ariaLabel: 'Select all',
            }),
        cell: ({ row }) =>
            h(Checkbox, {
                modelValue: row.getIsSelected(),
                'onUpdate:modelValue': (value) => row.toggleSelected(!!value),
                ariaLabel: 'Select row',
            }),
        enableSorting: false,
        enableHiding: false,
    },
    {
        accessorKey: 'product',
        header: 'Produk',
        cell: ({ row }) => {
            const product = row.original.product;
            return h('div', { class: 'flex items-center gap-3' }, [
                product.image
                    ? h('img', {
                          src: '/storage/' + product.image,
                          class: 'h-10 w-10 rounded-md object-cover border',
                          alt: product.name,
                      })
                    : h('div', { class: 'h-10 w-10 rounded-md bg-gray-100 flex items-center justify-center text-gray-400' }, 
                        h(Package, { class: 'h-5 w-5' })
                    ),
                h('div', { class: 'font-medium' }, product.name),
            ]);
        },
    },
    {
        accessorKey: 'branch',
        header: 'Cabang',
        cell: ({ row }) =>
            h('div', {}, [
                h('div', { class: 'font-medium' }, row.original.branch.name),
                h('div', { class: 'text-xs text-muted-foreground' }, row.original.branch.code),
            ]),
    },
    {
        accessorKey: 'stock',
        header: ({ column }) => {
            return h(
                Button,
                {
                    variant: 'ghost',
                    onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'),
                },
                () => ['Stok', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })]
            );
        },
        cell: ({ row }) => {
            const stock = row.original.stock;
            const minStock = row.original.min_stock;
            const isLow = stock <= minStock;
            return h(Badge, { 
                variant: isLow ? 'destructive' : 'default',
                class: 'font-mono'
            }, () => stock);
        },
    },
    {
        accessorKey: 'min_stock',
        header: 'Min. Stok',
        cell: ({ row }) =>
            h('div', { class: 'text-muted-foreground' }, row.original.min_stock),
    },
    {
        id: 'actions',
        enableHiding: false,
        cell: ({ row }) => {
            return h(
                'div',
                { class: 'flex gap-2 justify-end' },
                [
                    h(
                        Button,
                        {
                            variant: 'outline',
                            size: 'sm',
                            onClick: () => openUpdateDialog(row.original),
                        },
                        () => [h(Plus, { class: 'h-4 w-4 mr-1' }), 'Update']
                    ),
                ]
            );
        },
    },
];

const sorting = ref<SortingState>([]);
const columnFilters = ref<ColumnFiltersState>([]);
const columnVisibility = ref<VisibilityState>({});
const rowSelection = ref({});

const table = useVueTable({
    data: props.stocks,
    columns,
    getCoreRowModel: getCoreRowModel(),
    getPaginationRowModel: getPaginationRowModel(),
    getSortedRowModel: getSortedRowModel(),
    getFilteredRowModel: getFilteredRowModel(),
    onSortingChange: updaterOrValue => valueUpdater(updaterOrValue, sorting),
    onColumnFiltersChange: updaterOrValue => valueUpdater(updaterOrValue, columnFilters),
    onColumnVisibilityChange: updaterOrValue => valueUpdater(updaterOrValue, columnVisibility),
    onRowSelectionChange: updaterOrValue => valueUpdater(updaterOrValue, rowSelection),
    state: {
        get sorting() { return sorting.value },
        get columnFilters() { return columnFilters.value },
        get columnVisibility() { return columnVisibility.value },
        get rowSelection() { return rowSelection.value },
    },
});
</script>

<template>
    <Head title="Manajemen Stok" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4">
            <!-- Header & Filter -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-semibold">Manajemen Stok</h2>
                    <p class="text-sm text-muted-foreground">
                        Kelola stok produk per cabang.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <Select v-model="selectedBranch" @update:model-value="applyFilter">
                        <SelectTrigger class="w-[180px]">
                            <SelectValue placeholder="Semua Cabang" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem :value="null">Semua Cabang</SelectItem>
                            <SelectItem v-for="branch in branches" :key="branch.id" :value="branch.id">
                                {{ branch.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </div>

            <!-- Table -->
            <div class="rounded-xl border">
                <div class="flex items-center justify-between p-4">
                    <Input
                        placeholder="Cari produk..."
                        :model-value="table.getColumn('product')?.getFilterValue() as string"
                        class="max-w-sm"
                        @update:model-value="table.getColumn('product')?.setFilterValue($event)"
                    />
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button variant="outline" class="ml-auto">
                                Columns <ChevronDown class="ml-2 h-4 w-4" />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end">
                            <DropdownMenuCheckboxItem
                                v-for="column in table.getAllColumns().filter((column) => column.getCanHide())"
                                :key="column.id"
                                class="capitalize"
                                :model-value="column.getIsVisible()"
                                @update:model-value="(value) => column.toggleVisibility(!!value)"
                            >
                                {{ column.id }}
                            </DropdownMenuCheckboxItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>

                <Table>
                    <TableHeader>
                        <TableRow
                            v-for="headerGroup in table.getHeaderGroups()"
                            :key="headerGroup.id"
                        >
                            <TableHead
                                v-for="header in headerGroup.headers"
                                :key="header.id"
                            >
                                <FlexRender
                                    v-if="!header.isPlaceholder"
                                    :render="header.column.columnDef.header"
                                    :props="header.getContext()"
                                />
                            </TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <template v-if="table.getRowModel().rows?.length">
                            <TableRow
                                v-for="row in table.getRowModel().rows"
                                :key="row.id"
                                :data-state="row.getIsSelected() && 'selected'"
                            >
                                <TableCell
                                    v-for="cell in row.getVisibleCells()"
                                    :key="cell.id"
                                >
                                    <FlexRender
                                        :render="cell.column.columnDef.cell"
                                        :props="cell.getContext()"
                                    />
                                </TableCell>
                            </TableRow>
                        </template>
                        <template v-else>
                            <TableRow>
                                <TableCell :colspan="columns.length" class="h-24 text-center">
                                    Tidak ada data stok.
                                </TableCell>
                            </TableRow>
                        </template>
                    </TableBody>
                </Table>

                <div class="flex items-center justify-end gap-2 p-4 border-t">
                    <div class="flex-1 text-sm text-muted-foreground">
                        {{ table.getFilteredSelectedRowModel().rows.length }} of
                        {{ table.getFilteredRowModel().rows.length }} row(s) selected.
                    </div>
                    <div class="space-x-2">
                        <Button
                            variant="outline"
                            size="sm"
                            :disabled="!table.getCanPreviousPage()"
                            @click="table.previousPage()"
                        >
                            Previous
                        </Button>
                        <Button
                            variant="outline"
                            size="sm"
                            :disabled="!table.getCanNextPage()"
                            @click="table.nextPage()"
                        >
                            Next
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Update Stock Dialog -->
            <Dialog v-model:open="isUpdateDialogOpen">
                <DialogContent class="sm:max-w-[420px]">
                    <DialogHeader>
                        <DialogTitle>Update Stok</DialogTitle>
                        <DialogDescription>
                            {{ selectedStock?.product.name }} - {{ selectedStock?.branch.name }}
                        </DialogDescription>
                    </DialogHeader>

                    <div class="space-y-4 py-4">
                        <div class="flex items-center justify-center gap-4">
                            <Button variant="outline" size="icon" @click="adjustStock(-10)">
                                -10
                            </Button>
                            <Button variant="outline" size="icon" @click="adjustStock(-1)">
                                <Minus class="h-4 w-4" />
                            </Button>
                            <div class="text-2xl font-bold w-20 text-center">
                                {{ stockAdjustment > 0 ? '+' : '' }}{{ stockAdjustment }}
                            </div>
                            <Button variant="outline" size="icon" @click="adjustStock(1)">
                                <Plus class="h-4 w-4" />
                            </Button>
                            <Button variant="outline" size="icon" @click="adjustStock(10)">
                                +10
                            </Button>
                        </div>

                        <div class="text-center text-sm text-muted-foreground">
                            Stok saat ini: <span class="font-medium">{{ selectedStock?.stock }}</span>
                            →
                            <span class="font-medium" :class="stockAdjustment + (selectedStock?.stock || 0) < 0 ? 'text-red-600' : ''">
                                {{ (selectedStock?.stock || 0) + stockAdjustment }}
                            </span>
                        </div>

                        <div class="space-y-2">
                            <label class="text-sm font-medium">Alasan perubahan (opsional)</label>
                            <Input v-model="stockReason" placeholder="Misal: Stok masuk dari supplier, rusak, hilang..." />
                        </div>
                    </div>

                    <DialogFooter>
                        <Button variant="outline" @click="isUpdateDialogOpen = false">Batal</Button>
                        <Button 
                            :disabled="stockAdjustment === 0 || (selectedStock?.stock || 0) + stockAdjustment < 0"
                            @click="submitStockUpdate"
                        >
                            Simpan Perubahan
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>
    </AppLayout>
</template>
