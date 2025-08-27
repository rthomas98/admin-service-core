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
        Schema::create('emergency_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->string('emergency_number')->unique();
            $table->datetime('request_datetime');
            $table->enum('urgency_level', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->enum('emergency_type', ['delivery', 'pickup', 'cleaning', 'repair', 'replacement']);
            $table->text('description');
            $table->string('location_address');
            $table->string('location_city');
            $table->string('location_parish');
            $table->string('location_postal_code')->nullable();
            $table->decimal('location_latitude', 10, 8)->nullable();
            $table->decimal('location_longitude', 11, 8)->nullable();
            $table->json('equipment_needed'); // types and quantities needed
            $table->datetime('required_by_datetime')->nullable();
            $table->datetime('assigned_datetime')->nullable();
            $table->datetime('dispatched_datetime')->nullable();
            $table->datetime('arrival_datetime')->nullable();
            $table->datetime('completion_datetime')->nullable();
            $table->integer('target_response_minutes')->default(60);
            $table->integer('actual_response_minutes')->nullable();
            $table->enum('status', ['pending', 'assigned', 'dispatched', 'on_site', 'completed', 'cancelled'])->default('pending');
            $table->foreignId('assigned_driver_id')->nullable()->constrained('drivers')->onDelete('set null');
            $table->foreignId('assigned_technician_id')->nullable()->constrained('users')->onDelete('set null');
            $table->decimal('emergency_surcharge', 8, 2)->default(0.00);
            $table->decimal('total_cost', 8, 2)->default(0.00);
            $table->text('completion_notes')->nullable();
            $table->json('photos')->nullable();
            $table->string('contact_phone');
            $table->string('contact_name');
            $table->text('special_instructions')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index(['company_id', 'request_datetime']);
            $table->index(['company_id', 'urgency_level', 'status']);
            $table->index(['assigned_driver_id', 'status']);
            $table->index(['assigned_technician_id', 'status']);
            $table->index(['status', 'required_by_datetime']);
            $table->index('emergency_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emergency_services');
    }
};
