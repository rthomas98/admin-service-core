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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('unit_number')->unique();
            $table->string('vin')->unique()->nullable();
            $table->year('year');
            $table->string('make');
            $table->string('model');
            $table->enum('type', ['truck', 'van', 'pickup', 'suv', 'car', 'other'])->default('truck');
            $table->string('color')->nullable();
            $table->string('license_plate')->unique()->nullable();
            $table->string('registration_state', 2)->default('LA');
            $table->date('registration_expiry')->nullable();
            $table->integer('odometer')->nullable();
            $table->date('odometer_date')->nullable();
            $table->decimal('fuel_capacity', 8, 2)->nullable();
            $table->enum('fuel_type', ['diesel', 'gasoline', 'electric', 'hybrid', 'other'])->default('diesel');
            $table->enum('status', ['active', 'maintenance', 'out_of_service', 'sold', 'retired'])->default('active');
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_price', 12, 2)->nullable();
            $table->string('purchase_vendor')->nullable();
            $table->boolean('is_leased')->default(false);
            $table->date('lease_end_date')->nullable();
            $table->text('notes')->nullable();
            $table->string('image_path')->nullable();
            $table->json('specifications')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('status');
            $table->index('type');
            $table->index(['status', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};