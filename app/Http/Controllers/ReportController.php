<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateReportJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ReportController extends Controller
{
    /**
     * Show report generation page
     */
    public function index()
    {
        return Inertia::render('reports/Index', [
            'availableReports' => [
                'sales' => 'Laporan Penjualan',
                'inventory' => 'Laporan Stok',
                'products' => 'Laporan Produk',
            ],
        ]);
    }

    /**
     * Queue report generation
     */
    public function generate(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:sales,inventory,products',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        // Dispatch job to queue
        GenerateReportJob::dispatch(
            $validated['type'],
            $validated['start_date'],
            $validated['end_date'],
            auth()->id()
        );

        return redirect()->back()->with('message', 'Laporan sedang diproses. Anda akan diberitahu saat selesai.');
    }

    /**
     * Download generated report
     */
    public function download(string $type, string $startDate, string $endDate)
    {
        $filename = "reports/{$type}_report_{$startDate}_{$endDate}.json";
        
        if (!Storage::exists($filename)) {
            return redirect()->back()->with('error', 'Laporan belum tersedia. Silakan generate terlebih dahulu.');
        }

        return Storage::download($filename);
    }

    /**
     * Check report status
     */
    public function status(string $type, string $startDate, string $endDate)
    {
        $cacheKey = "report_{$type}_{$startDate}_{$endDate}";
        $exists = Cache::has($cacheKey);
        $filename = "reports/{$type}_report_{$startDate}_{$endDate}.json";
        $fileExists = Storage::exists($filename);

        return response()->json([
            'ready' => $exists || $fileExists,
            'cached' => $exists,
            'file_exists' => $fileExists,
        ]);
    }
}
