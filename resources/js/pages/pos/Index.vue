<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import pos from '@/routes/pos';
import type { BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { provide, ref } from 'vue';
import OrderPanel from './components/OrderPanel.vue';
import ProductGrid from './components/ProductGrid.vue';
import PlaceholderPattern from '../../components/PlaceholderPattern.vue';

export interface CartItem {
    id: number;
    name: string;
    price: number;
    qty: number;
}

const breadcrumbs: BreadcrumbItem[] = [
    { 
        title: 'POS',
        href: pos.index().url, 
    }];

const cart = ref<CartItem[]>([]);

function addToCart(product: Omit<CartItem, 'qty'>) {
    const item = cart.value.find((i) => i.id === product.id);
    if (item) item.qty++;
    else cart.value.push({ ...product, qty: 1 });
}

function removeFromCart(id: number) {
    cart.value = cart.value.filter((i) => i.id !== id);
}

function updateQty(id: number, qty: number) {
    const item = cart.value.find((i) => i.id === id);
    if (item && qty > 0) item.qty = qty;
}

provide('cart', cart);
provide('addToCart', addToCart);
provide('removeFromCart', removeFromCart);
provide('updateQty', updateQty);
</script>

<template>
    <Head title="POS" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <!-- <div class="grid grid-cols-12 gap-4 p-4">
            <div class="col-span-8">
                POS

            </div>
            <div class="col-span-4">

            </div>
        </div> -->
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                <div
                    class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <PlaceholderPattern />
                </div>
                <div
                    class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <PlaceholderPattern />
                </div>
                <div
                    class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <PlaceholderPattern />
                </div>
            </div>
            <div
                class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
                <PlaceholderPattern />
            </div>
        </div>
    </AppLayout>
</template>
