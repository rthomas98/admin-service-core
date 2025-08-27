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
        Schema::create('driver_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained();
            $table->foreignId('driver_id')->constrained();
            $table->foreignId('vehicle_id')->nullable()->constrained();
            $table->foreignId('trailer_id')->nullable()->constrained();
            $table->datetime('start_date');
            $table->datetime('end_date')->nullable();
            $table->enum('status', ['scheduled', 'active', 'completed', 'cancelled'])->default('scheduled');
            $table->string('route')->nullable();
            $table->string('origin')->nullable();
            $table->string('destination')->nullable();
            $table->string('cargo_type')->nullable();
            $table->decimal('cargo_weight', 10, 2)->nullable();
            $table->decimal('expected_duration_hours', 5, 2)->nullable();
            $table->decimal('actual_duration_hours', 5, 2)->nullable();
            $table->integer('mileage_start')->nullable();
            $table->integer('mileage_end')->nullable();
            $table->decimal('fuel_used', 8, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'status']);
            $table->index(['driver_id', 'status']);
            $table->index(['vehicle_id', 'status']);
            $table->index('start_date');
            $table->index('end_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_assignments');
    }
};