<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
// import products from '@/routes/products'
// import productRoutes from '@/routes/products'
import productsRoutes from '@/routes/products'
import stockHistory from '@/routes/stockHistory'
import { type BreadcrumbItem } from '@/types'
import { Head, router, useForm } from '@inertiajs/vue3'
import { computed, ref, h, watch } from 'vue'
import { toast } from 'vue-sonner'
import { cn } from '@/lib/utils'

// Format currency to IDR
function formatIDR(value: number | string | null): string {
  if (value === null || value === undefined) return 'Rp 0'
  const num = typeof value === 'string' ? parseFloat(value) : value
  if (isNaN(num)) return 'Rp 0'
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(num)
}

import DataTable from '@/components/ui/data-table/DataTable.vue'
import type { ColumnDef } from '@tanstack/vue-table'

import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Checkbox } from '@/components/ui/checkbox'
import {
  NumberField,
  NumberFieldContent,
  NumberFieldDecrement,
  NumberFieldIncrement,
  NumberFieldInput,
} from '@/components/ui/number-field'
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
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
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

type Option = { id: number; name: string }

type ProductRow = {
  id: number
  name: string
  category_id: number
  supplier_id: number
  size: string
  stock: number
  min_stock: number
  is_consignment: boolean
  supplier_price: string | number | null
  sell_price: string | number
  image: string | null
  category?: Option | null
  supplier?: Option | null
  created_at: string
}

const props = defineProps<{
  products: ProductRow[]
  categories: Option[]
  suppliers: Option[]
}>()

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Produk', href: productsRoutes.index().url },
]

const mode = ref<'create' | 'edit'>('create')
const editingId = ref<number | null>(null)

const form = useForm({
  name: '',
  category_id: null as number | null,
  supplier_id: null as number | null,
  size: '',
  stock: 0,
  min_stock: 0,
  is_consignment: false,
  supplier_price: null as number | null,
  sell_price: null as number | null,
  image: null as File | null,
})

const imagePreview = ref<string | null>(null)

const categoryOptions = computed(() => props.categories)
const supplierOptions = computed(() => props.suppliers)

const isDialogOpen = ref(false)
const isDeleteDialogOpen = ref(false)

// Popover open states
const categoryOpen = ref(false)
const supplierOpen = ref(false)

// Watch dialog close to reset popover states
watch(isDialogOpen, (open) => {
  if (!open) {
    categoryOpen.value = false
    supplierOpen.value = false
  }
})

function resetForm() {
  form.reset()
  form.clearErrors()
  form.image = null
  imagePreview.value = null
  mode.value = 'create'
  editingId.value = null
  categoryOpen.value = false
  supplierOpen.value = false
  isDialogOpen.value = false
}

function openCreateDialog() {
  resetForm()
  isDialogOpen.value = true
}

function startEdit(row: ProductRow) {
  mode.value = 'edit'
  editingId.value = row.id
  form.name = row.name
  form.category_id = row.category_id
  form.supplier_id = row.supplier_id
  form.size = row.size
  form.stock = row.stock ?? 0
  form.min_stock = row.min_stock ?? 0
  form.is_consignment = !!row.is_consignment
  form.supplier_price = row.supplier_price ? Number(row.supplier_price) : null
  form.sell_price = row.sell_price ? Number(row.sell_price) : null
  form.image = null
  imagePreview.value = row.image ? '/storage/' + row.image : null
  form.clearErrors()
  categoryOpen.value = false
  supplierOpen.value = false
  isDialogOpen.value = true
}

function submit() {
  const formData = new FormData()
  formData.append('name', form.name)
  formData.append('category_id', String(form.category_id ?? ''))
  formData.append('supplier_id', String(form.supplier_id ?? ''))
  formData.append('size', form.size)
  formData.append('stock', String(form.stock ?? 0))
  formData.append('min_stock', String(form.min_stock ?? 0))
  formData.append('is_consignment', form.is_consignment ? '1' : '0')
  formData.append('supplier_price', String(form.supplier_price ?? ''))
  formData.append('sell_price', String(form.sell_price ?? ''))
  
  if (form.image) {
    formData.append('image', form.image)
  }

  if (mode.value === 'create') {
    const toastId = toast.loading('Menyimpan produk...')
    router.post(productsRoutes.store().url, formData, {
      preserveScroll: true,
      onSuccess: () => {
        resetForm()
        toast.dismiss(toastId)
        setTimeout(() => {
          toast.success('Produk berhasil ditambahkan')
        }, 100)
      },
      onError: (errors) => {
        toast.dismiss(toastId)
        setTimeout(() => {
          toast.error('Gagal menambahkan produk', { description: errors.message || 'Terjadi kesalahan.' })
        }, 100)
      },
    })
    return
  }

  if (!editingId.value) return
  
  // Use POST with _method=PUT for file upload
  formData.append('_method', 'PUT')
  const toastId = toast.loading('Memperbarui produk...')
  router.post(productsRoutes.update(editingId.value).url, formData, {
    preserveScroll: true,
    onSuccess: () => {
      resetForm()
      toast.dismiss(toastId)
      setTimeout(() => {
        toast.success('Produk berhasil diperbarui')
      }, 100)
    },
    onError: (errors) => {
      toast.dismiss(toastId)
      setTimeout(() => {
        toast.error('Gagal memperbarui produk', { description: errors.message || 'Terjadi kesalahan.' })
      }, 100)
    },
  })
}

