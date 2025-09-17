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
        Schema::table('customers', function (Blueprint $table) {
            $table->boolean('portal_access')->default(false)->after('notifications_enabled');
            $table->string('portal_password')->nullable()->after('portal_access');
            $table->timestamp('email_verified_at')->nullable()->after('portal_password');
            $table->string('remember_token')->nullable()->after('email_verified_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['portal_access', 'portal_password', 'email_verified_at', 'remember_token']);
        });
    }
};
