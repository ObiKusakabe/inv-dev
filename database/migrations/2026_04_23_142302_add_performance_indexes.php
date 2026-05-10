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
        Schema::table('invoices', function (Blueprint $table) {
            $table->index(['created_at', 'status'], 'idx_invoices_date_status');
            $table->index('branch_id', 'idx_invoices_branch');
            $table->index('status', 'idx_invoices_status');
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->index(['invoice_id', 'product_id'], 'idx_items_invoice_product');
            $table->index('product_id', 'idx_items_product');
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->index(['product_id', 'branch_id'], 'idx_stock_product_branch');
            $table->index(['created_at', 'type'], 'idx_stock_date_type');
            $table->index('branch_id', 'idx_stock_branch');
        });

        Schema::table('product_stocks', function (Blueprint $table) {
            $table->index(['product_id', 'branch_id'], 'idx_product_stock_product_branch');
            $table->index('quantity', 'idx_product_stock_qty');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->index(['category_id', 'supplier_id'], 'idx_product_cat_supplier');
            $table->index('created_at', 'idx_product_created');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex('idx_invoices_date_status');
            $table->dropIndex('idx_invoices_branch');
            $table->dropIndex('idx_invoices_status');
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropIndex('idx_items_invoice_product');
            $table->dropIndex('idx_items_product');
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropIndex('idx_stock_product_branch');
            $table->dropIndex('idx_stock_date_type');
            $table->dropIndex('idx_stock_branch');
        });

        Schema::table('product_stocks', function (Blueprint $table) {
            $table->dropIndex('idx_product_stock_product_branch');
            $table->dropIndex('idx_product_stock_qty');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('idx_product_cat_supplier');
            $table->dropIndex('idx_product_created');
        });
    }
};
