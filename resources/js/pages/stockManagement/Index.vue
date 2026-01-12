<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import stockManagement from '@/routes/stockManagement';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Manajemen Stok',
        href: stockManagement.index().url,
    },
];
//
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import {
    DropdownMenu,
    DropdownMenuCheckboxItem,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
// import { Badge } from '@/components/ui/badge';
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
    cabang: string;
    pakaian: string;
    tglUpdate: string;
    stok: number;
}
const data: Payment[] = [
    {
        id: 'm5gr84i9',
        cabang: 'Nanana 1',
        pakaian: 'one set vest linen',
        tglUpdate: '7 Januari 2006',
        stok: 2,
    },
    {
        id: '3u1reuv4',
        cabang: 'Nanana 2',
        pakaian: 'midi bhn twill+vest linen',
        tglUpdate: '10 Januari 2006',
        stok: 1,
    },
    {
        id: 'derv1ws0',
        cabang: 'Nanana 2',
        pakaian: 'one set vest kulot jeans',
        tglUpdate: '12 Januari 2006',
        stok: 4,
    },
    {
        id: '5kma53ae',
        cabang: 'Nanana 1',
        pakaian: 'one set kemeja+vest kemeja',
        tglUpdate: '9 Januari 2006',
        stok: 2,
    },
    {
        id: 'bhqecj4p',
        cabang: 'Nanana 2',
        pakaian: 'kemeja bahan linen',
        tglUpdate: '5 Januari 2006',
        stok: 9,
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
        accessorKey: 'cabang',
        header: ({ column }) => {
            return h(
                Button,
                {
                    variant: 'ghost',
                    onClick: () =>
                        column.toggleSorting(column.getIsSorted() === 'asc'),
                },
                () => ['Cabang', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })],
            );
        },
        cell: ({ row }) =>
            h('div', { class: 'capitalize' }, row.getValue('cabang')),
    },
    {
        accessorKey: 'pakaian',
        header: ({ column }) => {
            return h(
                Button,
                {
                    variant: 'ghost',
                    onClick: () =>
                        column.toggleSorting(column.getIsSorted() === 'asc'),
                },
                () => ['Pakaian', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })],
            );
        },
        cell: ({ row }) =>
            h('div', { class: 'capitalize' }, row.getValue('pakaian')),
    },
    {
        accessorKey: 'tglUpdate',
        header: ({ column }) => {
            return h(
                Button,
                {
                    variant: 'ghost',
                    onClick: () =>
                        column.toggleSorting(column.getIsSorted() === 'asc'),
                },
                () => ['Tanggal Update', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })],
            );
        },
        cell: ({ row }) =>
            h('div', { class: 'capitalize' }, row.getValue('tglUpdate')),
    },
    {
        accessorKey: 'stok',
        header: ({ column }) => {
            return h(
                Button,
                {
                    variant: 'ghost',
                    onClick: () =>
                        column.toggleSorting(column.getIsSorted() === 'asc'),
                },
                () => ['Stok', h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })],
            );
        },
        cell: ({ row }) =>
            h('div', { class: 'capitalize' }, row.getValue('stok')),
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

function editCategory(id: string) {
    console.log('Edit', id);
}

function deleteCategory(id: string) {
    console.log('Delete', id);
}
</script>

<template>
    <Head title="Stok" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4">
            <div class="w-full">
                <div class="flex items-center py-4">
                    <Input
                        class="max-w-sm"
                        placeholder="Cari Pakaian..."
                        :model-value="
                            table.getColumn('pakaian')?.getFilterValue() as string
                        "
                        @update:model-value="
                            table.getColumn('pakaian')?.setFilterValue($event)
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
