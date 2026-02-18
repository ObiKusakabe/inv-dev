<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import pos from '@/routes/pos';
import type { BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import ProductGrid from './components/ProductGrid.vue';
import OrderPanel from './components/OrderPanel.vue';

export interface Product {
    id: number | string;
    name: string;
    price: number;
    stock?: number;
    image?: string | null;
    description?: string | null;
}

const props = defineProps<{
    products?: Product[]; // nanti dari backend
}>();

const breadcrumbs: BreadcrumbItem[] = [{ title: 'POS', href: pos.index().url }];

// Fallback dummy (biar UI nyala dulu). Nanti diganti props.products dari backend.
const fallbackProducts: Product[] = [
    { id: 1, name: 'Kaos Polos', price: 50000, stock: 10, description: 'Cotton combed' },
    { id: 2, name: 'Hoodie', price: 150000, stock: 5, description: 'Fleece nyaman' },
    { id: 3, name: 'Jaket', price: 250000, stock: 0, description: 'Out of stock test' },
    { id: 4, name: 'Kemeja', price: 120000, stock: 7, description: 'Slim fit' },
];

const products = props.products?.length ? props.products : fallbackProducts;
</script>

<template>
    <Head title="POS" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div v-if="$page.props.flash?.success"
     class="mb-4 rounded-md bg-green-100 px-4 py-2 text-sm text-green-800">
  {{ $page.props.flash.success }}
</div>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
            <!-- LEFT: Products -->
            <div class="lg:col-span-8">
                <div class="rounded-lg border bg-white p-4">
                    <h1 class="mb-4 text-xl font-semibold">Point of Sale</h1>
                    <ProductGrid :products="products" />
                </div>
            </div>

            <!-- RIGHT: Order -->
            <div class="lg:col-span-4">
                <OrderPanel />
            </div>
        </div>
    </AppLayout>
</template>
