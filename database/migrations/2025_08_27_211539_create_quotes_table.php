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
            
            // Form submission fields
            $table->string('name');
            $table->string('company')->nullable();
            $table->string('email');
            $table->string('phone');
            $table->string('project_type');
            $table->json('services')->nullable();
            $table->date('start_date');
            $table->string('duration')->nullable();
            $table->string('location');
            $table->text('message')->nullable();
            
            // Quote management fields
            $table->foreignId('company_id')->default(1)->constrained()->cascadeOnDelete(); // Raw Disposal
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('quote_number')->unique();
            $table->date('quote_date');
            $table->date('valid_until')->nullable();
            $table->json('items')->nullable();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax_rate', 5, 4)->default(8.25);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->string('status')->default('new'); // new, pending, sent, accepted, rejected, expired
            $table->text('description')->nullable();
            $table->text('terms_conditions')->nullable();
            $table->text('notes')->nullable();
            
            // Delivery fields
            $table->string('delivery_address')->nullable();
            $table->string('delivery_city')->nullable();
            $table->string('delivery_parish')->nullable();
            $table->string('delivery_postal_code')->nullable();
            $table->date('requested_delivery_date')->nullable();
            $table->date('requested_pickup_date')->nullable();
            
            // Tracking fields
            $table->date('accepted_date')->nullable();
            $table->foreignId('converted_service_order_id')->nullable()->constrained('service_orders')->nullOnDelete();
            
            $table->timestamps();
            
            $table->index('quote_number');
            $table->index('status');
            $table->index(['company_id', 'status']);
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
