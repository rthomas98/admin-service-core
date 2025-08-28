<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Customer;
use App\Models\DeliverySchedule;
use App\Models\DisposalSite;
use App\Models\Driver;
use App\Models\DriverAssignment;
use App\Models\EmergencyService;
use App\Models\Equipment;
use App\Models\FuelLog;
use App\Models\Invoice;
use App\Models\MaintenanceLog;
use App\Models\Payment;
use App\Models\Pricing;
use App\Models\Quote;
use App\Models\ServiceArea;
use App\Models\ServiceOrder;
use App\Models\ServiceSchedule;
use App\Models\VehicleFinance;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DashboardDataSeeder extends Seeder
{
    public function run(): void
    {
        // Get the first company or create one
        $company = Company::first();
        if (!$company) {
            $company = Company::create([
                'name' => 'Raw Disposal Services',
                'address' => '123 Waste Management Way',
                'city' => 'Houston',
                'state' => 'TX',
                'zip' => '77001',
                'phone' => '(713) 555-0100',
                'email' => 'info@rawdisposal.com',
                'website' => 'https://rawdisposal.com',
                'tax_id' => '12-3456789',
                'is_active' => true,
            ]);
        }

        // Create Customers
        $customers = [];
        $customerTypes = ['Residential', 'Commercial', 'Industrial', 'Municipal'];
        $businessTypes = ['Restaurant', 'Office Building', 'Retail Store', 'Manufacturing', 'Healthcare', 'School', 'Hotel'];
        
        for ($i = 1; $i <= 50; $i++) {
            $isCommercial = $i % 3 !== 0; // 2/3 commercial, 1/3 residential
            $customers[] = Customer::create([
                'company_id' => $company->id,
                'customer_number' => 'CUST-' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'name' => $isCommercial ? null : fake()->name(),
                'organization' => $isCommercial ? fake()->company() : null,
                'first_name' => !$isCommercial ? fake()->firstName() : null,
                'last_name' => !$isCommercial ? fake()->lastName() : null,
                'phone' => fake()->phoneNumber(),
                'address' => fake()->streetAddress(),
                'city' => fake()->city(),
                'state' => fake()->stateAbbr(),
                'zip' => fake()->postcode(),
                'customer_since' => Carbon::now()->subDays(rand(30, 730)),
                'business_type' => $isCommercial ? fake()->randomElement($businessTypes) : 'Residential',
                'emails' => json_encode([fake()->safeEmail()]),
            ]);
        }

        // Create Service Areas
        $serviceAreas = [];
        $areaNames = ['Downtown', 'North District', 'South District', 'East Zone', 'West Zone', 'Industrial Park', 'Business District'];
        $zipCodes = ['77001', '77002', '77003', '77004', '77005', '77006', '77007'];
        
        foreach ($areaNames as $index => $area) {
            $serviceAreas[] = ServiceArea::create([
                'company_id' => $company->id,
                'name' => $area,
                'description' => "Service coverage for $area",
                'zip_codes' => json_encode([$zipCodes[$index], $zipCodes[($index + 1) % 7]]),
                'parishes' => json_encode(['Harris County']),
                'delivery_surcharge' => fake()->randomFloat(2, 0, 25),
                'pickup_surcharge' => fake()->randomFloat(2, 0, 15),
                'emergency_surcharge' => fake()->randomFloat(2, 50, 150),
                'standard_delivery_days' => rand(2, 5),
                'rush_delivery_hours' => rand(2, 8),
                'rush_delivery_surcharge' => fake()->randomFloat(2, 25, 100),
                'is_active' => true,
                'priority' => $index + 1,
            ]);
        }

        // Create Disposal Sites
        $disposalSites = [];
        $siteTypes = ['landfill', 'recycling', 'composting', 'hazardous', 'transfer_station'];
        $siteNames = ['Landfill', 'Recycling Center', 'Composting Facility', 'Hazardous Waste', 'Transfer Station'];
        
        for ($i = 1; $i <= 5; $i++) {
            $typeIndex = array_rand($siteTypes);
            $siteType = $siteTypes[$typeIndex];
            $siteName = $siteNames[$typeIndex];
            
            $disposalSites[] = DisposalSite::create([
                'company_id' => $company->id,
                'name' => fake()->company() . ' ' . $siteName,
                'location' => fake()->streetAddress() . ', ' . fake()->city() . ', ' . fake()->stateAbbr(),
                'parish' => 'Harris County',
                'site_type' => $siteType,
                'total_capacity' => rand(100000, 500000), // tons
                'current_capacity' => rand(20000, 90000), // tons
                'daily_intake_average' => rand(100, 500), // tons per day
                'status' => 'active', // Must be: active, maintenance, closed, or inactive
                'manager_name' => fake()->name(),
                'contact_phone' => fake()->phoneNumber(),
                'operating_hours' => '6:00 AM - 6:00 PM',
                'environmental_permit' => 'EPA-' . strtoupper(Str::random(8)),
                'last_inspection_date' => Carbon::now()->subDays(rand(30, 180)),
            ]);
        }

        // Create Drivers
        $drivers = [];
        $vehicleTypes = ['Box Truck', 'Pickup Truck', 'Flatbed', 'Van', 'Dump Truck'];
        $availableDays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
        
        for ($i = 1; $i <= 15; $i++) {
            $hireDate = Carbon::now()->subDays(rand(30, 1095));
            $drivers[] = Driver::create([
                'company_id' => $company->id,
                'employee_id' => 'DRV-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'first_name' => fake()->firstName(),
                'last_name' => fake()->lastName(),
                'phone' => fake()->phoneNumber(),
                'email' => fake()->safeEmail(),
                'license_number' => strtoupper(Str::random(8)),
                'license_class' => fake()->randomElement(['A', 'B', 'CDL-A', 'CDL-B']),
                'license_expiry_date' => Carbon::now()->addMonths(rand(6, 36)),
                'license_state' => fake()->stateAbbr(),
                'vehicle_type' => fake()->randomElement($vehicleTypes),
                'vehicle_registration' => strtoupper(Str::random(7)),
                'vehicle_make' => fake()->randomElement(['Ford', 'Chevrolet', 'Dodge', 'Freightliner']),
                'vehicle_model' => fake()->randomElement(['F-150', 'Silverado', 'Ram', 'Cascadia']),
                'vehicle_year' => rand(2018, 2024),
                'service_areas' => json_encode(fake()->randomElements($serviceAreas, rand(1, 3))->pluck('name')->toArray()),
                'can_lift_heavy' => fake()->boolean(80),
                'has_truck_crane' => fake()->boolean(40),
                'hourly_rate' => fake()->randomFloat(2, 18, 35),
                'shift_start_time' => fake()->randomElement(['06:00', '07:00', '08:00']),
                'shift_end_time' => fake()->randomElement(['14:00', '15:00', '16:00', '17:00']),
                'available_days' => json_encode(fake()->randomElements($availableDays, rand(4, 6))),
                'status' => $i <= 12 ? 'active' : 'inactive',
                'hired_date' => $hireDate,
                'emergency_contact' => fake()->name(),
                'emergency_phone' => fake()->phoneNumber(),
                'date_of_birth' => Carbon::now()->subYears(rand(25, 55))->subDays(rand(0, 365)),
                'medical_card_expiry' => Carbon::now()->addMonths(rand(12, 24)),
                'address' => fake()->streetAddress(),
                'city' => fake()->city(),
                'state' => fake()->stateAbbr(),
                'zip' => fake()->postcode(),
                'employment_type' => fake()->randomElement(['full-time', 'part-time', 'contract']),
                'drug_test_passed' => true,
                'last_drug_test_date' => Carbon::now()->subMonths(rand(1, 6)),
                'safety_score' => rand(85, 100),
            ]);
        }

        // Create Equipment/Vehicles
        $equipment = [];
        $vehicleTypes = ['Rear Loader', 'Side Loader', 'Front Loader', 'Roll-off Truck', 'Grapple Truck'];
        $manufacturers = ['Mack', 'Peterbilt', 'Volvo', 'Freightliner'];
        
        for ($i = 1; $i <= 20; $i++) {
            $purchaseDate = Carbon::now()->subDays(rand(180, 1825));
            $equipment[] = Equipment::create([
                'company_id' => $company->id,
                'equipment_number' => 'VEH-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'type' => fake()->randomElement($vehicleTypes),
                'make' => fake()->randomElement($manufacturers),
                'model' => 'Model ' . fake()->randomElement(['2020', '2021', '2022', '2023']),
                'year' => rand(2018, 2024),
                'vin' => strtoupper(Str::random(17)),
                'license_plate' => strtoupper(Str::random(3)) . '-' . rand(1000, 9999),
                'purchase_date' => $purchaseDate,
                'purchase_price' => fake()->randomFloat(2, 80000, 250000),
                'current_mileage' => rand(10000, 150000),
                'fuel_type' => fake()->randomElement(['Diesel', 'CNG', 'Electric']),
                'capacity' => rand(10, 30) . ' cubic yards',
                'status' => fake()->randomElement(['active', 'maintenance', 'repair', 'inactive']),
                'is_active' => $i <= 18, // 2 inactive vehicles
            ]);
        }

        // Create past 6 months of Driver Assignments
        $statuses = ['pending', 'in_progress', 'completed', 'cancelled'];
        $cargoTypes = ['General Waste', 'Recyclables', 'Organic Waste', 'Construction Debris', 'Hazardous Materials'];
        
        for ($month = 5; $month >= 0; $month--) {
            $monthDate = Carbon::now()->subMonths($month);
            $daysInMonth = $monthDate->daysInMonth;
            
            // Create 100-150 assignments per month
            $assignmentsPerMonth = rand(100, 150);
            for ($i = 0; $i < $assignmentsPerMonth; $i++) {
                $startDate = $monthDate->copy()->day(rand(1, $daysInMonth))->setTime(rand(6, 18), rand(0, 59));
                $endDate = $startDate->copy()->addHours(rand(4, 10));
                
                // 85% completed, 5% cancelled, 10% other statuses
                $statusRand = rand(1, 100);
                if ($statusRand <= 85) {
                    $status = 'completed';
                } elseif ($statusRand <= 90) {
                    $status = 'cancelled';
                } elseif ($month === 0 && $statusRand <= 95) {
                    $status = 'in_progress';
                } else {
                    $status = 'pending';
                }

                DriverAssignment::create([
                    'company_id' => $company->id,
                    'driver_id' => fake()->randomElement($drivers)->id,
                    'vehicle_id' => fake()->randomElement($equipment)->id,
                    'route_name' => fake()->randomElement($serviceAreas)->name . ' Route ' . rand(1, 10),
                    'start_date' => $startDate,
                    'end_date' => $status === 'completed' ? $endDate : null,
                    'status' => $status,
                    'cargo_type' => fake()->randomElement($cargoTypes),
                    'cargo_weight' => $status === 'completed' ? rand(1000, 8000) : 0,
                    'distance_traveled' => $status === 'completed' ? rand(20, 150) : 0,
                    'notes' => fake()->optional()->sentence(),
                    'pickup_location' => fake()->address(),
                    'delivery_location' => fake()->randomElement($disposalSites)->name,
                ]);
            }
        }

        // Create Fuel Logs for past 6 months
        foreach ($equipment as $vehicle) {
            for ($month = 5; $month >= 0; $month--) {
                $monthDate = Carbon::now()->subMonths($month);
                // 2-4 fuel entries per vehicle per month
                $fuelEntries = rand(2, 4);
                
                for ($i = 0; $i < $fuelEntries; $i++) {
                    FuelLog::create([
                        'company_id' => $company->id,
                        'vehicle_id' => $vehicle->id,
                        'driver_id' => fake()->randomElement($drivers)->id,
                        'fuel_date' => $monthDate->copy()->day(rand(1, $monthDate->daysInMonth)),
                        'fuel_type' => $vehicle->fuel_type,
                        'quantity' => fake()->randomFloat(2, 20, 80),
                        'price_per_gallon' => fake()->randomFloat(2, 3.50, 5.00),
                        'total_cost' => fake()->randomFloat(2, 100, 400),
                        'odometer_reading' => $vehicle->current_mileage - (($month + 1) * rand(1000, 3000)),
                        'station_name' => fake()->company() . ' Fuel Station',
                        'location' => fake()->city() . ', ' . fake()->stateAbbr(),
                    ]);
                }
            }
        }

        // Create Maintenance Logs
        $maintenanceTypes = ['Oil Change', 'Tire Rotation', 'Brake Service', 'Transmission Service', 'Engine Repair', 'Hydraulic System', 'Preventive Maintenance'];
        
        foreach ($equipment as $vehicle) {
            // 2-6 maintenance records per vehicle
            $maintenanceCount = rand(2, 6);
            for ($i = 0; $i < $maintenanceCount; $i++) {
                $serviceDate = Carbon::now()->subDays(rand(1, 365));
                MaintenanceLog::create([
                    'company_id' => $company->id,
                    'vehicle_id' => $vehicle->id,
                    'service_date' => $serviceDate,
                    'service_type' => fake()->randomElement($maintenanceTypes),
                    'description' => fake()->paragraph(2),
                    'parts_cost' => fake()->randomFloat(2, 50, 2000),
                    'labor_cost' => fake()->randomFloat(2, 100, 1000),
                    'total_cost' => fake()->randomFloat(2, 150, 3000),
                    'vendor_name' => fake()->company() . ' Auto Service',
                    'odometer_reading' => $vehicle->current_mileage - rand(1000, 20000),
                    'next_service_date' => $serviceDate->copy()->addMonths(rand(3, 6)),
                    'next_service_miles' => $vehicle->current_mileage + rand(5000, 15000),
                    'warranty_claim' => fake()->boolean(20),
                ]);
            }
        }

        // Create Invoices and Payments
        foreach ($customers as $customer) {
            // Create 3-12 invoices per customer
            $invoiceCount = rand(3, 12);
            for ($i = 0; $i < $invoiceCount; $i++) {
                $invoiceDate = Carbon::now()->subMonths(rand(0, 6))->subDays(rand(0, 28));
                $dueDate = $invoiceDate->copy()->addDays(30);
                $amount = fake()->randomFloat(2, 150, 5000);
                
                // Determine invoice status based on date
                if ($dueDate->isPast()) {
                    $status = fake()->randomElement(['paid', 'paid', 'paid', 'overdue']); // 75% paid
                } else {
                    $status = fake()->randomElement(['pending', 'pending', 'sent']);
                }

                $invoice = Invoice::create([
                    'company_id' => $company->id,
                    'customer_id' => $customer->id,
                    'invoice_number' => 'INV-' . Carbon::now()->year . '-' . str_pad(Invoice::count() + 1, 6, '0', STR_PAD_LEFT),
                    'invoice_date' => $invoiceDate,
                    'due_date' => $dueDate,
                    'amount' => $amount,
                    'tax_amount' => $amount * 0.0825, // 8.25% tax
                    'total_amount' => $amount * 1.0825,
                    'status' => $status,
                    'description' => 'Waste Management Services - ' . $invoiceDate->format('F Y'),
                    'terms' => 'Net 30',
                    'notes' => fake()->optional()->sentence(),
                ]);

                // Create payment if invoice is paid
                if ($status === 'paid') {
                    Payment::create([
                        'company_id' => $company->id,
                        'invoice_id' => $invoice->id,
                        'customer_id' => $customer->id,
                        'payment_date' => $dueDate->copy()->subDays(rand(0, 20)),
                        'amount' => $invoice->total_amount,
                        'payment_method' => fake()->randomElement(['credit_card', 'check', 'ach', 'cash']),
                        'reference_number' => strtoupper(Str::random(10)),
                        'status' => 'completed',
                        'notes' => 'Payment received - Thank you',
                    ]);
                }
            }
        }

        // Create Service Orders
        $serviceTypes = ['Regular Pickup', 'Special Pickup', 'Bulk Collection', 'Recycling Service', 'Emergency Service'];
        $priorities = ['low', 'normal', 'high', 'urgent'];
        
        foreach ($customers as $customer) {
            // Create 2-8 service orders per customer
            $orderCount = rand(2, 8);
            for ($i = 0; $i < $orderCount; $i++) {
                $scheduledDate = Carbon::now()->addDays(rand(-30, 30));
                $status = $scheduledDate->isPast() 
                    ? fake()->randomElement(['completed', 'completed', 'completed', 'cancelled'])
                    : fake()->randomElement(['scheduled', 'pending', 'confirmed']);

                ServiceOrder::create([
                    'company_id' => $company->id,
                    'customer_id' => $customer->id,
                    'order_number' => 'SO-' . str_pad(ServiceOrder::count() + 1, 6, '0', STR_PAD_LEFT),
                    'service_type' => fake()->randomElement($serviceTypes),
                    'scheduled_date' => $scheduledDate,
                    'completed_date' => $status === 'completed' ? $scheduledDate : null,
                    'status' => $status,
                    'priority' => fake()->randomElement($priorities),
                    'assigned_driver_id' => fake()->optional()->randomElement($drivers)?->id,
                    'assigned_vehicle_id' => fake()->optional()->randomElement($equipment)?->id,
                    'notes' => fake()->optional()->paragraph(),
                    'special_instructions' => fake()->optional()->sentence(),
                    'estimated_duration' => rand(15, 120), // minutes
                    'actual_duration' => $status === 'completed' ? rand(10, 150) : null,
                ]);
            }
        }

        // Create Service Schedules (recurring services)
        $frequencies = ['daily', 'weekly', 'bi-weekly', 'monthly'];
        $dayOfWeek = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
        
        foreach (fake()->randomElements($customers, 30) as $customer) {
            ServiceSchedule::create([
                'company_id' => $company->id,
                'customer_id' => $customer->id,
                'service_type' => fake()->randomElement($serviceTypes),
                'frequency' => fake()->randomElement($frequencies),
                'day_of_week' => fake()->randomElement($dayOfWeek),
                'time_window_start' => fake()->randomElement(['06:00', '07:00', '08:00', '09:00']),
                'time_window_end' => fake()->randomElement(['12:00', '14:00', '16:00', '18:00']),
                'start_date' => Carbon::now()->subMonths(rand(1, 6)),
                'end_date' => fake()->optional()->dateTimeBetween('now', '+1 year'),
                'is_active' => fake()->boolean(90),
                'notes' => fake()->optional()->sentence(),
                'assigned_driver_id' => fake()->optional()->randomElement($drivers)?->id,
                'assigned_vehicle_id' => fake()->optional()->randomElement($equipment)?->id,
            ]);
        }

        // Create Quotes
        foreach (fake()->randomElements($customers, 20) as $customer) {
            $quoteDate = Carbon::now()->subDays(rand(0, 60));
            $validUntil = $quoteDate->copy()->addDays(30);
            
            Quote::create([
                'company_id' => $company->id,
                'customer_id' => $customer->id,
                'quote_number' => 'QT-' . str_pad(Quote::count() + 1, 6, '0', STR_PAD_LEFT),
                'quote_date' => $quoteDate,
                'valid_until' => $validUntil,
                'service_type' => fake()->randomElement($serviceTypes),
                'frequency' => fake()->randomElement($frequencies),
                'monthly_rate' => fake()->randomFloat(2, 200, 2000),
                'setup_fee' => fake()->randomFloat(2, 0, 500),
                'total_amount' => fake()->randomFloat(2, 200, 2500),
                'status' => fake()->randomElement(['draft', 'sent', 'accepted', 'rejected', 'expired']),
                'notes' => fake()->optional()->paragraph(),
                'terms_and_conditions' => 'Standard terms and conditions apply.',
            ]);
        }

        // Create Pricing tiers
        $pricingTiers = [
            ['name' => 'Residential Basic', 'base_rate' => 35, 'per_pickup' => 15],
            ['name' => 'Residential Premium', 'base_rate' => 50, 'per_pickup' => 12],
            ['name' => 'Commercial Small', 'base_rate' => 150, 'per_pickup' => 25],
            ['name' => 'Commercial Medium', 'base_rate' => 300, 'per_pickup' => 20],
            ['name' => 'Commercial Large', 'base_rate' => 500, 'per_pickup' => 18],
            ['name' => 'Industrial', 'base_rate' => 1000, 'per_pickup' => 30],
        ];

        foreach ($pricingTiers as $tier) {
            Pricing::create([
                'company_id' => $company->id,
                'name' => $tier['name'],
                'description' => "Pricing tier for {$tier['name']} customers",
                'base_rate' => $tier['base_rate'],
                'per_pickup_rate' => $tier['per_pickup'],
                'is_active' => true,
                'effective_date' => Carbon::now()->subMonths(6),
            ]);
        }

        // Create Emergency Services
        $emergencyTypes = ['Hazmat Spill', 'Storm Debris', 'Emergency Cleanup', 'Accident Response', 'Flooding Response'];
        
        for ($i = 0; $i < 10; $i++) {
            $reportedAt = Carbon::now()->subDays(rand(1, 90))->subHours(rand(0, 23));
            $status = $reportedAt->diffInDays(now()) > 7 
                ? 'resolved' 
                : fake()->randomElement(['reported', 'in_progress', 'resolved']);

            EmergencyService::create([
                'company_id' => $company->id,
                'type' => fake()->randomElement($emergencyTypes),
                'location' => fake()->address(),
                'reported_at' => $reportedAt,
                'responded_at' => $status !== 'reported' ? $reportedAt->copy()->addMinutes(rand(15, 120)) : null,
                'resolved_at' => $status === 'resolved' ? $reportedAt->copy()->addHours(rand(2, 24)) : null,
                'status' => $status,
                'priority' => fake()->randomElement(['low', 'medium', 'high', 'critical']),
                'description' => fake()->paragraph(),
                'assigned_driver_id' => fake()->optional()->randomElement($drivers)?->id,
                'assigned_vehicle_id' => fake()->optional()->randomElement($equipment)?->id,
                'contact_name' => fake()->name(),
                'contact_phone' => fake()->phoneNumber(),
                'estimated_cost' => fake()->randomFloat(2, 500, 10000),
                'actual_cost' => $status === 'resolved' ? fake()->randomFloat(2, 400, 12000) : null,
            ]);
        }

        // Create Vehicle Finance records for some vehicles
        $financeCompanies = ['Wells Fargo Equipment Finance', 'US Bank Equipment Finance', 'PNC Equipment Finance', 'Key Equipment Finance'];
        
        foreach (fake()->randomElements($equipment, 10) as $vehicle) {
            VehicleFinance::create([
                'company_id' => $company->id,
                'vehicle_id' => $vehicle->id,
                'finance_company' => fake()->randomElement($financeCompanies),
                'account_number' => strtoupper(Str::random(12)),
                'monthly_payment' => fake()->randomFloat(2, 1500, 4000),
                'interest_rate' => fake()->randomFloat(2, 3.5, 8.5),
                'term_months' => fake()->randomElement([36, 48, 60, 72]),
                'start_date' => $vehicle->purchase_date,
                'end_date' => $vehicle->purchase_date->copy()->addMonths(fake()->randomElement([36, 48, 60, 72])),
                'down_payment' => fake()->randomFloat(2, 10000, 50000),
                'total_amount' => fake()->randomFloat(2, 100000, 300000),
                'remaining_balance' => fake()->randomFloat(2, 20000, 250000),
                'is_active' => true,
            ]);
        }

        // Create Delivery Schedules for upcoming week
        $deliveryTypes = ['Regular Route', 'Special Collection', 'Recycling Route', 'Commercial Route'];
        $startOfWeek = Carbon::now()->startOfWeek();
        
        for ($day = 0; $day < 7; $day++) {
            $deliveryDate = $startOfWeek->copy()->addDays($day);
            
            // Create 5-10 delivery schedules per day
            $schedulesPerDay = rand(5, 10);
            for ($i = 0; $i < $schedulesPerDay; $i++) {
                DeliverySchedule::create([
                    'company_id' => $company->id,
                    'driver_id' => fake()->randomElement($drivers)->id,
                    'vehicle_id' => fake()->randomElement($equipment)->id,
                    'route_name' => fake()->randomElement($serviceAreas)->name . ' - ' . fake()->randomElement($deliveryTypes),
                    'scheduled_date' => $deliveryDate,
                    'start_time' => fake()->randomElement(['06:00', '07:00', '08:00']),
                    'end_time' => fake()->randomElement(['14:00', '15:00', '16:00', '17:00']),
                    'stops' => rand(15, 40),
                    'estimated_duration' => rand(6, 9) * 60, // 6-9 hours in minutes
                    'status' => $deliveryDate->isPast() 
                        ? fake()->randomElement(['completed', 'completed', 'completed', 'partial'])
                        : ($deliveryDate->isToday() 
                            ? fake()->randomElement(['in_progress', 'scheduled'])
                            : 'scheduled'),
                    'notes' => fake()->optional()->sentence(),
                ]);
            }
        }

        $this->command->info('Dashboard data seeded successfully!');
        $this->command->info('Created:');
        $this->command->info('- 50 Customers');
        $this->command->info('- 15 Drivers');
        $this->command->info('- 20 Vehicles/Equipment');
        $this->command->info('- ' . DriverAssignment::count() . ' Driver Assignments');
        $this->command->info('- ' . FuelLog::count() . ' Fuel Logs');
        $this->command->info('- ' . MaintenanceLog::count() . ' Maintenance Records');
        $this->command->info('- ' . Invoice::count() . ' Invoices');
        $this->command->info('- ' . Payment::count() . ' Payments');
        $this->command->info('- ' . ServiceOrder::count() . ' Service Orders');
        $this->command->info('- ' . Quote::count() . ' Quotes');
        $this->command->info('- ' . DeliverySchedule::count() . ' Delivery Schedules');
        $this->command->info('- 10 Emergency Services');
    }
}