<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Product;
use App\Models\ProductStock;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class BranchController extends Controller
{
    public function select(Request $request)
    {
        $data = $request->validate([
            'branch_id' => [
                'required', 
                'integer',
                Rule::exists('branches','id')->where('is_active', true)],
        ]);

        session(['current_branch_id' => (int) $data['branch_id']]);

        return back();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:20', 'unique:branches,code'],
        ]);

        $code = $data['code'] ?? null;
        if (! $code) {
            // Auto code: CBG1, CBG2, ...
            $next = (int) (Branch::query()->max('id') ?? 0) + 1;
            $code = 'CBG'.$next;
        }

        $branch = Branch::create([
            'name' => $data['name'],
            'code' => Str::upper($code),
            'is_active' => true,
        ]);

        $products = Product::query()->get(['id']);

        foreach ($products as $p) {
            ProductStock::firstOrCreate(
                ['branch_id' => $branch->id, 'product_id' => $p->id],
                ['stock' => 0]
            );
        }

        session(['current_branch_id' => (int) $branch->id]);

        return redirect()->route('pos.index')->with('success', 'Cabang dibuat & dipilih.');
    }

    public function index()
    {
        $branches = Branch::query()
            ->orderBy('name')
            ->get();

        return Inertia::render('branches/Index', [
            'branches' => $branches,
        ]);
    }

    public function create()
    {
        return Inertia::render('branches/Create');
    }
}
