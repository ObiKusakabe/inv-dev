<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Separator } from '@/components/ui/separator';
import { computed, ref } from 'vue';

import { useCartStore } from '@/stores/cart';
import { router } from '@inertiajs/vue3';

const cart = useCartStore();

function formatIDR(v: number) {
    return new Intl.NumberFormat('id-ID').format(
        Math.max(0, Math.round(v || 0)),
    );
}

// ----- Payment -----
const paymentMethod = ref<'cash' | 'transfer' | 'qris' | 'ewallet'>('cash');

// ----- Discount -----
const discountMode = ref<'percent' | 'amount'>('percent'); // percent or amount
const discountInput = ref<string>('0');

const subtotal = computed(() => cart.totalPrice);

const discountAmount = computed(() => {
    const raw = Number(discountInput.value || 0);
    if (!raw || raw <= 0) return 0;

    if (discountMode.value === 'percent') {
        // clamp 0..100
        const pct = Math.min(100, Math.max(0, raw));
        return Math.round((subtotal.value * pct) / 100);
    }

    // nominal
    return Math.min(subtotal.value, Math.max(0, Math.round(raw)));
});

const grandTotal = computed(() => {
    return Math.max(0, subtotal.value - discountAmount.value);
});

// ----- Cash Received / Change -----
const cashReceived = ref<string>('0');

const change = computed(() => {
    if (paymentMethod.value !== 'cash') return 0;
    const paid = Number(cashReceived.value || 0);
    return Math.max(0, Math.round(paid - grandTotal.value));
});

const isCashPaidEnough = computed(() => {
    if (paymentMethod.value !== 'cash') return true;
    const paid = Number(cashReceived.value || 0);
    return paid >= grandTotal.value;
});

// ----- Customer (frontend only for now) -----
type CustomerOption = { id: string; name: string };

const customers = ref<CustomerOption[]>([
    { id: 'walkin', name: 'Walk-in Customer' },
]);

const selectedCustomerId = ref<string>('walkin');

const newCustomerName = ref<string>('');
function addCustomer() {
    const name = newCustomerName.value.trim();
    if (!name) return;

    const id = `c_${Date.now()}`;
    customers.value.push({ id, name });
    selectedCustomerId.value = id;
    newCustomerName.value = '';
}

// ----- Cart actions -----
function checkout() {
    if (cart.isEmpty) return;

    const payload = {
        customer_id:
            selectedCustomerId.value === 'walkin'
                ? null
                : selectedCustomerId.value,
        customer_name:
            customers.value.find((c) => c.id === selectedCustomerId.value)
                ?.name ?? null,

        payment_method: paymentMethod.value,
        discount_mode: discountMode.value,
        discount_input: Number(discountInput.value || 0),
        cash_received:
            paymentMethod.value === 'cash'
                ? Number(cashReceived.value || 0)
                : null,

        items: cart.items.map((i) => ({
            id: i.id,
            qty: i.qty,
        })),
    };

    router.post(route('pos.checkout'), payload, {
        onSuccess: () => {
            cart.clear();
        },
        onError: (errors) => {
            console.error(errors);
        },
    });
}
</script>

