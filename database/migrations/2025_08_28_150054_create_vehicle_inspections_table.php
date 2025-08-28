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
        Schema::create('vehicle_inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');
            $table->foreignId('driver_id')->nullable()->constrained()->onDelete('set null');
            $table->string('inspection_number')->unique();
            $table->date('inspection_date');
            $table->time('inspection_time')->nullable();
            $table->enum('inspection_type', ['daily', 'weekly', 'monthly', 'annual', 'dot', 'pre_trip', 'post_trip']);
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'failed', 'needs_repair']);
            $table->integer('odometer_reading')->nullable();
            
            // Inspection checklist items
            $table->json('exterior_items')->nullable(); // lights, tires, body, etc.
            $table->json('interior_items')->nullable(); // seats, mirrors, controls, etc.
            $table->json('engine_items')->nullable(); // fluids, belts, hoses, etc.
            $table->json('safety_items')->nullable(); // brakes, emergency equipment, etc.
            $table->json('documentation_items')->nullable(); // registration, insurance, permits, etc.
            
            // Issues and notes
            $table->json('issues_found')->nullable();
            $table->text('notes')->nullable();
            $table->text('corrective_actions')->nullable();
            
            // Certification
            $table->string('inspector_name')->nullable();
            $table->string('inspector_signature')->nullable();
            $table->string('inspector_certification_number')->nullable();
            $table->datetime('certified_at')->nullable();
            
            // Next inspection
            $table->date('next_inspection_date')->nullable();
            $table->integer('next_inspection_miles')->nullable();
            
            // Attachments
            $table->json('photos')->nullable();
            $table->json('documents')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['company_id', 'vehicle_id']);
            $table->index(['inspection_date', 'status']);
            $table->index('next_inspection_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_inspections');
    }
};