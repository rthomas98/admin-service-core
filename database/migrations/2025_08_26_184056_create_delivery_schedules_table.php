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
        Schema::create('delivery_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('service_order_id')->constrained('service_orders')->onDelete('cascade');
            $table->foreignId('equipment_id')->nullable()->constrained('equipment')->onDelete('set null');
            $table->foreignId('driver_id')->nullable()->constrained('drivers')->onDelete('set null');
            $table->enum('type', ['delivery', 'pickup', 'maintenance', 'emergency']);
            $table->datetime('scheduled_datetime');
            $table->datetime('actual_datetime')->nullable();
            $table->enum('status', ['scheduled', 'en_route', 'completed', 'cancelled', 'failed'])->default('scheduled');
            $table->string('delivery_address');
            $table->string('delivery_city');
            $table->string('delivery_parish');
            $table->string('delivery_postal_code')->nullable();
            $table->decimal('delivery_latitude', 10, 8)->nullable();
            $table->decimal('delivery_longitude', 11, 8)->nullable();
            $table->text('delivery_instructions')->nullable();
            $table->text('completion_notes')->nullable();
            $table->json('photos')->nullable(); // before/after photos
            $table->string('signature')->nullable(); // path to signature file
            $table->integer('estimated_duration_minutes')->nullable();
            $table->integer('actual_duration_minutes')->nullable();
            $table->decimal('travel_distance_km', 8, 2)->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index(['company_id', 'scheduled_datetime']);
            $table->index(['company_id', 'driver_id', 'scheduled_datetime']);
            $table->index(['service_order_id', 'type']);
            $table->index(['status', 'scheduled_datetime']);
            $table->index('equipment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_schedules');
    }
};
