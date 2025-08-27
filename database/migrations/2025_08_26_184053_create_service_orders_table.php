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
        Schema::create('service_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->string('order_number')->unique();
            $table->enum('service_type', ['rental', 'delivery_pickup', 'cleaning', 'maintenance', 'emergency']);
            $table->enum('status', ['pending', 'confirmed', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->date('delivery_date')->nullable();
            $table->time('delivery_time_start')->nullable();
            $table->time('delivery_time_end')->nullable();
            $table->date('pickup_date')->nullable();
            $table->time('pickup_time_start')->nullable();
            $table->time('pickup_time_end')->nullable();
            $table->string('delivery_address')->nullable();
            $table->string('delivery_city')->nullable();
            $table->string('delivery_parish')->nullable();
            $table->string('delivery_postal_code')->nullable();
            $table->string('pickup_address')->nullable();
            $table->string('pickup_city')->nullable();
            $table->string('pickup_parish')->nullable();
            $table->string('pickup_postal_code')->nullable();
            $table->text('special_instructions')->nullable();
            $table->decimal('total_amount', 10, 2)->default(0.00);
            $table->decimal('discount_amount', 10, 2)->default(0.00);
            $table->decimal('tax_amount', 10, 2)->default(0.00);
            $table->decimal('final_amount', 10, 2)->default(0.00);
            $table->json('equipment_requested')->nullable(); // Store equipment types and quantities
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'customer_id']);
            $table->index(['company_id', 'delivery_date']);
            $table->index(['company_id', 'pickup_date']);
            $table->index('order_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_orders');
    }
};
