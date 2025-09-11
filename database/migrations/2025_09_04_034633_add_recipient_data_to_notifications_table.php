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
        Schema::table('notifications', function (Blueprint $table) {
            $table->json('recipient_data')->nullable()->after('recipient_phone');
            $table->json('action_data')->nullable()->after('data');
            $table->boolean('is_read')->default(false)->after('read_at');
            $table->string('priority')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn(['recipient_data', 'action_data', 'is_read', 'priority']);
        });
    }
};
