<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Product;
use App\Models\StockMovement;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $cacheKey = 'dashboard_' . $today->format('Y-m-d');
        $cacheMinutes = 5; // Cache dashboard data for 5 minutes

        // Today's stats - fresh data
        $todaySales = Invoice::whereDate('created_at', $today)
            ->where('status', 'paid')
            ->sum('total');
        
        $todayTransactions = Invoice::whereDate('created_at', $today)
            ->where('status', 'paid')
            ->count();

        // yal products (cached for 1 hour as it rarely changes)
        $totalProducts = Cache::remember('total_products_count', 3600, function () {
            return Product::count();
        });
        
        // Low stock products (cached for 10 minutes)
        $lowStockProducts = Cache::remember('low_stock_products', 600, function () {
            return Product::whereHas('stocks', function ($query) {
                $query->where('quantity', '<=', 10);
            })->with(['stocks' => function ($q) {
                $q->select('id', 'product_id', 'quantity');
            }])->select('id', 'name')->limit(5)->get();
        });

        // Top selling products today (cached for 30 minutes)
        $topProducts = Cache::remember('top_products_' . $today->format('Y-m-d'), 1800, function () use ($today) {
            return Product::select('products.id', 'products.name')
                ->join('invoice_items', 'products.id', '=', 'invoice_items.product_id')
                ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
                ->whereDate('invoices.created_at', $today)
                ->where('invoices.status', 'paid')
                ->selectRaw('SUM(invoice_items.qty) as total_sold')
                ->groupBy('products.id', 'products.name')
                ->orderByDesc('total_sold')
                ->limit(5)
                ->get();
        });

        // Chart data (cached for 15 minutes)
        $chartData = Cache::remember('dashboard_chart_' . $today->format('Y-m-d'), 900, function () {
            $sevenDaysAgo = Carbon::today()->subDays(6)->startOfDay();
            
            // Single query untuk semua daily stats
            $dailyStats = DB::table('invoices')
                ->where('status', 'paid')
                ->whereBetween('created_at', [$sevenDaysAgo, Carbon::now()])
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('SUM(total) as total_sales'),
                    DB::raw('COUNT(*) as transaction_count')
                )
                ->groupBy('date')
                ->get()
                ->keyBy('date');
            
            // Batch query: get products created per day in one query
            $productsByDate = DB::table('products')
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('COUNT(*) as count')
                )
                ->whereBetween('created_at', [$sevenDaysAgo->copy()->subYear(), Carbon::now()]) // Buffer for cumulative
                ->groupBy('date')
                ->get()
                ->keyBy('date');
            
            // Batch query: get low stock products count per day
            $lowStockByDate = DB::table('products')
                ->join('product_stocks', 'products.id', '=', 'product_stocks.product_id')
                ->where('product_stocks.stock', '<=', DB::raw('product_stocks.min_stock'))
                ->select(
                    DB::raw('DATE(products.updated_at) as date'),
                    DB::raw('COUNT(DISTINCT products.id) as count')
                )
                ->whereBetween('products.updated_at', [$sevenDaysAgo->copy()->subMonth(), Carbon::now()])
                ->groupBy('date')
                ->get()
                ->keyBy('date');
            
            $salesChartData = [];
            $transactionsChartData = [];
            $productsChartData = [];
            $lowStockChartData = [];
            
            // Cumulative counters
            $cumulativeProducts = Product::whereDate('created_at', '<', $sevenDaysAgo)->count();
            $cumulativeLowStock = Product::whereHas('stocks', function ($q) {
                $q->where('quantity', '<=', 10);
            })->whereDate('updated_at', '<', $sevenDaysAgo)->count();
            
            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::today()->subDays($i);
                $dateStr = $date->format('Y-m-d');
                
                // Sales stats
                $stats = $dailyStats->get($dateStr);
                $salesChartData[] = (int) ($stats->total_sales ?? 0);
                $transactionsChartData[] = (int) ($stats->transaction_count ?? 0);
                
                // Cumulative products (add daily count to running total)
                $dailyProducts = $productsByDate->get($dateStr)?->count ?? 0;
                $cumulativeProducts += $dailyProducts;
                $productsChartData[] = $cumulativeProducts;
                
                // Cumulative low stock
                $dailyLowStock = $lowStockByDate->get($dateStr)?->count ?? 0;
                $cumulativeLowStock = max(0, $cumulativeLowStock + $dailyLowStock); // Ensure not negative
                $lowStockChartData[] = $cumulativeLowStock;
            }
            
            return [
                'sales' => $salesChartData,
                'transactions' => $transactionsChartData,
                'products' => $productsChartData,
                'lowStock' => $lowStockChartData,
            ];
        });

        // Recent transactions (fresh data, limited to 5)
        $recentTransactions = Invoice::with(['customer:id,name'])
            ->where('status', 'paid')
            ->select('id', 'invoice_number', 'customer_id', 'total', 'created_at')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(function ($invoice) {
                return [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'customer' => $invoice->customer?->name ?? 'Walk-in Customer',
                    'total' => $invoice->total,
                    'created_at' => $invoice->created_at->format('H:i'),
                ];
            });

        // Weekly comparison (cached for 1 hour)
        $weekGrowth = Cache::remember('week_growth', 3600, function () {
            $thisWeekSales = Invoice::whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()])
                ->where('status', 'paid')
                ->sum('total');
            
            $lastWeekSales = Invoice::whereBetween('created_at', [Carbon::now()->subWeek()->startOfWeek(), Carbon::now()->subWeek()->endOfWeek()])
                ->where('status', 'paid')
                ->sum('total');
            
            return $lastWeekSales > 0 
                ? round((($thisWeekSales - $lastWeekSales) / $lastWeekSales) * 100, 1)
                : 0;
        });

        return Inertia::render('Dashboard', [
            'stats' => [
                'todaySales' => $todaySales,
                'todayTransactions' => $todayTransactions,
                'totalProducts' => $totalProducts,
                'lowStockCount' => $lowStockProducts->count(),
                'weekGrowth' => $weekGrowth,
            ],
            'sparklineData' => [
                'sales' => $chartData['sales'],
                'transactions' => $chartData['transactions'],
                'products' => $chartData['products'],
                'lowStock' => $chartData['lowStock'],
            ],
            'lowStockProducts' => $lowStockProducts,
            'topProducts' => $topProducts,
            'salesChartData' => array_map(function($sales, $i) {
                return [
                    'date' => Carbon::today()->subDays(6-$i)->format('D'),
                    'sales' => $sales,
                ];
            }, $chartData['sales'], array_keys($chartData['sales'])),
            'recentTransactions' => $recentTransactions,
        ]);
    }
}
