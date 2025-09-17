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
        Schema::table('equipment', function (Blueprint $table) {
            // Pricing fields
            $table->decimal('daily_rate', 10, 2)->nullable()->after('purchase_price');
            $table->decimal('weekly_rate', 10, 2)->nullable()->after('daily_rate');
            $table->decimal('monthly_rate', 10, 2)->nullable()->after('weekly_rate');
            $table->decimal('delivery_fee', 10, 2)->nullable()->after('monthly_rate');
            $table->decimal('pickup_fee', 10, 2)->nullable()->after('delivery_fee');
            $table->decimal('cleaning_fee', 10, 2)->nullable()->after('pickup_fee');
            $table->decimal('damage_deposit', 10, 2)->nullable()->after('cleaning_fee');

            // Equipment details
            $table->string('serial_number')->nullable()->after('unit_number');
            $table->string('manufacturer')->nullable()->after('serial_number');
            $table->string('model')->nullable()->after('manufacturer');
            $table->year('year')->nullable()->after('model');
            $table->string('weight_capacity')->nullable()->after('year');
            $table->string('dimensions')->nullable()->after('weight_capacity');

            // Service information
            $table->string('service_interval')->nullable()->after('next_service_due');
            $table->string('service_provider')->nullable()->after('service_interval');
            $table->string('service_contact')->nullable()->after('service_provider');

            // Additional flags
            $table->boolean('requires_cdl')->default(false)->after('dimensions');
            $table->boolean('has_gps_tracker')->default(false)->after('requires_cdl');

            // Indexes for better performance
            $table->index('status');
            $table->index('type');
            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('equipment', function (Blueprint $table) {
            // Drop indexes
            $table->dropIndex(['company_id', 'type']);
            $table->dropIndex(['company_id', 'status']);
            $table->dropIndex(['type']);
            $table->dropIndex(['status']);

            // Drop columns
            $table->dropColumn([
                'daily_rate',
                'weekly_rate',
                'monthly_rate',
                'delivery_fee',
                'pickup_fee',
                'cleaning_fee',
                'damage_deposit',
                'serial_number',
                'manufacturer',
                'model',
                'year',
                'weight_capacity',
                'dimensions',
                'service_interval',
                'service_provider',
                'service_contact',
                'requires_cdl',
                'has_gps_tracker',
            ]);
        });
    }
};
