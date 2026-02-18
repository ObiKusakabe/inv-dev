<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { useCartStore } from '@/stores/cart';
import type { Product } from '../Index.vue';

const props = defineProps<{ product: Product }>();

const cart = useCartStore();

function formatIDR(v: number) {
    return new Intl.NumberFormat('id-ID').format(v);
}

function add() {
    if ((props.product.stock ?? 999999) <= 0) return;

    cart.add({
        id: props.product.id,
        name: props.product.name,
        price: props.product.price,
        image: props.product.image ?? null,
        stock: props.product.stock,
    });
}
</script>

<template>
    <div
        class="rounded-lg border bg-white p-3 shadow-sm"
        :class="(product.stock ?? 1) <= 0 ? 'opacity-60' : ''"
    >
        <div class="mb-2 flex items-start justify-between gap-2">
            <h3 class="line-clamp-2 text-sm font-semibold">{{ product.name }}</h3>

            <span
                v-if="(product.stock ?? 0) <= 0"
                class="rounded-full bg-gray-200 px-2 py-1 text-xs text-gray-700"
            >
                Habis
            </span>
            <span
                v-else
                class="rounded-full bg-green-100 px-2 py-1 text-xs text-green-700"
            >
                Stock: {{ product.stock }}
            </span>
        </div>

        <p class="text-sm font-bold text-indigo-600">Rp {{ formatIDR(product.price) }}</p>
        <p v-if="product.description" class="mt-1 line-clamp-2 text-xs text-muted-foreground">
            {{ product.description }}
        </p>

        <Button
            class="mt-3 h-8 w-full"
            size="sm"
            :disabled="(product.stock ?? 1) <= 0"
            @click="add"
        >
            + Tambah
        </Button>
    </div>
</template>
