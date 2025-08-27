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
        Schema::create('fuel_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained();
            $table->foreignId('vehicle_id')->nullable()->constrained();
            $table->foreignId('driver_id')->nullable()->constrained();
            $table->foreignId('driver_assignment_id')->nullable()->constrained();
            $table->datetime('fuel_date');
            $table->string('fuel_station')->nullable();
            $table->string('location')->nullable();
            $table->decimal('gallons', 10, 2);
            $table->decimal('price_per_gallon', 8, 3);
            $table->decimal('total_cost', 10, 2);
            $table->integer('odometer_reading')->nullable();
            $table->enum('fuel_type', ['regular', 'diesel', 'premium', 'def'])->default('diesel');
            $table->string('payment_method')->nullable();
            $table->string('receipt_number')->nullable();
            $table->string('receipt_image')->nullable();
            $table->boolean('is_personal')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'fuel_date']);
            $table->index(['vehicle_id', 'fuel_date']);
            $table->index(['driver_id', 'fuel_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fuel_logs');
    }
};