<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('waste_routes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('zone')->nullable();
            $table->enum('frequency', ['daily', 'weekly', 'biweekly', 'monthly'])->default('weekly');
            $table->decimal('estimated_duration_hours', 5, 2)->nullable();
            $table->decimal('total_distance_km', 8, 2)->nullable();
            $table->enum('status', ['active', 'inactive', 'maintenance'])->default('active');
            $table->timestamps();

            $table->index(['company_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('waste_routes');
    }
};