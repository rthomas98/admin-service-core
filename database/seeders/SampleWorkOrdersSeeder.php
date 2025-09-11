<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WorkOrder;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\Company;
use App\Enums\WorkOrderStatus;
use App\Enums\WorkOrderAction;
use App\Enums\TimePeriod;
use Carbon\Carbon;

class SampleWorkOrdersSeeder extends Seeder
{
    public function run(): void
    {
        // Get companies
        $livTransport = Company::where('name', 'LIV Transport')->orWhere('name', 'Service Core Transport')->first();
        $rawDisposal = Company::where('name', 'RAW Disposal')->orWhere('name', 'Service Core Disposal')->first();

        // Create work orders for LIV Transport
        if ($livTransport) {
            $livCustomer = Customer::where('company_id', $livTransport->id)->first() ?? Customer::create([
                'company_id' => $livTransport->id,
                'company_name' => 'ABC Construction',
                'contact_name' => 'John Smith',
                'email' => 'john@abcconstruction.com',
                'phone' => '555-0101',
                'address' => '123 Main Street',
                'city' => 'Phoenix',
                'state' => 'AZ',
                'zip' => '85001',
            ]);

            $livDriver = Driver::where('company_id', $livTransport->id)->first();

            // Create LIV Transport work orders
            for ($i = 1; $i <= 5; $i++) {
                WorkOrder::create([
                    'company_id' => $livTransport->id,
                    'customer_id' => $livCustomer->id,
                    'driver_id' => $livDriver?->id,
                    'ticket_number' => 'LIV-2025-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                    'po_number' => 'PO-LIV-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                    'service_date' => Carbon::today()->addDays($i - 3),
                    'time_on_site' => Carbon::createFromTime(8 + $i, 0, 0),
                    'time_on_site_period' => TimePeriod::AM,
                    'status' => $i <= 2 ? WorkOrderStatus::COMPLETED : WorkOrderStatus::DRAFT,
                    'action' => $i % 2 == 0 ? WorkOrderAction::DELIVERY : WorkOrderAction::PICKUP,
                    'address' => $livCustomer->address,
                    'city' => $livCustomer->city,
                    'state' => $livCustomer->state,
                    'zip' => $livCustomer->zip,
                    'container_size' => ['10 yard', '20 yard', '30 yard', '40 yard'][($i - 1) % 4],
                    'waste_type' => ['Construction', 'Concrete', 'Mixed Waste', 'Recyclables'][($i - 1) % 4],
                    'service_description' => 'Transport service for container ' . $i,
                    'customer_name' => $livCustomer->company_name,
                    'completed_at' => $i <= 2 ? Carbon::now() : null,
                ]);
            }
        }

        // Create work orders for RAW Disposal
        if ($rawDisposal) {
            $rawCustomer = Customer::where('company_id', $rawDisposal->id)->first() ?? Customer::create([
                'company_id' => $rawDisposal->id,
                'company_name' => 'XYZ Industries',
                'contact_name' => 'Jane Doe',
                'email' => 'jane@xyzindustries.com',
                'phone' => '555-0202',
                'address' => '456 Industrial Ave',
                'city' => 'Tucson',
                'state' => 'AZ',
                'zip' => '85701',
            ]);

            $rawDriver = Driver::where('company_id', $rawDisposal->id)->first();

            // Create RAW Disposal work orders
            for ($i = 1; $i <= 7; $i++) {
                WorkOrder::create([
                    'company_id' => $rawDisposal->id,
                    'customer_id' => $rawCustomer->id,
                    'driver_id' => $rawDriver?->id,
                    'ticket_number' => 'RAW-2025-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                    'po_number' => 'PO-RAW-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                    'service_date' => Carbon::today()->addDays($i - 4),
                    'time_on_site' => Carbon::createFromTime(7 + $i, 30, 0),
                    'time_on_site_period' => $i > 4 ? TimePeriod::PM : TimePeriod::AM,
                    'status' => $i <= 3 ? WorkOrderStatus::COMPLETED : ($i == 4 ? WorkOrderStatus::IN_PROGRESS : WorkOrderStatus::DRAFT),
                    'action' => $i % 3 == 0 ? WorkOrderAction::SERVICE : ($i % 2 == 0 ? WorkOrderAction::DELIVERY : WorkOrderAction::PICKUP),
                    'address' => $rawCustomer->address,
                    'city' => $rawCustomer->city,
                    'state' => $rawCustomer->state,
                    'zip' => $rawCustomer->zip,
                    'container_size' => ['15 yard', '20 yard', '30 yard', '40 yard'][($i - 1) % 4],
                    'waste_type' => ['Industrial Waste', 'Hazardous', 'Recyclables', 'General Waste'][($i - 1) % 4],
                    'service_description' => 'Waste disposal service #' . $i,
                    'customer_name' => $rawCustomer->company_name,
                    'completed_at' => $i <= 3 ? Carbon::now() : null,
                ]);
            }
        }

        $this->command->info('Sample work orders created successfully!');
    }
}