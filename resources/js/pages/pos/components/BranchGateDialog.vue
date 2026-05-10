<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'

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

import branches from '@/routes/branches'

type Branch = {
    id: number
    name: string
    code: string
}

const props = defineProps<{
    branches: Branch[]
    currentBranchId: number | null
    branchMissing: boolean
}>()

const open = ref<boolean>(false)

// auto-open kalau branchMissing
watch(
    () => props.branchMissing,
    (v) => {
        open.value = !!v
    },
    { immediate: true },
)

const q = ref('')
const filtered = computed(() => {
    const query = q.value.trim().toLowerCase()
    if (!query) return props.branches
    return props.branches.filter(b =>
        `${b.name} ${b.code}`.toLowerCase().includes(query),
    )
})

const selectedId = ref<number | null>(props.currentBranchId ?? null)
watch(
    () => props.currentBranchId,
    (v) => (selectedId.value = v ?? null),
)

function selectBranch(id: number) {
    router.post(
        branches.select().url,
        { branch_id: id },
        {
            preserveScroll: true,
            onSuccess: () => {
                open.value = false
            },
        },
    )
}

// create branch (shown when branches empty or user wants)
const newName = ref('')
const newCode = ref('')

const canCreate = computed(() => newName.value.trim().length > 0)

function createBranch() {
    if (!canCreate.value) return
    router.post(
        branches.store().url,
        {
            name: newName.value.trim(),
            code: newCode.value.trim() || null,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                newName.value = ''
                newCode.value = ''
                open.value = false
            },
        },
    )
}

// Prevent closing when missing branch
function onOpenChange(v: boolean) {
    if (props.branchMissing) {
        // kalau belum ada branch dipilih, paksa tetap open
        open.value = true
        return
    }
    open.value = v
}
</script>

<template>
    <Dialog :open="open" @update:open="onOpenChange">
        <DialogContent class="sm:max-w-[520px]" :close-button="false" @interact-outside.prevent @escape-key-down.prevent
            @pointer-down-outside.prevent>
            <DialogHeader>
                <DialogTitle>Pilih Cabang</DialogTitle>
                <DialogDescription>
                    Kamu harus memilih cabang sebelum menggunakan POS.
                </DialogDescription>
            </DialogHeader>

            <!-- Search + list (combobox-ish) -->
            <div class="space-y-2">
                <Label>Cari cabang</Label>
                <Input v-model="q" placeholder="Ketik nama / kode cabang..." />
            </div>

            <div class="mt-3 rounded-md border">
                <div class="max-h-[220px] overflow-auto p-2" v-if="filtered.length > 0">
                    <button v-for="b in filtered" :key="b.id"
                        class="flex w-full items-center justify-between rounded-md px-3 py-2 text-left text-sm hover:bg-muted"
                        @click="selectBranch(b.id)">
                        <div class="min-w-0">
                            <div class="truncate font-medium">{{ b.name }}</div>
                            <div class="truncate text-xs text-muted-foreground">
                                {{ b.code }}
                            </div>
                        </div>

                        <span v-if="b.id === selectedId" class="ml-3 text-xs text-muted-foreground">
                            Dipilih
                        </span>
                    </button>
                </div>

                <div v-else class="p-3 text-sm text-muted-foreground">
                    Belum ada cabang.
                </div>
            </div>

            <!-- Create branch form -->
            <div class="mt-4 space-y-3 rounded-md border p-3">
                <div class="text-sm font-semibold">Tambah cabang baru</div>

                <div class="space-y-2">
                    <Label>Nama cabang</Label>
                    <Input v-model="newName" placeholder="Contoh: Cabang Utama" />
                </div>

                <div class="space-y-2">
                    <Label>Kode cabang (opsional)</Label>
                    <Input v-model="newCode" placeholder="Contoh: CBG1" />
                    <p class="text-xs text-muted-foreground">
                        Kalau kosong, kode akan dibuat otomatis (CBG1, CBG2, ...).
                    </p>
                </div>

                <DialogFooter>
                    <Button :disabled="!canCreate" @click="createBranch">
                        Simpan & Pilih Cabang
                    </Button>
                </DialogFooter>
            </div>
        </DialogContent>
    </Dialog>
</template>