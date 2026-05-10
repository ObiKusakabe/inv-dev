<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import pos from '@/routes/pos'
import type { BreadcrumbItem } from '@/types'
import { Head } from '@inertiajs/vue3'

import ProductGrid from './components/ProductGrid.vue'
import OrderPanel from './components/OrderPanel.vue'
import BranchGateDialog from './components/BranchGateDialog.vue'

type Branch = {
    id: number
    name: string
    code: string
}

export interface Product {
    id: number | string
    name: string
    price: number
    stock?: number
    image?: string | null
    description?: string | null
    category?: string | null
}

const props = defineProps<{
    products: Product[]
    branches: Branch[]
    currentBranchId: number | null
    branchMissing: boolean
}>()

const breadcrumbs: BreadcrumbItem[] = [{ title: 'POS', href: pos.index().url }]
</script>

<template>

    <Head title="POS" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <BranchGateDialog :branches="props.branches" :current-branch-id="props.currentBranchId"
            :branch-missing="props.branchMissing" />

        <div v-if="$page.props.flash?.success" class="mb-4 rounded-md bg-green-100 px-4 py-2 text-sm text-green-800 dark:bg-green-900/30 dark:text-green-300">
            {{ $page.props.flash.success }}
        </div>

        <div v-if="props.branchMissing"
            class="mb-4 rounded-md border bg-muted/30 px-4 py-2 text-sm text-muted-foreground">
            Pilih / buat cabang dulu untuk mulai transaksi.
        </div>

        <main class="relative flex-1 overflow-auto bg-background p-4 lg:p-6">
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
                <!-- LEFT: Products -->
                <div class="lg:col-span-8">
                    <div class="rounded-lg border bg-card p-4">
                        <h1 class="mb-4 text-xl font-semibold">Point of Sale</h1>
                        <ProductGrid :products="props.products" />
                    </div>
                </div>

                <!-- RIGHT: Order -->
                <div class="lg:col-span-4">
                    <OrderPanel :disabled="props.branchMissing" />
                </div>
            </div>
        </main>
    </AppLayout>
</template>