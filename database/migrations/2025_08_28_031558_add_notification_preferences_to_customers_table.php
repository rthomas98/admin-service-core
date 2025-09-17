<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'notification_preferences')) {
                $table->json('notification_preferences')->nullable()->after('emails');
            }
            if (!Schema::hasColumn('customers', 'notifications_enabled')) {
                $table->boolean('notifications_enabled')->default(true)->after('notification_preferences');
            }
            if (!Schema::hasColumn('customers', 'preferred_notification_method')) {
                $table->string('preferred_notification_method')->default('email')->after('notifications_enabled');
            }
            if (!Schema::hasColumn('customers', 'sms_number')) {
                $table->string('sms_number')->nullable()->after('preferred_notification_method');
            }
            if (!Schema::hasColumn('customers', 'sms_verified')) {
                $table->boolean('sms_verified')->default(false)->after('sms_number');
            }
            if (!Schema::hasColumn('customers', 'sms_verified_at')) {
                $table->timestamp('sms_verified_at')->nullable()->after('sms_verified');
            }
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