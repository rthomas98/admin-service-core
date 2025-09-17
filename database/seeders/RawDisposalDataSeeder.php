<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Customer;
use App\Models\DisposalSite;
use App\Models\Driver;
use App\Models\DriverAssignment;
use App\Models\Equipment;
use App\Models\FuelLog;
use App\Models\Invoice;
use App\Models\MaintenanceLog;
use App\Models\Payment;
use App\Models\ServiceArea;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\WasteCollection;
use App\Models\WasteRoute;
use App\Models\WorkOrder;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RawDisposalDataSeeder extends Seeder
{
    public function run(): void
    {
        // Get RAW Disposal company
        $rawDisposal = Company::where('slug', 'raw-disposal')->orWhere('name', 'LIKE', '%RAW Disposal%')->first();

        if (! $rawDisposal) {
            $this->command->error('RAW Disposal company not found. Please run CompanySeeder first.');

            return;
        }

        $this->command->info('Seeding data for RAW Disposal...');

        // Create Vehicles for RAW Disposal
        $this->command->info('Creating vehicles...');
        $vehicles = [
            [
                'company_id' => $rawDisposal->id,
                'unit_number' => 'RAW-001',
                'make' => 'Mack',
                'model' => 'TerraPro',
                'year' => 2022,
                'vin' => '1M2AG11C22M123456',
                'license_plate' => 'RAW001TX',
                'type' => 'truck',
                'status' => 'active',
                'fuel_capacity' => 25,
                'fuel_type' => 'diesel',
                'purchase_date' => Carbon::now()->subMonths(18),
                'odometer' => 45000,
                'odometer_date' => Carbon::now(),
                'registration_expiry' => Carbon::now()->addMonths(8),
            ],
            [
                'company_id' => $rawDisposal->id,
                'unit_number' => 'RAW-002',
                'make' => 'Peterbilt',
                'model' => '520',
                'year' => 2021,
                'vin' => '1XPWD40X1ED123457',
                'license_plate' => 'RAW002TX',
                'type' => 'truck',
                'status' => 'active',
                'fuel_capacity' => 30,
                'fuel_type' => 'diesel',
                'purchase_date' => Carbon::now()->subMonths(24),
                'odometer' => 62000,
                'odometer_date' => Carbon::now(),
                'registration_expiry' => Carbon::now()->addMonths(8),
            ],
            [
                'company_id' => $rawDisposal->id,
                'unit_number' => 'RAW-003',
                'make' => 'Freightliner',
                'model' => 'M2 106',
                'year' => 2023,
                'vin' => '1FVACWDT3HHGR1234',
                'license_plate' => 'RAW003TX',
                'type' => 'truck',
                'status' => 'active',
                'fuel_capacity' => 40,
                'fuel_type' => 'diesel',
                'purchase_date' => Carbon::now()->subMonths(6),
                'odometer' => 18000,
                'odometer_date' => Carbon::now(),
                'registration_expiry' => Carbon::now()->addMonths(8),
            ],
            [
                'company_id' => $rawDisposal->id,
                'unit_number' => 'RAW-004',
                'make' => 'International',
                'model' => 'WorkStar 7600',
                'year' => 2020,
                'vin' => '1HTMKALN8HH123458',
                'license_plate' => 'RAW004TX',
                'type' => 'truck',
                'status' => 'maintenance',
                'fuel_capacity' => 28,
                'fuel_type' => 'diesel',
                'purchase_date' => Carbon::now()->subMonths(36),
                'odometer' => 95000,
                'odometer_date' => Carbon::now(),
                'registration_expiry' => Carbon::now()->addMonths(8),
            ],
        ];

        foreach ($vehicles as $vehicleData) {
            Vehicle::firstOrCreate(
                ['unit_number' => $vehicleData['unit_number'], 'company_id' => $rawDisposal->id],
                $vehicleData
            );
        }

        // Create Drivers for RAW Disposal
        $this->command->info('Creating drivers...');
        $drivers = [
            [
                'first_name' => 'Mike',
                'last_name' => 'Johnson',
                'email' => 'mike.johnson@rawdisposal.com',
                'phone' => '512-555-0201',
                'license_number' => 'DL789012',
                'license_class' => 'CDL-A',
                'license_expiry_date' => Carbon::now()->addMonths(18),
                'medical_card_expiry' => Carbon::now()->addMonths(12),
                'hired_date' => Carbon::now()->subYears(3),
                'address' => '789 Oak St',
                'city' => 'Austin',
                'state' => 'TX',
                'zip' => '78703',
                'emergency_contact' => 'Sarah Johnson',
                'emergency_phone' => '512-555-0202',
                'status' => 'active',
                'company_id' => $rawDisposal->id,
            ],
            [
                'first_name' => 'Carlos',
                'last_name' => 'Martinez',
                'email' => 'carlos.martinez@rawdisposal.com',
                'phone' => '512-555-0203',
                'license_number' => 'DL345678',
                'license_class' => 'CDL-A',
                'license_expiry_date' => Carbon::now()->addMonths(24),
                'medical_card_expiry' => Carbon::now()->addMonths(18),
                'hired_date' => Carbon::now()->subYears(2),
                'address' => '456 Pine Ave',
                'city' => 'Austin',
                'state' => 'TX',
                'zip' => '78704',
                'emergency_contact' => 'Maria Martinez',
                'emergency_phone' => '512-555-0204',
                'status' => 'active',
                'company_id' => $rawDisposal->id,
            ],
            [
                'first_name' => 'David',
                'last_name' => 'Thompson',
                'email' => 'david.thompson@rawdisposal.com',
                'phone' => '512-555-0205',
                'license_number' => 'DL901234',
                'license_class' => 'CDL-B',
                'license_expiry_date' => Carbon::now()->addMonths(12),
                'medical_card_expiry' => Carbon::now()->addMonths(6),
                'hired_date' => Carbon::now()->subMonths(6),
                'address' => '321 Elm Blvd',
                'city' => 'Austin',
                'state' => 'TX',
                'zip' => '78705',
                'emergency_contact' => 'Lisa Thompson',
                'emergency_phone' => '512-555-0206',
                'status' => 'active',
                'company_id' => $rawDisposal->id,
            ],
            [
                'first_name' => 'Robert',
                'last_name' => 'Anderson',
                'email' => 'robert.anderson@rawdisposal.com',
                'phone' => '512-555-0207',
                'license_number' => 'DL567890',
                'license_class' => 'CDL-A',
                'license_expiry_date' => Carbon::now()->addMonths(15),
                'medical_card_expiry' => Carbon::now()->addMonths(9),
                'hired_date' => Carbon::now()->subYears(4),
                'address' => '654 Maple Dr',
                'city' => 'Austin',
                'state' => 'TX',
                'zip' => '78706',
                'emergency_contact' => 'Jennifer Anderson',
                'emergency_phone' => '512-555-0208',
                'status' => 'active',
                'company_id' => $rawDisposal->id,
            ],
        ];

        $driverModels = [];
        foreach ($drivers as $driverData) {
            // Check if user already exists
            $user = User::firstOrCreate(
                ['email' => $driverData['email']],
                [
                    'name' => $driverData['first_name'].' '.$driverData['last_name'],
                    'password' => Hash::make('password'),
                ]
            );

            $driverData['user_id'] = $user->id;

            // Check if driver already exists
            $driver = Driver::firstOrCreate(
                ['email' => $driverData['email'], 'company_id' => $rawDisposal->id],
                $driverData
            );
            $driverModels[] = $driver;
        }

        // Create Disposal Sites
        $this->command->info('Creating disposal sites...');
        $sites = [
            [
                'company_id' => $rawDisposal->id,
                'name' => 'Travis County Landfill',
                'location' => '9500 FM 973 N, Austin, TX 78724',
                'parish' => 'Travis County',
                'total_capacity' => 5000000,
                'current_capacity' => 2500000,
                'daily_intake_average' => 150,
                'status' => 'active',
                'manager_name' => 'John Miller',
                'contact_phone' => '512-555-0301',
                'operating_hours' => '6:00 AM - 6:00 PM',
                'environmental_permit' => 'MSW-2024-001',
                'last_inspection_date' => Carbon::now()->subMonths(3),
            ],
            [
                'company_id' => $rawDisposal->id,
                'name' => 'Austin Recycling Center',
                'location' => '2514 Business Center Dr, Austin, TX 78744',
                'parish' => 'Travis County',
                'total_capacity' => 1000000,
                'current_capacity' => 450000,
                'daily_intake_average' => 75,
                'status' => 'active',
                'manager_name' => 'Sarah Green',
                'contact_phone' => '512-555-0302',
                'operating_hours' => '7:00 AM - 5:00 PM',
                'environmental_permit' => 'RC-2024-002',
                'last_inspection_date' => Carbon::now()->subMonths(2),
            ],
            [
                'company_id' => $rawDisposal->id,
                'name' => 'Hazardous Waste Facility',
                'location' => '8901 Industrial Blvd, Austin, TX 78758',
                'parish' => 'Travis County',
                'total_capacity' => 500000,
                'current_capacity' => 125000,
                'daily_intake_average' => 25,
                'status' => 'active',
                'manager_name' => 'Dr. Robert Hayes',
                'contact_phone' => '512-555-0303',
                'operating_hours' => '8:00 AM - 4:00 PM',
                'environmental_permit' => 'HW-2024-003',
                'last_inspection_date' => Carbon::now()->subMonths(1),
            ],
        ];

        foreach ($sites as $siteData) {
            try {
                DisposalSite::firstOrCreate(
                    ['name' => $siteData['name'], 'company_id' => $rawDisposal->id],
                    $siteData
                );
            } catch (\Exception $e) {
                $this->command->warn('Could not create disposal site: '.$siteData['name']);
            }
        }

        // Create Customers for RAW Disposal
        $this->command->info('Creating customers...');
        $customers = [
            [
                'company_id' => $rawDisposal->id,
                'organization' => 'Austin Shopping Center',
                'first_name' => 'Jennifer',
                'last_name' => 'Brown',
                'emails' => json_encode(['manager@austinshoppingcenter.com', 'jbrown@austinshoppingcenter.com']),
                'phone' => '512-555-0401',
                'address' => '1000 Commerce St',
                'city' => 'Austin',
                'state' => 'TX',
                'zip' => '78701',
                'business_type' => 'commercial',
                'customer_since' => Carbon::now()->subYears(2),
                'portal_access' => false,
            ],
            [
                'company_id' => $rawDisposal->id,
                'organization' => 'Westlake Office Complex',
                'first_name' => 'Michael',
                'last_name' => 'Chen',
                'emails' => json_encode(['facilities@westlakeoffice.com', 'mchen@westlakeoffice.com']),
                'phone' => '512-555-0403',
                'address' => '3600 Executive Center Dr',
                'city' => 'Austin',
                'state' => 'TX',
                'zip' => '78746',
                'business_type' => 'commercial',
                'customer_since' => Carbon::now()->subYears(1),
                'portal_access' => false,
            ],
            [
                'company_id' => $rawDisposal->id,
                'organization' => 'Downtown Restaurant Group',
                'first_name' => 'Antonio',
                'last_name' => 'Rodriguez',
                'emails' => json_encode(['operations@downtownrestaurants.com', 'arodriguez@downtownrestaurants.com']),
                'phone' => '512-555-0405',
                'address' => '500 6th St',
                'city' => 'Austin',
                'state' => 'TX',
                'zip' => '78701',
                'business_type' => 'restaurant',
                'customer_since' => Carbon::now()->subMonths(18),
                'portal_access' => false,
            ],
            [
                'company_id' => $rawDisposal->id,
                'organization' => 'St. David\'s Medical Center',
                'first_name' => 'Patricia',
                'last_name' => 'Williams',
                'emails' => json_encode(['facilities@stdavids.com', 'pwilliams@stdavids.com']),
                'phone' => '512-555-0407',
                'address' => '919 E 32nd St',
                'city' => 'Austin',
                'state' => 'TX',
                'zip' => '78705',
                'business_type' => 'healthcare',
                'tax_exemption_details' => '74-1234567',
                'tax_exempt_reason' => 'Non-profit healthcare',
                'customer_since' => Carbon::now()->subYears(3),
                'portal_access' => false,
            ],
            [
                'company_id' => $rawDisposal->id,
                'organization' => 'Cedar Park Construction',
                'first_name' => 'Bob',
                'last_name' => 'Miller',
                'emails' => json_encode(['office@cedarparkconst.com', 'bmiller@cedarparkconst.com']),
                'phone' => '512-555-0409',
                'address' => '2001 Construction Way',
                'secondary_address' => 'Various Job Sites',
                'city' => 'Cedar Park',
                'state' => 'TX',
                'zip' => '78613',
                'business_type' => 'construction',
                'customer_since' => Carbon::now()->subMonths(6),
                'portal_access' => false,
            ],
        ];

        foreach ($customers as $customerData) {
            Customer::firstOrCreate(
                ['organization' => $customerData['organization'], 'company_id' => $rawDisposal->id],
                $customerData
            );
        }

        // Create Waste Routes
        $this->command->info('Creating waste routes...');
        $routes = [
            [
                'company_id' => $rawDisposal->id,
                'route_number' => 'WR-001',
                'name' => 'Downtown Commercial Route',
                'description' => 'Commercial waste collection in downtown Austin',
                'driver_id' => $driverModels[0]->id,
                'vehicle_id' => $vehicles[0]['id'] ?? 1,
                'start_time' => '06:00:00',
                'end_time' => '14:00:00',
                'days_of_week' => json_encode(['monday', 'wednesday', 'friday']),
                'estimated_duration' => 480,
                'total_stops' => 25,
                'total_distance' => 45.5,
                'status' => 'active',
            ],
            [
                'company_id' => $rawDisposal->id,
                'route_number' => 'WR-002',
                'name' => 'West Austin Residential',
                'description' => 'Residential waste collection in West Austin',
                'driver_id' => $driverModels[1]->id,
                'vehicle_id' => $vehicles[1]['id'] ?? 2,
                'start_time' => '07:00:00',
                'end_time' => '15:00:00',
                'days_of_week' => json_encode(['tuesday', 'thursday']),
                'estimated_duration' => 480,
                'total_stops' => 150,
                'total_distance' => 65.2,
                'status' => 'active',
            ],
            [
                'company_id' => $rawDisposal->id,
                'route_number' => 'WR-003',
                'name' => 'Medical Waste Route',
                'description' => 'Medical facility waste collection',
                'driver_id' => $driverModels[2]->id,
                'vehicle_id' => $vehicles[2]['id'] ?? 3,
                'start_time' => '05:00:00',
                'end_time' => '13:00:00',
                'days_of_week' => json_encode(['monday', 'tuesday', 'thursday', 'friday']),
                'estimated_duration' => 480,
                'total_stops' => 12,
                'total_distance' => 38.7,
                'status' => 'active',
            ],
        ];

        foreach ($routes as $routeData) {
            WasteRoute::firstOrCreate(
                ['route_number' => $routeData['route_number'], 'company_id' => $rawDisposal->id],
                $routeData
            );
        }

        // Create Driver Assignments
        $this->command->info('Creating driver assignments...');
        for ($i = 0; $i < min(count($driverModels), count($vehicles)); $i++) {
            DriverAssignment::create([
                'company_id' => $rawDisposal->id,
                'driver_id' => $driverModels[$i]->id,
                'vehicle_id' => Vehicle::where('company_id', $rawDisposal->id)
                    ->skip($i)
                    ->first()
                    ->id,
                'assigned_date' => Carbon::now()->subDays(rand(1, 30)),
                'start_date' => Carbon::now()->subDays(rand(1, 30)),
                'status' => 'active',
                'notes' => 'Regular assignment',
            ]);
        }

        // Create Equipment for RAW Disposal
        $this->command->info('Creating equipment...');
        $equipment = [
            [
                'company_id' => $rawDisposal->id,
                'equipment_number' => 'EQ-RAW-001',
                'name' => '20 Yard Roll-Off Container',
                'type' => 'container',
                'manufacturer' => 'Wastequip',
                'model' => 'RO-20',
                'serial_number' => 'WQ20234567',
                'purchase_date' => Carbon::now()->subYears(2),
                'purchase_price' => 3500,
                'status' => 'available',
                'location' => 'Depot',
            ],
            [
                'company_id' => $rawDisposal->id,
                'equipment_number' => 'EQ-RAW-002',
                'name' => '30 Yard Roll-Off Container',
                'type' => 'container',
                'manufacturer' => 'Wastequip',
                'model' => 'RO-30',
                'serial_number' => 'WQ30234568',
                'purchase_date' => Carbon::now()->subYears(2),
                'purchase_price' => 4500,
                'status' => 'in_use',
                'location' => 'Customer Site - Austin Shopping Center',
            ],
            [
                'company_id' => $rawDisposal->id,
                'equipment_number' => 'EQ-RAW-003',
                'name' => '40 Yard Roll-Off Container',
                'type' => 'container',
                'manufacturer' => 'Wastequip',
                'model' => 'RO-40',
                'serial_number' => 'WQ40234569',
                'purchase_date' => Carbon::now()->subYears(1),
                'purchase_price' => 5500,
                'status' => 'in_use',
                'location' => 'Customer Site - Cedar Park Construction',
            ],
            [
                'company_id' => $rawDisposal->id,
                'equipment_number' => 'EQ-RAW-004',
                'name' => 'Medical Waste Container',
                'type' => 'specialty',
                'manufacturer' => 'Daniels Health',
                'model' => 'Sharpsmart 64',
                'serial_number' => 'DH64234570',
                'purchase_date' => Carbon::now()->subMonths(6),
                'purchase_price' => 1200,
                'status' => 'in_use',
                'location' => 'St. David\'s Medical Center',
            ],
        ];

        foreach ($equipment as $equipmentData) {
            Equipment::firstOrCreate(
                ['equipment_number' => $equipmentData['equipment_number'], 'company_id' => $rawDisposal->id],
                $equipmentData
            );
        }

        // Create Service Areas
        $this->command->info('Creating service areas...');
        $serviceAreas = [
            [
                'company_id' => $rawDisposal->id,
                'name' => 'Downtown Austin',
                'description' => 'Central business district and surrounding areas',
                'zip_codes' => json_encode(['78701', '78702', '78703']),
                'coverage_type' => 'full',
                'service_days' => json_encode(['monday', 'wednesday', 'friday']),
                'status' => 'active',
            ],
            [
                'company_id' => $rawDisposal->id,
                'name' => 'West Austin',
                'description' => 'Westlake, Rollingwood, and surrounding areas',
                'zip_codes' => json_encode(['78746', '78733', '78738']),
                'coverage_type' => 'full',
                'service_days' => json_encode(['tuesday', 'thursday']),
                'status' => 'active',
            ],
            [
                'company_id' => $rawDisposal->id,
                'name' => 'North Austin',
                'description' => 'Cedar Park, Round Rock adjacent areas',
                'zip_codes' => json_encode(['78758', '78759', '78750']),
                'coverage_type' => 'partial',
                'service_days' => json_encode(['monday', 'thursday']),
                'status' => 'active',
            ],
        ];

        foreach ($serviceAreas as $areaData) {
            ServiceArea::firstOrCreate(
                ['name' => $areaData['name'], 'company_id' => $rawDisposal->id],
                $areaData
            );
        }

        // Create Maintenance Logs
        $this->command->info('Creating maintenance logs...');
        $maintenanceLogs = [
            [
                'company_id' => $rawDisposal->id,
                'vehicle_id' => Vehicle::where('company_id', $rawDisposal->id)->first()->id,
                'type' => 'scheduled',
                'description' => 'Regular 90-day maintenance',
                'service_date' => Carbon::now()->subDays(30),
                'performed_by' => 'Austin Truck Service',
                'cost' => 850.00,
                'mileage_at_service' => 44000,
                'next_service_date' => Carbon::now()->addDays(60),
                'next_service_mileage' => 54000,
                'parts_replaced' => json_encode(['Oil filter', 'Air filter', 'Fuel filter']),
                'notes' => 'All systems checked and operating normally',
            ],
            [
                'company_id' => $rawDisposal->id,
                'vehicle_id' => Vehicle::where('company_id', $rawDisposal->id)->skip(1)->first()->id,
                'type' => 'repair',
                'description' => 'Hydraulic system repair',
                'service_date' => Carbon::now()->subDays(45),
                'performed_by' => 'Austin Truck Service',
                'cost' => 2500.00,
                'mileage_at_service' => 61000,
                'parts_replaced' => json_encode(['Hydraulic pump', 'Hydraulic lines', 'Hydraulic fluid']),
                'notes' => 'Hydraulic system completely overhauled',
            ],
        ];

        foreach ($maintenanceLogs as $logData) {
            MaintenanceLog::create($logData);
        }

        // Create Fuel Logs
        $this->command->info('Creating fuel logs...');
        $vehicles = Vehicle::where('company_id', $rawDisposal->id)->get();

        foreach ($vehicles as $vehicle) {
            for ($i = 0; $i < 5; $i++) {
                FuelLog::create([
                    'company_id' => $rawDisposal->id,
                    'vehicle_id' => $vehicle->id,
                    'driver_id' => $driverModels[array_rand($driverModels)]->id,
                    'date' => Carbon::now()->subDays(rand(1, 30)),
                    'odometer_reading' => $vehicle->odometer + ($i * 500),
                    'gallons' => rand(30, 60),
                    'price_per_gallon' => rand(350, 450) / 100,
                    'total_cost' => rand(105, 270),
                    'fuel_type' => 'diesel',
                    'station' => 'Shell Station #'.rand(100, 999),
                    'location' => 'Austin, TX',
                    'notes' => 'Regular refueling',
                ]);
            }
        }

        // Create Work Orders
        $this->command->info('Creating work orders...');
        $customers = Customer::where('company_id', $rawDisposal->id)->get();

        foreach ($customers as $customer) {
            WorkOrder::create([
                'company_id' => $rawDisposal->id,
                'work_order_number' => 'WO-RAW-'.str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT),
                'customer_id' => $customer->id,
                'driver_id' => $driverModels[array_rand($driverModels)]->id,
                'vehicle_id' => $vehicles->random()->id,
                'type' => 'waste_collection',
                'status' => collect(['pending', 'in_progress', 'completed'])->random(),
                'priority' => collect(['low', 'medium', 'high'])->random(),
                'scheduled_date' => Carbon::now()->addDays(rand(1, 14)),
                'scheduled_time' => sprintf('%02d:00:00', rand(6, 16)),
                'description' => 'Regular waste collection service',
                'service_address' => $customer->address,
                'service_city' => $customer->city,
                'service_state' => $customer->state,
                'service_zip' => $customer->zip,
                'contact_name' => $customer->full_name,
                'contact_phone' => $customer->phone,
                'estimated_duration' => rand(15, 60),
                'notes' => 'Standard collection procedure',
            ]);
        }

        // Create Invoices
        $this->command->info('Creating invoices and payments...');
        foreach ($customers as $customer) {
            $invoice = Invoice::create([
                'company_id' => $rawDisposal->id,
                'customer_id' => $customer->id,
                'invoice_number' => 'INV-RAW-'.Carbon::now()->format('Y').'-'.str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT),
                'invoice_date' => Carbon::now()->subDays(rand(1, 45)),
                'due_date' => Carbon::now()->addDays(rand(15, 45)),
                'subtotal' => rand(500, 5000),
                'tax_rate' => 8.25,
                'tax_amount' => 0,
                'total_amount' => 0,
                'balance_due' => 0,
                'status' => collect(['draft', 'sent', 'paid', 'overdue'])->random(),
                'payment_terms' => 'net30',
                'notes' => 'Monthly waste disposal services',
            ]);

            $invoice->tax_amount = $invoice->subtotal * 0.0825;
            $invoice->total_amount = $invoice->subtotal + $invoice->tax_amount;
            $invoice->balance_due = $invoice->total_amount;
            $invoice->save();

            // Create payment for some invoices
            if ($invoice->status === 'paid') {
                Payment::create([
                    'company_id' => $rawDisposal->id,
                    'invoice_id' => $invoice->id,
                    'customer_id' => $customer->id,
                    'payment_date' => $invoice->invoice_date->addDays(rand(5, 20)),
                    'amount' => $invoice->total_amount,
                    'payment_method' => collect(['check', 'credit_card', 'ach', 'cash'])->random(),
                    'reference_number' => 'PAY-'.str_pad(rand(10000, 99999), 5, '0', STR_PAD_LEFT),
                    'notes' => 'Payment received',
                ]);

                $invoice->balance_due = 0;
                $invoice->save();
            }
        }

        // Create Waste Collections
        $this->command->info('Creating waste collection records...');
        $routes = WasteRoute::where('company_id', $rawDisposal->id)->get();
        $sites = DisposalSite::where('company_id', $rawDisposal->id)->get();

        foreach ($routes as $route) {
            for ($i = 0; $i < 10; $i++) {
                WasteCollection::create([
                    'company_id' => $rawDisposal->id,
                    'route_id' => $route->id,
                    'driver_id' => $route->driver_id,
                    'vehicle_id' => $route->vehicle_id,
                    'disposal_site_id' => $sites->random()->id,
                    'collection_date' => Carbon::now()->subDays(rand(1, 30)),
                    'start_time' => Carbon::parse($route->start_time),
                    'end_time' => Carbon::parse($route->end_time),
                    'total_weight' => rand(5000, 15000),
                    'total_volume' => rand(100, 500),
                    'number_of_stops' => $route->total_stops,
                    'mileage' => $route->total_distance,
                    'fuel_used' => rand(10, 30),
                    'notes' => 'Collection completed successfully',
                    'status' => 'completed',
                ]);
            }
        }

        $this->command->info('RAW Disposal data seeding completed successfully!');
    }
}
