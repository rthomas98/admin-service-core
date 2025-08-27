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
        Schema::create('service_areas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->string('name'); // e.g., "St. Andrew Central", "Kingston Metro"
            $table->text('description')->nullable();
            $table->json('zip_codes')->nullable(); // ZIP/postal codes covered
            $table->json('parishes')->nullable(); // Jamaica parishes covered
            $table->json('boundaries')->nullable(); // geographical boundaries (coordinates)
            $table->decimal('delivery_surcharge', 8, 2)->default(0.00);
            $table->decimal('pickup_surcharge', 8, 2)->default(0.00);
            $table->decimal('emergency_surcharge', 8, 2)->default(0.00);
            $table->integer('standard_delivery_days')->default(1); // days for standard delivery
            $table->integer('rush_delivery_hours')->nullable(); // hours for rush delivery
            $table->decimal('rush_delivery_surcharge', 8, 2)->default(0.00);
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(100); // for ordering service areas
            $table->text('service_notes')->nullable(); // special instructions for this area
            $table->timestamps();

            // Indexes for performance
            $table->index(['company_id', 'is_active']);
            $table->index(['company_id', 'priority']);
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_areas');
    }
};
