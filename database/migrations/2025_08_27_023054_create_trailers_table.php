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
        Schema::create('trailers', function (Blueprint $table) {
            $table->id();
            $table->string('unit_number')->unique();
            $table->string('vin')->unique()->nullable();
            $table->year('year')->nullable();
            $table->string('make');
            $table->string('model')->nullable();
            $table->enum('type', ['flatbed', 'dry_van', 'refrigerated', 'tanker', 'lowboy', 'dump', 'container', 'other'])->default('flatbed');
            $table->decimal('length', 8, 2)->nullable()->comment('Length in feet');
            $table->decimal('width', 8, 2)->nullable()->comment('Width in feet');
            $table->decimal('height', 8, 2)->nullable()->comment('Height in feet');
            $table->decimal('capacity_weight', 10, 2)->nullable()->comment('Weight capacity in lbs');
            $table->decimal('capacity_volume', 10, 2)->nullable()->comment('Volume capacity in cubic feet');
            $table->string('license_plate')->unique()->nullable();
            $table->string('registration_state', 2)->default('LA');
            $table->date('registration_expiry')->nullable();
            $table->enum('status', ['active', 'maintenance', 'out_of_service', 'sold', 'retired'])->default('active');
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_price', 12, 2)->nullable();
            $table->string('purchase_vendor')->nullable();
            $table->boolean('is_leased')->default(false);
            $table->date('lease_end_date')->nullable();
            $table->date('last_inspection_date')->nullable();
            $table->date('next_inspection_date')->nullable();
            $table->text('notes')->nullable();
            $table->string('image_path')->nullable();
            $table->json('specifications')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('status');
            $table->index('type');
            $table->index(['status', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trailers');
    }
};