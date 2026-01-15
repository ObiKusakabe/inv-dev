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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();

            $table->foreignId('branch_id')
                  ->constrained()
                  ->cascadeOnDelete();

            $table->string('invoice_number')->unique(); // CBG1-010126-0001

            $table->foreignId('customer_id')
                  ->nullable()
                  ->constrained()
                  ->nullOnDelete();

            $table->enum('payment_method', ['cash', 'transfer']);
            $table->enum('status', ['paid', 'unpaid'])->default('paid');

            $table->decimal('total', 12, 2);

            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
