<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import invoices from '@/routes/invoices'
import { type BreadcrumbItem } from '@/types'
import { Head, router } from '@inertiajs/vue3'
import { computed, ref, h, watch } from 'vue'
import { cn } from '@/lib/utils'
import type { DateRange } from 'reka-ui'
import type { DateValue } from '@internationalized/date'
import { DateFormatter, parseDate, getLocalTimeZone } from '@internationalized/date'
import { format } from 'date-fns'
import { id } from 'date-fns/locale'
import type { ColumnDef } from '@tanstack/vue-table'
import DataTable from '@/components/ui/data-table/DataTable.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Badge } from '@/components/ui/badge'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from '@/components/ui/popover'
import { Calendar } from '@/components/ui/calendar'
import { RangeCalendar } from '@/components/ui/range-calendar'
import { FileText, Eye, CalendarIcon } from 'lucide-vue-next'

type Branch = { id: number; name: string }
type Customer = { id: number; name: string }
type Product = { id: number; name: string }
type InvoiceItem = { id: number; product: Product; qty: number; price: number; subtotal: number }
type Invoice = {
  id: number
  invoice_number: string
  invoice_date: string
  branch: Branch
  customer: Customer | null
  items: InvoiceItem[]
  subtotal: number
  discount: number
  tax: number
  total: number
  status: 'PAID' | 'UNPAID' | 'CANCELLED'
  payment_method: string
}

const props = defineProps<{
  invoices: Invoice[]
  branches: Branch[]
  filters: { branch_id?: string; date_from?: string; date_to?: string; status?: string; search?: string }
}>()

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Transaksi', href: invoices.index().url }]

const formatDate = (date: string) => format(new Date(date), 'dd MMM yyyy HH:mm', { locale: id })

const formatRupiah = (amount: number) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount)

const getStatusBadge = (status: string) => {
  const statusUpper = status.toUpperCase();
  const variants: Record<string, { variant: 'default' | 'secondary' | 'destructive' | 'outline'; class: string; label: string }> = {
    PAID: { variant: 'default', class: 'bg-emerald-500 hover:bg-emerald-600 text-white', label: 'Lunas' },
    UNPAID: { variant: 'secondary', class: '', label: 'Belum Lunas' },
    CANCELLED: { variant: 'destructive', class: '', label: 'Dibatalkan' }
  };
  const config = variants[statusUpper] || { variant: 'outline', class: '', label: status };
  return h(Badge, { variant: config.variant, class: config.class }, () => config.label);
}

const columns: ColumnDef<Invoice>[] = [
  { accessorKey: 'invoice_number', header: 'No. Invoice', cell: ({ row }) => h('span', { class: 'font-mono font-medium' }, row.original.invoice_number) },
  { accessorKey: 'invoice_date', header: 'Tanggal', cell: ({ row }) => h('span', {}, formatDate(row.original.invoice_date)) },
  { accessorKey: 'branch', header: 'Cabang', cell: ({ row }) => h('span', {}, row.original.branch?.name ?? '-') },
  { accessorKey: 'customer', header: 'Pelanggan', cell: ({ row }) => h('span', {}, row.original.customer?.name ?? 'Umum') },
  { accessorKey: 'total', header: 'Total', cell: ({ row }) => h('span', { class: 'font-medium' }, formatRupiah(row.original.total)) },
  { accessorKey: 'status', header: 'Status', cell: ({ row }) => getStatusBadge(row.original.status) },
  { id: 'actions', header: 'Aksi', cell: ({ row }) => h('a', { href: invoices.show(row.original.id).url, class: 'inline-flex items-center justify-center rounded-md text-sm font-medium border border-input bg-background hover:bg-accent hover:text-accent-foreground h-8 w-8 p-0' }, h(Eye, { class: 'h-4 w-4' })) }
]

const branchId = ref(props.filters.branch_id || 'all')
const dateFrom = ref(props.filters.date_from ?? '')
const dateTo = ref(props.filters.date_to ?? '')
const status = ref(props.filters.status || 'all')
const search = ref(props.filters.search ?? '')

// Date picker popover state
const dateRangeOpen = ref(false)

// Date formatter
const df = new DateFormatter('id-ID', { dateStyle: 'medium' })

// Range calendar state
const dateRange = ref<DateRange>({
  start: dateFrom.value ? parseDate(dateFrom.value) : undefined,
  end: dateTo.value ? parseDate(dateTo.value) : undefined,
})

// Watch dateRange changes and update refs
watch(dateRange, (newRange) => {
  if (newRange?.start) {
    dateFrom.value = newRange.start.toString()
  } else {
    dateFrom.value = ''
  }
  if (newRange?.end) {
    dateTo.value = newRange.end.toString()
  } else {
    dateTo.value = ''
  }
}, { deep: true })

// Format date range for display
const dateRangeDisplay = computed(() => {
  if (dateRange.value?.start && dateRange.value?.end) {
    const start = df.format(dateRange.value.start.toDate(getLocalTimeZone()))
    const end = df.format(dateRange.value.end.toDate(getLocalTimeZone()))
    return `${start} - ${end}`
  }
  if (dateRange.value?.start) {
    return df.format(dateRange.value.start.toDate(getLocalTimeZone()))
  }
  return 'Pilih rentang'
})

