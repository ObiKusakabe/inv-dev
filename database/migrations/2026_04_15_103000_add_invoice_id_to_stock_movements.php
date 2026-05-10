<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('stock_movements', 'invoice_id')) {
            Schema::table('stock_movements', function (Blueprint $table) {
                $table->foreignId('invoice_id')
                    ->nullable()
                    ->after('branch_id')
                    ->constrained()
                    ->nullOnDelete();

                $table->index('invoice_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('stock_movements', 'invoice_id')) {
            Schema::table('stock_movements', function (Blueprint $table) {
                $table->dropConstrainedForeignId('invoice_id');
            });
        }
    }
};