<template>
    <div class="rounded-lg border bg-white p-4">
        <!-- Header -->
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-lg font-semibold">Keranjang</h2>
            <Button
                variant="outline"
                size="sm"
                :disabled="cart.isEmpty"
                @click="cart.clear()"
            >
                Clear
            </Button>
        </div>

        <!-- Empty state -->
        <div
            v-if="cart.isEmpty"
            class="py-6 text-center text-sm text-muted-foreground"
        >
            Belum ada barang
        </div>

        <div v-else class="space-y-4">
            <!-- Cart items -->
            <div class="space-y-3">
                <div
                    v-for="item in cart.items"
                    :key="item.id"
                    class="flex items-center justify-between gap-3 rounded-lg border p-3"
                >
                    <div class="min-w-0">
                        <div class="truncate text-sm font-semibold">
                            {{ item.name }}
                        </div>
                        <div class="text-xs text-muted-foreground">
                            Rp {{ formatIDR(item.price) }} × {{ item.qty }}
                        </div>
                        <div class="mt-1 text-sm font-bold">
                            Rp {{ formatIDR(item.price * item.qty) }}
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <button
                            class="h-8 w-8 rounded border text-sm"
                            @click="cart.decrease(item.id)"
                        >
                            -
                        </button>

                        <Input
                            class="h-8 w-14 text-center text-sm"
                            type="number"
                            min="1"
                            :model-value="String(item.qty)"
                            @update:model-value="
                                (v) => cart.setQty(item.id, Number(v))
                            "
                        />

                        <button
                            class="h-8 w-8 rounded border text-sm"
                            @click="cart.increase(item.id)"
                        >
                            +
                        </button>

                        <Button
                            variant="destructive"
                            size="sm"
                            class="h-8 px-3"
                            @click="cart.remove(item.id)"
                        >
                            x
                        </Button>
                    </div>
                </div>
            </div>

            <Separator />

            <!-- Customer -->
            <div class="space-y-2">
                <Label>Customer</Label>
                <Select v-model="selectedCustomerId">
                    <SelectTrigger>
                        <SelectValue placeholder="Pilih customer" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="c in customers"
                            :key="c.id"
                            :value="c.id"
                        >
                            {{ c.name }}
                        </SelectItem>
                    </SelectContent>
                </Select>

                <div class="flex gap-2">
                    <Input
                        v-model="newCustomerName"
                        placeholder="Tambah customer baru..."
                    />
                    <Button
                        type="button"
                        variant="outline"
                        @click="addCustomer"
                    >
                        Tambah
                    </Button>
                </div>
                <p class="text-xs text-muted-foreground">
                    (Step 1: masih frontend. Step 2: baru disimpan ke database)
                </p>
            </div>

            <Separator />

            <!-- Payment method -->
            <div class="space-y-2">
                <Label>Metode pembayaran</Label>
                <Select v-model="paymentMethod">
                    <SelectTrigger>
                        <SelectValue placeholder="Pilih metode pembayaran" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="cash">Cash</SelectItem>
                        <SelectItem value="transfer">Transfer</SelectItem>
                        <SelectItem value="qris">QRIS</SelectItem>
                        <SelectItem value="ewallet">E-Wallet</SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <!-- Discount -->
            <div class="space-y-2">
                <Label>Diskon</Label>

                <RadioGroup
                    v-model="discountMode"
                    class="flex items-center gap-6"
                >
                    <div class="flex items-center gap-2">
                        <RadioGroupItem id="disc_percent" value="percent" />
                        <Label for="disc_percent">Persen (%)</Label>
                    </div>
                    <div class="flex items-center gap-2">
                        <RadioGroupItem id="disc_amount" value="amount" />
                        <Label for="disc_amount">Nominal (Rp)</Label>
                    </div>
                </RadioGroup>

                <Input
                    v-model="discountInput"
                    type="number"
                    min="0"
                    :max="discountMode === 'percent' ? 100 : undefined"
                    :placeholder="
                        discountMode === 'percent'
                            ? 'Contoh: 10'
                            : 'Contoh: 20000'
                    "
                />
            </div>

            <!-- Cash received -->
            <div v-if="paymentMethod === 'cash'" class="space-y-2">
                <Label>Uang diterima</Label>
                <Input
                    v-model="cashReceived"
                    type="number"
                    min="0"
                    placeholder="Contoh: 500000"
                />
                <div class="flex justify-between text-sm">
                    <span class="text-muted-foreground">Kembalian</span>
                    <span class="font-semibold"
                        >Rp {{ formatIDR(change) }}</span
                    >
                </div>
                <p v-if="!isCashPaidEnough" class="text-xs text-red-600">
                    Uang diterima kurang dari total.
                </p>
            </div>

            <Separator />

            <!-- Summary -->
            <div class="space-y-2">
                <div class="flex justify-between text-sm">
                    <span class="text-muted-foreground">Total item</span>
                    <span class="font-semibold">{{ cart.totalQty }}</span>
                </div>

                <div class="flex justify-between text-sm">
                    <span class="text-muted-foreground">Subtotal</span>
                    <span class="font-semibold"
                        >Rp {{ formatIDR(subtotal) }}</span
                    >
                </div>

                <div class="flex justify-between text-sm">
                    <span class="text-muted-foreground">Diskon</span>
                    <span class="font-semibold"
                        >- Rp {{ formatIDR(discountAmount) }}</span
                    >
                </div>

                <div class="flex justify-between text-lg">
                    <span class="text-muted-foreground">Total</span>
                    <span class="text-xl font-bold"
                        >Rp {{ formatIDR(grandTotal) }}</span
                    >
                </div>

                <Button
                    class="mt-2 w-full"
                    :disabled="cart.isEmpty || !isCashPaidEnough"
                    @click="checkout"
                >
                    Checkout
                </Button>

                <p class="text-xs text-muted-foreground">
                    (Step 1: masih simulasi. Step 2: POST backend → invoice +
                    stock movement + payment)
                </p>
            </div>
        </div>
    </div>
</template>
