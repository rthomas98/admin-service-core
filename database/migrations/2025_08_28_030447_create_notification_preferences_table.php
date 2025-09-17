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
        if (!Schema::hasTable('notification_preferences')) {
            Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->string('preferenceable_type'); // customer, driver, user
            $table->unsignedBigInteger('preferenceable_id');
            $table->boolean('email_enabled')->default(true);
            $table->boolean('sms_enabled')->default(true);
            $table->boolean('push_enabled')->default(false);
            // Notification categories
            $table->boolean('service_reminders')->default(true);
            $table->boolean('payment_reminders')->default(true);
            $table->boolean('emergency_alerts')->default(true);
            $table->boolean('dispatch_notifications')->default(true);
            $table->boolean('marketing_messages')->default(false);
            $table->boolean('system_updates')->default(true);
            // Timing preferences
            $table->string('preferred_time')->default('anytime'); // morning, afternoon, evening, anytime
            $table->json('quiet_hours')->nullable(); // {"start": "22:00", "end": "08:00"}
            $table->timestamps();
            
            // Indexes (with custom name to avoid length issues)
            $table->unique(['preferenceable_type', 'preferenceable_id'], 'notif_prefs_poly_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
    }
};
