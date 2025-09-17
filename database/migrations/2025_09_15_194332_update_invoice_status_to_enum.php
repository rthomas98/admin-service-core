<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // First set all existing statuses to a valid enum value
            DB::statement("UPDATE invoices SET status = 'draft' WHERE status IS NULL OR status = '';");
            DB::statement("UPDATE invoices SET status = 'sent' WHERE status = 'pending';");

            // Update the column to use the enum values
            $table->string('status', 50)->default('draft')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('status')->nullable()->change();
        });
    }
};
