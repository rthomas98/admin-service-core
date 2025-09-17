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
            // Check if columns don't exist before adding them
            if (! Schema::hasColumn('work_orders', 'equipment_id')) {
                $table->foreignId('equipment_id')->nullable()->after('service_order_id')->constrained()->nullOnDelete();
            }
            if (! Schema::hasColumn('work_orders', 'scheduled_date')) {
                $table->date('scheduled_date')->nullable()->after('service_date');
            }
            if (! Schema::hasColumn('work_orders', 'start_date')) {
                $table->date('start_date')->nullable()->after('scheduled_date');
            }
            if (! Schema::hasColumn('work_orders', 'end_date')) {
                $table->date('end_date')->nullable()->after('start_date');
            }
            if (! Schema::hasColumn('work_orders', 'estimated_cost')) {
                $table->decimal('estimated_cost', 10, 2)->nullable()->after('cod_amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            if (Schema::hasColumn('work_orders', 'equipment_id')) {
                $table->dropForeign(['equipment_id']);
                $table->dropColumn('equipment_id');
            }
            if (Schema::hasColumn('work_orders', 'scheduled_date')) {
                $table->dropColumn('scheduled_date');
            }
            if (Schema::hasColumn('work_orders', 'start_date')) {
                $table->dropColumn('start_date');
            }
            if (Schema::hasColumn('work_orders', 'end_date')) {
                $table->dropColumn('end_date');
            }
            if (Schema::hasColumn('work_orders', 'estimated_cost')) {
                $table->dropColumn('estimated_cost');
            }
        });
    }
};
