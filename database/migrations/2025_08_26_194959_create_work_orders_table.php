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
        Schema::create('work_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            
            // Service Ticket Info
            $table->string('ticket_number')->unique();
            $table->string('po_number')->nullable();
            $table->date('service_date');
            
            // Time tracking
            $table->time('time_on_site')->nullable();
            $table->time('time_off_site')->nullable();
            $table->enum('time_on_site_period', ['AM', 'PM'])->nullable();
            $table->enum('time_off_site_period', ['AM', 'PM'])->nullable();
            
            // Truck/Driver Info
            $table->string('truck_number')->nullable();
            $table->string('dispatch_number')->nullable();
            $table->foreignId('driver_id')->nullable()->constrained()->nullOnDelete();
            
            // Customer Info
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('customer_name')->nullable(); // Fallback for manual entry
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state', 2)->nullable();
            $table->string('zip', 10)->nullable();
            
            // Service Details
            $table->enum('action', ['Delivery', 'Pickup', 'Service', 'Emergency', 'Other'])->default('Service');
            $table->string('container_size')->nullable();
            $table->string('waste_type')->nullable();
            $table->text('service_description')->nullable();
            
            // Equipment tracking
            $table->string('container_delivered')->nullable();
            $table->string('container_picked_up')->nullable();
            
            // Disposal Info
            $table->string('disposal_id')->nullable();
            $table->string('disposal_ticket')->nullable();
            
            // COD Info
            $table->decimal('cod_amount', 10, 2)->nullable();
            $table->string('cod_signature')->nullable(); // Store signature as base64 or file path
            
            // Comments
            $table->text('comments')->nullable();
            
            // Signatures
            $table->string('customer_signature')->nullable(); // Store signature as base64 or file path
            $table->datetime('customer_signature_date')->nullable();
            $table->string('driver_signature')->nullable(); // Store signature as base64 or file path
            $table->datetime('driver_signature_date')->nullable();
            
            // Status tracking
            $table->enum('status', ['draft', 'in_progress', 'completed', 'cancelled'])->default('draft');
            $table->datetime('completed_at')->nullable();
            
            // Service Order relationship (if applicable)
            $table->foreignId('service_order_id')->nullable()->constrained()->nullOnDelete();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['company_id', 'service_date']);
            $table->index(['company_id', 'status']);
            $table->index(['customer_id']);
            $table->index(['driver_id']);
            $table->index('ticket_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_orders');
    }
};