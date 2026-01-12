<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import invoices from '@/routes/invoices';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Transaksi & Tagihan',
        href: invoices.index(),
    },
];
//
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import {
    DropdownMenu,
    DropdownMenuCheckboxItem,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
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
import { createReusableTemplate } from '@vueuse/core';
import { ArrowUpDown, ChevronDown, Pencil, Trash2 } from 'lucide-vue-next';
import { h, ref } from 'vue';
export interface Payment {
    id: string;
    kodeInvoice: string;
    status: 'lunas' | 'belum lunas';
    tglDibuat: string;  
}
const data: Payment[] = [
    {
        id: 'm5gr84i9',
        kodeInvoice: 'CBG1  -010126-0001',
        status: 'lunas',
        tglDibuat: '7 Januari 2006',
    },
    {
        id: '3u1reuv4',
        kodeInvoice: 'CBG2-020226-0001',
        status: 'lunas',
        tglDibuat: '10 Januari 2006',
    },
    {
        id: 'derv1ws0',
        kodeInvoice: 'CBG1-010126-0002',
        status: 'belum lunas',
        tglDibuat: '12 Januari 2006',
    },
    {
        id: '5kma53ae',
        kodeInvoice: 'CBG2-010126-0001',
        status: 'lunas',
        tglDibuat: '9 Januari 2006',
    },
    {
        id: 'bhqecj4p',
        kodeInvoice: 'CBG2-010126-0002',
        status: 'belum lunas',
        tglDibuat: '5 Januari 2006',
    },
];
const [DefineTemplate] = createReusableTemplate<{
    payment: {
        id: string;
    };
    onExpand: () => void;
}>();
const columns: ColumnDef<Payment>[] = [
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
        accessorKey: 'kodeInvoice',
        header: ({ column }) => {
            return h(
                Button,
                {
                    variant: 'ghost',
                    onClick: () =>
                        column.toggleSorting(column.getIsSorted() === 'asc'),
                },
                () => ['Kode Invoice', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })],
            );
        },
        cell: ({ row }) =>
            h('div', { class: 'capitalize' }, row.getValue('kodeInvoice')),
    },
    {
        accessorKey: 'tglDibuat',
        header: ({ column }) => {
            return h(
                Button,
                {
                    variant: 'ghost',
                    onClick: () =>
                        column.toggleSorting(column.getIsSorted() === 'asc'),
                },
                () => ['Tanggal Dibuat', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })],
            );
        },
        cell: ({ row }) =>
            h('div', { class: 'capitalize' }, row.getValue('tglDibuat')),
    },
    {
        accessorKey: 'status',
        header: ({ column }) => {
            return h(
                Button,
                {
                    variant: 'ghost',
                    onClick: () =>
                        column.toggleSorting(column.getIsSorted() === 'asc'),
                },
                () => ['Status', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })],
            );
        },
        cell: ({ row }) => {
            const status = row.getValue('status') as string;

            return h(
                Badge,
                {
                    variant: status === 'lunas' ? 'success' : 'destructive',
                    class:
                        status === 'lunas'
                            ? 'bg-green-500 text-white'
                            : '',
                },
                () => (status === 'lunas' ? 'lunas' : 'belum lunas'),
            );
        },

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
                            size: 'icon',
                            onClick: () => editCategory(row.original.id),
                        },
                        () => h(Pencil, { class: 'h-4 w-4' }),
                    ),
                    h(
                        Button,
                        {
                            variant: 'destructive',
                            size: 'icon',
                            onClick: () => deleteCategory(row.original.id),
                        },
                        () => h(Trash2, { class: 'h-4 w-4' }),
                    ),
                ],
            );
        },
    },
];
const sorting = ref<SortingState>([]);
const columnFilters = ref<ColumnFiltersState>([]);
const columnVisibility = ref<VisibilityState>({});
const rowSelection = ref({});
const expanded = ref<ExpandedState>({});
const table = useVueTable({
    data,
    columns,
    getCoreRowModel: getCoreRowModel(),
    getPaginationRowModel: getPaginationRowModel(),
    getSortedRowModel: getSortedRowModel(),
    getFilteredRowModel: getFilteredRowModel(),
    getExpandedRowModel: getExpandedRowModel(),
    onSortingChange: (updaterOrValue) => valueUpdater(updaterOrValue, sorting),
    onColumnFiltersChange: (updaterOrValue) =>
        valueUpdater(updaterOrValue, columnFilters),
    onColumnVisibilityChange: (updaterOrValue) =>
        valueUpdater(updaterOrValue, columnVisibility),
    onRowSelectionChange: (updaterOrValue) =>
        valueUpdater(updaterOrValue, rowSelection),
    onExpandedChange: (updaterOrValue) =>
        valueUpdater(updaterOrValue, expanded),
    state: {
        get sorting() {
            return sorting.value;
        },
        get columnFilters() {
            return columnFilters.value;
        },
        get columnVisibility() {
            return columnVisibility.value;
        },
        get rowSelection() {
            return rowSelection.value;
        },
        get expanded() {
            return expanded.value;
        },
    },
});
function copy(id: string) {
    navigator.clipboard.writeText(id);
}
</script>

