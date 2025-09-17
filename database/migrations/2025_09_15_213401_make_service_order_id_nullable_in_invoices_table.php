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
        Schema::table('invoices', function (Blueprint $table) {
            // Make service_order_id nullable since invoices can be created manually
            $table->foreignId('service_order_id')->nullable()->change();

            // Also add work_order_id as nullable for RAW Disposal work orders
            $table->foreignId('work_order_id')->nullable()->after('service_order_id')
                ->constrained('work_orders')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['work_order_id']);
            $table->dropColumn('work_order_id');

            $table->foreignId('service_order_id')->nullable(false)->change();
        });
    }
};
