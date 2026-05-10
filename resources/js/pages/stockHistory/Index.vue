<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { cn } from '@/lib/utils';
import stockHistory from '@/routes/stockHistory';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import {
    DateFormatter,
    getLocalTimeZone,
    parseDate,
} from '@internationalized/date';
import type { DateRange } from 'reka-ui';
import { computed, h, ref, watch } from 'vue';

import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Command,
    CommandEmpty,
    CommandGroup,
    CommandInput,
    CommandItem,
    CommandList,
} from '@/components/ui/command';
import DataTable from '@/components/ui/data-table/DataTable.vue';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import { RangeCalendar } from '@/components/ui/range-calendar';
import type { ColumnDef } from '@tanstack/vue-table';
import { format } from 'date-fns';
import { id } from 'date-fns/locale';
import {
    ArrowDown,
    ArrowUp,
    CalendarIcon,
    Check,
    ChevronsUpDown,
    Package,
    User,
} from 'lucide-vue-next';

type StockMovement = {
    id: number;
    product_id: number;
    branch_id: number;
    type: 'IN' | 'OUT';
    qty: number;
    reason: string | null;
    note: string | null;
    created_at: string;
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
    user: {
        id: number;
        name: string;
    } | null;
};

type Product = {
    id: number;
    name: string;
};

type Branch = {
    id: number;
    name: string;
};

const props = defineProps<{
    movements: StockMovement[];
    products: Product[];
    branches: Branch[];
    filters: {
        product_id?: number | null;
        branch_id?: number | null;
        type?: 'IN' | 'OUT' | null;
        date_from?: string | null;
        date_to?: string | null;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Riwayat Stok',
        href: stockHistory.index().url,
    },
];

// Filter state
const selectedProduct = ref<number | null>(props.filters.product_id || null);
const selectedBranch = ref<number | null>(props.filters.branch_id || null);
const selectedType = ref<'IN' | 'OUT' | null>(props.filters.type || null);
const dateFrom = ref<string>(props.filters.date_from || '');
const dateTo = ref<string>(props.filters.date_to || '');

// Popover states
const productOpen = ref(false);
const branchOpen = ref(false);
const typeOpen = ref(false);
const dateRangeOpen = ref(false);

// Date formatter
const df = new DateFormatter('id-ID', { dateStyle: 'medium' });

// Range calendar state
const dateRange = ref<DateRange>({
    start: dateFrom.value ? parseDate(dateFrom.value) : undefined,
    end: dateTo.value ? parseDate(dateTo.value) : undefined,
});

// Watch dateRange changes and update refs
watch(
    dateRange,
    (newRange) => {
        if (newRange?.start) {
            dateFrom.value = newRange.start.toString();
        } else {
            dateFrom.value = '';
        }
        if (newRange?.end) {
            dateTo.value = newRange.end.toString();
        } else {
            dateTo.value = '';
        }
    },
    { deep: true },
);

// Format date range for display
const dateRangeDisplay = computed(() => {
    if (dateRange.value?.start && dateRange.value?.end) {
        const start = df.format(
            dateRange.value.start.toDate(getLocalTimeZone()),
        );
        const end = df.format(dateRange.value.end.toDate(getLocalTimeZone()));
        return `${start} - ${end}`;
    }
    if (dateRange.value?.start) {
        return df.format(dateRange.value.start.toDate(getLocalTimeZone()));
    }
    return 'Pilih rentang tanggal';
});

function applyFilters() {
    router.get(
        stockHistory.index().url,
        {
            product_id: selectedProduct.value,
            branch_id: selectedBranch.value,
            type: selectedType.value,
            date_from: dateFrom.value || null,
            date_to: dateTo.value || null,
        },
        { preserveScroll: true },
    );
}

function resetFilters() {
    selectedProduct.value = null;
    selectedBranch.value = null;
    selectedType.value = null;
    dateFrom.value = '';
    dateTo.value = '';
    dateRange.value = { start: undefined, end: undefined };
    router.get(stockHistory.index().url, {}, { preserveScroll: true });
}

const renderTypeBadge = (type: 'IN' | 'OUT') => {
    const isIn = type === 'IN';

    return h(
        Badge,
        {
            variant: isIn ? 'default' : 'destructive',
            class: 'flex items-center gap-1 w-fit',
        },
        () => [
            h(isIn ? ArrowUp : ArrowDown, { class: 'h-3 w-3' }),
            isIn ? 'Masuk' : 'Keluar',
        ],
    );
};

