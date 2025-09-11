<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FuelLog;
use App\Models\Driver;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class FuelLogController extends Controller
{
    /**
     * Get fuel log history
     */
    public function index(Request $request): JsonResponse
    {
        $driver = Driver::where('user_id', $request->user()->id)->first();

        if (!$driver) {
            return response()->json(['message' => 'Driver not found'], 404);
        }

        $query = FuelLog::where('driver_id', $driver->id)
            ->where('company_id', $driver->company_id)
            ->with(['vehicle', 'driver']);

        // Filter by vehicle
        if ($request->has('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }

        // Filter by date range
        if ($request->has('from_date')) {
            $query->whereDate('fuel_date', '>=', $request->from_date);
        }

        if ($request->has('to_date')) {
            $query->whereDate('fuel_date', '<=', $request->to_date);
        }

        // Default to last 30 days
        if (!$request->has('from_date') && !$request->has('to_date')) {
            $query->where('fuel_date', '>=', Carbon::now()->subDays(30));
        }

        $fuelLogs = $query->orderBy('fuel_date', 'desc')
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'fuel_date' => $log->fuel_date,
                    'vehicle' => [
                        'id' => $log->vehicle->id,
                        'vehicle_number' => $log->vehicle->vehicle_number,
                        'make' => $log->vehicle->make,
                        'model' => $log->vehicle->model,
                        'license_plate' => $log->vehicle->license_plate,
                    ],
                    'fuel_type' => $log->fuel_type,
                    'quantity' => $log->quantity,
                    'unit_price' => $log->unit_price,
                    'total_cost' => $log->total_cost,
                    'odometer' => $log->odometer,
                    'location' => $log->location,
                    'mpg' => $log->mpg,
                ];
            });

        // Calculate statistics
        $stats = [
            'total_gallons' => $fuelLogs->sum('quantity'),
            'total_cost' => $fuelLogs->sum('total_cost'),
            'average_price' => $fuelLogs->avg('unit_price'),
            'average_mpg' => $fuelLogs->avg('mpg'),
        ];

        return response()->json([
            'fuel_logs' => $fuelLogs,
            'stats' => $stats
        ]);
    }

    /**
     * Get single fuel log details
     */
    public function show(Request $request, $id): JsonResponse
    {
        $driver = Driver::where('user_id', $request->user()->id)->first();
        
        if (!$driver) {
            return response()->json(['message' => 'Driver not found'], 404);
        }

        $fuelLog = FuelLog::where('id', $id)
            ->where('driver_id', $driver->id)
            ->where('company_id', $driver->company_id)
            ->with(['vehicle', 'driver'])
            ->first();

        if (!$fuelLog) {
            return response()->json(['message' => 'Fuel log not found'], 404);
        }

        return response()->json([
            'fuel_log' => [
                'id' => $fuelLog->id,
                'fuel_date' => $fuelLog->fuel_date,
                'vehicle' => [
                    'id' => $fuelLog->vehicle->id,
                    'vehicle_number' => $fuelLog->vehicle->vehicle_number,
                    'make' => $fuelLog->vehicle->make,
                    'model' => $fuelLog->vehicle->model,
                    'year' => $fuelLog->vehicle->year,
                    'license_plate' => $fuelLog->vehicle->license_plate,
                ],
                'fuel_type' => $fuelLog->fuel_type,
                'quantity' => $fuelLog->quantity,
                'unit_price' => $fuelLog->unit_price,
                'total_cost' => $fuelLog->total_cost,
                'odometer' => $fuelLog->odometer,
                'location' => $fuelLog->location,
                'vendor' => $fuelLog->vendor,
                'payment_method' => $fuelLog->payment_method,
                'reference_number' => $fuelLog->reference_number,
                'mpg' => $fuelLog->mpg,
                'notes' => $fuelLog->notes,
                'receipt_photo' => $fuelLog->receipt_photo,
            ]
        ]);
    }

    /**
     * Create new fuel log entry
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'fuel_type' => 'required|in:diesel,gasoline,def,other',
            'quantity' => 'required|numeric|min:0',
            'unit_price' => 'required|numeric|min:0',
            'odometer' => 'required|integer|min:0',
            'location' => 'required|string|max:255',
            'vendor' => 'nullable|string|max:255',
            'payment_method' => 'nullable|in:company_card,cash,personal_card',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'receipt_photo' => 'nullable|string', // Base64 image
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

        // Calculate total cost
        $totalCost = $request->quantity * $request->unit_price;

        // Calculate MPG if there's a previous fuel log
        $mpg = null;
        $previousLog = FuelLog::where('vehicle_id', $request->vehicle_id)
            ->where('odometer', '<', $request->odometer)
            ->orderBy('odometer', 'desc')
            ->first();

        if ($previousLog) {
            $milesDriven = $request->odometer - $previousLog->odometer;
            if ($milesDriven > 0 && $request->quantity > 0) {
                $mpg = round($milesDriven / $request->quantity, 2);
            }
        }

        // Save receipt photo
        $receiptPath = null;
        if ($request->receipt_photo) {
            $receiptData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $request->receipt_photo));
            $receiptPath = 'fuel-logs/receipts/' . uniqid() . '.jpg';
            Storage::disk('public')->put($receiptPath, $receiptData);
        }

        $fuelLog = FuelLog::create([
            'company_id' => $driver->company_id,
            'driver_id' => $driver->id,
            'vehicle_id' => $request->vehicle_id,
            'fuel_date' => Carbon::now(),
            'fuel_type' => $request->fuel_type,
            'quantity' => $request->quantity,
            'unit_price' => $request->unit_price,
            'total_cost' => $totalCost,
            'odometer' => $request->odometer,
            'location' => $request->location,
            'vendor' => $request->vendor,
            'payment_method' => $request->payment_method,
            'reference_number' => $request->reference_number,
            'mpg' => $mpg,
            'notes' => $request->notes,
            'receipt_photo' => $receiptPath,
        ]);

        // Update vehicle mileage
        $vehicle->current_mileage = $request->odometer;
        $vehicle->save();

        return response()->json([
            'message' => 'Fuel log created successfully',
            'fuel_log' => [
                'id' => $fuelLog->id,
                'total_cost' => $fuelLog->total_cost,
                'mpg' => $fuelLog->mpg,
            ]
        ], 201);
    }

    /**
     * Get fuel statistics for dashboard
     */
    public function statistics(Request $request): JsonResponse
    {
        $driver = Driver::where('user_id', $request->user()->id)
            ->where('company_id', $driver->company_id)
            ->first();

        if (!$driver) {
            return response()->json(['message' => 'Driver not found'], 404);
        }

        // Get current month stats
        $currentMonth = FuelLog::where('driver_id', $driver->id)
            ->where('company_id', $driver->company_id)
            ->whereMonth('fuel_date', Carbon::now()->month)
            ->whereYear('fuel_date', Carbon::now()->year);

        // Get previous month stats
        $previousMonth = FuelLog::where('driver_id', $driver->id)
            ->where('company_id', $driver->company_id)
            ->whereMonth('fuel_date', Carbon::now()->subMonth()->month)
            ->whereYear('fuel_date', Carbon::now()->subMonth()->year);

        // Get fuel consumption by vehicle
        $byVehicle = FuelLog::where('driver_id', $driver->id)
            ->where('company_id', $driver->company_id)
            ->where('fuel_date', '>=', Carbon::now()->subDays(30))
            ->with('vehicle')
            ->get()
            ->groupBy('vehicle_id')
            ->map(function ($logs, $vehicleId) {
                $vehicle = $logs->first()->vehicle;
                return [
                    'vehicle' => [
                        'id' => $vehicle->id,
                        'vehicle_number' => $vehicle->vehicle_number,
                        'make' => $vehicle->make,
                        'model' => $vehicle->model,
                    ],
                    'total_gallons' => $logs->sum('quantity'),
                    'total_cost' => $logs->sum('total_cost'),
                    'average_mpg' => round($logs->avg('mpg'), 2),
                    'fill_ups' => $logs->count(),
                ];
            })->values();

        // Get fuel price trends (last 6 months)
        $priceTrends = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $avg = FuelLog::where('driver_id', $driver->id)
                ->where('company_id', $driver->company_id)
                ->whereMonth('fuel_date', $month->month)
                ->whereYear('fuel_date', $month->year)
                ->avg('unit_price');

            $priceTrends[] = [
                'month' => $month->format('M Y'),
                'average_price' => $avg ? round($avg, 3) : null,
            ];
        }

        return response()->json([
            'current_month' => [
                'total_gallons' => $currentMonth->sum('quantity'),
                'total_cost' => $currentMonth->sum('total_cost'),
                'average_price' => round($currentMonth->avg('unit_price'), 3),
                'average_mpg' => round($currentMonth->avg('mpg'), 2),
                'fill_ups' => $currentMonth->count(),
            ],
            'previous_month' => [
                'total_gallons' => $previousMonth->sum('quantity'),
                'total_cost' => $previousMonth->sum('total_cost'),
                'average_price' => round($previousMonth->avg('unit_price'), 3),
                'average_mpg' => round($previousMonth->avg('mpg'), 2),
                'fill_ups' => $previousMonth->count(),
            ],
            'by_vehicle' => $byVehicle,
            'price_trends' => $priceTrends,
        ]);
    }

    /**
     * Get nearby fuel stations (placeholder for future integration)
     */
    public function nearbyStations(Request $request): JsonResponse
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius' => 'nullable|integer|min:1|max:50', // miles
        ]);

        // This would integrate with a fuel station API in production
        // For now, return mock data
        $stations = [
            [
                'name' => 'Pilot Travel Center',
                'address' => '1234 Highway 101',
                'distance' => 2.3,
                'diesel_price' => 3.89,
                'gasoline_price' => 3.29,
                'def_available' => true,
            ],
            [
                'name' => 'Love\'s Travel Stop',
                'address' => '5678 Interstate 80',
                'distance' => 5.1,
                'diesel_price' => 3.85,
                'gasoline_price' => 3.25,
                'def_available' => true,
            ],
            [
                'name' => 'TA Travel Center',
                'address' => '9012 Route 66',
                'distance' => 8.7,
                'diesel_price' => 3.92,
                'gasoline_price' => 3.32,
                'def_available' => false,
            ],
        ];

        return response()->json([
            'stations' => $stations
        ]);
    }
}