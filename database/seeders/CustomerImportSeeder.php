<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class CustomerImportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvPath = '/Users/robthomas/Downloads/Customers_export_68ade85ebeeb2.csv';
        
        if (!file_exists($csvPath)) {
            $this->command->error("CSV file not found at: {$csvPath}");
            return;
        }
        
        // Get RAW Disposal company
        $rawDisposal = Company::where('slug', 'raw-disposal')->first();
        
        if (!$rawDisposal) {
            $this->command->error('RAW Disposal company not found! Run CompanySeeder first.');
            return;
        }
        
        $this->command->info('Importing customers for RAW Disposal...');
        
        // Open and read CSV file
        $file = fopen($csvPath, 'r');
        
        // Skip header row
        $header = fgetcsv($file);
        
        $count = 0;
        
        while (($row = fgetcsv($file)) !== false) {
            // Map CSV columns to database fields
            $customerData = [
                'company_id' => $rawDisposal->id,
                'external_id' => $row[0] ?: null,
                'customer_since' => $this->parseDate($row[1]),
                'first_name' => $row[2] ?: null,
                'last_name' => $row[3] ?: null,
                'name' => $row[4] ?: null,
                'organization' => $row[5] ?: null,
                'emails' => $row[6] ?: null,
                'phone' => $row[7] ?: null,
                'phone_ext' => $row[8] ?: null,
                'secondary_phone' => $row[9] ?: null,
                'secondary_phone_ext' => $row[10] ?: null,
                'fax' => $row[11] ?: null,
                'fax_ext' => $row[12] ?: null,
                'address' => $row[13] ?: null,
                'secondary_address' => $row[14] ?: null,
                'city' => $row[15] ?: null,
                'zip' => $row[16] ?: null,
                'external_message' => $row[17] ?: null,
                'internal_memo' => $row[18] ?: null,
                'delivery_method' => $row[19] ?: null,
                'referral' => $row[20] ?: null,
                'customer_number' => $row[21] ?: null,
                'tax_exemption_details' => $row[22] ?: null,
                'tax_exempt_reason' => $row[23] ?: null,
                'state' => $row[25] ?? null,
                'county' => $row[26] ?? null,
                'divisions' => $row[27] ?? null,
                'business_type' => $row[28] ?? null,
                'tax_code_name' => $row[29] ?? null,
            ];
            
            // Create or update customer based on external_id
            Customer::updateOrCreate(
                [
                    'company_id' => $rawDisposal->id,
                    'external_id' => $customerData['external_id'],
                ],
                $customerData
            );
            
            $count++;
            
            if ($count % 100 === 0) {
                $this->command->info("Imported {$count} customers...");
            }
        }
        
        fclose($file);
        
        $this->command->info("Successfully imported {$count} customers for RAW Disposal!");
    }
    
    private function parseDate(?string $dateString): ?string
    {
        if (empty($dateString)) {
            return null;
        }
        
        try {
            // Parse ISO 8601 format from CSV
            return Carbon::parse($dateString)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }
}
