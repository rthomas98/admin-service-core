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
        Schema::create('equipment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['dumpster', 'portable_toilet', 'handwash_station', 'holding_tank', 'water_tank']);
            $table->string('size')->nullable(); // 10-yard, 20-yard, etc.
            $table->string('unit_number')->unique();
            $table->enum('status', ['available', 'rented', 'maintenance', 'retired'])->default('available');
            $table->enum('condition', ['excellent', 'good', 'fair', 'poor'])->default('good');
            $table->string('current_location')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->date('purchase_date')->nullable();
            $table->date('last_service_date')->nullable();
            $table->date('next_service_due')->nullable();
            $table->decimal('purchase_price', 10, 2)->nullable();
            $table->string('color')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['company_id', 'type', 'status']);
            $table->index(['company_id', 'unit_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment');
    }
};