const applyFilters = () => {
  router.get(invoices.index().url, {
    branch_id: branchId.value === 'all' ? undefined : branchId.value,
    date_from: dateFrom.value || undefined,
    date_to: dateTo.value || undefined,
    status: status.value === 'all' ? undefined : status.value,
    search: search.value || undefined,
  }, { preserveState: true, preserveScroll: true })
}

const resetFilters = () => {
  branchId.value = 'all'
  dateFrom.value = ''
  dateTo.value = ''
  dateRange.value = { start: undefined, end: undefined }
  status.value = 'all'
  search.value = ''
  applyFilters()
}

const totalRevenue = computed(() => props.invoices.filter(i => i.status === 'PAID').reduce((sum, i) => sum + i.total, 0))
const totalTransactions = computed(() => props.invoices.length)
</script>

<template>
  <Head title="Transaksi" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex flex-col gap-6 p-4">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-lg font-semibold">Daftar Transaksi</h2>
          <p class="text-sm text-muted-foreground">Riwayat transaksi & invoice penjualan.</p>
        </div>
        <Button variant="outline" @click="window.print()">
          <FileText class="mr-2 h-4 w-4" /> Export
        </Button>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-2 gap-4">
        <div class="rounded-xl border p-4">
          <div class="text-sm text-muted-foreground">Total Transaksi</div>
          <div class="text-2xl font-bold">{{ totalTransactions }}</div>
        </div>
        <div class="rounded-xl border p-4">
          <div class="text-sm text-muted-foreground">Total Pendapatan</div>
          <div class="text-2xl font-bold text-green-600">{{ formatRupiah(totalRevenue) }}</div>
        </div>
      </div>

      <!-- Filters -->
      <div class="flex flex-wrap items-end gap-3">
        <div class="w-48">
          <label class="text-xs text-muted-foreground mb-1 block">Cabang</label>
          <Select v-model="branchId" @update:model-value="applyFilters">
            <SelectTrigger><SelectValue placeholder="Semua Cabang" /></SelectTrigger>
            <SelectContent>
              <SelectItem value="all">Semua Cabang</SelectItem>
              <SelectItem v-for="b in branches" :key="b.id" :value="String(b.id)">{{ b.name }}</SelectItem>
            </SelectContent>
          </Select>
        </div>
        <div class="w-[200px]">
          <label class="text-xs text-muted-foreground mb-1 block">Rentang Tanggal</label>
          <Popover v-model:open="dateRangeOpen">
            <PopoverTrigger as-child>
              <Button variant="outline" :class="cn('w-full justify-start text-left font-normal h-9', !dateRange?.start && 'text-muted-foreground')">
                <CalendarIcon class="mr-2 h-4 w-4" />
                {{ dateRangeDisplay }}
              </Button>
            </PopoverTrigger>
            <PopoverContent class="w-auto p-0 z-[100]" side="bottom" align="center" :side-offset="8" :hide-when-detached="true">
              <div class="p-3 pb-0">
                <RangeCalendar
                  v-model="dateRange"
                  :number-of-months="2"
                  initial-focus
                />
              </div>
              <div class="flex items-center justify-between p-3 border-t mt-3">
                <span class="text-sm text-muted-foreground">
                  {{ dateRange?.start ? df.format(dateRange.start.toDate(getLocalTimeZone())) : '-' }} - {{ dateRange?.end ? df.format(dateRange.end.toDate(getLocalTimeZone())) : '-' }}
                </span>
                <div class="flex gap-2">
                  <Button variant="ghost" size="sm" @click="dateRangeOpen = false">
                    Batal
                  </Button>
                  <Button size="sm" @click="() => { dateRangeOpen = false; applyFilters(); }" :disabled="!dateRange?.start || !dateRange?.end">
                    Pilih
                  </Button>
                </div>
              </div>
            </PopoverContent>
          </Popover>
        </div>
        <div class="w-40">
          <label class="text-xs text-muted-foreground mb-1 block">Status</label>
          <Select v-model="status" @update:model-value="applyFilters">
            <SelectTrigger><SelectValue placeholder="Semua Status" /></SelectTrigger>
            <SelectContent>
              <SelectItem value="all">Semua Status</SelectItem>
              <SelectItem value="PAID">Lunas</SelectItem>
              <SelectItem value="UNPAID">Belum Lunas</SelectItem>
              <SelectItem value="CANCELLED">Batal</SelectItem>
            </SelectContent>
          </Select>
        </div>
        <div class="flex-1 min-w-[200px]">
          <label class="text-xs text-muted-foreground mb-1 block">Cari No. Invoice</label>
          <Input v-model="search" placeholder="INV-..." @keyup.enter="applyFilters" />
        </div>
        <Button variant="outline" @click="resetFilters">Reset</Button>
      </div>

      <!-- Table -->
      <DataTable :columns="columns" :data="props.invoices" search-key="invoice_number" search-placeholder="Cari invoice..." />
    </div>
  </AppLayout>
</template>