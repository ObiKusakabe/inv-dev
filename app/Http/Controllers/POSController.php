<?php

namespace App\Http\Controllers;

use App\Http\Requests\PosCheckoutRequest;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\StockMovement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;

class POSController extends Controller
{
    public function index()
    {
        $currentBranchId = session('current_branch_id');

        $branches = Branch::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        $branchesCount = $branches->count();

        // 1) Belum ada cabang sama sekali => arahkan ke setup cabang
        if ($branchesCount === 0) {
            session()->forget('current_branch_id');

            return redirect()
                ->route('branches.create')
                ->with('error', 'Buat cabang dulu sebelum menggunakan POS.');
        }

        // 2) Kalau ada session tapi branch-nya sudah tidak valid (deleted/nonaktif), anggap unset
        if ($currentBranchId && ! $branches->contains('id', (int) $currentBranchId)) {
            session()->forget('current_branch_id');
            $currentBranchId = null;
        }

        // 3) Kalau cuma ada 1 branch dan session belum diset => auto pilih
        if (! $currentBranchId && $branchesCount === 1) {
            $currentBranchId = (int) $branches->first()->id;
            session(['current_branch_id' => $currentBranchId]);
        }

        // 4) Kalau branch > 1 dan belum pilih => render POS, tapi modal harus muncul
        $branchMissing = ! $currentBranchId;

        if ($branchMissing) {
            return Inertia::render('pos/Index', [
                'products' => [],
                'branches' => $branches,
                'currentBranchId' => null,
                'branchMissing' => true,
            ]);
        }

        // 5) Normal: ambil stok cabang tsb
        $stocks = ProductStock::query()
            ->with(['product:id,name,sell_price,image,category_id', 'product.category:id,name'])
            ->where('branch_id', $currentBranchId)
            ->where('stock', '>=', 0)
            ->get()
            ->map(fn ($ps) => [
                'id' => $ps->product->id,
                'name' => $ps->product->name,
                'price' => (float) $ps->product->sell_price,
                'stock' => (int) $ps->stock,
                'image' => $ps->product->image ? asset('storage/' . $ps->product->image) : null,
                'category' => $ps->product->category?->name,
            ]);

        return Inertia::render('pos/Index', [
            'products' => $stocks,
            'branches' => $branches,
            'currentBranchId' => (int) $currentBranchId,
            'branchMissing' => false,
        ]);
    }

