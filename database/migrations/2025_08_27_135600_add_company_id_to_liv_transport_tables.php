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
        // Add company_id to vehicles table
        if (Schema::hasTable('vehicles') && !Schema::hasColumn('vehicles', 'company_id')) {
            Schema::table('vehicles', function (Blueprint $table) {
                $table->foreignId('company_id')->after('id')->constrained();
            });
        }

        // Add company_id to trailers table
        if (Schema::hasTable('trailers') && !Schema::hasColumn('trailers', 'company_id')) {
            Schema::table('trailers', function (Blueprint $table) {
                $table->foreignId('company_id')->after('id')->constrained();
            });
        }

        // Add company_id to finance_companies table
        if (Schema::hasTable('finance_companies') && !Schema::hasColumn('finance_companies', 'company_id')) {
            Schema::table('finance_companies', function (Blueprint $table) {
                $table->foreignId('company_id')->after('id')->constrained();
            });
        }

        // Add company_id to vehicle_finances table
        if (Schema::hasTable('vehicle_finances') && !Schema::hasColumn('vehicle_finances', 'company_id')) {
            Schema::table('vehicle_finances', function (Blueprint $table) {
                $table->foreignId('company_id')->after('id')->constrained();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('vehicles', 'company_id')) {
            Schema::table('vehicles', function (Blueprint $table) {
                $table->dropForeign(['company_id']);
                $table->dropColumn('company_id');
            });
        }

        if (Schema::hasColumn('trailers', 'company_id')) {
            Schema::table('trailers', function (Blueprint $table) {
                $table->dropForeign(['company_id']);
                $table->dropColumn('company_id');
            });
        }

        if (Schema::hasColumn('finance_companies', 'company_id')) {
            Schema::table('finance_companies', function (Blueprint $table) {
                $table->dropForeign(['company_id']);
                $table->dropColumn('company_id');
            });
        }

        if (Schema::hasTable('vehicle_finances') && Schema::hasColumn('vehicle_finances', 'company_id')) {
            Schema::table('vehicle_finances', function (Blueprint $table) {
                $table->dropForeign(['company_id']);
                $table->dropColumn('company_id');
            });
        }
    }
};