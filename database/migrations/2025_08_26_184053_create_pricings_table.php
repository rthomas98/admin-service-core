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
        Schema::create('pricings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->string('equipment_type'); // porta-potty, dumpster, etc.
            $table->string('size')->nullable(); // 20-yard, 30-yard, standard, deluxe
            $table->string('category')->nullable(); // construction, event, residential
            $table->decimal('daily_rate', 8, 2)->nullable();
            $table->decimal('weekly_rate', 8, 2)->nullable();
            $table->decimal('monthly_rate', 8, 2)->nullable();
            $table->decimal('delivery_fee', 8, 2)->default(0.00);
            $table->decimal('pickup_fee', 8, 2)->default(0.00);
            $table->decimal('cleaning_fee', 8, 2)->default(0.00);
            $table->decimal('maintenance_fee', 8, 2)->default(0.00);
            $table->decimal('damage_fee', 8, 2)->default(0.00);
            $table->decimal('late_fee_daily', 8, 2)->default(0.00);
            $table->decimal('emergency_surcharge', 8, 2)->default(0.00);
            $table->integer('minimum_rental_days')->default(1);
            $table->integer('maximum_rental_days')->nullable();
            $table->text('description')->nullable();
            $table->json('included_services')->nullable(); // what's included in base price
            $table->json('additional_charges')->nullable(); // extra fees structure
            $table->boolean('is_active')->default(true);
            $table->date('effective_from')->nullable();
            $table->date('effective_until')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index(['company_id', 'equipment_type']);
            $table->index(['company_id', 'is_active']);
            $table->index(['equipment_type', 'size']);
            $table->index(['effective_from', 'effective_until']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pricings');
    }
};
