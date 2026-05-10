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
import {
  NumberField,
  NumberFieldContent,
  NumberFieldDecrement,
  NumberFieldIncrement,
  NumberFieldInput,
} from '@/components/ui/number-field';
import { Spinner } from '@/components/ui/spinner';
import { computed, h, ref } from 'vue';
import { toast } from 'vue-sonner';

import { useCartStore } from '@/stores/cart';
import { router } from '@inertiajs/vue3';

import pos from '@/routes/pos'

const props = defineProps<{
    disabled?: boolean
}>() 

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
const discountInput = ref<number>(0);

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
const cashReceived = ref<number>(0);

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

// Checkout processing state
const isProcessing = ref(false);

function checkout() {
    if (props.disabled || isProcessing.value) return;
    if (cart.isEmpty) return;
    if (!isCashPaidEnough.value) return;
    
    isProcessing.value = true;
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
            price: i.price,
        })),
    };

    router.post(pos.checkout().url, payload, {
        onSuccess: (page) => {
            isProcessing.value = false;
            cart.clear();
            cashReceived.value = 0;
            discountInput.value = 0;
            const invoice = page.props.invoice as { invoice_number?: string; id?: number } | undefined;
            // Delay toast to ensure it shows after component updates
            setTimeout(() => {
                toast.success('Transaksi berhasil!', {
                    description: invoice?.invoice_number ? `Invoice ${invoice.invoice_number} telah dibuat.` : 'Pesanan telah diproses.',
                    duration: 5000,
                    action: invoice?.id ? {
                        label: 'Lihat',
                        onClick: () => router.visit(route('invoices.show', invoice.id)),
                    } : undefined,
                });
            }, 100);
        },
        onError: (errors) => {
            isProcessing.value = false;
            console.error(errors);
            const message = errors.message || (typeof errors === 'object' ? Object.values(errors).join(', ') : 'Terjadi kesalahan saat checkout.');
            setTimeout(() => {
                toast.error('Checkout gagal', { description: message });
            }, 100);
        },
    });
}

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

</script>

<template>
    <div class="rounded-lg border bg-background p-4">
        <!-- Header -->
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-lg font-semibold">Keranjang</h2>
            <Button variant="outline" size="sm" :disabled="props.disabled || cart.isEmpty" @click="cart.clear()">
                Clear
            </Button>
        </div>
        <div v-if="props.disabled" class="mb-3 rounded-md border bg-muted/30 px-3 py-2 text-sm text-muted-foreground">
            Pilih cabang dulu untuk checkout.
        </div>
        <!-- Empty state -->
        <div v-if="cart.isEmpty" class="py-6 text-center text-sm text-muted-foreground">
            Belum ada barang
        </div>

        <div v-else class="space-y-4">
            <!-- Cart items -->
            <div class="space-y-3">
                <div v-for="item in cart.items" :key="item.id"
                    class="flex items-center justify-between gap-3 rounded-lg border p-3">
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
                        <div class="flex items-center h-8 rounded-lg border bg-background overflow-hidden">
                            <button
                                class="h-8 w-8 flex items-center justify-center hover:bg-muted border-r text-sm font-medium disabled:opacity-50"
                                @click="cart.decrease(item.id)"
                                :disabled="item.qty <= 1"
                            >
                                −
                            </button>
                            <span class="h-8 w-8 flex items-center justify-center text-sm font-semibold bg-background">
                                {{ item.qty }}
                            </span>
                            <button
                                class="h-8 w-8 flex items-center justify-center hover:bg-muted border-l text-sm font-medium"
                                @click="cart.increase(item.id)"
                            >
                                +
                            </button>
                        </div>

                        <Button variant="destructive" size="sm" class="h-8 px-3" @click="cart.remove(item.id)">
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
                        <SelectItem v-for="c in customers" :key="c.id" :value="c.id">
                            {{ c.name }}
                        </SelectItem>
                    </SelectContent>
                </Select>

                <div class="flex gap-2">
                    <Input v-model="newCustomerName" placeholder="Tambah customer baru..." />
                    <Button type="button" variant="outline" @click="addCustomer">
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

                <RadioGroup v-model="discountMode" class="flex items-center gap-6">
                    <div class="flex items-center gap-2">
                        <RadioGroupItem id="disc_percent" value="percent" />
                        <Label for="disc_percent">Persen (%)</Label>
                    </div>
                    <div class="flex items-center gap-2">
                        <RadioGroupItem id="disc_amount" value="amount" />
                        <Label for="disc_amount">Nominal (Rp)</Label>
                    </div>
                </RadioGroup>

                <NumberField v-model="discountInput" :min="0" :max="discountMode === 'percent' ? 100 : undefined">
                  <NumberFieldContent>
                    <NumberFieldDecrement />
                    <NumberFieldInput :placeholder="discountMode === 'percent' ? 'Contoh: 10' : 'Contoh: 20000'" />
                    <NumberFieldIncrement />
                  </NumberFieldContent>
                </NumberField>
            </div>

            <!-- Cash received -->
            <div v-if="paymentMethod === 'cash'" class="space-y-2">
                <Label>Uang diterima</Label>
                <NumberField v-model="cashReceived" :min="0" :step="1000">
                  <NumberFieldContent>
                    <NumberFieldDecrement />
                    <NumberFieldInput placeholder="Contoh: 500000" />
                    <NumberFieldIncrement />
                  </NumberFieldContent>
                </NumberField>
                <div class="flex justify-between text-sm">
                    <span class="text-muted-foreground">Kembalian</span>
                    <span class="font-semibold">Rp {{ formatIDR(change) }}</span>
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
                    <span class="font-semibold">Rp {{ formatIDR(subtotal) }}</span>
                </div>

                <div class="flex justify-between text-sm">
                    <span class="text-muted-foreground">Diskon</span>
                    <span class="font-semibold">- Rp {{ formatIDR(discountAmount) }}</span>
                </div>

                <div class="flex justify-between text-lg">
                    <span class="text-muted-foreground">Total</span>
                    <span class="text-xl font-bold">Rp {{ formatIDR(grandTotal) }}</span>
                </div>

                <Button class="mt-2 w-full" :disabled="cart.isEmpty || !isCashPaidEnough || isProcessing" @click="checkout">
                    <Spinner v-if="isProcessing" class="mr-2 size-4" />
                    {{ isProcessing ? 'Memproses…' : 'Checkout' }}
                </Button>
            </div>
        </div>
    </div>
</template>
