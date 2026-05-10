<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import stockHistory from '@/routes/stockHistory';
import productsRoutes from '@/routes/products';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { h } from 'vue';

import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Package, ArrowUp, ArrowDown, User, ArrowLeft } from 'lucide-vue-next';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
import type { ColumnDef } from '@tanstack/vue-table';
import DataTable from '@/components/ui/data-table/DataTable.vue';
import { format } from 'date-fns';
import { id } from 'date-fns/locale';

type StockMovement = {
  id: number;
  product_id: number;
  branch_id: number;
  type: 'IN' | 'OUT';
  qty: number;
  reason: string | null;
  note: string | null;
  created_at: string;
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
  image: string | null;
};

const props = defineProps<{
  product: Product;
  movements: StockMovement[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Produk',
    href: productsRoutes.index().url,
  },
  {
    title: props.product.name,
    href: stockHistory.byProduct(props.product.id).url,
  },
];

const columns: ColumnDef<StockMovement>[] = [
  {
    accessorKey: 'created_at',
    header: 'Waktu',
    cell: ({ row }) => {
      const date = new Date(row.getValue('created_at') as string);
      return h('div', { class: 'text-sm' }, [
        h('div', {}, format(date, 'dd MMM yyyy', { locale: id })),
        h('div', { class: 'text-xs text-muted-foreground' }, format(date, 'HH:mm', { locale: id })),
      ]);
    },
  },
  {
    accessorKey: 'branch',
    header: 'Cabang',
    cell: ({ row }) => h('div', {}, [
      h('div', { class: 'font-medium' }, row.original.branch.name),
      h('div', { class: 'text-xs text-muted-foreground' }, row.original.branch.code),
    ]),
  },
  {
    accessorKey: 'type',
    header: 'Tipe',
    cell: ({ row }) => {
      const type = row.getValue('type') as 'IN' | 'OUT';
      return h(Badge, {
        variant: type === 'IN' ? 'default' : 'destructive',
        class: 'flex items-center gap-1 w-fit',
      }, () => [
        type === 'IN' ? h(ArrowUp, { class: 'h-3 w-3' }) : h(ArrowDown, { class: 'h-3 w-3' }),
        type === 'IN' ? 'Masuk' : 'Keluar',
      ]);
    },
  },
  {
    accessorKey: 'qty',
    header: 'Jumlah',
    cell: ({ row }) => h('div', { class: 'font-mono font-medium' }, row.getValue('qty')),
  },
  {
    accessorKey: 'reason',
    header: 'Alasan',
    cell: ({ row }) => h('div', { class: 'text-sm text-muted-foreground max-w-[250px] truncate' },
      row.getValue('reason') || '-'),
  },
  {
    accessorKey: 'note',
    header: 'Catatan',
    cell: ({ row }) => h('div', { class: 'text-sm text-muted-foreground max-w-[200px] truncate' },
      row.getValue('note') || '-'),
  },
  {
    accessorKey: 'user',
    header: 'Oleh',
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
  <Head :title="`Riwayat Stok - ${product.name}`" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex flex-col gap-6 p-4">
      <!-- Header -->
      <div class="flex items-center gap-4">
        <Button variant="outline" size="icon" as-child>
          <Link :href="productsRoutes.index().url">
            <ArrowLeft class="h-4 w-4" />
          </Link>
        </Button>
        <div class="flex items-center gap-3">
          <img
            v-if="product.image"
            :src="`/storage/${product.image}`"
            class="h-12 w-12 rounded-lg object-cover border"
            :alt="product.name"
          />
          <div v-else class="h-12 w-12 rounded-lg bg-gray-100 flex items-center justify-center">
            <Package class="h-6 w-6 text-gray-400" />
          </div>
          <div>
            <h2 class="text-lg font-semibold">{{ product.name }}</h2>
            <p class="text-sm text-muted-foreground">
              Riwayat perubahan stok per produk
            </p>
          </div>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-3 gap-4">
        <div class="rounded-xl border p-4">
          <div class="text-sm text-muted-foreground">Total Masuk</div>
          <div class="text-2xl font-bold text-green-600">
            {{ movements.filter(m => m.type === 'IN').reduce((a, m) => a + m.qty, 0) }}
          </div>
        </div>
        <div class="rounded-xl border p-4">
          <div class="text-sm text-muted-foreground">Total Keluar</div>
          <div class="text-2xl font-bold text-red-600">
            {{ movements.filter(m => m.type === 'OUT').reduce((a, m) => a + m.qty, 0) }}
          </div>
        </div>
        <div class="rounded-xl border p-4">
          <div class="text-sm text-muted-foreground">Total Perubahan</div>
          <div class="text-2xl font-bold">
            {{ movements.length }}
          </div>
        </div>
      </div>

      <!-- Table -->
      <DataTable
        :columns="columns"
        :data="movements"
        search-key="branch"
        search-placeholder="Cari cabang..."
      />
    </div>
  </AppLayout>
</template>
