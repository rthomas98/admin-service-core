<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VehicleMaintenance;
use App\Models\Driver;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class VehicleMaintenanceController extends Controller
{
    /**
     * Get maintenance history for a vehicle
     */
    public function index(Request $request): JsonResponse
    {
        $driver = Driver::where('user_id', $request->user()->id)->first();
        
        if (!$driver) {
            return response()->json(['message' => 'Driver not found'], 404);
        }

        // Get the vehicle assigned to the driver
        $vehicle = Vehicle::where('company_id', $driver->company_id)
            ->where('id', $request->vehicle_id ?? null)
            ->first();

        if (!$vehicle && $request->has('vehicle_id')) {
            return response()->json(['message' => 'Vehicle not found'], 404);
        }

        $query = VehicleMaintenance::where('company_id', $driver->company_id);
        
        if ($vehicle) {
            $query->where('vehicle_id', $vehicle->id);
        }

        // Filter by maintenance type
        if ($request->has('type')) {
            $query->where('service_type', $request->type);
        }

        // Filter by date range
        if ($request->has('from_date')) {
            $query->whereDate('service_date', '>=', $request->from_date);
        }

        if ($request->has('to_date')) {
            $query->whereDate('service_date', '<=', $request->to_date);
        }

        // Default to last 90 days
        if (!$request->has('from_date') && !$request->has('to_date')) {
            $query->where('service_date', '>=', Carbon::now()->subDays(90));
        }

        $maintenances = $query->orderBy('service_date', 'desc')
            ->get()
            ->map(function ($maintenance) {
                return [
                    'id' => $maintenance->id,
                    'service_type' => $maintenance->maintenance_type,
                    'service_date' => $maintenance->scheduled_date ?? $maintenance->completed_date,
                    'next_service_date' => $maintenance->next_service_date,
                    'mileage_at_service' => $maintenance->odometer_at_service,
                    'next_service_mileage' => $maintenance->next_service_miles,
                    'service_provider' => $maintenance->service_provider,
                    'cost' => $maintenance->total_cost,
                    'description' => $maintenance->description,
                    'status' => $maintenance->status,
                    'severity' => $maintenance->priority,
                ];
            });

        return response()->json([
            'maintenances' => $maintenances
        ]);
    }

    /**
     * Get single maintenance record details
     */
    public function show(Request $request, $id): JsonResponse
    {
        $driver = Driver::where('user_id', $request->user()->id)->first();
        
        if (!$driver) {
            return response()->json(['message' => 'Driver not found'], 404);
        }

        $maintenance = VehicleMaintenance::where('id', $id)
            ->where('company_id', $driver->company_id)
            ->first();

        if (!$maintenance) {
            return response()->json(['message' => 'Maintenance record not found'], 404);
        }

        return response()->json([
            'maintenance' => [
                'id' => $maintenance->id,
                'service_type' => $maintenance->maintenance_type,
                'service_date' => $maintenance->scheduled_date ?? $maintenance->completed_date,
                'next_service_date' => $maintenance->next_service_date,
                'mileage_at_service' => $maintenance->odometer_at_service,
                'next_service_mileage' => $maintenance->next_service_miles,
                'service_provider' => $maintenance->service_provider,
                'cost' => $maintenance->total_cost,
                'description' => $maintenance->description,
                'parts_replaced' => $maintenance->parts_replaced,
                'labor_hours' => $maintenance->labor_cost,
                'warranty_info' => $maintenance->warranty_claim_number,
                'invoice_number' => $maintenance->invoice_number,
                'notes' => $maintenance->notes,
                'status' => $maintenance->status,
                'severity' => $maintenance->priority,
                'vehicle' => [
                    'id' => $maintenance->vehicle->id,
                    'vehicle_number' => $maintenance->vehicle->unit_number,
                    'make' => $maintenance->vehicle->make,
                    'model' => $maintenance->vehicle->model,
                    'year' => $maintenance->vehicle->year,
                    'license_plate' => $maintenance->vehicle->license_plate,
                ]
            ]
        ]);
    }

    /**
     * Report a maintenance issue
     */
    public function reportIssue(Request $request): JsonResponse
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'issue_type' => 'required|string',
            'severity' => 'required|in:low,medium,high,critical',
            'description' => 'required|string|min:10',
            'mileage' => 'nullable|integer|min:0',
            'photos' => 'nullable|array',
            'photos.*' => 'string', // Base64 images
        ]);

        $driver = Driver::where('user_id', $request->user()->id)->first();

        if (!$driver) {
            return response()->json(['message' => 'Driver not found'], 404);
        }

        // Verify vehicle belongs to company
        $vehicle = Vehicle::where('id', $request->vehicle_id)
            ->where('company_id', $driver->company_id)
            ->first();

        if (!$vehicle) {
            return response()->json(['message' => 'Vehicle not found'], 404);
        }

        // Map issue types to valid maintenance types
        $maintenanceTypeMap = [
            'Engine Issue' => 'engine_service',
            'Transmission Problem' => 'transmission_service',
            'Brake Issue' => 'brake_service',
            'Tire Problem' => 'tire_rotation',
            'Electrical Issue' => 'electrical',
            'Oil Leak' => 'oil_change',
            'Coolant Leak' => 'cooling_system',
            'Strange Noise' => 'corrective',
            'Warning Light' => 'corrective',
            'AC/Heating Issue' => 'other',
            'Suspension Problem' => 'corrective',
            'Other' => 'other',
        ];

        $maintenanceType = $maintenanceTypeMap[$request->issue_type] ?? 'other';

        // Generate maintenance number
        $maintenanceNumber = 'MAINT-' . date('Ymd') . '-' . str_pad(
            VehicleMaintenance::where('company_id', $driver->company_id)
                ->whereDate('created_at', Carbon::today())
                ->count() + 1,
            3, '0', STR_PAD_LEFT
        );

        // Create maintenance record
        $maintenance = VehicleMaintenance::create([
            'company_id' => $driver->company_id,
            'vehicle_id' => $request->vehicle_id,
            'maintenance_number' => $maintenanceNumber,
            'maintenance_type' => $maintenanceType,
            'scheduled_date' => Carbon::now()->toDateString(),
            'odometer_at_service' => $request->mileage ?? $vehicle->odometer,
            'description' => $request->issue_type . ': ' . $request->description,
            'technician_name' => $driver->first_name . ' ' . $driver->last_name,
            'status' => 'scheduled',
            'priority' => $request->severity,
            'total_cost' => 0,
            'notes' => 'Reported by driver: ' . $request->description,
        ]);

        return response()->json([
            'message' => 'Issue reported successfully',
            'maintenance' => [
                'id' => $maintenance->id,
                'service_number' => $maintenance->maintenance_number,
                'status' => $maintenance->status,
                'severity' => $maintenance->severity,
            ]
        ], 201);
    }

    /**
     * Get upcoming maintenance schedule
     */
    public function upcoming(Request $request): JsonResponse
    {
        $driver = Driver::where('user_id', $request->user()->id)->first();
        
        if (!$driver) {
            return response()->json(['message' => 'Driver not found'], 404);
        }

        // Get upcoming maintenance based on date or mileage
        $upcomingMaintenance = VehicleMaintenance::where('company_id', $driver->company_id)
            ->where('status', 'scheduled')
            ->where(function($query) {
                $query->where('next_service_date', '<=', Carbon::now()->addDays(30))
                    ->orWhereNotNull('next_service_mileage');
            })
            ->orderBy('next_service_date', 'asc')
            ->get()
            ->map(function ($maintenance) {
                return [
                    'id' => $maintenance->id,
                    'vehicle_id' => $maintenance->vehicle_id,
                    'vehicle_number' => $maintenance->vehicle->unit_number,
                    'service_type' => $maintenance->maintenance_type,
                    'next_service_date' => $maintenance->next_service_date,
                    'next_service_mileage' => $maintenance->next_service_mileage,
                    'current_mileage' => $maintenance->vehicle->odometer,
                    'days_until_due' => $maintenance->next_service_date ? 
                        Carbon::parse($maintenance->next_service_date)->diffInDays(Carbon::now(), false) : null,
                    'miles_until_due' => $maintenance->next_service_mileage ? 
                        $maintenance->next_service_mileage - $maintenance->vehicle->odometer : null,
                ];
            });

        return response()->json([
            'upcoming_maintenance' => $upcomingMaintenance
        ]);
    }
}