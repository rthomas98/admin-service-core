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
        // Add polymorphic columns to maintenance_logs
        if (Schema::hasTable('maintenance_logs')) {
            Schema::table('maintenance_logs', function (Blueprint $table) {
                // Add polymorphic columns if they don't exist
                if (!Schema::hasColumn('maintenance_logs', 'maintainable_type')) {
                    $table->string('maintainable_type')->nullable()->after('company_id');
                }
                if (!Schema::hasColumn('maintenance_logs', 'maintainable_id')) {
                    $table->unsignedBigInteger('maintainable_id')->nullable()->after('maintainable_type');
                }
                
                // Add vehicle-specific columns if they don't exist
                if (!Schema::hasColumn('maintenance_logs', 'mileage')) {
                    $table->integer('mileage')->nullable()->after('service_date');
                }
                if (!Schema::hasColumn('maintenance_logs', 'driver_id')) {
                    $table->foreignId('driver_id')->nullable()->after('technician_id')->constrained();
                }
                
                // Add index for polymorphic relationship if it doesn't exist
                $indexName = 'maintenance_logs_maintainable_index';
                $existingIndexes = DB::select("SELECT indexname FROM pg_indexes WHERE tablename = 'maintenance_logs' AND indexname = ?", [$indexName]);
                if (empty($existingIndexes)) {
                    $table->index(['maintainable_type', 'maintainable_id'], $indexName);
                }
                
                // Make equipment_id nullable since we'll be transitioning to polymorphic
                if (Schema::hasColumn('maintenance_logs', 'equipment_id')) {
                    $table->unsignedBigInteger('equipment_id')->nullable()->change();
                }
            });
            
            // Migrate existing data to polymorphic structure
            DB::statement("
                UPDATE maintenance_logs 
                SET maintainable_type = 'App\\Models\\Equipment', 
                    maintainable_id = equipment_id 
                WHERE equipment_id IS NOT NULL 
                  AND maintainable_type IS NULL
            ");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('maintenance_logs')) {
            Schema::table('maintenance_logs', function (Blueprint $table) {
                // Restore equipment_id data from polymorphic columns
                DB::statement("
                    UPDATE maintenance_logs 
                    SET equipment_id = maintainable_id 
                    WHERE maintainable_type = 'App\\Models\\Equipment'
                ");
                
                // Drop vehicle-specific columns
                if (Schema::hasColumn('maintenance_logs', 'mileage')) {
                    $table->dropColumn('mileage');
                }
                if (Schema::hasColumn('maintenance_logs', 'driver_id')) {
                    $table->dropForeign(['driver_id']);
                    $table->dropColumn('driver_id');
                }
                
                // Drop polymorphic columns and index
                $indexName = 'maintenance_logs_maintainable_index';
                $existingIndexes = DB::select("SELECT indexname FROM pg_indexes WHERE tablename = 'maintenance_logs' AND indexname = ?", [$indexName]);
                if (!empty($existingIndexes)) {
                    $table->dropIndex($indexName);
                }
                
                if (Schema::hasColumn('maintenance_logs', 'maintainable_type')) {
                    $table->dropColumn('maintainable_type');
                }
                if (Schema::hasColumn('maintenance_logs', 'maintainable_id')) {
                    $table->dropColumn('maintainable_id');
                }
                
                // Make equipment_id required again
                if (Schema::hasColumn('maintenance_logs', 'equipment_id')) {
                    $table->unsignedBigInteger('equipment_id')->nullable(false)->change();
                }
            });
        }
    }
};