<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disposal_sites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained();
            $table->string('name');
            $table->string('location');
            $table->string('parish')->nullable();
            $table->enum('site_type', ['landfill', 'recycling', 'composting', 'hazardous', 'transfer_station'])->default('landfill');
            $table->decimal('total_capacity', 12, 2);
            $table->decimal('current_capacity', 12, 2)->default(0);
            $table->decimal('daily_intake_average', 10, 2)->default(0);
            $table->enum('status', ['active', 'maintenance', 'closed', 'inactive'])->default('active');
            $table->string('manager_name')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('operating_hours')->nullable();
            $table->string('environmental_permit')->nullable();
            $table->date('last_inspection_date')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disposal_sites');
    }
};