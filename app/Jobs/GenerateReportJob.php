<?php

namespace App\Jobs;

use App\Models\Invoice;
use App\Models\Product;
use App\Models\StockMovement;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GenerateReportJob implements ShouldQueue
{
    use Queueable;

    public $timeout = 300; // 5 minutes timeout
    public $tries = 3;    // 3 retry attempts

    private string $reportType;
    private string $startDate;
    private string $endDate;
    private ?int $userId;

    /**
     * Create a new job instance.
     */
    public function __construct(
        string $reportType = 'sales',
        string $startDate = null,
        string $endDate = null,
        ?int $userId = null
    ) {
        $this->reportType = $reportType;
        $this->startDate = $startDate ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->endDate = $endDate ?? Carbon::now()->format('Y-m-d');
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $startTime = microtime(true);
        $cacheKey = "report_{$this->reportType}_{$this->startDate}_{$this->endDate}";

        Log::info("Generating {$this->reportType} report", [
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'user_id' => $this->userId,
        ]);

        try {
            $data = match ($this->reportType) {
                'sales' => $this->generateSalesReport(),
                'inventory' => $this->generateInventoryReport(),
                'products' => $this->generateProductReport(),
                default => $this->generateSalesReport(),
            };

            // Store report in cache for quick access
            Cache::put($cacheKey, $data, now()->addHours(24));

            // Optionally store to storage for download
            $filename = "reports/{$this->reportType}_report_{$this->startDate}_{$this->endDate}.json";
            Storage::put($filename, json_encode($data));

            $duration = round(microtime(true) - $startTime, 2);
            Log::info("Report generated successfully", [
                'type' => $this->reportType,
                'duration' => $duration,
                'records' => count($data),
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to generate report", [
                'type' => $this->reportType,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Generate sales report data
     */
    private function generateSalesReport(): array
    {
        $data = [];

        // Process in chunks to avoid memory issues
        Invoice::whereBetween('created_at', [$this->startDate, $this->endDate])
            ->where('status', 'paid')
            ->select('id', 'total', 'created_at', 'branch_id')
            ->with(['items' => fn($q) => $q->select('invoice_id', 'product_id', 'qty', 'price', 'subtotal')])
            ->chunk(500, function ($invoices) use (&$data) {
                foreach ($invoices as $invoice) {
                    $data[] = [
                        'id' => $invoice->id,
                        'total' => $invoice->total,
                        'date' => $invoice->created_at->format('Y-m-d'),
                        'items_count' => $invoice->items->count(),
                        'items' => $invoice->items->map(fn($item) => [
                            'product_id' => $item->product_id,
                            'qty' => $item->qty,
                            'subtotal' => $item->subtotal,
                        ]),
                    ];
                }
            });

        return $data;
    }

    /**
     * Generate inventory report data
     */
    private function generateInventoryReport(): array
    {
        $data = [];

        StockMovement::whereBetween('created_at', [$this->startDate, $this->endDate])
            ->select('id', 'product_id', 'branch_id', 'type', 'quantity', 'created_at')
            ->with(['product:id,name', 'branch:id,name'])
            ->chunk(500, function ($movements) use (&$data) {
                foreach ($movements as $movement) {
                    $data[] = [
                        'id' => $movement->id,
                        'product' => $movement->product->name ?? 'Unknown',
                        'branch' => $movement->branch->name ?? 'Unknown',
                        'type' => $movement->type,
                        'quantity' => $movement->quantity,
                        'date' => $movement->created_at->format('Y-m-d H:i'),
                    ];
                }
            });

        return $data;
    }

    /**
     * Generate product report data
     */
    private function generateProductReport(): array
    {
        return Product::select('id', 'name', 'code', 'category_id', 'supplier_id', 'sell_price', 'buy_price')
            ->with([
                'category:id,name',
                'supplier:id,name',
                'stocks' => fn($q) => $q->select('product_id', 'branch_id', 'quantity')
            ])
            ->get()
            ->map(fn($product) => [
                'id' => $product->id,
                'name' => $product->name,
                'code' => $product->code,
                'category' => $product->category->name ?? 'N/A',
                'supplier' => $product->supplier->name ?? 'N/A',
                'price' => $product->sell_price,
                'total_stock' => $product->stocks->sum('quantity'),
            ])
            ->toArray();
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('GenerateReportJob failed after all retries', [
            'type' => $this->reportType,
            'error' => $exception->getMessage(),
        ]);
    }
}
