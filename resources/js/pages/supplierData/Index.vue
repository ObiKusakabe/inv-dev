<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import supplierData from '@/routes/supplierData'
import { type BreadcrumbItem } from '@/types'
import { Head, router, useForm } from '@inertiajs/vue3'
import { ref, h } from 'vue'

import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Textarea } from '@/components/ui/textarea'
import { toast } from 'vue-sonner'

import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from '@/components/ui/alert-dialog'
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogDescription,
  DialogFooter,
} from '@/components/ui/dialog'

import type { ColumnDef } from '@tanstack/vue-table'
import DataTable from '@/components/ui/data-table/DataTable.vue'

import { Checkbox } from '@/components/ui/checkbox'
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'

type SupplierRow = {
    id: number
    name: string
    contact: string | null
    note: string | null
    created_at: string
}

const columns: ColumnDef<SupplierRow>[] = [
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
        id: 'name',
        header: () => 'Nama',
        cell: ({ row }) => h('div', { class: 'font-medium' }, row.getValue('name')),
    },
    {
        accessorKey: 'contact',
        id: 'contact',
        header: () => 'Kontak',
        cell: ({ row }) => h('div', {}, (row.getValue('contact') as string) ?? '-'),
    },
    {
        accessorKey: 'note',
        id: 'note',
        header: () => 'Catatan',
        cell: ({ row }) =>
            h('div', { class: 'max-w-[420px] truncate' }, (row.getValue('note') as string) ?? '-'),
    },
    {
        id: 'actions',
        header: () => h('div', { class: 'text-right' }, 'Aksi'),
        cell: ({ row }) =>
            h('div', { class: 'flex justify-end' }, [
                h(
                    DropdownMenu,
                    {},
                    {
                        default: () => [
                            h(
                                DropdownMenuTrigger,
                                { asChild: true },
                                {
                                    default: () => h(Button, { variant: 'outline', size: 'sm' }, () => 'Menu'),
                                }
                            ),
                            h(
                                DropdownMenuContent,
                                { align: 'end' },
                                {
                                    default: () => [
                                        h(DropdownMenuItem, { onClick: () => startEdit(row.original) }, () => 'Edit'),
                                        h(DropdownMenuItem, { onClick: () => openDeleteDialog(row.original), class: 'text-red-600' }, () => 'Hapus'),
                                    ],
                                }
                            ),
                        ],
                    }
                ),
            ]),
        enableSorting: false,
    },
]

const props = defineProps<{
    suppliers: SupplierRow[]
}>()

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Supplier', href: supplierData.index().url },
]

const mode = ref<'create' | 'edit'>('create')
const editingId = ref<number | null>(null)
const isDialogOpen = ref(false)

const form = useForm<{
    name: string
    contact: string
    note: string
}>({
    name: '',
    contact: '',
    note: '',
})

function resetForm() {
    form.reset()
    form.clearErrors()
    mode.value = 'create'
    editingId.value = null
    isDialogOpen.value = false
}

function openCreateDialog() {
    resetForm()
    isDialogOpen.value = true
}

function startEdit(row: SupplierRow) {
    mode.value = 'edit'
    editingId.value = row.id
    form.name = row.name
    form.contact = row.contact ?? ''
    form.note = row.note ?? ''
    form.clearErrors()
    isDialogOpen.value = true
}

function submit() {
    if (mode.value === 'create') {
        const toastId = toast.loading('Menambah data supplier...')
        form.post(supplierData.store().url, {
            preserveScroll: true,
            onSuccess: () => {
                toast.dismiss(toastId)
                setTimeout(() => toast.success('Data supplier berhasil ditambah'), 100)
                resetForm()
                isDialogOpen.value = false
            },
            onError: (errors) => {
                toast.dismiss(toastId)
                const msg = typeof errors === 'object' ? Object.values(errors).flat().join(', ') : 'Terjadi kesalahan'
                setTimeout(() => toast.error('Gagal menambah data', { description: msg }), 100)
            }
        })
        return
    }

    if (!editingId.value) return
    const toastId = toast.loading('Memperbarui data supplier...')
    form.put(supplierData.update(editingId.value).url, {
        preserveScroll: true,
        onSuccess: () => {
            toast.dismiss(toastId)
            setTimeout(() => toast.success('Data supplier berhasil diperbarui'), 100)
            resetForm()
            isDialogOpen.value = false
        },
        onError: (errors) => {
            toast.dismiss(toastId)
            const msg = typeof errors === 'object' ? Object.values(errors).flat().join(', ') : 'Terjadi kesalahan'
            setTimeout(() => toast.error('Gagal memperbarui data', { description: msg }), 100)
        }
    })
}