<template>
    <Head title="Invoice" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4">
            <div class="w-full">
                <div class="flex items-center py-4">
                    <Input
                        class="max-w-sm"
                        placeholder="Cari kode invoice..."
                        :model-value="
                            table.getColumn('kodeInvoice')?.getFilterValue() as string
                        "
                        @update:model-value="
                            table.getColumn('kodeInvoice')?.setFilterValue($event)
                        "
                    />
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button variant="outline" class="ml-auto">
                                Kolom <ChevronDown class="ml-2 h-4 w-4" />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end">
                            <DropdownMenuCheckboxItem
                                v-for="column in table
                                    .getAllColumns()
                                    .filter((column) => column.getCanHide())"
                                :key="column.id"
                                class="capitalize"
                                :model-value="column.getIsVisible()"
                                @update:model-value="
                                    (value) => {
                                        column.toggleVisibility(!!value);
                                    }
                                "
                            >
                                {{ column.id }}
                            </DropdownMenuCheckboxItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>
                <div class="rounded-md border">
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
                                <template
                                    v-for="row in table.getRowModel().rows"
                                    :key="row.id"
                                >
                                    <TableRow
                                        :data-state="
                                            row.getIsSelected() && 'selected'
                                        "
                                    >
                                        <TableCell
                                            v-for="cell in row.getVisibleCells()"
                                            :key="cell.id"
                                        >
                                            <FlexRender
                                                :render="
                                                    cell.column.columnDef.cell
                                                "
                                                :props="cell.getContext()"
                                            />
                                        </TableCell>
                                    </TableRow>
                                    <TableRow v-if="row.getIsExpanded()">
                                        <TableCell
                                            :colspan="row.getAllCells().length"
                                        >
                                            {{ JSON.stringify(row.original) }}
                                        </TableCell>
                                    </TableRow>
                                </template>
                                <TableRow>
                                    <TableCell
                                        :colspan="columns.length"
                                        class="text-center py-1 bg-muted/30"
                                    >
                                        <Button
                                            variant="outline"
                                            class="gap-2"
                                            @click="createCategory"
                                        >
                                            + Tambah Kategori
                                        </Button>
                                    </TableCell>
                                </TableRow>
                            </template>
                            <TableRow v-else>
                                <TableCell
                                    :colspan="columns.length"
                                    class="h-24 text-center"
                                >
                                    Pencarian tidak ditemukan. :(
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
                <div class="flex items-center justify-end space-x-2 py-4">
                    <div class="flex-1 text-sm text-muted-foreground">
                        {{ table.getFilteredSelectedRowModel().rows.length }} dari
                        {{ table.getFilteredRowModel().rows.length }} baris
                        terpilih.
                    </div>
                    <div class="space-x-2">
                        <Button
                            variant="outline"
                            size="sm"
                            :disabled="!table.getCanPreviousPage()"
                            @click="table.previousPage()"
                        >
                            Sebelumnya
                        </Button>
                        <Button
                            variant="outline"
                            size="sm"
                            :disabled="!table.getCanNextPage()"
                            @click="table.nextPage()"
                        >
                            Selanjutnya
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
