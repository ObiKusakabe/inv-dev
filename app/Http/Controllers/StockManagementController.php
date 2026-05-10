<?php

namespace App\Http\Controllers;

use App\Models\ProductStock;
use App\Models\Branch;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class StockManagementController extends Controller
{
    public function index(Request $request): Response
    {
        $currentBranchId = session('current_branch_id');
        
        $query = ProductStock::query()
            ->with([
                'product:id,name,image',
                'branch:id,name,code',
            ]);
        
        // Filter by branch if selected
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }
        
        $stocks = $query->orderBy('id', 'desc')->get();
        
        $branches = Branch::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);
        
        return Inertia::render('stockManagement/Index', [
            'stocks' => $stocks,
            'branches' => $branches,
            'currentBranchId' => $currentBranchId,
            'filters' => [
                'branch_id' => $request->branch_id,
            ],
        ]);
    }

    public function update(Request $request, ProductStock $productStock): RedirectResponse
    {
        $data = $request->validate([
            'adjustment' => ['required', 'integer', 'not_in:0'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $adjustment = (int) $data['adjustment'];
        $newStock = $productStock->stock + $adjustment;

        // Prevent negative stock
        if ($newStock < 0) {
            return back()->withErrors(['adjustment' => 'Stok tidak boleh negatif.']);
        }

        // Update stock
        $productStock->update(['stock' => $newStock]);

        // Record movement
        StockMovement::create([
            'product_id' => $productStock->product_id,
            'branch_id' => $productStock->branch_id,
            'type' => $adjustment > 0 ? 'IN' : 'OUT',
            'qty' => abs($adjustment),
            'reason' => $data['reason'] ?: 'Penyesuaian stok manual',
            'created_by' => Auth::id(),
        ]);

        return back()->with('success', 'Stok berhasil diperbarui.');
    }
}
