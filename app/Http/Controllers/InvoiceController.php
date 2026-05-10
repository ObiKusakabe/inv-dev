<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InvoiceController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Invoice::query()
            ->with([
                'branch:id,name,code',
                'customer:id,name',
                'items:id,invoice_id,product_id,qty,price,subtotal',
                'items.product:id,name',
            ])
            ->orderBy('created_at', 'desc');

        // Filter by branch
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('invoice_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('invoice_date', '<=', $request->date_to);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by invoice number
        if ($request->filled('search')) {
            $query->where('invoice_number', 'like', '%'.$request->search.'%');
        }

        $invoices = $query->get();

        // Get filter options
        $branches = Branch::where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return Inertia::render('invoices/Index', [
            'invoices' => $invoices,
            'branches' => $branches,
            'filters' => [
                'branch_id' => $request->branch_id,
                'date_from' => $request->date_from,
                'date_to' => $request->date_to,
                'status' => $request->status,
                'search' => $request->search,
            ],
        ]);
    }

    public function show(Invoice $invoice): Response
    {
        $invoice->load([
            'branch:id,name,code,address,phone',
            'customer:id,name',
            'items:id,invoice_id,product_id,qty,price,subtotal',
            'items.product:id,name',
            'payments:id,invoice_id,method,amount,payment_date',
        ]);

        return Inertia::render('invoices/Show', [
            'invoice' => $invoice,
        ]);
    }
}
