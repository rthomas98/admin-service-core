<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VehicleInspection;
use App\Models\Driver;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class VehicleInspectionController extends Controller
{
    /**
     * Get inspection history
     */
    public function index(Request $request): JsonResponse
    {
        $driver = Driver::where('user_id', $request->user()->id)->first();

        if (!$driver) {
            return response()->json(['message' => 'Driver not found'], 404);
        }

        $query = VehicleInspection::where('driver_id', $driver->id)
            ->where('company_id', $driver->company_id)
            ->with(['vehicle', 'driver']);

        // Filter by inspection type
        if ($request->has('type')) {
            $query->where('inspection_type', $request->type);
        }

        // Filter by date range
        if ($request->has('from_date')) {
            $query->whereDate('inspection_date', '>=', $request->from_date);
        }

        if ($request->has('to_date')) {
            $query->whereDate('inspection_date', '<=', $request->to_date);
        }

        // Default to last 30 days
        if (!$request->has('from_date') && !$request->has('to_date')) {
            $query->where('inspection_date', '>=', Carbon::now()->subDays(30));
        }

        $inspections = $query->orderBy('inspection_date', 'desc')
            ->get()
            ->map(function ($inspection) {
                return [
                    'id' => $inspection->id,
                    'inspection_type' => $inspection->inspection_type,
                    'inspection_date' => $inspection->inspection_date,
                    'vehicle' => [
                        'id' => $inspection->vehicle->id,
                        'vehicle_number' => $inspection->vehicle->vehicle_number,
                        'make' => $inspection->vehicle->make,
                        'model' => $inspection->vehicle->model,
                        'license_plate' => $inspection->vehicle->license_plate,
                    ],
                    'mileage' => $inspection->mileage,
                    'status' => $inspection->status,
                    'issues_found' => $inspection->issues_found,
                    'corrective_actions' => $inspection->corrective_actions,
                ];
            });

        return response()->json([
            'inspections' => $inspections
        ]);
    }

    /**
     * Get single inspection details
     */
    public function show(Request $request, $id): JsonResponse
    {
        $driver = Driver::where('user_id', $request->user()->id)->first();
        
        if (!$driver) {
            return response()->json(['message' => 'Driver not found'], 404);
        }

        $inspection = VehicleInspection::where('id', $id)
            ->where('driver_id', $driver->id)
            ->where('company_id', $driver->company_id)
            ->with(['vehicle', 'driver'])
            ->first();

        if (!$inspection) {
            return response()->json(['message' => 'Inspection not found'], 404);
        }

        return response()->json([
            'inspection' => [
                'id' => $inspection->id,
                'inspection_type' => $inspection->inspection_type,
                'inspection_date' => $inspection->inspection_date,
                'vehicle' => [
                    'id' => $inspection->vehicle->id,
                    'vehicle_number' => $inspection->vehicle->vehicle_number,
                    'make' => $inspection->vehicle->make,
                    'model' => $inspection->vehicle->model,
                    'year' => $inspection->vehicle->year,
                    'license_plate' => $inspection->vehicle->license_plate,
                    'vin' => $inspection->vehicle->vin,
                ],
                'mileage' => $inspection->mileage,
                'status' => $inspection->status,
                'issues_found' => $inspection->issues_found,
                'corrective_actions' => $inspection->corrective_actions,
                'checklist' => $inspection->checklist,
                'notes' => $inspection->notes,
                'signature' => $inspection->signature,
                'photos' => $inspection->photos,
            ]
        ]);
    }

    /**
     * Create new inspection (pre-trip or post-trip)
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'inspection_type' => 'required|in:pre_trip,post_trip',
            'mileage' => 'required|integer|min:0',
            'checklist' => 'required|array',
            'checklist.*.item' => 'required|string',
            'checklist.*.passed' => 'required|boolean',
            'checklist.*.notes' => 'nullable|string',
            'defects_found' => 'nullable|string',
            'signature' => 'required|string', // Base64 signature
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

        // Check if all checklist items passed
        $passed = collect($request->checklist)->every(function ($item) {
            return $item['passed'] === true;
        });

        // Save signature
        $signaturePath = null;
        if ($request->signature) {
            $signatureData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $request->signature));
            $signaturePath = 'inspections/signatures/' . uniqid() . '.png';
            Storage::disk('public')->put($signaturePath, $signatureData);
        }

        // Save photos
        $photoPaths = [];
        if ($request->has('photos')) {
            foreach ($request->photos as $photo) {
                $photoData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $photo));
                $photoPath = 'inspections/photos/' . uniqid() . '.jpg';
                Storage::disk('public')->put($photoPath, $photoData);
                $photoPaths[] = $photoPath;
            }
        }

        // Generate unique inspection number
        $inspectionNumber = 'INSP-' . date('Ymd') . '-' . str_pad(VehicleInspection::where('company_id', $driver->company_id)->whereDate('created_at', Carbon::today())->count() + 1, 3, '0', STR_PAD_LEFT);
        
        $inspection = VehicleInspection::create([
            'company_id' => $driver->company_id,
            'driver_id' => $driver->id,
            'vehicle_id' => $request->vehicle_id,
            'inspection_number' => $inspectionNumber,
            'inspection_type' => $request->inspection_type,
            'inspection_date' => Carbon::now()->toDateString(),
            'inspection_time' => Carbon::now()->toTimeString(),
            'status' => $passed ? 'completed' : 'failed',
            'odometer_reading' => $request->mileage,
            'issues_found' => $request->defects_found,
            'notes' => $request->input('notes'),
            'inspector_name' => $driver->first_name . ' ' . $driver->last_name,
            'inspector_signature' => $signaturePath,
            'photos' => json_encode($photoPaths),
            'exterior_items' => json_encode($request->input('checklist.exterior', [])),
            'interior_items' => json_encode($request->input('checklist.interior', [])),
            'engine_items' => json_encode($request->input('checklist.engine', [])),
            'safety_items' => json_encode($request->input('checklist.safety', [])),
        ]);

        // Update vehicle odometer
        $vehicle->odometer = $request->mileage;
        $vehicle->save();

        return response()->json([
            'message' => 'Inspection created successfully',
            'inspection' => [
                'id' => $inspection->id,
                'inspection_number' => $inspection->inspection_number,
                'status' => $inspection->status,
                'issues_found' => $inspection->issues_found,
            ]
        ], 201);
    }

    /**
     * Update defects as corrected
     */
    public function correctDefects(Request $request, $id): JsonResponse
    {
        $request->validate([
            'defects_corrected' => 'required|string',
            'photos' => 'nullable|array',
            'photos.*' => 'string', // Base64 images
        ]);

        $driver = Driver::where('user_id', $request->user()->id)->first();
        
        if (!$driver) {
            return response()->json(['message' => 'Driver not found'], 404);
        }

        $inspection = VehicleInspection::where('id', $id)
            ->where('driver_id', $driver->id)
            ->where('company_id', $driver->company_id)
            ->first();

        if (!$inspection) {
            return response()->json(['message' => 'Inspection not found'], 404);
        }

        // Save correction photos
        $photoPaths = $inspection->photos ?? [];
        if ($request->has('photos')) {
            foreach ($request->photos as $photo) {
                $photoData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $photo));
                $photoPath = 'inspections/corrections/' . uniqid() . '.jpg';
                Storage::disk('public')->put($photoPath, $photoData);
                $photoPaths[] = $photoPath;
            }
        }

        $inspection->corrective_actions = $request->defects_corrected;
        $inspection->photos = json_encode($photoPaths);
        $inspection->status = 'completed'; // Mark as completed after corrections
        $inspection->save();

        return response()->json([
            'message' => 'Defects marked as corrected',
            'inspection' => [
                'id' => $inspection->id,
                'status' => $inspection->status,
                'corrective_actions' => $inspection->corrective_actions,
            ]
        ]);
    }

    /**
     * Get inspection checklist template
     */
    public function getChecklist(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|in:pre_trip,post_trip',
        ]);

        // Define standard DOT inspection checklist items
        $preTrip = [
            ['category' => 'Engine Compartment', 'items' => [
                ['item' => 'Oil Level', 'description' => 'Check oil level and condition'],
                ['item' => 'Coolant Level', 'description' => 'Check coolant level and leaks'],
                ['item' => 'Power Steering Fluid', 'description' => 'Check fluid level'],
                ['item' => 'Belts and Hoses', 'description' => 'Check for wear and proper tension'],
                ['item' => 'Battery', 'description' => 'Check terminals and security'],
            ]],
            ['category' => 'Exterior', 'items' => [
                ['item' => 'Lights', 'description' => 'Check all lights are working'],
                ['item' => 'Reflectors', 'description' => 'Check all reflectors are clean and secure'],
                ['item' => 'Tires', 'description' => 'Check tread depth and pressure'],
                ['item' => 'Wheels and Rims', 'description' => 'Check for damage and loose lug nuts'],
                ['item' => 'Windshield', 'description' => 'Check for cracks and cleanliness'],
                ['item' => 'Wipers', 'description' => 'Check blades and operation'],
                ['item' => 'Mirrors', 'description' => 'Check adjustment and cleanliness'],
            ]],
            ['category' => 'Cab', 'items' => [
                ['item' => 'Gauges', 'description' => 'Check all gauges are working'],
                ['item' => 'Horn', 'description' => 'Test horn operation'],
                ['item' => 'Heater/Defroster', 'description' => 'Check operation'],
                ['item' => 'Seat Belt', 'description' => 'Check condition and operation'],
                ['item' => 'Emergency Equipment', 'description' => 'Fire extinguisher, triangles, first aid'],
            ]],
            ['category' => 'Brakes', 'items' => [
                ['item' => 'Parking Brake', 'description' => 'Test holding power'],
                ['item' => 'Service Brakes', 'description' => 'Test pedal and response'],
                ['item' => 'Air Pressure', 'description' => 'Check build-up and warning lights'],
                ['item' => 'Brake Lines', 'description' => 'Check for leaks and damage'],
            ]],
        ];

        $postTrip = [
            ['category' => 'General Condition', 'items' => [
                ['item' => 'Body Damage', 'description' => 'Check for new damage'],
                ['item' => 'Fluid Leaks', 'description' => 'Check for any fluid leaks'],
                ['item' => 'Tires', 'description' => 'Check for damage or wear'],
            ]],
            ['category' => 'Interior', 'items' => [
                ['item' => 'Cleanliness', 'description' => 'Clean and organized cab'],
                ['item' => 'Equipment', 'description' => 'All equipment secure'],
                ['item' => 'Documentation', 'description' => 'Logs and paperwork complete'],
            ]],
            ['category' => 'Report', 'items' => [
                ['item' => 'Defects', 'description' => 'Report any defects found'],
                ['item' => 'Maintenance', 'description' => 'Note any maintenance needs'],
            ]],
        ];

        $checklist = $request->type === 'pre_trip' ? $preTrip : $postTrip;

        return response()->json([
            'checklist' => $checklist
        ]);
    }
}