<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import category from '@/routes/category'
import { type BreadcrumbItem } from '@/types'
import { Head, router, useForm } from '@inertiajs/vue3'
import { computed, ref, h, watch } from 'vue'
import { toast } from 'vue-sonner'
import { cn } from '@/lib/utils'

import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Spinner } from '@/components/ui/spinner'
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
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from '@/components/ui/popover'
import {
  Command,
  CommandEmpty,
  CommandGroup,
  CommandInput,
  CommandItem,
  CommandList,
} from '@/components/ui/command'
import { Check, ChevronsUpDown } from 'lucide-vue-next'
import type { ColumnDef } from '@tanstack/vue-table'
import DataTable from '@/components/ui/data-table/DataTable.vue'

type Parent = { id: number; name: string }
type CategoryRow = {
  id: number
  name: string
  parent_id: number | null
  parent?: Parent | null
  created_at: string
}

const props = defineProps<{
  categories: CategoryRow[]
  parents: Parent[]
}>()

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Kategori', href: category.index().url },
]

const columns: ColumnDef<CategoryRow>[] = [
  {
    accessorKey: 'id',
    header: 'ID',
    cell: ({ row }) => h('span', {}, row.original.id),
  },
  {
    accessorKey: 'name',
    header: 'Nama',
    cell: ({ row }) => h('span', { class: 'font-medium' }, row.original.name),
  },
  {
    accessorKey: 'parent',
    header: 'Parent',
    cell: ({ row }) => h('span', {}, row.original.parent?.name ?? '-'),
  },
  {
    id: 'actions',
    header: 'Aksi',
    cell: ({ row }) => h('div', { class: 'flex justify-end gap-2' }, [
      h(Button, {
        variant: 'outline',
        size: 'sm',
        onClick: () => startEdit(row.original),
      }, () => 'Edit'),
      h(Button, {
        variant: 'destructive',
        size: 'sm',
        onClick: () => openDeleteDialog(row.original),
      }, () => 'Hapus'),
    ]),
  },
]

const mode = ref<'create' | 'edit'>('create')
const editingId = ref<number | null>(null)

const form = useForm<{
  name: string
  parent_id: number | null
}>({
  name: '',
  parent_id: null,
})

const parentOptions = computed(() => props.parents)

// Dialog state - must be before watch
const isDialogOpen = ref(false)

// Popover state for parent
const parentOpen = ref(false)

// Watch dialog close to reset popover
watch(isDialogOpen, (open) => {
  if (!open) parentOpen.value = false
})

function resetForm() {
  parentOpen.value = false
  form.reset()
  form.clearErrors()
  mode.value = 'create'
  editingId.value = null
}

function openCreateDialog() {
  resetForm()
  isDialogOpen.value = true
}

function startEdit(row: CategoryRow) {
  mode.value = 'edit'
  editingId.value = row.id
  form.name = row.name
  form.parent_id = row.parent_id
  form.clearErrors()
  parentOpen.value = false
  isDialogOpen.value = true
}

function submit() {
  if (mode.value === 'create') {
    form.post(category.store().url, {
      preserveScroll: true,
      onSuccess: () => {
        resetForm()
        isDialogOpen.value = false
        toast.success('Kategori berhasil ditambahkan', { duration: 4000 })
      },
      onError: (errors) => {
        toast.error('Gagal menambahkan kategori', { description: errors.message || 'Terjadi kesalahan.', duration: 4000 })
      },
    })
    return
  }

  if (!editingId.value) return
  form.put(category.update(editingId.value).url, {
    preserveScroll: true,
    onSuccess: () => {
      resetForm()
      isDialogOpen.value = false
      toast.success('Kategori berhasil diperbarui', { duration: 6000 })
    },
    onError: (errors) => {
      toast.error('Gagal memperbarui kategori', { description: errors.message || 'Terjadi kesalahan.', duration: 6000 })
    },
  })
}

// Alert Dialog state for delete confirmation
const categoryToDelete = ref<CategoryRow | null>(null)
const isDeleteDialogOpen = ref(false)
const isDeleting = ref(false)

function openDeleteDialog(row: CategoryRow) {
  categoryToDelete.value = row
  isDeleteDialogOpen.value = true
}

function confirmDelete() {
  if (!categoryToDelete.value || isDeleting.value) return
  
  isDeleting.value = true
  const categoryName = categoryToDelete.value.name;
  router.delete(category.destroy(categoryToDelete.value.id).url, {
    preserveScroll: true,
    onSuccess: () => {
      categoryToDelete.value = null
      isDeleteDialogOpen.value = false
      isDeleting.value = false
      toast.success('Kategori berhasil dihapus', { description: `"${categoryName}" telah dihapus.`, duration: 4000 })
      router.reload({ only: ['categories'] })
    },
    onError: (errors) => {
      isDeleting.value = false
      toast.error('Gagal menghapus kategori', { description: errors.message || 'Terjadi kesalahan.' })
    },
  })
}
</script>

