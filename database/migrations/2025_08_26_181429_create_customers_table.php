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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('external_id')->nullable()->index();
            $table->date('customer_since')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('name')->nullable();
            $table->string('organization')->nullable();
            $table->string('emails')->nullable();
            $table->string('phone')->nullable();
            $table->string('phone_ext')->nullable();
            $table->string('secondary_phone')->nullable();
            $table->string('secondary_phone_ext')->nullable();
            $table->string('fax')->nullable();
            $table->string('fax_ext')->nullable();
            $table->text('address')->nullable();
            $table->text('secondary_address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip')->nullable();
            $table->string('county')->nullable();
            $table->text('external_message')->nullable();
            $table->text('internal_memo')->nullable();
            $table->string('delivery_method')->nullable();
            $table->string('referral')->nullable();
            $table->string('customer_number')->nullable()->index();
            $table->text('tax_exemption_details')->nullable();
            $table->text('tax_exempt_reason')->nullable();
            $table->string('divisions')->nullable();
            $table->string('business_type')->nullable();
            $table->string('tax_code_name')->nullable();
            $table->timestamps();
            
            $table->index(['company_id', 'customer_since']);
            $table->index(['company_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
