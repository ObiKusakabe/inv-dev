<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { 
    TrendingUp, 
    TrendingDown, 
    Package, 
    ShoppingCart, 
    DollarSign, 
    AlertTriangle,
    ArrowUpRight,
    ArrowDownRight,
    Plus
} from 'lucide-vue-next';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { AreaChart } from '@/components/ui/chart';

interface Props {
    stats: {
        todaySales: number;
        todayTransactions: number;
        totalProducts: number;
        lowStockCount: number;
        weekGrowth: number;
    };
    sparklineData: {
        sales: number[];
        transactions: number[];
        products: number[];
        lowStock: number[];
    };
    lowStockProducts: Array<{
        id: number;
        name: string;
        stock: Array<{ quantity: number }>;
    }>;
    topProducts: Array<{
        id: number;
        name: string;
        total_sold: number;
    }>;
    salesChartData: Array<{
        date: string;
        sales: number;
    }>;
    recentTransactions: Array<{
        id: number;
        invoice_number: string;
        customer: string;
        total: number;
        created_at: string;
    }>;
}

const props = withDefaults(defineProps<Props>(), {
    sparklineData: () => ({
        sales: [0, 0, 0, 0, 0, 0, 0],
        transactions: [0, 0, 0, 0, 0, 0, 0],
        products: [0, 0, 0, 0, 0, 0, 0],
        lowStock: [0, 0, 0, 0, 0, 0, 0],
    }),
});

// Debug sparkline data
console.log('Sparkline Data:', props.sparklineData);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

// Sparkline SVG generator
function generateSparkline(data: number[], color: string = 'currentColor'): string {
    if (data.length === 0) return '';
    const max = Math.max(...data, 1);
    const min = Math.min(...data);
    const range = max - min || 1;
    const viewBoxWidth = 80;
    const viewBoxHeight = 30;
    const padding = 2;
    const points = data.map((value, i) => {
        const x = (i / (data.length - 1)) * viewBoxWidth;
        const y = viewBoxHeight - padding - ((value - min) / range) * (viewBoxHeight - 2 * padding);
        return `${x},${y}`;
    }).join(' ');
    
    return `data:image/svg+xml,${encodeURIComponent(`<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ${viewBoxWidth} ${viewBoxHeight}" preserveAspectRatio="none" width="100%" height="100%" fill="none" stroke="${color}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="${points}"/></svg>`)}`;
}

function isTrendUp(data: number[]): boolean {
    if (data.length < 2) return false;
    return data[data.length - 1] >= data[0];
}

function formatIDR(value: number): string {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value);
}

