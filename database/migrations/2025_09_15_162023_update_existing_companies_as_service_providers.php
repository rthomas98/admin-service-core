<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update existing RAW Disposal and LIV Transport companies to be service providers
        DB::table('companies')
            ->whereIn('slug', ['raw-disposal', 'liv-transport'])
            ->update(['company_type' => 'service_provider']);

        // All other companies should remain as customers (default)
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reset all companies to customer type (the default)
        DB::table('companies')
            ->update(['company_type' => 'customer']);
    }
};
