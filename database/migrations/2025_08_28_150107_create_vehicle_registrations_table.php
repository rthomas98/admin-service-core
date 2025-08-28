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
        Schema::create('vehicle_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');
            $table->string('registration_number')->unique();
            
            // Registration details
            $table->string('license_plate');
            $table->string('registration_state');
            $table->date('registration_date');
            $table->date('expiry_date');
            $table->enum('status', ['active', 'expired', 'pending_renewal', 'suspended', 'cancelled']);
            
            // Renewal information
            $table->date('renewal_reminder_date')->nullable();
            $table->boolean('auto_renew')->default(false);
            $table->date('last_renewal_date')->nullable();
            $table->date('next_renewal_date')->nullable();
            $table->integer('renewal_notice_days')->default(30); // Days before expiry to send notice
            
            // Fee information
            $table->decimal('registration_fee', 10, 2)->default(0);
            $table->decimal('renewal_fee', 10, 2)->default(0);
            $table->decimal('late_fee', 10, 2)->default(0);
            $table->decimal('other_fees', 10, 2)->default(0);
            $table->decimal('total_paid', 10, 2)->default(0);
            $table->enum('payment_status', ['pending', 'paid', 'partial', 'overdue'])->default('pending');
            $table->date('payment_date')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('transaction_number')->nullable();
            
            // Vehicle information at registration
            $table->string('vin')->nullable();
            $table->string('make')->nullable();
            $table->string('model')->nullable();
            $table->integer('year')->nullable();
            $table->string('color')->nullable();
            $table->integer('weight')->nullable(); // in pounds
            $table->string('vehicle_class')->nullable();
            $table->string('fuel_type')->nullable();
            
            // Owner information
            $table->string('registered_owner')->nullable();
            $table->string('owner_address')->nullable();
            $table->string('owner_city')->nullable();
            $table->string('owner_state')->nullable();
            $table->string('owner_zip')->nullable();
            
            // Insurance information
            $table->string('insurance_company')->nullable();
            $table->string('insurance_policy_number')->nullable();
            $table->date('insurance_expiry_date')->nullable();
            
            // Additional permits and endorsements
            $table->json('permits')->nullable(); // IFTA, IRP, etc.
            $table->json('endorsements')->nullable();
            $table->boolean('dot_compliant')->default(false);
            $table->string('dot_number')->nullable();
            $table->string('mc_number')->nullable();
            
            // Documents and attachments
            $table->string('registration_document')->nullable();
            $table->string('insurance_document')->nullable();
            $table->json('other_documents')->nullable();
            $table->json('photos')->nullable();
            
            // Notes and history
            $table->text('notes')->nullable();
            $table->json('renewal_history')->nullable();
            $table->json('violation_history')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['company_id', 'vehicle_id']);
            $table->index(['expiry_date', 'status']);
            $table->index(['renewal_reminder_date']);
            $table->index('license_plate');
            $table->index('registration_state');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_registrations');
    }
};