<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->json('notification_preferences')->nullable()->after('email');
            $table->boolean('notifications_enabled')->default(true)->after('notification_preferences');
            $table->string('preferred_notification_method')->default('email')->after('notifications_enabled');
            $table->string('sms_number')->nullable()->after('preferred_notification_method');
            $table->boolean('sms_verified')->default(false)->after('sms_number');
            $table->timestamp('sms_verified_at')->nullable()->after('sms_verified');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([
                'notification_preferences',
                'notifications_enabled',
                'preferred_notification_method',
                'sms_number',
                'sms_verified',
                'sms_verified_at',
            ]);
        });
    }
};