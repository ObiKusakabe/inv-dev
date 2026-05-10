<script setup lang="ts" generic="TData, TValue">
import type { ColumnDef } from '@tanstack/vue-table'
import {
    FlexRender,
    getCoreRowModel,
    getFilteredRowModel,
    getPaginationRowModel,
    getSortedRowModel,
    useVueTable,
} from '@tanstack/vue-table'
import { ref } from 'vue'
import { valueUpdater } from '@/lib/utils'

import { Button } from '@/components/ui/button'
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table'

import DataTableToolbar from '@/components/ui/data-table/DataTableToolbar.vue'

const props = defineProps<{
    columns: ColumnDef<TData, TValue>[]
    data: TData[]
    searchPlaceholder?: string
    searchKey?: string
}>()

const sorting = ref([])
const columnFilters = ref([])
const rowSelection = ref({})

const table = useVueTable({
    get data() {
        return props.data
    },
    get columns() {
        return props.columns
    },
    state: {
        get sorting() {
            return sorting.value
        },
        get columnFilters() {
            return columnFilters.value
        },
        get rowSelection() {
            return rowSelection.value
        },
    },
    enableRowSelection: true,
    onSortingChange: (updater) => valueUpdater(updater, sorting),
    onColumnFiltersChange: (updater) => valueUpdater(updater, columnFilters),
    onRowSelectionChange: (updater) => valueUpdater(updater, rowSelection),
    getCoreRowModel: getCoreRowModel(),
    getSortedRowModel: getSortedRowModel(),
    getFilteredRowModel: getFilteredRowModel(),
    getPaginationRowModel: getPaginationRowModel(),
})
</script>

<template>
    <div class="rounded-xl border">
        <DataTableToolbar :table="table" :search-key="searchKey" :search-placeholder="searchPlaceholder" />

        <Table>
            <TableHeader>
                <TableRow v-for="headerGroup in table.getHeaderGroups()" :key="headerGroup.id">
                    <TableHead v-for="header in headerGroup.headers" :key="header.id">
                        <FlexRender v-if="!header.isPlaceholder" :render="header.column.columnDef.header"
                            :props="header.getContext()" />
                    </TableHead>
                </TableRow>
            </TableHeader>

            <TableBody>
                <template v-if="table.getRowModel().rows?.length">
                    <TableRow v-for="row in table.getRowModel().rows" :key="row.id" v-memo="[row.original, sorting, columnFilters]">
                        <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id" v-memo="[cell.getValue(), sorting]">
                            <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
                        </TableCell>
                    </TableRow>
                </template>

                <template v-else>
                    <TableRow>
                        <TableCell :colspan="columns.length" class="h-24 text-center text-muted-foreground">
                            Tidak ada data.
                        </TableCell>
                    </TableRow>
                </template>
            </TableBody>
        </Table>

        <div class="flex items-center justify-between gap-2 p-4">
            <div class="text-sm text-muted-foreground">
                {{ table.getFilteredRowModel().rows.length }} rows
            </div>

            <div class="flex items-center gap-2">
                <Button variant="outline" size="sm" :disabled="!table.getCanPreviousPage()"
                    @click="table.previousPage()">
                    Previous
                </Button>
                <Button variant="outline" size="sm" :disabled="!table.getCanNextPage()" @click="table.nextPage()">
                    Next
                </Button>
            </div>
        </div>
    </div>
</template>