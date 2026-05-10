<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Inertia;
use Illuminate\Support\Facades\DB;
use Laravel\Fortify\Features;

use App\Http\Controllers\BranchController;
use App\Http\Controllers\DashboardController;

use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\StockManagementController;
use App\Http\Controllers\StockHistoryController;
use App\Http\Controllers\POSController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\SupplierDataController;
use App\Http\Controllers\SummaryController;
// Temporary fix: clear broken avatar paths
Route::get('/fix-avatar', function () {
    DB::table('users')->update(['avatar' => null]);
    return 'Avatar cleared! Refresh halaman dan upload ulang.';
});

Route::get('/', function () {
    // Redirect to dashboard if authenticated, otherwise to login
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
})->name('home');

Route::get('dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function(){

    Route::get('/products', [ProductController::class, 'index'])
        ->name('products.index');
    Route::post('/products', [ProductController::class, 'store'])
        ->name('products.store');
    Route::put('/products/{product}', [ProductController::class, 'update'])
        ->name('products.update');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])
        ->name('products.destroy');

    Route::get('/category', [CategoryController::class, 'index'])
        ->name('category.index');
    Route::post('/category', [CategoryController::class, 'store'])
        ->name('category.store');
    Route::put('/category/{category}', [CategoryController::class, 'update'])
        ->name('category.update');
    Route::delete('/category/{category}', [CategoryController::class, 'destroy'])
        ->name('category.destroy');

    Route::get('/stockManagement', [StockManagementController::class, 'index'])
        ->name('stockManagement.index');
    Route::post('/stockManagement/{productStock}', [StockManagementController::class, 'update'])
        ->name('stockManagement.update');

    // Stock History
    Route::get('/stock-history', [StockHistoryController::class, 'index'])
        ->name('stockHistory.index');
    Route::get('/stock-history/product/{product}', [StockHistoryController::class, 'byProduct'])
        ->name('stockHistory.byProduct');

    Route::get('/pos', [POSController::class, 'index'])
        ->name('pos.index');
    Route::post('/pos/checkout', [POSController::class, 'checkout'])
        ->name('pos.checkout');

    Route::get('/invoices', [InvoiceController::class, 'index'])
        ->name('invoices.index');
    Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])
        ->name('invoices.show');

    Route::get('/supplierData', [SupplierDataController::class, 'index'])
        ->name('supplierData.index');
    Route::post('/supplierData', [SupplierDataController::class, 'store'])
        ->name('supplierData.store');
    Route::put('/supplierData/{supplier}', [SupplierDataController::class, 'update'])
        ->name('supplierData.update');
    Route::delete('/supplierData/{supplier}', [SupplierDataController::class, 'destroy'])
        ->name('supplierData.destroy');    

    Route::get('/summary', [SummaryController::class, 'index'])
        ->name('summary.index');

    // Branch management
    Route::get('/branches', [BranchController::class, 'index'])
        ->name('branches.index');
    Route::get('/branches/create', [BranchController::class, 'create'])
        ->name('branches.create');
    Route::post('/branches', [BranchController::class, 'store'])
        ->name('branches.store');

    // Session branch selector (dipakai modal & nanti switcher)
    Route::post('/branches/select', [BranchController::class, 'select'])
        ->name('branches.select');
});

require __DIR__.'/settings.php';
