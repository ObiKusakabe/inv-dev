<?php

namespace App\Http\Controllers;

use App\Http\Requests\SupplierStoreRequest;
use App\Http\Requests\SupplierUpdateRequest;
use App\Models\SupplierData;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SupplierDataController extends Controller
{
    public function index(): Response
    {
        $suppliers = SupplierData::query()
            ->orderBy('name')
            ->get(['id', 'name', 'contact', 'note', 'created_at']);

        return Inertia::render('supplierData/Index', [
            'suppliers' => $suppliers,
        ]);
    }

    public function store(SupplierStoreRequest $request): RedirectResponse
    {
        SupplierData::create($request->validated());

        return redirect()->route('supplierData.index');
    }

    public function update(SupplierUpdateRequest $request, SupplierData $supplier): RedirectResponse
    {
        $supplier->update($request->validated());

        return redirect()->route('supplierData.index');
    }

    public function destroy(SupplierData $supplier): RedirectResponse
    {
        $supplier->delete();

        return redirect()->route('supplierData.index');
    }
}