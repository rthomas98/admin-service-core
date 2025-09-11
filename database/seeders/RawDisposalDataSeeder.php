<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\Vehicle;
use App\Models\Driver;
use App\Models\Customer;
use App\Models\DisposalSite;
use App\Models\WasteRoute;
use App\Models\WasteCollection;
use App\Models\ServiceArea;
use App\Models\ServiceSchedule;
use App\Models\Equipment;
use App\Models\MaintenanceLog;
use App\Models\FuelLog;
use App\Models\DriverAssignment;
use App\Models\WorkOrder;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class RawDisposalDataSeeder extends Seeder
{
    public function run(): void
    {
        // Get RAW Disposal company
        $rawDisposal = Company::where('name', 'RAW Disposal')->first();
        
        if (!$rawDisposal) {
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
            Vehicle::create($vehicleData);
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
                'license_state' => 'TX',
                'license_expiry_date' => Carbon::now()->addMonths(18),
                'medical_card_expiry' => Carbon::now()->addMonths(12),
                'hire_date' => Carbon::now()->subYears(3),
                'birth_date' => Carbon::now()->subYears(35),
                'address' => '789 Oak St',
                'city' => 'Austin',
                'state' => 'TX',
                'zip' => '78703',
                'emergency_contact_name' => 'Sarah Johnson',
                'emergency_contact_phone' => '512-555-0202',
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
                'license_state' => 'TX',
                'license_expiry_date' => Carbon::now()->addMonths(24),
                'medical_card_expiry' => Carbon::now()->addMonths(18),
                'hire_date' => Carbon::now()->subYears(2),
                'birth_date' => Carbon::now()->subYears(28),
                'address' => '456 Pine Ave',
                'city' => 'Austin',
                'state' => 'TX',
                'zip' => '78704',
                'emergency_contact_name' => 'Maria Martinez',
                'emergency_contact_phone' => '512-555-0204',
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
                'license_state' => 'TX',
                'license_expiry_date' => Carbon::now()->addMonths(12),
                'medical_card_expiry' => Carbon::now()->addMonths(6),
                'hire_date' => Carbon::now()->subMonths(6),
                'birth_date' => Carbon::now()->subYears(42),
                'address' => '321 Elm Blvd',
                'city' => 'Austin',
                'state' => 'TX',
                'zip' => '78705',
                'emergency_contact_name' => 'Lisa Thompson',
                'emergency_contact_phone' => '512-555-0206',
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
                'license_state' => 'TX',
                'license_expiry_date' => Carbon::now()->addMonths(15),
                'medical_card_expiry' => Carbon::now()->addMonths(9),
                'hire_date' => Carbon::now()->subYears(4),
                'birth_date' => Carbon::now()->subYears(45),
                'address' => '654 Maple Dr',
                'city' => 'Austin',
                'state' => 'TX',
                'zip' => '78706',
                'emergency_contact_name' => 'Jennifer Anderson',
                'emergency_contact_phone' => '512-555-0208',
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
                    'name' => $driverData['first_name'] . ' ' . $driverData['last_name'],
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
                'address' => '9500 FM 973 N',
                'city' => 'Austin',
                'state' => 'TX',
                'zip' => '78724',
                'phone' => '512-555-0301',
                'contact_name' => 'John Miller',
                'type' => 'landfill',
                'capacity' => 5000000,
                'current_usage' => 2500000,
                'accepts_hazardous' => false,
                'accepts_recyclables' => true,
                'operating_hours' => '6:00 AM - 6:00 PM',
                'permit_number' => 'MSW-2024-001',
                'permit_expiry' => Carbon::now()->addYears(2),
                'latitude' => 30.3345,
                'longitude' => -97.6234,
                'status' => 'active',
            ],
            [
                'company_id' => $rawDisposal->id,
                'name' => 'Austin Recycling Center',
                'address' => '2514 Business Center Dr',
                'city' => 'Austin',
                'state' => 'TX',
                'zip' => '78744',
                'phone' => '512-555-0302',
                'contact_name' => 'Sarah Green',
                'type' => 'recycling_center',
                'capacity' => 1000000,
                'current_usage' => 450000,
                'accepts_hazardous' => false,
                'accepts_recyclables' => true,
                'operating_hours' => '7:00 AM - 5:00 PM',
                'permit_number' => 'RC-2024-002',
                'permit_expiry' => Carbon::now()->addYears(3),
                'latitude' => 30.2145,
                'longitude' => -97.7645,
                'status' => 'active',
            ],
            [
                'company_id' => $rawDisposal->id,
                'name' => 'Hazardous Waste Facility',
                'address' => '8901 Industrial Blvd',
                'city' => 'Austin',
                'state' => 'TX',
                'zip' => '78758',
                'phone' => '512-555-0303',
                'contact_name' => 'Dr. Robert Hayes',
                'type' => 'hazardous_waste',
                'capacity' => 500000,
                'current_usage' => 125000,
                'accepts_hazardous' => true,
                'accepts_recyclables' => false,
                'operating_hours' => '8:00 AM - 4:00 PM',
                'permit_number' => 'HW-2024-003',
                'permit_expiry' => Carbon::now()->addMonths(18),
                'latitude' => 30.3856,
                'longitude' => -97.7234,
                'status' => 'active',
            ],
        ];

        foreach ($sites as $siteData) {
            DisposalSite::create($siteData);
        }

        // Create Customers for RAW Disposal
        $this->command->info('Creating customers...');
        $customers = [
            [
                'company_id' => $rawDisposal->id,
                'name' => 'Austin Shopping Center',
                'type' => 'commercial',
                'email' => 'manager@austinshoppingcenter.com',
                'phone' => '512-555-0401',
                'billing_address' => '1000 Commerce St',
                'billing_city' => 'Austin',
                'billing_state' => 'TX',
                'billing_zip' => '78701',
                'service_address' => '1000 Commerce St',
                'service_city' => 'Austin',
                'service_state' => 'TX',
                'service_zip' => '78701',
                'contact_name' => 'Jennifer Brown',
                'contact_email' => 'jbrown@austinshoppingcenter.com',
                'contact_phone' => '512-555-0402',
                'credit_limit' => 10000,
                'payment_terms' => 'net30',
                'tax_exempt' => false,
                'status' => 'active',
            ],
            [
                'company_id' => $rawDisposal->id,
                'name' => 'Westlake Office Complex',
                'type' => 'commercial',
                'email' => 'facilities@westlakeoffice.com',
                'phone' => '512-555-0403',
                'billing_address' => '3600 Executive Center Dr',
                'billing_city' => 'Austin',
                'billing_state' => 'TX',
                'billing_zip' => '78746',
                'service_address' => '3600 Executive Center Dr',
                'service_city' => 'Austin',
                'service_state' => 'TX',
                'service_zip' => '78746',
                'contact_name' => 'Michael Chen',
                'contact_email' => 'mchen@westlakeoffice.com',
                'contact_phone' => '512-555-0404',
                'credit_limit' => 15000,
                'payment_terms' => 'net30',
                'tax_exempt' => false,
                'status' => 'active',
            ],
            [
                'company_id' => $rawDisposal->id,
                'name' => 'Downtown Restaurant Group',
                'type' => 'commercial',
                'email' => 'operations@downtownrestaurants.com',
                'phone' => '512-555-0405',
                'billing_address' => '500 6th St',
                'billing_city' => 'Austin',
                'billing_state' => 'TX',
                'billing_zip' => '78701',
                'service_address' => '500 6th St',
                'service_city' => 'Austin',
                'service_state' => 'TX',
                'service_zip' => '78701',
                'contact_name' => 'Antonio Rodriguez',
                'contact_email' => 'arodriguez@downtownrestaurants.com',
                'contact_phone' => '512-555-0406',
                'credit_limit' => 8000,
                'payment_terms' => 'net15',
                'tax_exempt' => false,
                'status' => 'active',
            ],
            [
                'company_id' => $rawDisposal->id,
                'name' => 'St. David\'s Medical Center',
                'type' => 'healthcare',
                'email' => 'facilities@stdavids.com',
                'phone' => '512-555-0407',
                'billing_address' => '919 E 32nd St',
                'billing_city' => 'Austin',
                'billing_state' => 'TX',
                'billing_zip' => '78705',
                'service_address' => '919 E 32nd St',
                'service_city' => 'Austin',
                'service_state' => 'TX',
                'service_zip' => '78705',
                'contact_name' => 'Dr. Patricia Williams',
                'contact_email' => 'pwilliams@stdavids.com',
                'contact_phone' => '512-555-0408',
                'credit_limit' => 25000,
                'payment_terms' => 'net45',
                'tax_exempt' => true,
                'tax_id' => '74-1234567',
                'status' => 'active',
            ],
            [
                'company_id' => $rawDisposal->id,
                'name' => 'Cedar Park Construction',
                'type' => 'construction',
                'email' => 'office@cedarparkconst.com',
                'phone' => '512-555-0409',
                'billing_address' => '2001 Construction Way',
                'billing_city' => 'Cedar Park',
                'billing_state' => 'TX',
                'billing_zip' => '78613',
                'service_address' => 'Various Job Sites',
                'service_city' => 'Cedar Park',
                'service_state' => 'TX',
                'service_zip' => '78613',
                'contact_name' => 'Bob Miller',
                'contact_email' => 'bmiller@cedarparkconst.com',
                'contact_phone' => '512-555-0410',
                'credit_limit' => 20000,
                'payment_terms' => 'net30',
                'tax_exempt' => false,
                'status' => 'active',
            ],
        ];

        foreach ($customers as $customerData) {
            Customer::create($customerData);
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
            WasteRoute::create($routeData);
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
            Equipment::create($equipmentData);
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
            ServiceArea::create($areaData);
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
                    'station' => 'Shell Station #' . rand(100, 999),
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
                'work_order_number' => 'WO-RAW-' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT),
                'customer_id' => $customer->id,
                'driver_id' => $driverModels[array_rand($driverModels)]->id,
                'vehicle_id' => $vehicles->random()->id,
                'type' => 'waste_collection',
                'status' => collect(['pending', 'in_progress', 'completed'])->random(),
                'priority' => collect(['low', 'medium', 'high'])->random(),
                'scheduled_date' => Carbon::now()->addDays(rand(1, 14)),
                'scheduled_time' => sprintf('%02d:00:00', rand(6, 16)),
                'description' => 'Regular waste collection service',
                'service_address' => $customer->service_address,
                'service_city' => $customer->service_city,
                'service_state' => $customer->service_state,
                'service_zip' => $customer->service_zip,
                'contact_name' => $customer->contact_name,
                'contact_phone' => $customer->contact_phone,
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
                'invoice_number' => 'INV-RAW-' . Carbon::now()->format('Y') . '-' . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT),
                'invoice_date' => Carbon::now()->subDays(rand(1, 45)),
                'due_date' => Carbon::now()->addDays(rand(15, 45)),
                'subtotal' => rand(500, 5000),
                'tax_rate' => 8.25,
                'tax_amount' => 0,
                'total_amount' => 0,
                'balance_due' => 0,
                'status' => collect(['draft', 'sent', 'paid', 'overdue'])->random(),
                'payment_terms' => $customer->payment_terms,
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
                    'reference_number' => 'PAY-' . str_pad(rand(10000, 99999), 5, '0', STR_PAD_LEFT),
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