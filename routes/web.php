<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\StockManagementController;
use App\Http\Controllers\POSController;
use App\Http\Controllers\InvoiceController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function(){

    Route::get('/products', [ProductController::class, 'index'])
        ->name('products.index');

    Route::get('/category', [CategoryController::class, 'index'])
        ->name('category.index');

    Route::get('/stockManagement', [StockManagementController::class, 'index'])
        ->name('stockManagement.index');

    Route::get('/pos', [POSController::class, 'index'])
        ->name('pos.index');

    Route::get('/invoices', [InvoiceController::class, 'index'])
        ->name('invoices.index');
});

require __DIR__.'/settings.php';