<template>
  <Head title="Kategori" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex flex-col gap-6 p-4">
      <!-- Header with Add Button -->
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-lg font-semibold">Kategori</h2>
          <p class="text-sm text-muted-foreground">
            Kelola kategori & sub-kategori (parent).
          </p>
        </div>
        <Button @click="openCreateDialog">
          + Tambah Kategori
        </Button>
      </div>

      <!-- Table -->
      <DataTable
        :columns="columns"
        :data="props.categories"
        search-key="name"
        search-placeholder="Cari kategori..."
      />

      <!-- Create/Edit Dialog -->
        <Dialog v-model:open="isDialogOpen">
          <DialogContent class="sm:max-w-[420px]">
            <DialogHeader>
              <DialogTitle>{{ mode === 'create' ? 'Tambah Kategori' : 'Edit Kategori' }}</DialogTitle>
              <DialogDescription>
                Kelola kategori dan sub-kategori (parent).
              </DialogDescription>
            </DialogHeader>

            <div class="space-y-4 py-4">
              <div class="space-y-2">
                <label class="text-sm font-medium">Nama</label>
                <Input v-model="form.name" placeholder="Misal: Atasan / Bawahan / Outer" />
                <div v-if="form.errors.name" class="text-sm text-red-600">
                  {{ form.errors.name }}
                </div>
              </div>

              <div class="space-y-2">
                <label class="text-sm font-medium">Parent (opsional)</label>
                <Popover v-model:open="parentOpen">
                  <PopoverTrigger as-child>
                    <Button variant="outline" role="combobox" :aria-expanded="parentOpen" class="w-full justify-between">
                      {{ parentOptions.find(p => p.id === form.parent_id)?.name || "- Tidak ada -" }}
                      <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                    </Button>
                  </PopoverTrigger>
                  <PopoverContent class="w-full p-0" :avoid-collisions="true" :collision-padding="10">
                    <Command>
                      <CommandInput placeholder="Cari parent..." />
                      <CommandList>
                        <CommandEmpty>Tidak ada parent.</CommandEmpty>
                        <CommandGroup>
                          <CommandItem
                            :value="null"
                            @select="() => { form.parent_id = null; parentOpen = false }"
                          >
                            - Tidak ada -
                            <Check :class="cn('ml-auto h-4 w-4', form.parent_id === null ? 'opacity-100' : 'opacity-0')" />
                          </CommandItem>
                          <CommandItem
                            v-for="p in parentOptions"
                            :key="p.id"
                            :value="p.id"
                            @select="() => { form.parent_id = p.id; parentOpen = false }"
                          >
                            {{ p.name }}
                            <Check :class="cn('ml-auto h-4 w-4', form.parent_id === p.id ? 'opacity-100' : 'opacity-0')" />
                          </CommandItem>
                        </CommandGroup>
                      </CommandList>
                    </Command>
                  </PopoverContent>
                </Popover>
                <div v-if="form.errors.parent_id" class="text-sm text-red-600">
                  {{ form.errors.parent_id }}
                </div>
              </div>
            </div>

            <DialogFooter>
              <Button variant="outline" @click="isDialogOpen = false" :disabled="form.processing">Batal</Button>
              <Button :disabled="form.processing" @click="submit">
                <Spinner v-if="form.processing" class="mr-2 size-4" />
                {{ form.processing ? 'Menyimpan…' : 'Simpan' }}
              </Button>
            </DialogFooter>
          </DialogContent>
        </Dialog>

        <!-- Delete Confirmation Alert Dialog -->
        <AlertDialog v-model:open="isDeleteDialogOpen">
          <AlertDialogContent>
            <AlertDialogHeader>
              <AlertDialogTitle>Hapus Kategori?</AlertDialogTitle>
              <AlertDialogDescription>
                Apakah Anda yakin ingin menghapus kategori "{{ categoryToDelete?.name }}"?
                <br />Semua sub-kategori akan kehilangan parent. Tindakan ini tidak dapat dibatalkan.
              </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
              <AlertDialogCancel @click="categoryToDelete = null" :disabled="isDeleting">Batal</AlertDialogCancel>
              <AlertDialogAction 
                @click="confirmDelete" 
                :disabled="isDeleting"
                class="bg-red-600 hover:bg-red-700 text-white"
              >
                <Spinner v-if="isDeleting" class="mr-2 size-4" />
                {{ isDeleting ? 'Menghapus…' : 'Hapus' }}
              </AlertDialogAction>
            </AlertDialogFooter>
          </AlertDialogContent>
        </AlertDialog>
    </div>
  </AppLayout>
</template>