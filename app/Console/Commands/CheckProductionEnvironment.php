<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckProductionEnvironment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-production-environment';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check production environment and database details';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('==== Production Environment Check ====');

        // Environment details
        $this->info("\n1. Environment:");
        $this->info("APP_ENV: " . env('APP_ENV', 'not set'));
        $this->info("APP_DEBUG: " . (env('APP_DEBUG', false) ? 'true' : 'false'));
        $this->info("APP_URL: " . env('APP_URL', 'not set'));

        // Database connection details
        $this->info("\n2. Database Configuration:");
        $connection = config('database.default');
        $this->info("Default connection: {$connection}");

        $dbConfig = config("database.connections.{$connection}");
        $this->info("Driver: " . ($dbConfig['driver'] ?? 'not set'));
        $this->info("Host: " . ($dbConfig['host'] ?? 'not set'));
        $this->info("Port: " . ($dbConfig['port'] ?? 'not set'));
        $this->info("Database: " . ($dbConfig['database'] ?? 'not set'));
        $this->info("Username: " . ($dbConfig['username'] ?? 'not set'));

        // Test database connection
        $this->info("\n3. Testing database connection:");
        try {
            $pdo = DB::connection()->getPdo();
            $this->info("✅ Database connected successfully");

            // Get database type
            $driverName = $pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);
            $this->info("Database driver: {$driverName}");

            // Get server version
            $version = $pdo->getAttribute(\PDO::ATTR_SERVER_VERSION);
            $this->info("Server version: {$version}");
        } catch (\Exception $e) {
            $this->error("❌ Database connection failed: " . $e->getMessage());
        }

        // Check users table
        $this->info("\n4. Users table check:");
        try {
            $userCount = DB::table('users')->count();
            $this->info("Total users: {$userCount}");

            $adminUsers = DB::table('users')
                ->where('email', 'rob.thomas@empuls3.com')
                ->get(['id', 'email', 'created_at', 'updated_at']);

            foreach ($adminUsers as $user) {
                $this->info("Admin user found:");
                $this->info("  ID: {$user->id}");
                $this->info("  Email: {$user->email}");
                $this->info("  Created: {$user->created_at}");
                $this->info("  Updated: {$user->updated_at}");
            }
        } catch (\Exception $e) {
            $this->error("❌ Error querying users table: " . $e->getMessage());
        }

        // Check companies table
        $this->info("\n5. Companies table check:");
        try {
            $companyCount = DB::table('companies')->count();
            $this->info("Total companies: {$companyCount}");

            $companies = DB::table('companies')
                ->whereIn('slug', ['liv-transport', 'raw-disposal'])
                ->get(['id', 'name', 'slug']);

            foreach ($companies as $company) {
                $this->info("Company: {$company->name} (ID: {$company->id}, Slug: {$company->slug})");
            }
        } catch (\Exception $e) {
            $this->error("❌ Error querying companies table: " . $e->getMessage());
        }

        // Check company_user pivot table
        $this->info("\n6. Company-User associations:");
        try {
            $associations = DB::table('company_user')
                ->join('users', 'company_user.user_id', '=', 'users.id')
                ->where('users.email', 'rob.thomas@empuls3.com')
                ->get(['company_user.*']);

            $this->info("Found " . $associations->count() . " associations");
            foreach ($associations as $assoc) {
                $this->info("  Company ID: {$assoc->company_id}, Role: {$assoc->role}");
            }
        } catch (\Exception $e) {
            $this->error("❌ Error querying associations: " . $e->getMessage());
        }

        // Check if we're using MySQL or PostgreSQL specific features
        $this->info("\n7. Database type specific check:");
        try {
            // Try to identify if this is MySQL or PostgreSQL
            $tables = DB::select("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' OR table_schema = DATABASE() LIMIT 5");
            if (!empty($tables)) {
                $this->info("Successfully queried information_schema");
                $this->info("Sample tables: " . implode(', ', array_slice(array_column($tables, 'table_name'), 0, 5)));
            }
        } catch (\Exception $e) {
            $this->warn("Could not query information_schema: " . $e->getMessage());
        }

        $this->info("\n==== Check Complete ====");

        return 0;
    }
}