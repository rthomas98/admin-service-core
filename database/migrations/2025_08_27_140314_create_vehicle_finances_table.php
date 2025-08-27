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
        if (!Schema::hasTable('vehicle_finances')) {
            Schema::create('vehicle_finances', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained();
                $table->morphs('financeable');
                $table->foreignId('finance_company_id')->constrained('finance_companies');
                $table->string('account_number')->nullable();
                $table->string('reference_number')->nullable();
                $table->string('finance_type');
                $table->date('start_date');
                $table->date('end_date')->nullable();
                $table->decimal('monthly_payment', 10, 2)->nullable();
                $table->decimal('total_amount', 10, 2)->nullable();
                $table->decimal('down_payment', 10, 2)->nullable();
                $table->decimal('interest_rate', 5, 2)->nullable();
                $table->integer('term_months')->nullable();
                $table->decimal('residual_value', 10, 2)->nullable();
                $table->text('notes')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                
                $table->index('is_active');
                $table->index('end_date');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_finances');
    }
};