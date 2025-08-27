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
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->nullable();
            $table->string('phone');
            $table->string('license_number')->unique();
            $table->enum('license_class', ['A', 'B', 'C', 'CDL-A', 'CDL-B', 'CDL-C']);
            $table->date('license_expiry_date');
            $table->string('vehicle_type')->nullable(); // truck, van, etc.
            $table->string('vehicle_registration')->nullable();
            $table->string('vehicle_make')->nullable();
            $table->string('vehicle_model')->nullable();
            $table->year('vehicle_year')->nullable();
            $table->json('service_areas')->nullable(); // parishes or zones they cover
            $table->boolean('can_lift_heavy')->default(false); // for heavy equipment
            $table->boolean('has_truck_crane')->default(false);
            $table->decimal('hourly_rate', 8, 2)->nullable();
            $table->time('shift_start_time')->nullable();
            $table->time('shift_end_time')->nullable();
            $table->json('available_days')->nullable(); // days of week available
            $table->enum('status', ['active', 'inactive', 'on_leave', 'suspended'])->default('active');
            $table->text('notes')->nullable();
            $table->date('hired_date')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'license_class']);
            $table->index('license_number');
            $table->index('license_expiry_date');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drivers');
    }
};
