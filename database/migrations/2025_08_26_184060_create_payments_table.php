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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('invoice_id')->constrained('invoices')->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->enum('payment_method', ['cash', 'check', 'credit_card', 'debit_card', 'bank_transfer', 'paypal', 'online']);
            $table->string('transaction_id')->nullable();
            $table->string('reference_number')->nullable();
            $table->date('payment_date');
            $table->datetime('processed_datetime')->nullable();
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'cancelled', 'refunded'])->default('pending');
            $table->string('gateway')->nullable(); // stripe, paypal, etc.
            $table->string('gateway_transaction_id')->nullable();
            $table->decimal('fee_amount', 8, 2)->default(0.00);
            $table->decimal('net_amount', 10, 2);
            $table->json('gateway_response')->nullable(); // store gateway response data
            $table->text('notes')->nullable();
            $table->string('check_number')->nullable();
            $table->date('check_date')->nullable();
            $table->string('bank_name')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index(['company_id', 'payment_date']);
            $table->index(['company_id', 'customer_id']);
            $table->index(['invoice_id', 'status']);
            $table->index(['payment_method', 'status']);
            $table->index('transaction_id');
            $table->index('gateway_transaction_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
