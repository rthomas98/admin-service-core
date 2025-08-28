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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // email, sms, push, in_app
            $table->string('channel'); // customer, driver, admin
            $table->string('category'); // service_reminder, payment_due, emergency, dispatch, marketing
            $table->string('recipient_type'); // customer, driver, user, admin
            $table->unsignedBigInteger('recipient_id');
            $table->string('recipient_email')->nullable();
            $table->string('recipient_phone')->nullable();
            $table->string('subject');
            $table->text('message');
            $table->json('data')->nullable(); // Additional data for the notification
            $table->string('status')->default('pending'); // pending, sent, failed, cancelled
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->text('error_message')->nullable();
            $table->integer('retry_count')->default(0);
            $table->timestamps();
            
            // Indexes
            $table->index(['company_id', 'status']);
            $table->index(['recipient_type', 'recipient_id']);
            $table->index('scheduled_at');
            $table->index('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