function quickCreate() {
    router.visit('/pos');
}
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Dashboard</h1>
                    <p class="text-muted-foreground">Ringkasan bisnis dan analitik toko Anda</p>
                </div>
                <Button @click="quickCreate">
                    <Plus class="mr-2 h-4 w-4" />
                    Quick Create
                </Button>
            </div>

            <!-- Stats Cards -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <!-- Sales Card -->
                <Card class="relative overflow-hidden">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Penjualan Hari Ini</CardTitle>
                        <DollarSign class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent class="pb-2">
                        <div class="text-2xl font-bold">{{ formatIDR(stats.todaySales) }}</div>
                        <div class="flex items-center text-xs text-muted-foreground mt-1">
                            <Badge :variant="stats.weekGrowth >= 0 ? 'default' : 'destructive'" class="mr-1">
                                <TrendingUp v-if="stats.weekGrowth >= 0" class="mr-1 h-3 w-3" />
                                <TrendingDown v-else class="mr-1 h-3 w-3" />
                                {{ stats.weekGrowth >= 0 ? '+' : '' }}{{ stats.weekGrowth }}%
                            </Badge>
                            vs minggu lalu
                        </div>
                    </CardContent>
                    <div class="h-16 w-full px-6 pb-4">
                        <img 
                            :src="generateSparkline(sparklineData.sales, isTrendUp(sparklineData.sales) ? '#10b981' : '#ef4444')" 
                            class="h-full w-full opacity-80 object-contain"
                            alt="trend"
                        />
                    </div>
                </Card>

                <!-- Transactions Card -->
                <Card class="relative overflow-hidden">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Transaksi Hari Ini</CardTitle>
                        <ShoppingCart class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent class="pb-2">
                        <div class="text-2xl font-bold">{{ stats.todayTransactions }}</div>
                        <p class="text-xs text-muted-foreground mt-1">Transaksi berhasil</p>
                    </CardContent>
                    <div class="h-16 w-full px-6 pb-4">
                        <img 
                            :src="generateSparkline(sparklineData.transactions, isTrendUp(sparklineData.transactions) ? '#10b981' : '#ef4444')" 
                            class="h-full w-full opacity-80 object-contain"
                            alt="trend"
                        />
                    </div>
                </Card>

                <!-- Products Card -->
                <Card class="relative overflow-hidden">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Produk</CardTitle>
                        <Package class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent class="pb-2">
                        <div class="text-2xl font-bold">{{ stats.totalProducts }}</div>
                        <Link :href="'/products'" class="text-xs text-muted-foreground hover:underline">
                            Lihat semua produk
                        </Link>
                    </CardContent>
                    <div class="h-16 w-full px-6 pb-4">
                        <img 
                            :src="generateSparkline(sparklineData.products, '#6366f1')" 
                            class="h-full w-full opacity-80 object-contain"
                            alt="trend"
                        />
                    </div>
                </Card>

                <!-- Low Stock Card -->
                <Card :class="[stats.lowStockCount > 0 ? 'border-red-200 dark:border-red-800' : '', 'relative overflow-hidden']">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Stok Rendah</CardTitle>
                        <AlertTriangle :class="stats.lowStockCount > 0 ? 'h-4 w-4 text-red-500' : 'h-4 w-4 text-muted-foreground'" />
                    </CardHeader>
                    <CardContent class="pb-2">
                        <div class="text-2xl font-bold" :class="stats.lowStockCount > 0 ? 'text-red-600' : ''">
                            {{ stats.lowStockCount }}
                        </div>
                        <Link :href="'/stockManagement'" class="text-xs text-muted-foreground hover:underline">
                            Kelola stok
                        </Link>
                    </CardContent>
                    <div class="h-16 w-full px-6 pb-4">
                        <img 
                            :src="generateSparkline(sparklineData.lowStock, '#ef4444')" 
                            class="h-full w-full opacity-80 object-contain"
                            alt="trend"
                        />
                    </div>
                </Card>
            </div>

            <!-- Main Content Grid -->
            <div class="grid gap-6 lg:grid-cols-7">
                <!-- Sales Area Chart -->
                <Card class="lg:col-span-4">
                    <CardHeader>
                        <CardTitle class="text-lg font-semibold">Total Penjualan</CardTitle>
                        <CardDescription>7 hari terakhir</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <AreaChart
                            :data="salesChartData"
                            :categories="['sales']"
                            index="date"
                            :colors="['#3b82f6']"
                            :show-legend="false"
                            :show-tooltip="true"
                        />
                    </CardContent>
                </Card>

                <!-- Right Column -->
                <div class="flex flex-col gap-6 lg:col-span-3">
                    <!-- Recent Transactions -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Transaksi Terbaru</CardTitle>
                            <CardDescription>{{ recentTransactions.length }} transaksi hari ini</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div 
                                v-for="transaction in recentTransactions" 
                                :key="transaction.id"
                                class="flex items-center justify-between p-3 rounded-lg border bg-card hover:bg-accent/50 transition-colors"
                            >
                                <div class="flex flex-col">
                                    <span class="font-medium text-sm">{{ transaction.invoice_number }}</span>
                                    <span class="text-xs text-muted-foreground">{{ transaction.customer }}</span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="font-semibold text-sm">{{ formatIDR(transaction.total) }}</span>
                                    <span class="text-xs text-muted-foreground">{{ transaction.created_at }}</span>
                                </div>
                            </div>
                            <div v-if="recentTransactions.length === 0" class="text-center py-8 text-muted-foreground">
                                Belum ada transaksi hari ini
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Top Products -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Produk Terlaris Hari Ini</CardTitle>
                            <CardDescription>Berdasarkan jumlah terjual</CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <div 
                                v-for="(product, index) in topProducts" 
                                :key="product.id"
                                class="flex items-center gap-3"
                            >
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary text-primary-foreground text-sm font-medium">
                                    {{ index + 1 }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium truncate">{{ product.name }}</p>
                                    <p class="text-xs text-muted-foreground">{{ product.total_sold }} terjual</p>
                                </div>
                            </div>
                            <div v-if="topProducts.length === 0" class="text-center py-4 text-muted-foreground text-sm">
                                Belum ada penjualan hari ini
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>

            <!-- Low Stock Alert Section -->
            <Card v-if="lowStockProducts.length > 0" class="border-red-200 dark:border-red-800">
                <CardHeader class="bg-red-50 dark:bg-red-900/20">
                    <div class="flex items-center gap-2">
                        <AlertTriangle class="h-5 w-5 text-red-600" />
                        <CardTitle class="text-red-800 dark:text-red-200">Peringatan Stok Rendah</CardTitle>
                    </div>
                    <CardDescription class="text-red-600/80 dark:text-red-300/80">
                        {{ lowStockProducts.length }} produk memerlukan perhatian segera
                    </CardDescription>
                </CardHeader>
                <CardContent class="pt-4">
                    <div class="grid gap-3 md:grid-cols-2 lg:grid-cols-3">
                        <div 
                            v-for="product in lowStockProducts" 
                            :key="product.id"
                            class="flex items-center justify-between p-3 rounded-lg border border-red-100 dark:border-red-800/50 bg-red-50/50 dark:bg-red-900/10"
                        >
                            <div>
                                <p class="font-medium text-sm">{{ product.name }}</p>
                                <p class="text-xs text-red-600">Sisa stok: {{ product.stock[0]?.quantity ?? 0 }}</p>
                            </div>
                            <Link :href="'/stockManagement'">
                                <Button variant="outline" size="sm" class="border-red-200 hover:bg-red-100">
                                    Restock
                                </Button>
                            </Link>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
