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
        Schema::table('work_orders', function (Blueprint $table) {
            // Composite index for driver schedule queries
            $table->index(['company_id', 'driver_id', 'service_date'], 'work_orders_company_driver_date_index');
            
            // Index for recent records queries
            $table->index('created_at', 'work_orders_created_at_index');
            
            // Index for COD queries
            $table->index('cod_amount', 'work_orders_cod_amount_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropIndex('work_orders_company_driver_date_index');
            $table->dropIndex('work_orders_created_at_index');
            $table->dropIndex('work_orders_cod_amount_index');
        });
    }
};