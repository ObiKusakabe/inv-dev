<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import pos from '@/routes/pos';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';

// import { Button } from '@/components/ui/button'
// import {
//   Card,
//   CardContent,
//   CardDescription,
//   CardTitle,
// } from '@/components/ui/card'
// import { Star } from 'lucide-vue-next'

import ProductGrid from './components/ProductGrid.vue';
import OrderPanel from './components/OrderPanel.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'POS',
        href: pos.index().url,
    },
];
</script>

<template>
    <Head title="POS" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="grid grid-cols-12 gap-4 p-4">
            <!-- Produk -->
            <div class="col-span-12 lg:col-span-8">
                <ProductGrid />
            </div>

            <!-- Keranjang / POS -->
            <div class="col-span-12 lg:col-span-4">
                <OrderPanel />
            </div>
        </div>
    </AppLayout>
</template>
