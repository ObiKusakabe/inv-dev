<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { ArrowLeft, Printer, Download, Receipt } from 'lucide-vue-next';
import { router } from '@inertiajs/vue3';
import { h } from 'vue';

interface InvoiceItem {
    id: number;
    product_id: number;
    qty: number;
    price: number;
    subtotal: number;
    product?: {
        id: number;
        name: string;
    };
}

interface Payment {
    id: number;
    method: string;
    amount: number;
    payment_date: string;
}

interface Branch {
    id: number;
    name: string;
    code: string;
    address?: string;
    phone?: string;
}

interface Customer {
    id: number;
    name: string;
}

interface Invoice {
    id: number;
    invoice_number: string;
    invoice_date: string;
    branch_id: number;
    customer_id?: number;
    subtotal: number;
    tax: number;
    discount_amount: number;
    total: number;
    status: string;
    payment_method: string;
    created_at: string;
    branch: Branch;
    customer?: Customer;
    items: InvoiceItem[];
    payments: Payment[];
}

const props = defineProps<{
    invoice: Invoice;
}>();

function formatIDR(v: number): string {
    return new Intl.NumberFormat('id-ID').format(Math.max(0, Math.round(v || 0)));
}

function formatDate(date: string): string {
    return new Date(date).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
    });
}

