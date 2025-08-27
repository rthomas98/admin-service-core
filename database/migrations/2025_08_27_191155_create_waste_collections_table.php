<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('waste_collections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained();
            $table->foreignId('customer_id')->nullable()->constrained();
            $table->foreignId('route_id')->nullable()->constrained('waste_routes');
            $table->foreignId('driver_id')->nullable()->constrained('drivers');
            $table->foreignId('truck_id')->nullable()->constrained('vehicles');
            $table->date('scheduled_date');
            $table->time('scheduled_time')->nullable();
            $table->enum('waste_type', ['general', 'recyclable', 'organic', 'hazardous', 'construction'])->default('general');
            $table->decimal('estimated_weight', 10, 2)->nullable();
            $table->decimal('actual_weight', 10, 2)->nullable();
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'missed', 'rescheduled'])->default('scheduled');
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'scheduled_date']);
            $table->index(['company_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('waste_collections');
    }
};