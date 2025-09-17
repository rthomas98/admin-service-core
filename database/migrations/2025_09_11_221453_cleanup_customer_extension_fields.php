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
        // First, clean up data that looks like phone numbers in extension fields
        // Extensions should only be short numbers (e.g., "123", "4567")

        // Clean up phone_ext field
        DB::table('customers')
            ->whereNotNull('phone_ext')
            ->whereRaw('LENGTH(phone_ext) > 10')
            ->update(['phone_ext' => null]);

        // If extension contains dashes or looks like a phone number, clear it
        DB::table('customers')
            ->where('phone_ext', 'like', '%-%')
            ->update(['phone_ext' => null]);

        DB::table('customers')
            ->where('secondary_phone_ext', 'like', '%-%')
            ->update(['secondary_phone_ext' => null]);

        DB::table('customers')
            ->where('fax_ext', 'like', '%-%')
            ->update(['fax_ext' => null]);

        // Now modify the columns to have appropriate length
        Schema::table('customers', function (Blueprint $table) {
            // Ensure all extension fields are nullable strings with appropriate length
            if (Schema::hasColumn('customers', 'phone_ext')) {
                $table->string('phone_ext', 10)->nullable()->change();
            }

            if (Schema::hasColumn('customers', 'secondary_phone_ext')) {
                $table->string('secondary_phone_ext', 10)->nullable()->change();
            }

            if (Schema::hasColumn('customers', 'fax_ext')) {
                $table->string('fax_ext', 10)->nullable()->change();
            }
        });

        // Clean up any empty or whitespace-only extension values
        DB::table('customers')
            ->where('phone_ext', '')
            ->orWhereRaw("phone_ext = ' '")
            ->orWhereRaw("TRIM(phone_ext) = ''")
            ->update(['phone_ext' => null]);

        DB::table('customers')
            ->where('secondary_phone_ext', '')
            ->orWhereRaw("secondary_phone_ext = ' '")
            ->orWhereRaw("TRIM(secondary_phone_ext) = ''")
            ->update(['secondary_phone_ext' => null]);

        DB::table('customers')
            ->where('fax_ext', '')
            ->orWhereRaw("fax_ext = ' '")
            ->orWhereRaw("TRIM(fax_ext) = ''")
            ->update(['fax_ext' => null]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to reverse this cleanup migration
    }
};
