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
        if (!Schema::hasTable('service_schedules')) {
            Schema::create('service_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('equipment_id')->constrained('equipment')->onDelete('cascade');
            $table->foreignId('technician_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('service_type', ['cleaning', 'maintenance', 'repair', 'inspection', 'pump_out']);
            $table->datetime('scheduled_datetime');
            $table->datetime('completed_datetime')->nullable();
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled', 'requires_followup'])->default('scheduled');
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->text('service_description')->nullable();
            $table->text('completion_notes')->nullable();
            $table->json('checklist_items')->nullable(); // service checklist
            $table->json('materials_used')->nullable(); // materials and quantities
            $table->decimal('service_cost', 8, 2)->default(0.00);
            $table->decimal('materials_cost', 8, 2)->default(0.00);
            $table->decimal('total_cost', 8, 2)->default(0.00);
            $table->integer('estimated_duration_minutes')->nullable();
            $table->integer('actual_duration_minutes')->nullable();
            $table->boolean('requires_followup')->default(false);
            $table->date('followup_date')->nullable();
            $table->json('photos')->nullable(); // before/after photos
            $table->timestamps();

            // Indexes for performance (with custom names to avoid length issues)
            $table->index(['company_id', 'scheduled_datetime'], 'svc_sched_company_datetime_idx');
            $table->index(['company_id', 'technician_id', 'scheduled_datetime'], 'svc_sched_comp_tech_datetime_idx');
            $table->index(['equipment_id', 'service_type'], 'svc_sched_equip_type_idx');
            $table->index(['status', 'scheduled_datetime'], 'svc_sched_status_datetime_idx');
            $table->index(['priority', 'scheduled_datetime'], 'svc_sched_priority_datetime_idx');
            $table->index('requires_followup', 'svc_sched_followup_idx');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_schedules');
    }
};
