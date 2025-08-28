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
        Schema::create('vehicle_maintenances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');
            $table->foreignId('driver_id')->nullable()->constrained()->onDelete('set null');
            $table->string('maintenance_number')->unique();
            $table->enum('maintenance_type', [
                'preventive', 
                'corrective', 
                'emergency', 
                'scheduled', 
                'oil_change',
                'tire_rotation',
                'brake_service',
                'engine_service',
                'transmission_service',
                'cooling_system',
                'electrical',
                'body_work',
                'other'
            ]);
            $table->enum('priority', ['low', 'medium', 'high', 'critical']);
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled', 'on_hold']);
            
            // Schedule information
            $table->date('scheduled_date');
            $table->time('scheduled_time')->nullable();
            $table->date('completed_date')->nullable();
            $table->time('completed_time')->nullable();
            
            // Mileage tracking
            $table->integer('odometer_at_service')->nullable();
            $table->integer('next_service_miles')->nullable();
            $table->date('next_service_date')->nullable();
            
            // Work details
            $table->text('description');
            $table->json('work_performed')->nullable();
            $table->json('parts_replaced')->nullable();
            $table->json('fluids_added')->nullable();
            
            // Service provider
            $table->string('service_provider')->nullable();
            $table->string('technician_name')->nullable();
            $table->string('work_order_number')->nullable();
            
            // Cost information
            $table->decimal('labor_cost', 10, 2)->default(0);
            $table->decimal('parts_cost', 10, 2)->default(0);
            $table->decimal('other_cost', 10, 2)->default(0);
            $table->decimal('total_cost', 10, 2)->default(0);
            $table->string('invoice_number')->nullable();
            $table->enum('payment_status', ['pending', 'paid', 'partial', 'disputed'])->default('pending');
            
            // Warranty information
            $table->boolean('under_warranty')->default(false);
            $table->string('warranty_claim_number')->nullable();
            $table->decimal('warranty_covered_amount', 10, 2)->default(0);
            
            // Downtime tracking
            $table->datetime('vehicle_down_from')->nullable();
            $table->datetime('vehicle_down_to')->nullable();
            $table->integer('total_downtime_hours')->nullable();
            
            // Notes and attachments
            $table->text('notes')->nullable();
            $table->text('recommendations')->nullable();
            $table->json('photos')->nullable();
            $table->json('documents')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['company_id', 'vehicle_id']);
            $table->index(['scheduled_date', 'status']);
            $table->index(['maintenance_type', 'status']);
            $table->index('next_service_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_maintenances');
    }
};