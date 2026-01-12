<!-- <script setup lang="ts">
import { useCartStore } from '@/stores/cart';
import { Inertia } from '@inertiajs/inertia';
import { computed, ref } from 'vue';

const cart = useCartStore();

const customerName = ref('Walk-in');
const paymentMethod = ref<'cash' | 'qris'>('qris');
const note = ref('');

const totalPrice = computed(() => cart.totalPrice);
const totalQty = computed(() => cart.totalQty);

function formatCurrency(v: number) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
    }).format(v);
}

function increase(itemId: string | number) {
    cart.increase(itemId);
}
function decrease(itemId: string | number) {
    cart.decrease(itemId);
}
function remove(itemId: string | number) {
    cart.remove(itemId);
}

function resetCart() {
    cart.clear();
}

function pay() {
    if (cart.items.length === 0) {
        alert('Keranjang kosong.');
        return;
    }

    const payload = {
        customer: customerName.value,
        method: paymentMethod.value,
        note: note.value,
        items: cart.items.map((i) => ({
            id: i.id,
            price: i.price,
            qty: i.qty,
        })),
        total: cart.totalPrice,
    };

    // Default: kirim ke endpoint backend (sesuaikan route)
    // Jika kamu belum mau kirim ke backend, ganti Inertia.post -> console.log
    Inertia.post('/pos/checkout', payload, {
        onSuccess: () => {
            resetCart();
            alert('Pembayaran berhasil (simulasi).');
        },
        onError: (err) => {
            console.error(err);
            alert('Gagal melakukan pembayaran. Cek console.');
        },
    });
}
</script>

<template>
    <Card>
        <CardContent class="space-y-4 p-4">
            <h3 class="text-lg font-semibold">Keranjang</h3>

            <div>
                <label class="text-xs text-muted-foreground">Customer</label>
                <Input v-model="customerName" placeholder="Nama customer" />
            </div>

            <div class="max-h-64 space-y-2 overflow-auto">
                <div
                    v-for="item in cart.items"
                    :key="item.id"
                    class="flex items-center justify-between gap-2"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded bg-gray-100 text-xs"
                        >
                            img
                        </div>
                        <div class="text-sm">
                            <div class="font-medium">{{ item.name }}</div>
                            <div class="text-xs text-muted-foreground">
                                {{ item.qty }} x
                                {{ formatCurrency(item.price) }}
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <button class="btn btn-xs" @click="decrease(item.id)">
                            -
                        </button>
                        <div class="w-6 text-center text-sm">
                            {{ item.qty }}
                        </div>
                        <button class="btn btn-xs" @click="increase(item.id)">
                            +
                        </button>
                        <div class="w-24 text-right font-semibold">
                            {{ formatCurrency(item.price * item.qty) }}
                        </div>
                        <button
                            class="btn btn-ghost btn-xs text-red-500"
                            @click="remove(item.id)"
                        >
                            x
                        </button>
                    </div>
                </div>

                <div
                    v-if="cart.items.length === 0"
                    class="py-6 text-center text-sm text-muted-foreground"
                >
                    Keranjang kosong
                </div>
            </div>

            <hr />

            <div class="flex justify-between">
                <div class="text-sm">Jumlah Barang</div>
                <div class="font-semibold">{{ totalQty }}</div>
            </div>

            <div class="flex justify-between text-lg">
                <div class="text-sm">Total</div>
                <div class="text-xl font-bold">
                    {{ formatCurrency(totalPrice) }}
                </div>
            </div>

            <div class="grid grid-cols-2 gap-2">
                <Button variant="outline" @click="paymentMethod = 'qris'"
                    >QRIS</Button
                >
                <Button variant="outline" @click="paymentMethod = 'cash'"
                    >Cash</Button
                >
            </div>

            <div class="space-y-2">
                <textarea
                    v-model="note"
                    class="input input-bordered w-full"
                    placeholder="Catatan (opsional)"
                ></textarea>
            </div>

            <div class="flex gap-2">
                <Button class="flex-1" @click="pay">Bayar</Button>
                <Button variant="destructive" class="w-32" @click="resetCart"
                    >Reset</Button
                >
            </div>
        </CardContent>
    </Card>
</template> -->

<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { computed, inject } from 'vue';
import type { CartItem } from '../Index.vue';

const cart = inject('cart') as { value: CartItem[] };
const removeFromCart = inject('removeFromCart') as (id: number) => void;
// const updateQty = inject('updateQty') as (id: number, qty: number) => void;

const total = computed(() =>
    cart.value.reduce((sum, item) => sum + item.price * item.qty, 0),
);
</script>

<template>
    <div class="space-y-4 rounded-lg border p-4">
        <h2 class="text-lg font-semibold">Keranjang</h2>

        <div
            v-if="cart.value.length === 0"
            class="text-sm text-muted-foreground"
        >
            Belum ada barang
        </div>

        <div
            v-for="item in cart.value"
            :key="item.id"
            class="flex items-center justify-between"
        >
            <div>
                <p class="font-medium">{{ item.name }}</p>
                <p class="text-sm">Rp {{ item.price.toLocaleString() }}</p>
            </div>

            <div class="flex items-center gap-2">
                <input
                    type="number"
                    min="1"
                    class="w-14 rounded border px-2"
                    :value="item.qty"
                    
                />
                <Button
                    size="sm"
                    variant="destructive"
                    @click="removeFromCart(item.id)"
                >
                    x
                </Button>
            </div>
        </div>

        <div class="flex justify-between border-t pt-3 font-semibold">
            <span>Total</span>
            <span>Rp {{ total.toLocaleString() }}</span>
        </div>

        <Button class="w-full" :disabled="cart.value.length === 0">
            Bayar
        </Button>
    </div>
</template>
