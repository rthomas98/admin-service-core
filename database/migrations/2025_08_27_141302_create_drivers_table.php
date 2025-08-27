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
        // Add LIV Transport specific columns to existing drivers table
        if (Schema::hasTable('drivers') && !Schema::hasColumn('drivers', 'employee_id')) {
            Schema::table('drivers', function (Blueprint $table) {
                $table->string('employee_id')->nullable()->after('company_id');
                $table->string('emergency_contact')->nullable()->after('phone');
                $table->string('emergency_phone')->nullable()->after('emergency_contact');
                $table->date('date_of_birth')->nullable()->after('emergency_phone');
                $table->string('license_state', 2)->default('LA')->after('license_number');
                $table->date('medical_card_expiry')->nullable()->after('license_expiry_date');
                $table->date('hazmat_expiry')->nullable()->after('medical_card_expiry');
                $table->date('twic_card_expiry')->nullable()->after('hazmat_expiry');
                $table->string('address')->nullable()->after('twic_card_expiry');
                $table->string('city')->nullable()->after('address');
                $table->string('state', 2)->nullable()->after('city');
                $table->string('zip', 10)->nullable()->after('state');
                $table->enum('employment_type', ['full_time', 'part_time', 'contractor'])->default('full_time')->after('hourly_rate');
                $table->boolean('drug_test_passed')->default(true)->after('status');
                $table->date('last_drug_test_date')->nullable()->after('drug_test_passed');
                $table->date('next_drug_test_date')->nullable()->after('last_drug_test_date');
                $table->integer('total_miles_driven')->default(0)->after('next_drug_test_date');
                $table->integer('safety_score')->default(100)->after('total_miles_driven');
                $table->string('photo')->nullable()->after('safety_score');
                $table->json('documents')->nullable()->after('photo');
                
                $table->index('employee_id');
                $table->index('medical_card_expiry');
                $table->index('next_drug_test_date');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('drivers')) {
            Schema::table('drivers', function (Blueprint $table) {
                $table->dropColumn([
                    'employee_id',
                    'emergency_contact',
                    'emergency_phone',
                    'date_of_birth',
                    'license_state',
                    'medical_card_expiry',
                    'hazmat_expiry',
                    'twic_card_expiry',
                    'address',
                    'city',
                    'state',
                    'zip',
                    'employment_type',
                    'drug_test_passed',
                    'last_drug_test_date',
                    'next_drug_test_date',
                    'total_miles_driven',
                    'safety_score',
                    'photo',
                    'documents',
                ]);
            });
        }
    }
};