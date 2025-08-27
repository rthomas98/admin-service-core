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
        Schema::create('maintenance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('equipment_id')->constrained('equipment')->onDelete('cascade');
            $table->foreignId('technician_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('service_type', ['cleaning', 'repair', 'inspection', 'preventive', 'emergency', 'pump_out']);
            $table->date('service_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->decimal('service_cost', 8, 2)->default(0.00);
            $table->decimal('parts_cost', 8, 2)->default(0.00);
            $table->decimal('labor_cost', 8, 2)->default(0.00);
            $table->decimal('total_cost', 8, 2)->default(0.00);
            $table->text('work_performed');
            $table->json('parts_used')->nullable(); // parts and quantities used
            $table->json('materials_used')->nullable(); // cleaning supplies, etc.
            $table->text('issues_found')->nullable();
            $table->text('recommendations')->nullable();
            $table->enum('condition_before', ['excellent', 'good', 'fair', 'poor', 'needs_repair'])->nullable();
            $table->enum('condition_after', ['excellent', 'good', 'fair', 'poor', 'needs_repair'])->nullable();
            $table->json('checklist_completed')->nullable(); // maintenance checklist items
            $table->json('photos')->nullable(); // before/after photos
            $table->boolean('requires_followup')->default(false);
            $table->date('next_service_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index(['company_id', 'service_date']);
            $table->index(['company_id', 'equipment_id']);
            $table->index(['equipment_id', 'service_type']);
            $table->index(['technician_id', 'service_date']);
            $table->index('next_service_date');
            $table->index('requires_followup');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_logs');
    }
};
