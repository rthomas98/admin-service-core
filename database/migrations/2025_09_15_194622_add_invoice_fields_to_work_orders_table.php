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
            if (!Schema::hasColumn('work_orders', 'invoice_id')) {
                $table->foreignId('invoice_id')->nullable()->after('service_order_id')->constrained()->nullOnDelete();
                $table->index('invoice_id');
            }
            if (!Schema::hasColumn('work_orders', 'invoiced_at')) {
                $table->timestamp('invoiced_at')->nullable()->after('completed_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropForeign(['invoice_id']);
            $table->dropColumn(['invoice_id', 'invoiced_at']);
        });
    }
};
