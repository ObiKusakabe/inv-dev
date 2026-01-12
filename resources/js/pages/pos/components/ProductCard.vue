<!-- <script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardTitle,
} from '@/components/ui/card';

interface Product {
    id: number | string;
    name: string;
    price: number;
    image?: string | null;
    stock?: number;
    description?: string;
}

const props = defineProps<{ product: Product }>();
const emit = defineEmits<{
    (e: 'add'): void;
}>();

function onAdd() {
    emit('add');
}

function formatCurrency(v: number) {
    // simple formatter for IDR; adapt if you want locale
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
    }).format(v);
}
</script>

<template>
    <Card class="w-full">
        <CardContent class="p-3">
            <div
                class="mb-2 flex aspect-square items-center justify-center overflow-hidden rounded-md bg-gray-100"
            >
                <template v-if="props.product.image">
                    <img
                        :src="props.product.image"
                        alt="product"
                        class="h-full w-full object-cover"
                    />
                </template>
                <template v-else>
                    <div class="text-xs text-muted-foreground">No image</div>
                </template>
            </div>

            <CardTitle class="mb-1 text-sm">{{ props.product.name }}</CardTitle>
            <CardDescription class="mb-2 line-clamp-2 text-xs">{{
                props.product.description
            }}</CardDescription>

            <div class="flex items-center justify-between">
                <span class="text-sm font-bold">{{
                    formatCurrency(props.product.price)
                }}</span>
                <Button size="sm" class="h-7 px-2 text-xs" @click="onAdd"
                    >Tambah</Button
                >
            </div>
        </CardContent>
    </Card>
</template> -->

<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { inject } from 'vue';

defineProps<{
    product: {
        id: number;
        name: string;
        price: number;
    };
}>();

const addToCart = inject('addToCart') as (p: any) => void;
</script>

<template>
    <div class="space-y-2 rounded-lg border p-4">
        <h3 class="font-semibold">{{ product.name }}</h3>
        <p class="text-sm text-muted-foreground">
            Rp {{ product.price.toLocaleString() }}
        </p>
        <Button size="sm" @click="addToCart(product)"> + Tambah </Button>
    </div>
</template>