function handleImageChange(e: Event) {
  const target = e.target as HTMLInputElement
  const file = target.files?.[0]
  if (file) {
    form.image = file
    imagePreview.value = URL.createObjectURL(file)
  }
}

function removeImage() {
  form.image = null
  imagePreview.value = null
}

// Alert Dialog state for delete confirmation
const productToDelete = ref<ProductRow | null>(null)
const isDeleting = ref(false)

function openDeleteDialog(row: ProductRow) {
  productToDelete.value = row
  isDeleteDialogOpen.value = true
}

function confirmDelete() {
  if (!productToDelete.value || isDeleting.value) return
  
  isDeleting.value = true
  const productName = productToDelete.value.name;
  const toastId = toast.loading('Menghapus produk...')
  router.delete(productsRoutes.destroy(productToDelete.value.id).url, {
    preserveScroll: true,
    onSuccess: () => {
      productToDelete.value = null
      isDeleteDialogOpen.value = false
      isDeleting.value = false
      toast.dismiss(toastId)
      // Reload data to refresh table
      router.reload({ only: ['products'] })
      setTimeout(() => {
        toast.success('Produk berhasil dihapus', { description: `"${productName}" telah dihapus.` })
      }, 100)
    },
    onError: (errors) => {
      isDeleting.value = false
      toast.dismiss(toastId)
      setTimeout(() => {
        toast.error('Gagal menghapus produk', { description: errors.message || 'Terjadi kesalahan.' })
      }, 100)
    },
  })
}