// Alert Dialog state for delete confirmation
const supplierToDelete = ref<SupplierRow | null>(null)
const isDeleteDialogOpen = ref(false)

function openDeleteDialog(row: SupplierRow) {
    supplierToDelete.value = row
    isDeleteDialogOpen.value = true
}

function confirmDelete() {
    if (!supplierToDelete.value) return
    
    const toastId = toast.loading('Menghapus data supplier...')
    
    router.delete(supplierData.destroy(supplierToDelete.value.id).url, {
        preserveScroll: true,
        onSuccess: () => {
            toast.dismiss(toastId)
            setTimeout(() => {
                toast.success('Data supplier berhasil dihapus')
            }, 100)
            supplierToDelete.value = null
            isDeleteDialogOpen.value = false
        },
        onError: (errors) => {
            toast.dismiss(toastId)
            const errorMessage = typeof errors === 'object' ? Object.values(errors).flat().join(', ') : 'Terjadi kesalahan'
            setTimeout(() => {
                toast.error('Gagal menghapus data supplier', { description: errorMessage })
            }, 100)
        }
    })
}
</script>

<template>

    <Head title="Supplier" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold">Supplier</h2>
                    <p class="text-sm text-muted-foreground">
                        Simpan data supplier untuk stok & pembelian.
                    </p>
                </div>
                <Button @click="openCreateDialog">+ Tambah Supplier</Button>
            </div>

            <!-- Table -->
            <DataTable :columns="columns" :data="props.suppliers" search-key="name"
                search-placeholder="Cari supplier..." />

                <!-- Create/Edit Dialog -->
                <Dialog v-model:open="isDialogOpen">
                    <DialogContent class="sm:max-w-[500px]">
                        <DialogHeader>
                            <DialogTitle>{{ mode === 'create' ? 'Tambah Supplier' : 'Edit Supplier' }}</DialogTitle>
                            <DialogDescription>
                                Simpan data supplier untuk stok & pembelian.
                            </DialogDescription>
                        </DialogHeader>

                        <div class="py-4 space-y-4">
                            <div class="flex flex-col gap-1">
                                <label class="text-sm font-medium">Nama</label>
                                <Input v-model="form.name" placeholder="Misal: PT Sumber Fashion" />
                                <div v-if="form.errors.name" class="text-sm text-red-600">
                                    {{ form.errors.name }}
                                </div>
                            </div>

                            <div class="flex flex-col gap-1">
                                <label class="text-sm font-medium">Kontak (opsional)</label>
                                <Input v-model="form.contact" placeholder="WA / Email / Nama PIC" />
                                <div v-if="form.errors.contact" class="text-sm text-red-600">
                                    {{ form.errors.contact }}
                                </div>
                            </div>

                            <div class="flex flex-col gap-1">
                                <label class="text-sm font-medium">Catatan (opsional)</label>
                                <Textarea v-model="form.note" placeholder="Alamat, rekening, terms, dll…" />
                                <div v-if="form.errors.note" class="text-sm text-red-600">
                                    {{ form.errors.note }}
                                </div>
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

                <!-- Delete Confirmation Alert Dialog -->
                <AlertDialog v-model:open="isDeleteDialogOpen">
                    <AlertDialogContent>
                        <AlertDialogHeader>
                            <AlertDialogTitle>Hapus Supplier?</AlertDialogTitle>
                            <AlertDialogDescription>
                                Apakah Anda yakin ingin menghapus supplier "{{ supplierToDelete?.name }}"?
                                <br />Semua produk yang terhubung ke supplier ini akan kehilangan data supplier.
                                Tindakan ini tidak dapat dibatalkan.
                            </AlertDialogDescription>
                        </AlertDialogHeader>
                        <AlertDialogFooter>
                            <AlertDialogCancel @click="supplierToDelete = null">Batal</AlertDialogCancel>
                            <AlertDialogAction 
                                @click="confirmDelete" 
                                class="bg-red-600 hover:bg-red-700 text-white"
                            >
                                Hapus
                            </AlertDialogAction>
                        </AlertDialogFooter>
                    </AlertDialogContent>
                </AlertDialog>
            </div>
    </AppLayout>
</template>
