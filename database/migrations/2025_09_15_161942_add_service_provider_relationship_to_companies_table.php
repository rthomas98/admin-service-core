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
        Schema::table('companies', function (Blueprint $table) {
            $table->unsignedBigInteger('service_provider_id')->nullable()->after('type');
            $table->foreign('service_provider_id')->references('id')->on('companies')->onDelete('set null');
            $table->enum('company_type', ['service_provider', 'customer'])->default('customer')->after('service_provider_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropForeign(['service_provider_id']);
            $table->dropColumn(['service_provider_id', 'company_type']);
        });
    }
};