const columns: ColumnDef<ProductRow>[] = [
  {
    id: 'select',
    header: ({ table }) =>
      h(Checkbox, {
        checked:
          table.getIsAllPageRowsSelected() ||
          (table.getIsSomePageRowsSelected() && 'indeterminate'),
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
    cell: ({ row }) => {
      const product = row.original
      const imageUrl = product.image ? '/storage/' + product.image : null
      return h('div', { class: 'flex items-center gap-3' }, [
        imageUrl
          ? h('img', {
              src: imageUrl,
              alt: product.name,
              class: 'h-10 w-10 rounded-lg object-cover border',
            })
          : h('div', { class: 'h-10 w-10 rounded-lg bg-gray-100 flex items-center justify-center text-gray-400 text-xs' }, 'No Img'),
        h('span', { class: 'font-medium' }, product.name),
      ])
    },
  },
  {
    id: 'category',
    header: () => 'Kategori',
    cell: ({ row }) => h('div', {}, row.original.category?.name ?? '-'),
  },
  {
    id: 'supplier',
    header: () => 'Supplier',
    cell: ({ row }) => h('div', {}, row.original.supplier?.name ?? '-'),
  },
  {
    accessorKey: 'size',
    id: 'size',
    header: () => 'Size',
    cell: ({ row }) => h('div', {}, (row.getValue('size') as string) ?? '-'),
  },
  {
    accessorKey: 'stock',
    id: 'stock',
    header: () => 'Stok',
    cell: ({ row }) => {
      const stock = Number(row.getValue('stock') ?? 0)
      const min = Number(row.original.min_stock ?? 0)
      const low = stock <= min
      return h(
        'div',
        { class: low ? 'font-semibold text-red-600' : '' },
        String(stock)
      )
    },
  },
  {
    accessorKey: 'sell_price',
    id: 'sell_price',
    header: () => 'Harga Jual',
    cell: ({ row }) => h('div', {}, formatIDR(row.getValue('sell_price'))),
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
                { default: () => h(Button, { variant: 'outline', size: 'sm' }, () => 'Menu') }
              ),
              h(
                DropdownMenuContent,
                { align: 'end' },
                {
                  default: () => [
                    h(DropdownMenuItem, { onClick: () => startEdit(row.original) }, () => 'Edit'),
                    h(DropdownMenuItem, { asChild: true }, () =>
                      h('a', { href: stockHistory.byProduct(row.original.id).url, class: 'flex w-full cursor-pointer' }, 'Riwayat Stok')
                    ),
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
</script>

<template>
  <Head title="Produk" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex flex-col gap-6 p-4">
      <!-- Header with Add Button -->
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-lg font-semibold">Produk</h2>
          <p class="text-sm text-muted-foreground">
            Produk terhubung ke kategori & supplier, plus stok.
          </p>
        </div>
        <Button @click="openCreateDialog">
          + Tambah Produk
        </Button>
      </div>

      <!-- Table -->
      <DataTable
        :columns="columns"
        :data="props.products"
        search-key="name"
        search-placeholder="Cari produk..."
      />

      <!-- Create/Edit Dialog -->
      <Dialog v-model:open="isDialogOpen">
        <DialogContent class="sm:max-w-[600px] max-h-[90vh] p-0 gap-0 overflow-hidden">
          <DialogHeader class="px-6 pt-6 pb-2">
            <DialogTitle>{{ mode === 'create' ? 'Tambah Produk' : 'Edit Produk' }}</DialogTitle>
            <DialogDescription>
              Produk terhubung ke kategori & supplier, plus stok.
            </DialogDescription>
          </DialogHeader>

          <div class="px-6 py-4 space-y-4 overflow-y-auto [overscroll-behavior-y:contain] max-h-[calc(90vh-180px)]">
            <div class="grid gap-4 md:grid-cols-2">
              <div class="flex flex-col gap-1">
                <label class="text-sm font-medium">Nama</label>
                <Input v-model="form.name" placeholder="Misal: Kemeja Oxford" />
                <div v-if="form.errors.name" class="text-sm text-red-600">{{ form.errors.name }}</div>
              </div>

              <div class="flex flex-col gap-1">
                <label class="text-sm font-medium">Size</label>
                <Input v-model="form.size" placeholder="S / M / L / 28 / 30" />
                <div v-if="form.errors.size" class="text-sm text-red-600">{{ form.errors.size }}</div>
              </div>

              <div class="flex flex-col gap-1">
                <label class="text-sm font-medium">Kategori</label>
                <Popover v-model:open="categoryOpen">
                  <PopoverTrigger as-child>
                    <Button variant="outline" role="combobox" :aria-expanded="categoryOpen" class="w-full justify-between">
                      {{ categoryOptions.find(c => c.id === form.category_id)?.name || "Pilih kategori..." }}
                      <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                    </Button>
                  </PopoverTrigger>
                  <PopoverContent class="w-full p-0" :avoid-collisions="true" :collision-padding="10">
                    <Command>
                      <CommandInput placeholder="Cari kategori..." />
                      <CommandList>
                        <CommandEmpty>Tidak ada kategori.</CommandEmpty>
                        <CommandGroup>
                          <CommandItem
                            v-for="c in categoryOptions"
                            :key="c.id"
                            :value="c.id"
                            @select="() => { form.category_id = c.id; categoryOpen = false }"
                          >
                            {{ c.name }}
                            <Check :class="cn('ml-auto h-4 w-4', form.category_id === c.id ? 'opacity-100' : 'opacity-0')" />
                          </CommandItem>
                        </CommandGroup>
                      </CommandList>
                    </Command>
                  </PopoverContent>
                </Popover>
                <div v-if="form.errors.category_id" class="text-sm text-red-600">{{ form.errors.category_id }}</div>
              </div>

              <div class="flex flex-col gap-1">
                <label class="text-sm font-medium">Supplier</label>
                <Popover v-model:open="supplierOpen">
                  <PopoverTrigger as-child>
                    <Button variant="outline" role="combobox" :aria-expanded="supplierOpen" class="w-full justify-between">
                      {{ supplierOptions.find(s => s.id === form.supplier_id)?.name || "Pilih supplier..." }}
                      <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                    </Button>
                  </PopoverTrigger>
                  <PopoverContent class="w-full p-0" :avoid-collisions="true" :collision-padding="10">
                    <Command>
                      <CommandInput placeholder="Cari supplier..." />
                      <CommandList>
                        <CommandEmpty>Tidak ada supplier.</CommandEmpty>
                        <CommandGroup>
                          <CommandItem
                            v-for="s in supplierOptions"
                            :key="s.id"
                            :value="s.id"
                            @select="() => { form.supplier_id = s.id; supplierOpen = false }"
                          >
                            {{ s.name }}
                            <Check :class="cn('ml-auto h-4 w-4', form.supplier_id === s.id ? 'opacity-100' : 'opacity-0')" />
                          </CommandItem>
                        </CommandGroup>
                      </CommandList>
                    </Command>
                  </PopoverContent>
                </Popover>
                <div v-if="form.errors.supplier_id" class="text-sm text-red-600">{{ form.errors.supplier_id }}</div>
              </div>

              <div class="flex flex-col gap-1">
                <label class="text-sm font-medium">Stok</label>
                <NumberField v-model="form.stock" :min="0">
                  <NumberFieldContent>
                    <NumberFieldDecrement />
                    <NumberFieldInput />
                    <NumberFieldIncrement />
                  </NumberFieldContent>
                </NumberField>
                <div v-if="form.errors.stock" class="text-sm text-red-600">{{ form.errors.stock }}</div>
              </div>

              <div class="flex flex-col gap-1">
                <label class="text-sm font-medium">Min. Stok</label>
                <NumberField v-model="form.min_stock" :min="0">
                  <NumberFieldContent>
                    <NumberFieldDecrement />
                    <NumberFieldInput />
                    <NumberFieldIncrement />
                  </NumberFieldContent>
                </NumberField>
                <div v-if="form.errors.min_stock" class="text-sm text-red-600">{{ form.errors.min_stock }}</div>
              </div>

              <div class="flex flex-col gap-1">
                <label class="text-sm font-medium">Harga Supplier (opsional)</label>
                <NumberField v-model="form.supplier_price" :min="0" :step="1000" :format-options="{ style: 'currency', currency: 'IDR' }">
                  <NumberFieldContent>
                    <NumberFieldDecrement />
                    <NumberFieldInput />
                    <NumberFieldIncrement />
                  </NumberFieldContent>
                </NumberField>
                <div v-if="form.errors.supplier_price" class="text-sm text-red-600">{{ form.errors.supplier_price }}</div>
              </div>

              <div class="flex flex-col gap-1">
                <label class="text-sm font-medium">Harga Jual</label>
                <NumberField v-model="form.sell_price" :min="0" :step="1000" :format-options="{ style: 'currency', currency: 'IDR' }">
                  <NumberFieldContent>
                    <NumberFieldDecrement />
                    <NumberFieldInput />
                    <NumberFieldIncrement />
                  </NumberFieldContent>
                </NumberField>
                <div v-if="form.errors.sell_price" class="text-sm text-red-600">{{ form.errors.sell_price }}</div>
              </div>
            </div>

            <div class="flex items-center gap-2">
              <Checkbox v-model:checked="form.is_consignment" />
              <span class="text-sm">Barang titipan (consignment)</span>
              <div v-if="form.errors.is_consignment" class="ml-2 text-sm text-red-600">
                {{ form.errors.is_consignment }}
              </div>
            </div>

            <!-- Image Upload -->
            <div class="flex flex-col gap-2">
              <label class="text-sm font-medium">Gambar Produk</label>
              <div class="flex items-center gap-4">
                <!-- Preview -->
                <div v-if="imagePreview" class="relative">
                  <img :src="imagePreview" alt="Preview" class="h-24 w-24 rounded-lg object-cover border" />
                  <button
                    type="button"
                    @click="removeImage"
                    class="absolute -top-2 -right-2 h-6 w-6 rounded-full bg-red-500 text-white text-xs flex items-center justify-center hover:bg-red-600"
                  >
                    ×
                  </button>
                </div>
                <div v-else class="h-24 w-24 rounded-lg bg-gray-100 flex items-center justify-center text-gray-400 text-sm border">
                  No Image
                </div>
                
                <!-- Input -->
                <div class="flex flex-col gap-1">
                  <input
                    type="file"
                    accept="image/jpeg,image/png,image/jpg,image/webp"
                    @change="handleImageChange"
                    class="text-sm file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                  />
                  <p class="text-xs text-muted-foreground">Format: JPEG, PNG, JPG, WebP. Maks: 2MB</p>
                  <div v-if="form.errors.image" class="text-sm text-red-600">{{ form.errors.image }}</div>
                </div>
              </div>
            </div>
          </div>

          <DialogFooter class="flex flex-row gap-2 justify-end px-6 pb-6 pt-2">
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
            <AlertDialogTitle>Hapus Produk?</AlertDialogTitle>
            <AlertDialogDescription>
              Apakah Anda yakin ingin menghapus produk "{{ productToDelete?.name }}"?
              <br />Tindakan ini tidak dapat dibatalkan dan akan menghapus data produk secara permanen.
            </AlertDialogDescription>
          </AlertDialogHeader>
          <AlertDialogFooter>
            <AlertDialogCancel @click="productToDelete = null" :disabled="isDeleting">Batal</AlertDialogCancel>
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