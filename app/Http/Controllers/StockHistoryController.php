<?php

namespace App\Http\Controllers;

use App\Models\StockMovement;
use App\Models\Branch;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StockHistoryController extends Controller
{
    public function index(Request $request): Response
    {
        $query = StockMovement::query()
            ->with([
                'product:id,name,image',
                'branch:id,name,code',
                'user:id,name',
            ])
            ->orderBy('created_at', 'desc');

        // Filter by product
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        // Filter by branch
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        // Filter by type (IN/OUT)
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $movements = $query->get();

        // Get filter options
        $products = Product::orderBy('name')->get(['id', 'name']);
        $branches = Branch::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return Inertia::render('stockHistory/Index', [
            'movements' => $movements,
            'products' => $products,
            'branches' => $branches,
            'filters' => [
                'product_id' => $request->product_id,
                'branch_id' => $request->branch_id,
                'type' => $request->type,
                'date_from' => $request->date_from,
                'date_to' => $request->date_to,
            ],
        ]);
    }

    public function byProduct(Product $product): Response
    {
        $movements = StockMovement::query()
            ->where('product_id', $product->id)
            ->with([
                'branch:id,name,code',
                'user:id,name',
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('stockHistory/ByProduct', [
            'product' => $product->only(['id', 'name', 'image']),
            'movements' => $movements,
        ]);
    }
}
