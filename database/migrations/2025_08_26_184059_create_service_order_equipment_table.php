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
        Schema::create('service_order_equipment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_order_id')->constrained('service_orders')->onDelete('cascade');
            $table->foreignId('equipment_id')->constrained('equipment')->onDelete('cascade');
            $table->integer('quantity')->default(1);
            $table->date('assigned_date')->nullable();
            $table->date('delivered_date')->nullable();
            $table->date('pickup_date')->nullable();
            $table->enum('status', ['assigned', 'delivered', 'in_service', 'picked_up', 'maintenance'])->default('assigned');
            $table->decimal('unit_price', 8, 2)->default(0.00);
            $table->decimal('total_price', 8, 2)->default(0.00);
            $table->text('notes')->nullable();
            $table->json('condition_notes')->nullable(); // condition at delivery/pickup
            $table->timestamps();

            // Indexes for performance
            $table->index(['service_order_id', 'equipment_id']);
            $table->index(['equipment_id', 'status']);
            $table->index(['status', 'assigned_date']);
            $table->unique(['service_order_id', 'equipment_id'], 'service_order_equipment_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_order_equipment');
    }
};
