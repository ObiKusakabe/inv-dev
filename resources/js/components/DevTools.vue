<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Trash2, AlertTriangle, Database } from 'lucide-vue-next';
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';

const confirmTextMyData = ref('');
const confirmTextAllData = ref('');
const deleteMyDataErrors = ref<Record<string, string>>({});
const deleteAllDataErrors = ref<Record<string, string>>({});
const processingMyData = ref(false);
const processingAllData = ref(false);

function deleteMyData() {
    if (confirmTextMyData.value !== 'HAPUS DATA SAYA') {
        deleteMyDataErrors.value = { confirm: 'Ketik "HAPUS DATA SAYA" untuk konfirmasi' };
        return;
    }

    processingMyData.value = true;
    router.delete('/settings/dev-tools/my-data', {
        onSuccess: () => {
            confirmTextMyData.value = '';
            processingMyData.value = false;
            toast.success('Data berhasil dihapus', { description: 'Semua data terkait akun Anda telah dihapus.' });
            window.location.reload();
        },
        onError: (errors) => {
            deleteMyDataErrors.value = errors;
            processingMyData.value = false;
            toast.error('Gagal menghapus data', { description: errors.message || 'Terjadi kesalahan.' });
        },
    });
}

function deleteAllData() {
    if (confirmTextAllData.value !== 'HAPUS SEMUA DATA') {
        deleteAllDataErrors.value = { confirm: 'Ketik "HAPUS SEMUA DATA" untuk konfirmasi' };
        return;
    }

    processingAllData.value = true;
    router.delete('/settings/dev-tools/all-data', {
        onSuccess: () => {
            confirmTextAllData.value = '';
            processingAllData.value = false;
            toast.success('Semua data dihapus', { description: 'Sistem telah dibersihkan. Akun Anda tetap aman.' });
            window.location.reload();
        },
        onError: (errors) => {
            deleteAllDataErrors.value = errors;
            processingAllData.value = false;
            toast.error('Gagal menghapus data', { description: errors.message || 'Terjadi kesalahan.' });
        },
    });
}
</script>

<template>
    <div class="space-y-6 border-t pt-6 mt-6">
        <div class="flex items-center gap-2">
            <Database class="h-5 w-5 text-amber-600" />
            <HeadingSmall
                title="Dev Tools - Data Management"
                description="Hapus data untuk keperluan testing/development"
            />
        </div>

        <div class="space-y-4 rounded-lg border border-amber-200 bg-amber-50 p-4 dark:border-amber-200/10 dark:bg-amber-700/10">
            <div class="relative space-y-0.5 text-amber-800 dark:text-amber-100">
                <p class="font-medium flex items-center gap-2">
                    <AlertTriangle class="h-4 w-4" />
                    Zona Berbahaya - Dev Only
                </p>
                <p class="text-sm">
                    Fitur ini hanya untuk development. Semua data yang dihapus tidak dapat dikembalikan.
                </p>
            </div>

            <div class="flex flex-wrap gap-3">
                <!-- Delete My Data Dialog -->
                <Dialog>
                    <DialogTrigger as-child>
                        <Button variant="outline" class="border-amber-300 hover:bg-amber-100">
                            <Trash2 class="mr-2 h-4 w-4" />
                            Hapus Data Saya
                        </Button>
                    </DialogTrigger>
                    <DialogContent>
                        <DialogHeader class="space-y-3">
                            <DialogTitle class="text-amber-600 flex items-center gap-2">
                                <AlertTriangle class="h-5 w-5" />
                                Hapus Data Terkait Akun Saya
                            </DialogTitle>
                            <DialogDescription>
                                Ini akan menghapus semua data yang terkait dengan akun Anda:
                                <ul class="list-disc ml-5 mt-2 space-y-1 text-sm">
                                    <li>Invoice yang Anda buat</li>
                                    <li>Transaksi & pembayaran</li>
                                    <li>Riwayat stok</li>
                                    <li>Data pelanggan yang Anda input</li>
                                </ul>
                                <p class="mt-3 font-medium text-red-600">
                                    Akun Anda akan tetap aman, hanya data transaksi yang dihapus.
                                </p>
                            </DialogDescription>
                        </DialogHeader>

                        <div class="grid gap-2">
                            <Label for="confirm-my-data">Konfirmasi</Label>
                            <Input
                                id="confirm-my-data"
                                v-model="confirmTextMyData"
                                placeholder='Ketik "HAPUS DATA SAYA"'
                            />
                            <p class="text-xs text-muted-foreground">Ketik "HAPUS DATA SAYA" untuk konfirmasi</p>
                            <InputError :message="deleteMyDataErrors.confirm" />
                        </div>

                        <DialogFooter class="gap-2">
                            <DialogClose as-child>
                                <Button variant="secondary">Batal</Button>
                            </DialogClose>
                            <Button
                                variant="destructive"
                                :disabled="processingMyData"
                                @click="deleteMyData"
                            >
                                {{ processingMyData ? 'Menghapus...' : 'Hapus Data Saya' }}
                            </Button>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>

                <!-- Delete All Data Dialog -->
                <Dialog>
                    <DialogTrigger as-child>
                        <Button variant="destructive">
                            <Trash2 class="mr-2 h-4 w-4" />
                            Hapus Semua Data
                        </Button>
                    </DialogTrigger>
                    <DialogContent>
                        <DialogHeader class="space-y-3">
                            <DialogTitle class="text-red-600 flex items-center gap-2">
                                <AlertTriangle class="h-5 w-5" />
                                Hapus SEMUA Data Sistem
                            </DialogTitle>
                            <DialogDescription>
                                Ini akan menghapus SELURUH data dalam sistem kecuali akun Anda:
                                <ul class="list-disc ml-5 mt-2 space-y-1 text-sm text-red-600">
                                    <li>Semua invoice dari semua user</li>
                                    <li>Semua transaksi & pembayaran</li>
                                    <li>Semua data stok dan riwayat</li>
                                    <li>Semua produk dan kategori</li>
                                    <li>Semua cabang dan pelanggan</li>
                                    <li>Semua akun user lain (kecuali Anda)</li>
                                </ul>
                                <p class="mt-3 font-bold text-red-600 bg-red-100 p-2 rounded">
                                    ⚠️ AKUN ANDA AKAN TETAP AMAN, TAPI SEMUA DATA LAIN AKAN HILANG!
                                </p>
                            </DialogDescription>
                        </DialogHeader>

                        <div class="grid gap-2">
                            <Label for="confirm-all-data">Konfirmasi</Label>
                            <Input
                                id="confirm-all-data"
                                v-model="confirmTextAllData"
                                placeholder='Ketik "HAPUS SEMUA DATA"'
                                class="border-red-300"
                            />
                            <p class="text-xs text-red-600 font-medium">Ketik "HAPUS SEMUA DATA" untuk konfirmasi</p>
                            <InputError :message="deleteAllDataErrors.confirm" />
                        </div>

                        <DialogFooter class="gap-2">
                            <DialogClose as-child>
                                <Button variant="secondary">Batal</Button>
                            </DialogClose>
                            <Button
                                variant="destructive"
                                :disabled="processingAllData"
                                @click="deleteAllData"
                            >
                                {{ processingAllData ? 'Menghapus...' : 'HAPUS SEMUA DATA' }}
                            </Button>
                        </DialogFooter>
                    </DialogContent>
                </Dialog>
            </div>
        </div>
    </div>
</template>
