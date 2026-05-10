<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductStoreRequest;
use App\Http\Requests\ProductUpdateRequest;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\SupplierData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function index(): Response
    {
        $products = Product::query()
            ->with([
                'category:id,name',
                'supplier:id,name',
            ])
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'category_id',
                'supplier_id',
                'size',
                'stock',
                'min_stock',
                'is_consignment',
                'supplier_price',
                'sell_price',
                'image',
                'created_at',
            ]);

        $categories = Category::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        $suppliers = SupplierData::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('products/Index', [
            'products' => $products,
            'categories' => $categories,
            'suppliers' => $suppliers,
        ]);
    }

    public function store(ProductStoreRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // defaults
        $data['stock'] = $data['stock'] ?? 0;
        $data['min_stock'] = $data['min_stock'] ?? 0;
        $data['is_consignment'] = (bool) ($data['is_consignment'] ?? false);

        // Handle image upload
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
            $data['image'] = $imagePath;
        }

        $product = Product::create($data);
        // 1) init stock record untuk semua cabang aktif
        $branches = Branch::query()
            ->where('is_active', true)
            ->get(['id']);

        foreach ($branches as $b) {
            ProductStock::firstOrCreate(
                ['branch_id' => $b->id, 'product_id' => $product->id],
                ['stock' => 0]
            );
        }

        // 2) set stok awal untuk cabang yang sedang aktif (topbar/session)
        $currentBranchId = session('current_branch_id');
        if ($currentBranchId) {
            ProductStock::where('branch_id', $currentBranchId)
                ->where('product_id', $product->id)
                ->update([
                    'stock' => (int) ($data['stock'] ?? 0),
                ]);
        }

        return redirect()->route('products.index');
    }

    public function update(ProductUpdateRequest $request, Product $product): RedirectResponse
    {
        $data = $request->validated();

        // Update product data (exclude stock - handled separately for multi-branch)
        $productData = collect($data)->except(['stock'])->toArray();
        $productData['is_consignment'] = (bool) ($data['is_consignment'] ?? false);

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }
            $imagePath = $request->file('image')->store('products', 'public');
            $productData['image'] = $imagePath;
        }

        $product->update($productData);

        // Update stock in current branch (product_stocks table)
        $currentBranchId = session('current_branch_id');
        if ($currentBranchId && isset($data['stock'])) {
            ProductStock::where('branch_id', $currentBranchId)
                ->where('product_id', $product->id)
                ->update(['stock' => (int) $data['stock']]);
        }

        return redirect()->route('products.index');
    }

    public function destroy(Product $product): RedirectResponse
    {
        // Delete image if exists
        if ($product->image && Storage::disk('public')->exists($product->image)) {
            Storage::disk('public')->delete($product->image);
        }

        ProductStock::where('product_id', $product->id)->delete();
        
        $product->delete();

        return redirect()->route('products.index');
    }
}