function formatDateTime(date: string): string {
    return new Date(date).toLocaleString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

function goBack() {
    router.visit('/invoices');
}

function printInvoice() {
    window.print();
}

function downloadPDF() {
    // Create a simple PDF using html2canvas and jsPDF approach
    const printWindow = window.open('', '_blank');
    if (!printWindow) return;

    const invoice = props.invoice;
    const itemsHtml = invoice.items.map(item => `
        <tr>
            <td>${item.product?.name || 'Produk #' + item.product_id}</td>
            <td style="text-align: center;">${item.qty}</td>
            <td style="text-align: right;">Rp ${formatIDR(item.price)}</td>
            <td style="text-align: right;">Rp ${formatIDR(item.subtotal)}</td>
        </tr>
    `).join('');

    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Invoice ${invoice.invoice_number}</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .header { text-align: center; margin-bottom: 20px; }
                .header h1 { margin: 0; font-size: 24px; }
                .header p { margin: 5px 0; color: #666; }
                .info { margin-bottom: 20px; }
                .info-row { display: flex; justify-content: space-between; margin-bottom: 5px; }
                table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                th, td { padding: 10px; border-bottom: 1px solid #ddd; }
                th { background: #f5f5f5; text-align: left; }
                .totals { margin-top: 20px; }
                .total-row { display: flex; justify-content: space-between; padding: 5px 0; }
                .grand-total { font-size: 18px; font-weight: bold; border-top: 2px solid #333; padding-top: 10px; margin-top: 10px; }
                .footer { margin-top: 40px; text-align: center; color: #666; font-size: 12px; }
                @media print { body { margin: 0; } }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>${invoice.branch.name}</h1>
                <p>${invoice.branch.address || ''}</p>
                <p>Telp: ${invoice.branch.phone || '-'}</p>
                <hr style="margin: 20px 0;">
                <h2>INVOICE</h2>
                <p>No: ${invoice.invoice_number}</p>
            </div>
            
            <div class="info">
                <div class="info-row">
                    <span><strong>Tanggal:</strong> ${formatDate(invoice.invoice_date)}</span>
                    <span><strong>Status:</strong> ${invoice.status.toUpperCase()}</span>
                </div>
                <div class="info-row">
                    <span><strong>Pelanggan:</strong> ${invoice.customer?.name || 'Walk-in Customer'}</span>
                    <span><strong>Metode:</strong> ${invoice.payment_method.toUpperCase()}</span>
                </div>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th style="text-align: center;">Qty</th>
                        <th style="text-align: right;">Harga</th>
                        <th style="text-align: right;">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    ${itemsHtml}
                </tbody>
            </table>
            
            <div class="totals">
                <div class="total-row">
                    <span>Subtotal:</span>
                    <span>Rp ${formatIDR(invoice.subtotal)}</span>
                </div>
                ${invoice.discount_amount > 0 ? `
                <div class="total-row">
                    <span>Diskon:</span>
                    <span>- Rp ${formatIDR(invoice.discount_amount)}</span>
                </div>
                ` : ''}
                ${invoice.tax > 0 ? `
                <div class="total-row">
                    <span>PPN:</span>
                    <span>Rp ${formatIDR(invoice.tax)}</span>
                </div>
                ` : ''}
                <div class="total-row grand-total">
                    <span>TOTAL:</span>
                    <span>Rp ${formatIDR(invoice.total)}</span>
                </div>
            </div>
            
            <div class="footer">
                <p>Terima kasih telah berbelanja!</p>
                <p>Dicetak pada: ${formatDateTime(new Date().toISOString())}</p>
            </div>
            
            <script>
                window.onload = function() {
                    window.print();
                    setTimeout(function() { window.close(); }, 1000);
                };
            <\/script>
        </body>
        </html>
    `);
    printWindow.document.close();
}
</script>

<template>
    <div class="min-h-screen bg-background">
        <!-- Header -->
        <div class="border-b bg-card sticky top-0 z-10 print:hidden">
            <div class="mx-auto max-w-4xl px-4 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <Button variant="outline" size="sm" @click="goBack">
                            <ArrowLeft class="mr-2 h-4 w-4" />
                            Kembali
                        </Button>
                        <h1 class="text-xl font-semibold">Detail Invoice</h1>
                    </div>
                    <div class="flex gap-2">
                        <Button variant="outline" size="sm" @click="printInvoice">
                            <Printer class="mr-2 h-4 w-4" />
                            Print
                        </Button>
                        <Button variant="default" size="sm" @click="downloadPDF">
                            <Download class="mr-2 h-4 w-4" />
                            PDF
                        </Button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Invoice Content -->
        <div class="mx-auto max-w-4xl px-4 py-8">
            <Card class="print:shadow-none print:border-none">
                <CardHeader class="border-b pb-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <CardTitle class="text-2xl">{{ invoice.branch.name }}</CardTitle>
                            <p class="text-sm text-muted-foreground mt-1">
                                {{ invoice.branch.address }}
                            </p>
                            <p class="text-sm text-muted-foreground">
                                Telp: {{ invoice.branch.phone || '-' }}
                            </p>
                        </div>
                        <div class="text-right">
                            <div class="flex items-center gap-2 justify-end">
                                <Receipt class="h-6 w-6 text-primary" />
                                <span class="text-lg font-bold">INVOICE</span>
                            </div>
                            <p class="text-sm font-mono mt-1">{{ invoice.invoice_number }}</p>
                        </div>
                    </div>
                </CardHeader>

                <CardContent class="pt-6 space-y-6">
                    <!-- Invoice Info -->
                    <div class="grid grid-cols-2 gap-8">
                        <div>
                            <p class="text-sm text-muted-foreground mb-1">Pelanggan</p>
                            <p class="font-medium">{{ invoice.customer?.name || 'Walk-in Customer' }}</p>
                        </div>
                        <div class="text-right">
                            <div class="space-y-1">
                                <div class="flex justify-between gap-8">
                                    <span class="text-sm text-muted-foreground">Tanggal:</span>
                                    <span class="font-medium">{{ formatDate(invoice.invoice_date) }}</span>
                                </div>
                                <div class="flex justify-between gap-8">
                                    <span class="text-sm text-muted-foreground">Status:</span>
                                    <span>
                                        <Badge v-if="invoice.status === 'paid'" class="bg-emerald-500 hover:bg-emerald-600 text-white">LUNAS</Badge>
                                        <Badge v-else-if="invoice.status === 'unpaid'" variant="secondary">BELUM LUNAS</Badge>
                                        <Badge v-else variant="destructive">BATAL</Badge>
                                    </span>
                                </div>
                                <div class="flex justify-between gap-8">
                                    <span class="text-sm text-muted-foreground">Metode:</span>
                                    <span class="font-medium uppercase">{{ invoice.payment_method }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <Separator />

                    <!-- Items Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[400px]">
                            <thead>
                                <tr class="border-b">
                                    <th class="text-left py-3 font-medium text-sm">Produk</th>
                                    <th class="text-center py-3 font-medium text-sm w-16">Qty</th>
                                    <th class="text-right py-3 font-medium text-sm w-28">Harga</th>
                                    <th class="text-right py-3 font-medium text-sm w-28">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in invoice.items" :key="item.id" class="border-b">
                                    <td class="py-3">{{ item.product?.name || 'Produk #' + item.product_id }}</td>
                                    <td class="py-3 text-center">{{ item.qty }}</td>
                                    <td class="py-3 text-right">Rp {{ formatIDR(item.price) }}</td>
                                    <td class="py-3 text-right">Rp {{ formatIDR(item.subtotal) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Totals -->
                    <div class="flex justify-end">
                        <div class="w-full max-w-xs space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-muted-foreground">Subtotal</span>
                                <span>Rp {{ formatIDR(invoice.subtotal) }}</span>
                            </div>
                            <div v-if="invoice.discount_amount > 0" class="flex justify-between text-sm">
                                <span class="text-muted-foreground">Diskon</span>
                                <span class="text-red-600">- Rp {{ formatIDR(invoice.discount_amount) }}</span>
                            </div>
                            <div v-if="invoice.tax > 0" class="flex justify-between text-sm">
                                <span class="text-muted-foreground">PPN</span>
                                <span>Rp {{ formatIDR(invoice.tax) }}</span>
                            </div>
                            <Separator />
                            <div class="flex justify-between text-lg font-bold">
                                <span>TOTAL</span>
                                <span class="text-primary">Rp {{ formatIDR(invoice.total) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Payments -->
                    <div v-if="invoice.payments.length > 0" class="bg-muted/50 rounded-lg p-4">
                        <p class="font-medium mb-3">Riwayat Pembayaran</p>
                        <div class="space-y-2">
                            <div v-for="payment in invoice.payments" :key="payment.id"
                                class="flex justify-between items-center py-2 border-b last:border-0">
                                <div>
                                    <p class="font-medium">{{ payment.method.toUpperCase() }}</p>
                                    <p class="text-sm text-muted-foreground">{{ formatDateTime(payment.payment_date) }}</p>
                                </div>
                                <span class="font-medium">Rp {{ formatIDR(payment.amount) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="text-center text-sm text-muted-foreground pt-8 border-t print:mt-8">
                        <p class="font-medium text-foreground">Terima kasih telah berbelanja!</p>
                        <p class="mt-1">Invoice dicetak pada {{ formatDateTime(new Date().toISOString()) }}</p>
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>
</template>

<style scoped>
@media print {
    .print\:hidden {
        display: none !important;
    }
    .print\:shadow-none {
        box-shadow: none !important;
    }
    .print\:border-none {
        border: none !important;
    }
    body {
        background: white !important;
    }
}
</style>
