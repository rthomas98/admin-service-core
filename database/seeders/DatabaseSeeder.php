<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Call seeders in the correct order
        $this->call([
            // Use the comprehensive Raw Disposal seeder
            RawDisposalSeeder::class,    // Creates company, users, customers, equipment, orders, etc.
            
            // Optionally, you can uncomment these if you want to use the original seeders
            // CompanySeeder::class,        // Create companies first
            // CreateUsersSeeder::class,    // Create users and assign to companies
            // CustomerImportSeeder::class, // Import customers for RAW Disposal
        ]);
        
        $this->command->info("\n🎉 Database seeding complete!");
        $this->command->info("You can now login with:");
        $this->command->info("  Email: admin@rawdisposal.com");
        $this->command->info("  Password: password");
    }
}
