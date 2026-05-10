<script setup lang="ts">
import { computed, ref } from 'vue'
import { router, usePage } from '@inertiajs/vue3'

import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogDescription,
  DialogFooter,
} from '@/components/ui/dialog'

import branchesRoutes from '@/routes/branches'

type Branch = { id: number; name: string; code: string }

const page = usePage()

const branches = computed(() => (page.props.branches ?? []) as Branch[])
const currentBranchId = computed(() => (page.props.currentBranchId ?? null) as number | null)

const open = ref(false)
const q = ref('')

const currentBranch = computed(() =>
  branches.value.find((b) => b.id === currentBranchId.value) ?? null,
)

const filtered = computed(() => {
  const query = q.value.trim().toLowerCase()
  if (!query) return branches.value
  return branches.value.filter((b) =>
    `${b.name} ${b.code}`.toLowerCase().includes(query),
  )
})

function select(id: number) {
  router.post(
    branchesRoutes.select().url,
    { branch_id: id },
    {
      preserveScroll: true,
      onSuccess: () => {
        open.value = false
        q.value = ''
      },
    },
  )
}
</script>

<template>
  <div>
    <Button variant="outline" class="gap-2" @click="open = true">
      <span class="text-muted-foreground">Cabang:</span>
      <span class="font-medium">
        {{ currentBranch?.name ?? 'Belum dipilih' }}
      </span>
      <span class="text-muted-foreground">⌄</span>
    </Button>

    <Dialog v-model:open="open">
      <DialogContent class="sm:max-w-[420px]">
        <DialogHeader>
          <DialogTitle>Pilih Cabang</DialogTitle>
          <DialogDescription>
            Pilih cabang aktif untuk session ini.
          </DialogDescription>
        </DialogHeader>

        <div class="space-y-3">
          <div class="space-y-2">
            <Label>Cari cabang</Label>
            <Input v-model="q" placeholder="Ketik nama / kode cabang..." />
          </div>

          <div class="rounded-md border">
            <div class="max-h-[240px] overflow-auto p-2" v-if="filtered.length > 0">
              <button
                v-for="b in filtered"
                :key="b.id"
                class="flex w-full items-center justify-between rounded-md px-3 py-2 text-left text-sm hover:bg-muted"
                @click="select(b.id)"
              >
                <div class="min-w-0">
                  <div class="truncate font-medium">{{ b.name }}</div>
                  <div class="truncate text-xs text-muted-foreground">{{ b.code }}</div>
                </div>

                <span v-if="b.id === currentBranchId" class="ml-3 text-xs text-green-600 font-medium">
                  Aktif
                </span>
              </button>
            </div>

            <div v-else class="p-3 text-sm text-muted-foreground">
              Cabang tidak ditemukan.
            </div>
          </div>
        </div>

        <DialogFooter class="text-xs text-muted-foreground">
          Untuk tambah cabang, buka menu Settings → Cabang.
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </div>
</template>