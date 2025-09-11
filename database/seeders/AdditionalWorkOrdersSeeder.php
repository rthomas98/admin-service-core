<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Driver;
use App\Models\WorkOrder;
use App\Models\Customer;
use App\Models\Vehicle;
use App\Enums\WorkOrderStatus;
use App\Enums\WorkOrderAction;
use App\Enums\TimePeriod;
use Carbon\Carbon;

class AdditionalWorkOrdersSeeder extends Seeder
{
    public function run(): void
    {
        // Get John Smith's driver record
        $user = User::where('email', 'john.smith@livtransport.com')->first();
        if (!$user) {
            $this->command->error('John Smith user not found. Run FieldAppDriverSeeder first.');
            return;
        }
        
        $driver = Driver::where('user_id', $user->id)->first();
        if (!$driver) {
            $this->command->error('John Smith driver record not found.');
            return;
        }
        
        $companyId = $driver->company_id;
        $vehicle = Vehicle::where('company_id', $companyId)->first();
        
        // Get existing customers
        $customers = Customer::where('company_id', $companyId)->get();
        if ($customers->count() < 3) {
            $this->command->error('Not enough customers found. Run FieldAppDataSeeder first.');
            return;
        }
        
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        $tomorrow = Carbon::tomorrow();
        
        // Generate unique ticket numbers with timestamp
        $timestamp = now()->timestamp;
        
        // Yesterday's completed work orders
        WorkOrder::firstOrCreate(
            [
                'ticket_number' => 'WO-' . $yesterday->format('Ymd') . '-101',
            ],
            [
                'company_id' => $companyId,
                'driver_id' => $driver->id,
                'customer_id' => $customers[0]->id,
                'po_number' => 'PO-2024-2001',
                'service_date' => $yesterday,
                'time_on_site' => Carbon::parse('07:00'),
                'time_off_site' => Carbon::parse('08:30'),
                'time_on_site_period' => TimePeriod::AM,
                'time_off_site_period' => TimePeriod::AM,
                'status' => WorkOrderStatus::COMPLETED,
                'action' => WorkOrderAction::PICKUP,
                'customer_name' => $customers[0]->organization,
                'address' => '500 West Madison Street',
                'city' => 'Chicago',
                'state' => 'IL',
                'zip' => '60661',
                'container_size' => '30 yard',
                'waste_type' => 'Mixed Commercial Waste',
                'service_description' => 'Picked up full 30 yard container from loading dock',
                'completed_at' => $yesterday->copy()->setTime(8, 30),
                'truck_number' => $vehicle->unit_number ?? 'LIV-001',
            ]
        );
        
        WorkOrder::firstOrCreate(
            [
                'ticket_number' => 'WO-' . $yesterday->format('Ymd') . '-102',
            ],
            [
                'company_id' => $companyId,
                'driver_id' => $driver->id,
                'customer_id' => $customers[1]->id,
                'po_number' => 'PO-2024-2002',
                'service_date' => $yesterday,
                'time_on_site' => Carbon::parse('09:00'),
                'time_off_site' => Carbon::parse('10:00'),
                'time_on_site_period' => TimePeriod::AM,
                'time_off_site_period' => TimePeriod::AM,
                'status' => WorkOrderStatus::COMPLETED,
                'action' => WorkOrderAction::DELIVERY,
                'customer_name' => $customers[1]->organization,
                'address' => $customers[1]->address,
                'city' => $customers[1]->city,
                'state' => $customers[1]->state,
                'zip' => $customers[1]->zip,
                'container_size' => '20 yard',
                'waste_type' => 'Construction Debris',
                'service_description' => 'Delivered empty 20 yard container for renovation project',
                'completed_at' => $yesterday->copy()->setTime(10, 0),
                'truck_number' => $vehicle->unit_number ?? 'LIV-001',
            ]
        );
        
        // Today's additional work orders
        WorkOrder::firstOrCreate(
            [
                'ticket_number' => 'WO-' . $today->format('Ymd') . '-201',
            ],
            [
                'company_id' => $companyId,
                'driver_id' => $driver->id,
                'customer_id' => $customers[1]->id,
                'po_number' => 'PO-2024-2003',
                'service_date' => $today,
                'time_on_site' => Carbon::parse('11:00'),
                'time_on_site_period' => TimePeriod::AM,
                'status' => WorkOrderStatus::IN_PROGRESS,
                'action' => WorkOrderAction::SERVICE,
                'customer_name' => $customers[1]->organization,
                'address' => '789 North Avenue',
                'city' => 'Elmhurst',
                'state' => 'IL',
                'zip' => '60126',
                'container_size' => '40 yard',
                'waste_type' => 'Recyclables',
                'service_description' => 'Service check and maintenance on container',
                'truck_number' => $vehicle->unit_number ?? 'LIV-001',
            ]
        );
        
        WorkOrder::firstOrCreate(
            [
                'ticket_number' => 'WO-' . $today->format('Ymd') . '-202',
            ],
            [
                'company_id' => $companyId,
                'driver_id' => $driver->id,
                'customer_id' => $customers[2]->id,
                'po_number' => 'PO-2024-2004',
                'service_date' => $today,
                'time_on_site' => Carbon::parse('14:00'),
                'time_on_site_period' => TimePeriod::PM,
                'status' => WorkOrderStatus::DRAFT,
                'action' => WorkOrderAction::PICKUP,
                'customer_name' => $customers[2]->organization,
                'address' => $customers[2]->address,
                'city' => $customers[2]->city,
                'state' => $customers[2]->state,
                'zip' => $customers[2]->zip,
                'container_size' => '30 yard',
                'waste_type' => 'Scrap Metal',
                'service_description' => 'Pickup 30 yard container with scrap metal for recycling',
                'truck_number' => $vehicle->unit_number ?? 'LIV-001',
            ]
        );
        
        WorkOrder::firstOrCreate(
            [
                'ticket_number' => 'WO-' . $today->format('Ymd') . '-203',
            ],
            [
                'company_id' => $companyId,
                'driver_id' => $driver->id,
                'customer_id' => $customers[0]->id,
                'po_number' => 'PO-2024-2005',
                'service_date' => $today,
                'time_on_site' => Carbon::parse('16:00'),
                'time_on_site_period' => TimePeriod::PM,
                'status' => WorkOrderStatus::DRAFT,
                'action' => WorkOrderAction::DELIVERY,
                'customer_name' => $customers[0]->organization,
                'address' => '1234 Commerce Drive',
                'city' => 'Rosemont',
                'state' => 'IL',
                'zip' => '60018',
                'container_size' => '10 yard',
                'waste_type' => 'Office Waste',
                'service_description' => 'Deliver 10 yard container for office cleanout',
                'truck_number' => $vehicle->unit_number ?? 'LIV-001',
            ]
        );
        
        WorkOrder::firstOrCreate(
            [
                'ticket_number' => 'WO-' . $today->format('Ymd') . '-204',
            ],
            [
                'company_id' => $companyId,
                'driver_id' => $driver->id,
                'customer_id' => $customers[1]->id,
                'po_number' => 'PO-2024-2006',
                'service_date' => $today,
                'time_on_site' => Carbon::parse('17:30'),
                'time_on_site_period' => TimePeriod::PM,
                'status' => WorkOrderStatus::DRAFT,
                'action' => WorkOrderAction::EMERGENCY,
                'customer_name' => $customers[1]->organization,
                'address' => '567 Emergency Lane',
                'city' => 'Des Plaines',
                'state' => 'IL',
                'zip' => '60016',
                'container_size' => '20 yard',
                'waste_type' => 'Hazardous Material',
                'service_description' => 'Emergency cleanup - hazardous spill containment',
                'truck_number' => $vehicle->unit_number ?? 'LIV-001',
            ]
        );
        
        // Tomorrow's scheduled work orders
        WorkOrder::firstOrCreate(
            [
                'ticket_number' => 'WO-' . $tomorrow->format('Ymd') . '-301',
            ],
            [
                'company_id' => $companyId,
                'driver_id' => $driver->id,
                'customer_id' => $customers[2]->id,
                'po_number' => 'PO-2024-2007',
                'service_date' => $tomorrow,
                'time_on_site' => Carbon::parse('08:00'),
                'time_on_site_period' => TimePeriod::AM,
                'status' => WorkOrderStatus::DRAFT,
                'action' => WorkOrderAction::SERVICE,
                'customer_name' => $customers[2]->organization,
                'address' => '999 Industrial Blvd',
                'city' => 'Aurora',
                'state' => 'IL',
                'zip' => '60505',
                'container_size' => '40 yard',
                'waste_type' => 'Industrial Waste',
                'service_description' => 'Swap container and perform maintenance check',
                'truck_number' => $vehicle->unit_number ?? 'LIV-001',
            ]
        );
        
        WorkOrder::firstOrCreate(
            [
                'ticket_number' => 'WO-' . $tomorrow->format('Ymd') . '-302',
            ],
            [
                'company_id' => $companyId,
                'driver_id' => $driver->id,
                'customer_id' => $customers[0]->id,
                'po_number' => 'PO-2024-2008',
                'service_date' => $tomorrow,
                'time_on_site' => Carbon::parse('10:00'),
                'time_on_site_period' => TimePeriod::AM,
                'status' => WorkOrderStatus::DRAFT,
                'action' => WorkOrderAction::PICKUP,
                'customer_name' => $customers[0]->organization,
                'address' => $customers[0]->address,
                'city' => $customers[0]->city,
                'state' => $customers[0]->state,
                'zip' => $customers[0]->zip,
                'container_size' => '30 yard',
                'waste_type' => 'Mixed Waste',
                'service_description' => 'Pickup full container from construction site',
                'truck_number' => $vehicle->unit_number ?? 'LIV-001',
            ]
        );
        
        WorkOrder::firstOrCreate(
            [
                'ticket_number' => 'WO-' . $tomorrow->format('Ymd') . '-303',
            ],
            [
                'company_id' => $companyId,
                'driver_id' => $driver->id,
                'customer_id' => $customers[1]->id,
                'po_number' => 'PO-2024-2009',
                'service_date' => $tomorrow,
                'time_on_site' => Carbon::parse('13:00'),
                'time_on_site_period' => TimePeriod::PM,
                'status' => WorkOrderStatus::DRAFT,
                'action' => WorkOrderAction::DELIVERY,
                'customer_name' => $customers[1]->organization,
                'address' => '222 Park Avenue',
                'city' => 'Wheaton',
                'state' => 'IL',
                'zip' => '60187',
                'container_size' => '20 yard',
                'waste_type' => 'Landscaping Debris',
                'service_description' => 'Deliver container for landscaping project',
                'truck_number' => $vehicle->unit_number ?? 'LIV-001',
            ]
        );
        
        $this->command->info('Additional work orders seeded successfully!');
        $this->command->info('- Added 2 completed orders from yesterday');
        $this->command->info('- Added 5 orders for today (1 in progress, 4 scheduled)');
        $this->command->info('- Added 3 scheduled orders for tomorrow');
    }
}