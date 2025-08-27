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
        Schema::create('quotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->string('quote_number')->unique();
            $table->date('quote_date');
            $table->date('valid_until');
            $table->json('items'); // equipment types, quantities, duration, etc.
            $table->decimal('subtotal', 10, 2)->default(0.00);
            $table->decimal('tax_rate', 5, 4)->default(0.0000);
            $table->decimal('tax_amount', 10, 2)->default(0.00);
            $table->decimal('discount_amount', 10, 2)->default(0.00);
            $table->decimal('total_amount', 10, 2)->default(0.00);
            $table->enum('status', ['draft', 'sent', 'accepted', 'rejected', 'expired', 'cancelled'])->default('draft');
            $table->text('description')->nullable();
            $table->text('terms_conditions')->nullable();
            $table->text('notes')->nullable();
            $table->string('delivery_address')->nullable();
            $table->string('delivery_city')->nullable();
            $table->string('delivery_parish')->nullable();
            $table->string('delivery_postal_code')->nullable();
            $table->date('requested_delivery_date')->nullable();
            $table->date('requested_pickup_date')->nullable();
            $table->date('accepted_date')->nullable();
            $table->foreignId('converted_service_order_id')->nullable()->constrained('service_orders')->onDelete('set null');
            $table->timestamps();

            // Indexes for performance
            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'customer_id']);
            $table->index(['company_id', 'quote_date']);
            $table->index('quote_number');
            $table->index(['status', 'valid_until']);
            $table->index('converted_service_order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotes');
    }
};
