<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\StockMovement;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DevToolsController extends Controller
{
    /**
     * Delete all data related to the current user (except their account)
     */
    public function deleteMyData(Request $request)
    {
        $user = Auth::user();
        $userId = $user->id;
        
        \Log::info('DeleteMyData started for user: ' . $userId);

        $deletedCounts = DB::transaction(function () use ($userId) {
            // Get invoices created by this user
            $invoiceIds = Invoice::where('user_id', $userId)->pluck('id');
            \Log::info('Found invoices: ' . $invoiceIds->count());

            $counts = [
                'payments' => 0,
                'invoice_items' => 0,
                'stock_movements_invoice' => 0,
                'invoices' => 0,
                'stock_movements_user' => 0,
            ];

            if ($invoiceIds->isNotEmpty()) {
                $counts['payments'] = Payment::whereIn('invoice_id', $invoiceIds)->delete();
                $counts['invoice_items'] = InvoiceItem::whereIn('invoice_id', $invoiceIds)->delete();
                $counts['stock_movements_invoice'] = StockMovement::whereIn('invoice_id', $invoiceIds)->delete();
                $counts['invoices'] = Invoice::whereIn('id', $invoiceIds)->delete();
            }

            // Also delete stock movements created by this user
            $counts['stock_movements_user'] = StockMovement::where('created_by', $userId)->delete();
            
            \Log::info('Deleted counts: ' . json_encode($counts));
            
            return $counts;
        });

        return redirect()->back()->with('success', 'Semua data terkait akun Anda telah dihapus. (' . json_encode($deletedCounts) . ')');
    }

    /**
     * Delete ALL data in the system except the current user's account
     */
    public function deleteAllData(Request $request)
    {
        $user = Auth::user();
        $userId = $user->id;

        DB::transaction(function () use ($userId) {
            Payment::query()->delete();
            InvoiceItem::query()->delete();
            StockMovement::query()->delete();
            Invoice::query()->delete();
            ProductStock::query()->delete();
            Product::query()->delete();
            Category::query()->delete();
            Supplier::query()->delete();
            Customer::query()->delete();
            Branch::query()->delete();
            User::where('id', '!=', $userId)->delete();

            // Reset auto-increment for SQLite
            $tables = [
                'payments', 'invoice_items', 'stock_movements', 'invoices',
                'product_stocks', 'products', 'categories', 'suppliers',
                'customers', 'branches', 'users',
            ];

            foreach ($tables as $table) {
                try {
                    DB::statement("DELETE FROM sqlite_sequence WHERE name = '{$table}'");
                } catch (\Exception $e) {
                    // Ignore errors
                }
            }
        });

        return redirect()->back()->with('success', 'Semua data sistem telah dihapus. Akun Anda tetap aman.');
    }
}