    public function checkout(PosCheckoutRequest $request)
    {

        $data = $request->validated();

        $branchId = session('current_branch_id');
        abort_unless($branchId, 422, 'Cabang belum dipilih, silahkan pilih cabang terlebih dahulu.');
        
        DB::transaction(function () use ($data, $branchId) {

            // ---- 1) Map qty by product id
            $qtyById = collect($data['items'])
                ->mapWithKeys(fn ($i) => [(string) $i['id'] => (int) $i['qty']]);

            $productIds = $qtyById->keys()->values()->all();

            // ---- 2) Lock products (anti oversell)
            $stocks = ProductStock::query()
                ->with(['product:id,name,sell_price,supplier_id'])
                ->where('branch_id', $branchId)
                ->whereIn('product_id', $productIds)
                ->lockForUpdate()
                ->get()
                ->keyBy(fn ($ps) => (int) $ps->product_id);

            foreach ($productIds as $pid) {
                if (! $stocks->has((int) $pid)) {
                    abort(422, "Stok cabang untuk produk ini belum dibuat: {$pid}");
                }
            }

            // ---- 3) Hitung subtotal + cek stok (server-side)
            $subtotal = 0;

            foreach ($qtyById as $pid => $qty) {
                $ps = $stocks[$pid];
                $p = $ps->product;

                $stock = (int) ($ps->stock ?? 0);
                if ($qty > $stock) {
                    abort(422, "Stok produk '{$p->name}' tidak cukup. Tersedia {$stock}, diminta {$qty}");
                }

                $price = (float) ($p->sell_price ?? 0);
                $subtotal += $price * $qty;
            }

            // ---- 4) Diskon server-side
            $discountMode = $data['discount_mode'] ?? 'percent';
            $discountInput = (float) ($data['discount_input'] ?? 0);

            $discountAmount = 0;

            if ($discountInput > 0) {
                if ($discountMode === 'percent') {
                    $pct = min(100, max(0, $discountInput));
                    $discountAmount = (int) round($subtotal * $pct / 100);
                } else {
                    $discountAmount = (int) min($subtotal, max(0, round($discountInput)));
                }
            }

            $total = max(0, $subtotal - $discountAmount);

            // ---- 5) Cash validation
            $paymentMethod = $data['payment_method'];
            $cashReceived = (float) ($data['cash_received'] ?? 0);

            if ($paymentMethod === 'cash' && $cashReceived < $total) {
                abort(422, 'Uang diterima kurang dari total.');
            }

            // ---- 6) Customer optional
            $customerId = $data['customer_id'] ?? null;

            if (! $customerId && ! empty($data['customer_name'])) {
                $customer = Customer::firstOrCreate(
                    ['name' => $data['customer_name']],
                    ['code' => 'CUST-'.Str::upper(Str::random(6))]
                );

                $customerId = $customer->id;
            }

            // ---- 7) Generate invoice number (CBG1-010126-0001)
            $branchCode = Branch::where('id', $branchId)->value('code') ?? 'CBG'; // nanti bisa ambil dari branch user
            $datePart = now()->format('dmy');
            $prefix = "{$branchCode}-{$datePart}-";

            $last = Invoice::query()
                ->where('invoice_number', 'like', $prefix.'%')
                ->orderByDesc('invoice_number')
                ->value('invoice_number');

            $nextSeq = 1;

            if ($last) {
                $lastSeq = (int) substr($last, -4);
                $nextSeq = $lastSeq + 1;
            }

            $invoiceNumber = $prefix.str_pad((string) $nextSeq, 4, '0', STR_PAD_LEFT);

            // ---- 8) Create invoice (match schema inv-dev)
            $invoice = Invoice::create([
                'invoice_number' => $invoiceNumber,
                'invoice_date' => now(),
                'branch_id' => $branchId,
                'customer_id' => $customerId,
                'subtotal' => $subtotal,
                'tax' => 0,
                'discount_amount' => $discountAmount,
                'total' => $total,
                'status' => 'paid',
                'payment_method' => $paymentMethod,
            ]);

            // ---- 9) Create invoice items + stock movement + update stock
            foreach ($qtyById as $pid => $qty) {
                $ps = $stocks[$pid];
                $p = $ps->product;

                $price = (float) ($p->sell_price ?? 0);
                $lineSubtotal = $price * $qty;

                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $p->id,
                    'supplier_id' => $p->supplier_id,
                    'qty' => $qty,
                    'price' => $price,
                    'subtotal' => $lineSubtotal,
                ]);

                StockMovement::create([
                    'product_id' => $p->id,
                    'supplier_id' => $p->supplier_id,
                    'branch_id' => $branchId,
                    'invoice_id' => $invoice->id,
                    'type' => 'OUT',
                    'qty' => $qty,
                    'reason' => 'POS',
                    'note' => 'POS Transaction '.$invoiceNumber,
                    'created_by' => Auth::id(),
                ]);

                // Lock and decrement stock to prevent race conditions
                $updated = ProductStock::where('branch_id', $branchId)
                    ->where('product_id', $p->id)
                    ->where('stock', '>=', $qty)
                    ->decrement('stock', $qty);

                if ($updated === 0) {
                    abort(422, "Gagal mengurangi stok produk '{$p->name}'. Stok mungkin sudah habis.");
                }
            }

            // ---- 10) Create payment
            Payment::create([
                'invoice_id' => $invoice->id,
                'payment_date' => now()->toDateString(),
                'method' => $paymentMethod,
                'amount' => $total,
                'received_by' => Auth::id(),
                'note' => 'POS Payment',
            ]);
        });

        return redirect()
            ->route('pos.index')
            ->with('success', 'Transaksi berhasil.');
    }
}
