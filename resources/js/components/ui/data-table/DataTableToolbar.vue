<script setup lang="ts">
import type { Table } from '@tanstack/vue-table'
import { computed } from 'vue'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'

import {
    DropdownMenu,
    DropdownMenuCheckboxItem,
    DropdownMenuContent,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'

const props = defineProps<{
    table: Table<any>
    searchKey?: string
    searchPlaceholder?: string
}>()

const searchValue = computed({
    get() {
        if (!props.searchKey) return ''
        return (props.table.getColumn(props.searchKey)?.getFilterValue() as string) ?? ''
    },
    set(v: string) {
        if (!props.searchKey) return
        props.table.getColumn(props.searchKey)?.setFilterValue(v)
    },
})
</script>

<template>
    <div class="flex flex-col gap-3 p-4 md:flex-row md:items-center">
        <Input v-if="searchKey" v-model="searchValue" class="max-w-sm" :placeholder="searchPlaceholder ?? 'Cari...'" />

        <div class="ml-auto flex items-center gap-2">
            <div class="text-sm text-muted-foreground">
                Selected: {{ table.getFilteredSelectedRowModel().rows.length }}
            </div>

            <DropdownMenu>
                <DropdownMenuTrigger as-child>
                    <Button variant="outline" size="sm">Columns</Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end" class="w-[220px]">
                    <DropdownMenuLabel>Toggle columns</DropdownMenuLabel>
                    <DropdownMenuSeparator />
                    <DropdownMenuCheckboxItem v-for="col in table.getAllLeafColumns()" :key="col.id"
                        :checked="col.getIsVisible()" @update:checked="col.toggleVisibility(!!$event)">
                        {{ col.id }}
                    </DropdownMenuCheckboxItem>
                </DropdownMenuContent>
            </DropdownMenu>
        </div>
    </div>
</template>