const columns: ColumnDef<StockMovement>[] = [
    {
        id: 'select',
        header: ({ table }) =>
            h(Checkbox, {
                checked:
                    table.getIsAllPageRowsSelected() ||
                    (table.getIsSomePageRowsSelected() && 'indeterminate'),
                'onUpdate:checked': (value: boolean) =>
                    table.toggleAllPageRowsSelected(!!value),
                ariaLabel: 'Select all',
            }),
        cell: ({ row }) =>
            h(Checkbox, {
                checked: row.getIsSelected(),
                'onUpdate:checked': (value: boolean) =>
                    row.toggleSelected(!!value),
                ariaLabel: 'Select row',
            }),
        enableSorting: false,
        enableHiding: false,
    },
    {
        accessorKey: 'created_at',
        header: 'Waktu',
        cell: ({ row }) => {
            const date = new Date(row.getValue('created_at') as string);
            return h('div', { class: 'text-sm' }, [
                h('div', {}, format(date, 'dd MMM yyyy', { locale: id })),
                h(
                    'div',
                    { class: 'text-xs text-muted-foreground' },
                    format(date, 'HH:mm', { locale: id }),
                ),
            ]);
        },
    },
    {
        accessorKey: 'product',
        header: 'Produk',
        cell: ({ row }) => {
            const product = row.original.product;
            return h('div', { class: 'flex items-center gap-2' }, [
                product.image
                    ? h('img', {
                          src: '/storage/' + product.image,
                          class: 'h-8 w-8 rounded object-cover',
                      })
                    : h(Package, { class: 'h-8 w-8 text-muted-foreground' }),
                h('span', { class: 'font-medium' }, product.name),
            ]);
        },
    },
    {
        accessorKey: 'branch',
        header: 'Cabang',
        cell: ({ row }) => h('div', {}, row.original.branch?.name ?? '-'),
    },
    {
        accessorKey: 'type',
        header: 'Tipe',
        cell: ({ row }) => renderTypeBadge(row.original.type),
    },
    // {
    //   accessorKey: 'type',
    //   header: 'Tipe',
    //   cell: ({ row }) => {
    //     const type = row.getValue('type') as 'IN' | 'OUT';
    //     return h(Badge, {
    //       variant: type === 'IN' ? 'default' : 'destructive',
    //       class: 'flex items-center gap-1 w-fit',
    //     }, [
    //       type === 'IN' ? h(ArrowUp, { class: 'h-3 w-3' }) : h(ArrowDown, { class: 'h-3 w-3' }),
    //       type === 'IN' ? 'Masuk' : 'Keluar',
    //     ]);
    //   },
    // },
    {
        accessorKey: 'qty',
        header: 'Jumlah',
        cell: ({ row }) =>
            h('div', { class: 'font-mono font-medium' }, row.getValue('qty')),
    },
    {
        accessorKey: 'reason',
        header: 'Alasan',
        cell: ({ row }) =>
            h(
                'div',
                {
                    class: 'text-sm text-muted-foreground max-w-[200px] truncate',
                },
                row.getValue('reason') || '-',
            ),
    },
    {
        accessorKey: 'user',
        header: 'User',
        cell: ({ row }) => {
            const user = row.original.user;
            return h('div', { class: 'flex items-center gap-1 text-sm' }, [
                h(User, { class: 'h-3 w-3 text-muted-foreground' }),
                user?.name || 'System',
            ]);
        },
    },
];
</script>

