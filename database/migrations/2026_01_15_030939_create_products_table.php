<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->string('name');                            // Nama produk
            $table->foreignId('category_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->foreignId('supplier_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->string('size');                            // Bebas: S/M/L/28/30
            $table->integer('stock')->default(0);
            $table->integer('min_stock')->default(0);          // Alert stok menipis

            $table->boolean('is_consignment')->default(false); // Barang titipan
            $table->decimal('supplier_price', 12, 2)->nullable(); // Harga ke supplier
            $table->decimal('sell_price', 12, 2);              // Harga jual

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
