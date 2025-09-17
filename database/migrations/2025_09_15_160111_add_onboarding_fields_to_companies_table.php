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
            $table->boolean('onboarding_completed')->default(false)->after('is_active');
            $table->timestamp('onboarded_at')->nullable()->after('onboarding_completed');
            $table->json('onboarding_steps')->nullable()->after('onboarded_at');

            // Additional fields that might be missing for new companies
            $table->string('tax_id')->nullable()->after('website');
            $table->string('business_type')->nullable()->after('tax_id');
            $table->string('industry')->nullable()->after('business_type');
            $table->string('city')->nullable()->after('address');
            $table->string('state')->nullable()->after('city');
            $table->string('postal_code')->nullable()->after('state');
            $table->string('country')->default('USA')->after('postal_code');
            $table->text('description')->nullable()->after('country');
            $table->string('contact_name')->nullable()->after('description');
            $table->string('contact_title')->nullable()->after('contact_name');
            $table->string('billing_email')->nullable()->after('email');
            $table->string('billing_address')->nullable()->after('billing_email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'onboarding_completed',
                'onboarded_at',
                'onboarding_steps',
                'tax_id',
                'business_type',
                'industry',
                'city',
                'state',
                'postal_code',
                'country',
                'description',
                'contact_name',
                'contact_title',
                'billing_email',
                'billing_address',
            ]);
        });
    }
};
