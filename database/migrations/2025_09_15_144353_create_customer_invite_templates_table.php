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
        Schema::create('customer_invite_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('subject');
            $table->text('body');
            $table->json('variables')->nullable(); // Available template variables
            $table->json('settings')->nullable(); // Template-specific settings
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->integer('expiration_days')->default(7);
            $table->string('language', 5)->default('en');
            $table->unsignedInteger('usage_count')->default(0);
            $table->timestamp('last_used_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // Indexes
            $table->index(['company_id', 'is_active']);
            $table->index(['company_id', 'is_default']);
            $table->index('slug');
            $table->index('language');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_invite_templates');
    }
};