<template>
    <Head title="Riwayat Stok" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold">
                        Riwayat Perubahan Stok
                    </h2>
                    <p class="text-sm text-muted-foreground">
                        Lihat semua riwayat masuk dan keluarnya stok.
                    </p>
                </div>
            </div>

            <!-- Filters -->
            <div class="space-y-4 rounded-xl border p-4">
                <div class="flex flex-wrap items-end gap-3">
                    <!-- Product Filter -->
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-medium text-muted-foreground"
                            >Produk</label
                        >
                        <Popover v-model:open="productOpen">
                            <PopoverTrigger as-child>
                                <Button
                                    variant="outline"
                                    role="combobox"
                                    :aria-expanded="productOpen"
                                    class="h-9 w-[180px] justify-between"
                                >
                                    {{
                                        products.find(
                                            (p) => p.id === selectedProduct,
                                        )?.name || 'Semua Produk'
                                    }}
                                    <ChevronsUpDown
                                        class="ml-2 h-4 w-4 shrink-0 opacity-50"
                                    />
                                </Button>
                            </PopoverTrigger>
                            <PopoverContent
                                class="w-[180px] p-0"
                                :avoid-collisions="true"
                            >
                                <Command>
                                    <CommandInput
                                        placeholder="Cari produk..."
                                    />
                                    <CommandList>
                                        <CommandEmpty
                                            >Tidak ada produk.</CommandEmpty
                                        >
                                        <CommandGroup>
                                            <CommandItem
                                                :value="null"
                                                @select="
                                                    () => {
                                                        selectedProduct = null;
                                                        productOpen = false;
                                                        applyFilters();
                                                    }
                                                "
                                            >
                                                Semua Produk
                                                <Check
                                                    :class="
                                                        cn(
                                                            'ml-auto h-4 w-4',
                                                            selectedProduct ===
                                                                null
                                                                ? 'opacity-100'
                                                                : 'opacity-0',
                                                        )
                                                    "
                                                />
                                            </CommandItem>
                                            <CommandItem
                                                v-for="p in products"
                                                :key="p.id"
                                                :value="p.id"
                                                @select="
                                                    () => {
                                                        selectedProduct = p.id;
                                                        productOpen = false;
                                                        applyFilters();
                                                    }
                                                "
                                            >
                                                {{ p.name }}
                                                <Check
                                                    :class="
                                                        cn(
                                                            'ml-auto h-4 w-4',
                                                            selectedProduct ===
                                                                p.id
                                                                ? 'opacity-100'
                                                                : 'opacity-0',
                                                        )
                                                    "
                                                />
                                            </CommandItem>
                                        </CommandGroup>
                                    </CommandList>
                                </Command>
                            </PopoverContent>
                        </Popover>
                    </div>

                    <!-- Branch Filter -->
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-medium text-muted-foreground"
                            >Cabang</label
                        >
                        <Popover v-model:open="branchOpen">
                            <PopoverTrigger as-child>
                                <Button
                                    variant="outline"
                                    role="combobox"
                                    :aria-expanded="branchOpen"
                                    class="h-9 w-[150px] justify-between"
                                >
                                    {{
                                        branches.find(
                                            (b) => b.id === selectedBranch,
                                        )?.name || 'Semua Cabang'
                                    }}
                                    <ChevronsUpDown
                                        class="ml-2 h-4 w-4 shrink-0 opacity-50"
                                    />
                                </Button>
                            </PopoverTrigger>
                            <PopoverContent
                                class="w-[150px] p-0"
                                :avoid-collisions="true"
                            >
                                <Command>
                                    <CommandInput
                                        placeholder="Cari cabang..."
                                    />
                                    <CommandList>
                                        <CommandEmpty
                                            >Tidak ada cabang.</CommandEmpty
                                        >
                                        <CommandGroup>
                                            <CommandItem
                                                :value="null"
                                                @select="
                                                    () => {
                                                        selectedBranch = null;
                                                        branchOpen = false;
                                                        applyFilters();
                                                    }
                                                "
                                            >
                                                Semua Cabang
                                                <Check
                                                    :class="
                                                        cn(
                                                            'ml-auto h-4 w-4',
                                                            selectedBranch ===
                                                                null
                                                                ? 'opacity-100'
                                                                : 'opacity-0',
                                                        )
                                                    "
                                                />
                                            </CommandItem>
                                            <CommandItem
                                                v-for="b in branches"
                                                :key="b.id"
                                                :value="b.id"
                                                @select="
                                                    () => {
                                                        selectedBranch = b.id;
                                                        branchOpen = false;
                                                        applyFilters();
                                                    }
                                                "
                                            >
                                                {{ b.name }}
                                                <Check
                                                    :class="
                                                        cn(
                                                            'ml-auto h-4 w-4',
                                                            selectedBranch ===
                                                                b.id
                                                                ? 'opacity-100'
                                                                : 'opacity-0',
                                                        )
                                                    "
                                                />
                                            </CommandItem>
                                        </CommandGroup>
                                    </CommandList>
                                </Command>
                            </PopoverContent>
                        </Popover>
                    </div>

                    <!-- Type Filter -->
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-medium text-muted-foreground"
                            >Tipe</label
                        >
                        <Popover v-model:open="typeOpen">
                            <PopoverTrigger as-child>
                                <Button
                                    variant="outline"
                                    role="combobox"
                                    :aria-expanded="typeOpen"
                                    class="h-9 w-[120px] justify-between"
                                >
                                    {{
                                        selectedType === 'IN'
                                            ? 'Masuk'
                                            : selectedType === 'OUT'
                                              ? 'Keluar'
                                              : 'Semua'
                                    }}
                                    <ChevronsUpDown
                                        class="ml-2 h-4 w-4 shrink-0 opacity-50"
                                    />
                                </Button>
                            </PopoverTrigger>
                            <PopoverContent
                                class="w-[120px] p-0"
                                :avoid-collisions="true"
                            >
                                <Command>
                                    <CommandList>
                                        <CommandGroup>
                                            <CommandItem
                                                :value="null"
                                                @select="
                                                    () => {
                                                        selectedType = null;
                                                        typeOpen = false;
                                                        applyFilters();
                                                    }
                                                "
                                            >
                                                Semua
                                                <Check
                                                    :class="
                                                        cn(
                                                            'ml-auto h-4 w-4',
                                                            selectedType ===
                                                                null
                                                                ? 'opacity-100'
                                                                : 'opacity-0',
                                                        )
                                                    "
                                                />
                                            </CommandItem>
                                            <CommandItem
                                                value="IN"
                                                @select="
                                                    () => {
                                                        selectedType = 'IN';
                                                        typeOpen = false;
                                                        applyFilters();
                                                    }
                                                "
                                            >
                                                Masuk
                                                <Check
                                                    :class="
                                                        cn(
                                                            'ml-auto h-4 w-4',
                                                            selectedType ===
                                                                'IN'
                                                                ? 'opacity-100'
                                                                : 'opacity-0',
                                                        )
                                                    "
                                                />
                                            </CommandItem>
                                            <CommandItem
                                                value="OUT"
                                                @select="
                                                    () => {
                                                        selectedType = 'OUT';
                                                        typeOpen = false;
                                                        applyFilters();
                                                    }
                                                "
                                            >
                                                Keluar
                                                <Check
                                                    :class="
                                                        cn(
                                                            'ml-auto h-4 w-4',
                                                            selectedType ===
                                                                'OUT'
                                                                ? 'opacity-100'
                                                                : 'opacity-0',
                                                        )
                                                    "
                                                />
                                            </CommandItem>
                                        </CommandGroup>
                                    </CommandList>
                                </Command>
                            </PopoverContent>
                        </Popover>
                    </div>

                    <!-- Date Range -->
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-medium text-muted-foreground"
                            >Rentang Tanggal</label
                        >
                        <Popover v-model:open="dateRangeOpen">
                            <PopoverTrigger as-child>
                                <Button
                                    variant="outline"
                                    :class="
                                        cn(
                                            'h-9 w-[240px] justify-start text-left font-normal',
                                            !dateRange?.start &&
                                                'text-muted-foreground',
                                        )
                                    "
                                >
                                    <CalendarIcon class="mr-2 h-4 w-4" />
                                    {{ dateRangeDisplay }}
                                </Button>
                            </PopoverTrigger>
                            <PopoverContent
                                class="z-[100] w-auto p-0"
                                side="bottom"
                                align="center"
                                :side-offset="8"
                                :hide-when-detached="true"
                            >
                                <div class="p-3 pb-0">
                                    <RangeCalendar
                                        v-model="dateRange"
                                        :number-of-months="2"
                                        initial-focus
                                    />
                                </div>
                                <div
                                    class="mt-3 flex items-center justify-between border-t p-3"
                                >
                                    <span class="text-sm text-muted-foreground">
                                        {{
                                            dateRange?.start
                                                ? df.format(
                                                      dateRange.start.toDate(
                                                          getLocalTimeZone(),
                                                      ),
                                                  )
                                                : '-'
                                        }}
                                        -
                                        {{
                                            dateRange?.end
                                                ? df.format(
                                                      dateRange.end.toDate(
                                                          getLocalTimeZone(),
                                                      ),
                                                  )
                                                : '-'
                                        }}
                                    </span>
                                    <div class="flex gap-2">
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            @click="dateRangeOpen = false"
                                        >
                                            Batal
                                        </Button>
                                        <Button
                                            size="sm"
                                            @click="
                                                () => {
                                                    dateRangeOpen = false;
                                                    applyFilters();
                                                }
                                            "
                                            :disabled="
                                                !dateRange?.start ||
                                                !dateRange?.end
                                            "
                                        >
                                            Pilih
                                        </Button>
                                    </div>
                                </div>
                            </PopoverContent>
                        </Popover>
                    </div>

                    <!-- Reset Button -->
                    <Button
                        variant="outline"
                        size="sm"
                        @click="resetFilters"
                        class="h-9"
                    >
                        Reset
                    </Button>
                </div>
            </div>

            <!-- Table -->
            <DataTable
                :columns="columns"
                :data="movements"
            />
        </div>
    </AppLayout>
</template>
