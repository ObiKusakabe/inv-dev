<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import { ref, h } from 'vue'

import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Checkbox } from '@/components/ui/checkbox'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogDescription,
  DialogFooter,
} from '@/components/ui/dialog'

import AppLayout from '@/layouts/AppLayout.vue'
import SettingsLayout from '@/layouts/settings/Layout.vue'
import branches from '@/routes/branches'
import { type BreadcrumbItem } from '@/types'
import type { ColumnDef } from '@tanstack/vue-table'
import DataTable from '@/components/ui/data-table/DataTable.vue'

type Branch = {
  id: number
  name: string
  code: string
  is_active: boolean
  created_at: string
}

const props = defineProps<{
  branches: Branch[]
}>()

const breadcrumbItems: BreadcrumbItem[] = [
  {
    title: 'Cabang',
    href: branches.index().url,
  },
]

// Dialog state
const isDialogOpen = ref(false)
const mode = ref<'create'>('create')

const form = useForm<{
  name: string
  code: string
}>({
  name: '',
  code: '',
})

function resetForm() {
  form.reset()
  form.clearErrors()
}

function openCreateDialog() {
  resetForm()
  isDialogOpen.value = true
}

function submit() {
  form.post(branches.store().url, {
    preserveScroll: true,
    onSuccess: () => {
      resetForm()
      isDialogOpen.value = false
    },
  })
}

const columns: ColumnDef<Branch>[] = [
  {
    id: 'select',
    header: ({ table }) =>
      h(Checkbox, {
        checked: table.getIsAllPageRowsSelected() || (table.getIsSomePageRowsSelected() && 'indeterminate'),
        'onUpdate:checked': (value: boolean) => table.toggleAllPageRowsSelected(!!value),
        ariaLabel: 'Select all',
      }),
    cell: ({ row }) =>
      h(Checkbox, {
        checked: row.getIsSelected(),
        'onUpdate:checked': (value: boolean) => row.toggleSelected(!!value),
        ariaLabel: 'Select row',
      }),
    enableSorting: false,
    enableHiding: false,
  },
  {
    accessorKey: 'name',
    header: 'Nama',
    cell: ({ row }) => h('div', { class: 'font-medium' }, row.getValue('name')),
  },
  {
    accessorKey: 'code',
    header: 'Kode',
    cell: ({ row }) => h('div', { class: 'font-mono text-sm' }, row.getValue('code')),
  },
  {
    accessorKey: 'is_active',
    header: 'Status',
    cell: ({ row }) => {
      const isActive = row.getValue('is_active') as boolean
      return h('span', {
        class: `text-sm ${isActive ? 'text-green-600 font-medium' : 'text-muted-foreground'}`
      }, isActive ? 'Aktif' : 'Nonaktif')
    },
  },
]
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbItems">
    <Head title="Cabang" />

    <SettingsLayout>
      <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
          <div>
            <h2 class="text-lg font-semibold">Cabang</h2>
            <p class="text-sm text-muted-foreground">Kelola data cabang toko.</p>
          </div>
          <Button @click="openCreateDialog">+ Tambah Cabang</Button>
        </div>

        <!-- Table -->
        <div class="rounded-xl border">
          <div class="p-4 border-b">
            <h3 class="text-base font-semibold">Daftar Cabang</h3>
          </div>
          <DataTable :columns="columns" :data="props.branches" search-key="name"
            search-placeholder="Cari cabang..." />
        </div>

        <!-- Create Dialog -->
        <Dialog v-model:open="isDialogOpen">
          <DialogContent class="sm:max-w-[420px]">
            <DialogHeader>
              <DialogTitle>Tambah Cabang</DialogTitle>
              <DialogDescription>
                Buat cabang baru untuk toko Anda.
              </DialogDescription>
            </DialogHeader>

            <div class="py-4 space-y-4">
              <div class="flex flex-col gap-1">
                <label class="text-sm font-medium">Nama Cabang</label>
                <Input v-model="form.name" placeholder="Misal: Cabang Utama, Cabang Bandung..." />
                <div v-if="form.errors.name" class="text-sm text-red-600">
                  {{ form.errors.name }}
                </div>
              </div>

              <div class="flex flex-col gap-1">
                <label class="text-sm font-medium">Kode (opsional)</label>
                <Input v-model="form.code" placeholder="Misal: CBG1, MAIN, BD1..." />
                <div v-if="form.errors.code" class="text-sm text-red-600">
                  {{ form.errors.code }}
                </div>
                <p class="text-xs text-muted-foreground">
                  Kosongkan untuk generate otomatis (CBG1, CBG2, ...)
                </p>
              </div>
            </div>

            <DialogFooter class="flex flex-row gap-2 justify-end">
              <Button variant="outline" @click="isDialogOpen = false">Batal</Button>
              <Button :disabled="form.processing" @click="submit">
                {{ form.processing ? 'Menyimpan…' : 'Simpan' }}
              </Button>
            </DialogFooter>
          </DialogContent>
        </Dialog>
      </div>
    </SettingsLayout>
  </AppLayout>
</template>