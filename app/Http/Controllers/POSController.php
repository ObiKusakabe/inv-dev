<?php

namespace App\Http\Controllers;

use App\Http\Requests\PosCheckoutRequest;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class POSController extends Controller
{
    public function index()
    {
        return inertia('pos/Index', [
            'products' => Product::select('id', 'name', 'price', 'stock')
                ->where('stock', '>', 0)
                ->get(),
        ]);
    }

    public function checkout(PosCheckoutRequest $request)
    {
        $data = $request->validated();

        DB::transaction(function () use ($data) {

            // ---- 1) Map qty by product id
            $qtyById = collect($data['items'])
                ->mapWithKeys(fn ($i) => [(string)$i['id'] => (int)$i['qty']]);

            $productIds = $qtyById->keys()->values()->all();

            // ---- 2) Lock products (anti oversell)
            $products = Product::query()
                ->whereIn('id', $productIds)
                ->lockForUpdate()
                ->get()
                ->keyBy(fn ($p) => (string)$p->id);

            foreach ($productIds as $pid) {
                if (!$products->has((string)$pid)) {
                    abort(422, "Produk tidak ditemukan: {$pid}");
                }
            }

            // ---- 3) Hitung subtotal + cek stok (server-side)
            $subtotal = 0;

            foreach ($qtyById as $pid => $qty) {
                $p = $products[$pid];

                $stock = (int)($p->stock ?? 0);
                if ($qty > $stock) {
                    abort(422, "Stok produk '{$p->name}' tidak cukup. Tersedia {$stock}, diminta {$qty}");
                }

                $price = (int)($p->price ?? 0);
                $subtotal += $price * $qty;
            }

            // ---- 4) Diskon server-side
            $discountMode = $data['discount_mode'] ?? 'percent';
            $discountInput = (float)($data['discount_input'] ?? 0);

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
            $cashReceived = (float)($data['cash_received'] ?? 0);

            if ($paymentMethod === 'cash' && $cashReceived < $total) {
                abort(422, "Uang diterima kurang dari total.");
            }

            // ---- 6) Customer optional
            $customerId = $data['customer_id'] ?? null;

            if (!$customerId && !empty($data['customer_name'])) {
                $customer = Customer::firstOrCreate(
                    ['name' => $data['customer_name']],
                    ['code' => 'CUST-' . Str::upper(Str::random(6))]
                );

                $customerId = $customer->id;
            }

            // ---- 7) Generate invoice number (CBG1-010126-0001)
            $branchCode = 'CBG1'; // nanti bisa ambil dari branch user
            $datePart = now()->format('dmy');
            $prefix = "{$branchCode}-{$datePart}-";

            $last = Invoice::query()
                ->where('invoice_number', 'like', $prefix . '%')
                ->orderByDesc('invoice_number')
                ->value('invoice_number');

            $nextSeq = 1;

            if ($last) {
                $lastSeq = (int) substr($last, -4);
                $nextSeq = $lastSeq + 1;
            }

            $invoiceNumber = $prefix . str_pad((string)$nextSeq, 4, '0', STR_PAD_LEFT);

            // ---- 8) Create invoice (match schema inv-dev)
            $invoice = Invoice::create([
                'invoice_number' => $invoiceNumber,
                'customer_id' => $validated['customer_id'] ?? null,
                'branch_id' => 1, // nanti kita rapikan
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'total' => $total,
            ]);

            // ---- 9) Create invoice items + stock movement + update stock
            foreach ($qtyById as $pid => $qty) {
                $p = $products[$pid];
                $price = (int)($p->price ?? 0);

                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $p->id,
                    'price' => $price,
                    'quantity' => $qty,
                ]);

                StockMovement::create([
                    'product_id' => $p->id,
                    'type' => 'OUT',
                    'quantity' => $qty,
                    'note' => 'POS Transaction ' . $invoiceNumber,
                    'user_id' => Auth::id(),
                ]);

                $p->update([
                    'stock' => (int)($p->stock ?? 0) - $qty,
                ]);
            }

            // ---- 10) Create payment
            Payment::create([
                'invoice_id' => $invoice->id,
                'method' => $paymentMethod,
                'amount' => $total,
                'paid_at' => now(),
            ]);
        });

        return redirect()
            ->route('pos.index')
            ->with('success', 'Transaksi berhasil.');
    }
}
