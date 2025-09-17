<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Since the role column is VARCHAR with a CHECK constraint in PostgreSQL,
        // we need to drop and recreate the constraint to add the new 'company' value
        // For SQLite (used in testing), we skip the constraint modification

        if (config('database.default') === 'pgsql') {
            // Drop the existing check constraint for company_user_invites table
            DB::statement('ALTER TABLE company_user_invites DROP CONSTRAINT IF EXISTS company_user_invites_role_check');

            // Add the new check constraint with 'company' role included
            DB::statement("ALTER TABLE company_user_invites ADD CONSTRAINT company_user_invites_role_check CHECK (role IN ('admin', 'company', 'manager', 'staff', 'viewer'))");

            // Also update company_users table if it exists
            if (Schema::hasTable('company_users')) {
                DB::statement('ALTER TABLE company_users DROP CONSTRAINT IF EXISTS company_users_role_check');
                DB::statement("ALTER TABLE company_users ADD CONSTRAINT company_users_role_check CHECK (role IN ('admin', 'company', 'manager', 'staff', 'viewer'))");
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (config('database.default') === 'pgsql') {
            // Restore the original check constraint without 'company' role
            DB::statement('ALTER TABLE company_user_invites DROP CONSTRAINT IF EXISTS company_user_invites_role_check');
            DB::statement("ALTER TABLE company_user_invites ADD CONSTRAINT company_user_invites_role_check CHECK (role IN ('admin', 'manager', 'staff', 'viewer'))");

            if (Schema::hasTable('company_users')) {
                DB::statement('ALTER TABLE company_users DROP CONSTRAINT IF EXISTS company_users_role_check');
                DB::statement("ALTER TABLE company_users ADD CONSTRAINT company_users_role_check CHECK (role IN ('admin', 'manager', 'staff', 'viewer'))");
            }
        }
    }
};
