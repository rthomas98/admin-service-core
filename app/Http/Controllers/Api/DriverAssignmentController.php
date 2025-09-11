<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DriverAssignment;
use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class DriverAssignmentController extends Controller
{
    /**
     * Get driver assignments
     */
    public function index(Request $request): JsonResponse
    {
        $driver = Driver::where('user_id', $request->user()->id)->first();

        if (!$driver) {
            return response()->json([
                'assignments' => [],
                'total' => 0,
                'message' => 'No driver profile found'
            ]);
        }

        $query = DriverAssignment::where('driver_id', $driver->id)
            ->where('company_id', $driver->company_id)
            ->with(['vehicle', 'trailer']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->has('date')) {
            $query->whereDate('start_date', $request->date);
        } else {
            // Default to current and future assignments
            $query->where(function ($q) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', Carbon::today());
            });
        }

        $assignments = $query->orderBy('start_date')
            ->get()
            ->map(function ($assignment) {
                return [
                    'id' => $assignment->id,
                    'status' => $assignment->status,
                    'route' => $assignment->route,
                    'origin' => $assignment->origin,
                    'destination' => $assignment->destination,
                    'start_date' => $assignment->start_date->toISOString(),
                    'end_date' => $assignment->end_date?->toISOString(),
                    'vehicle' => $assignment->vehicle ? [
                        'id' => $assignment->vehicle->id,
                        'vehicle_number' => $assignment->vehicle->vehicle_number,
                        'make' => $assignment->vehicle->make,
                        'model' => $assignment->vehicle->model,
                        'year' => $assignment->vehicle->year,
                        'license_plate' => $assignment->vehicle->license_plate,
                    ] : null,
                    'trailer' => $assignment->trailer ? [
                        'id' => $assignment->trailer->id,
                        'trailer_number' => $assignment->trailer->trailer_number,
                        'type' => $assignment->trailer->type,
                        'license_plate' => $assignment->trailer->license_plate,
                    ] : null,
                    'cargo_type' => $assignment->cargo_type,
                    'cargo_weight' => $assignment->cargo_weight,
                    'expected_duration_hours' => $assignment->expected_duration_hours,
                    'mileage_start' => $assignment->mileage_start,
                    'mileage_end' => $assignment->mileage_end,
                    'notes' => $assignment->notes,
                ];
            });

        return response()->json([
            'assignments' => $assignments
        ]);
    }

    /**
     * Get a single assignment
     */
    public function show(Request $request, $id): JsonResponse
    {
        $driver = Driver::where('user_id', $request->user()->id)->first();
        
        if (!$driver) {
            return response()->json(['message' => 'Driver not found'], 404);
        }

        $assignment = DriverAssignment::where('id', $id)
            ->where('driver_id', $driver->id)
            ->where('company_id', $driver->company_id)
            ->with(['vehicle', 'trailer'])
            ->first();

        if (!$assignment) {
            return response()->json(['message' => 'Assignment not found'], 404);
        }

        return response()->json([
            'assignment' => [
                'id' => $assignment->id,
                'status' => $assignment->status,
                'route' => $assignment->route,
                'origin' => $assignment->origin,
                'destination' => $assignment->destination,
                'start_date' => $assignment->start_date->toISOString(),
                'end_date' => $assignment->end_date?->toISOString(),
                'vehicle' => $assignment->vehicle ? [
                    'id' => $assignment->vehicle->id,
                    'vehicle_number' => $assignment->vehicle->vehicle_number,
                    'make' => $assignment->vehicle->make,
                    'model' => $assignment->vehicle->model,
                    'year' => $assignment->vehicle->year,
                    'license_plate' => $assignment->vehicle->license_plate,
                    'vin' => $assignment->vehicle->vin,
                    'current_mileage' => $assignment->vehicle->current_mileage,
                ] : null,
                'trailer' => $assignment->trailer ? [
                    'id' => $assignment->trailer->id,
                    'trailer_number' => $assignment->trailer->trailer_number,
                    'type' => $assignment->trailer->type,
                    'license_plate' => $assignment->trailer->license_plate,
                    'vin' => $assignment->trailer->vin,
                ] : null,
                'cargo_type' => $assignment->cargo_type,
                'cargo_weight' => $assignment->cargo_weight,
                'expected_duration_hours' => $assignment->expected_duration_hours,
                'actual_duration_hours' => $assignment->actual_duration_hours,
                'mileage_start' => $assignment->mileage_start,
                'mileage_end' => $assignment->mileage_end,
                'fuel_used' => $assignment->fuel_used,
                'notes' => $assignment->notes,
            ]
        ]);
    }

    /**
     * Start an assignment
     */
    public function start(Request $request, $id): JsonResponse
    {
        $request->validate([
            'mileage_start' => 'required|integer|min:0',
            'notes' => 'nullable|string'
        ]);

        $driver = Driver::where('user_id', $request->user()->id)->first();
        
        if (!$driver) {
            return response()->json(['message' => 'Driver not found'], 404);
        }

        $assignment = DriverAssignment::where('id', $id)
            ->where('driver_id', $driver->id)
            ->where('company_id', $driver->company_id)
            ->first();

        if (!$assignment) {
            return response()->json(['message' => 'Assignment not found'], 404);
        }

        $assignment->status = 'active';
        $assignment->mileage_start = $request->mileage_start;
        
        if ($request->has('notes')) {
            $assignment->notes = $request->notes;
        }

        $assignment->save();

        return response()->json([
            'message' => 'Assignment started successfully',
            'assignment' => [
                'id' => $assignment->id,
                'status' => $assignment->status,
                'mileage_start' => $assignment->mileage_start,
            ]
        ]);
    }

    /**
     * Complete an assignment
     */
    public function complete(Request $request, $id): JsonResponse
    {
        $request->validate([
            'mileage_end' => 'required|integer|min:0',
            'fuel_used' => 'nullable|numeric|min:0',
            'actual_duration_hours' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string'
        ]);

        $driver = Driver::where('user_id', $request->user()->id)->first();
        
        if (!$driver) {
            return response()->json(['message' => 'Driver not found'], 404);
        }

        $assignment = DriverAssignment::where('id', $id)
            ->where('driver_id', $driver->id)
            ->where('company_id', $driver->company_id)
            ->first();

        if (!$assignment) {
            return response()->json(['message' => 'Assignment not found'], 404);
        }

        // Validate mileage_end is greater than mileage_start
        if ($request->mileage_end <= $assignment->mileage_start) {
            return response()->json([
                'message' => 'Ending mileage must be greater than starting mileage'
            ], 422);
        }

        $assignment->status = 'completed';
        $assignment->end_date = Carbon::now();
        $assignment->mileage_end = $request->mileage_end;
        
        if ($request->has('fuel_used')) {
            $assignment->fuel_used = $request->fuel_used;
        }
        
        if ($request->has('actual_duration_hours')) {
            $assignment->actual_duration_hours = $request->actual_duration_hours;
        }
        
        if ($request->has('notes')) {
            $assignment->notes = $assignment->notes . "\n\n" . $request->notes;
        }

        $assignment->save();

        // Update vehicle mileage if vehicle is assigned
        if ($assignment->vehicle) {
            $assignment->vehicle->current_mileage = $request->mileage_end;
            $assignment->vehicle->save();
        }

        return response()->json([
            'message' => 'Assignment completed successfully',
            'assignment' => [
                'id' => $assignment->id,
                'status' => $assignment->status,
                'end_date' => $assignment->end_date->toISOString(),
                'total_mileage' => $assignment->mileage_end - $assignment->mileage_start,
            ]
        ]);
    }

    /**
     * Update mileage during assignment
     */
    public function updateMileage(Request $request, $id): JsonResponse
    {
        $request->validate([
            'current_mileage' => 'required|integer|min:0',
            'location' => 'nullable|string'
        ]);

        $driver = Driver::where('user_id', $request->user()->id)->first();
        
        if (!$driver) {
            return response()->json(['message' => 'Driver not found'], 404);
        }

        $assignment = DriverAssignment::where('id', $id)
            ->where('driver_id', $driver->id)
            ->where('company_id', $driver->company_id)
            ->where('status', 'active')
            ->first();

        if (!$assignment) {
            return response()->json(['message' => 'Active assignment not found'], 404);
        }

        // Update vehicle mileage if vehicle is assigned
        if ($assignment->vehicle) {
            $assignment->vehicle->current_mileage = $request->current_mileage;
            $assignment->vehicle->save();
        }

        // Log the mileage update in notes
        $note = "Mileage update: {$request->current_mileage} miles";
        if ($request->has('location')) {
            $note .= " at {$request->location}";
        }
        $note .= " - " . Carbon::now()->format('Y-m-d H:i:s');
        
        $assignment->notes = $assignment->notes ? $assignment->notes . "\n" . $note : $note;
        $assignment->save();

        return response()->json([
            'message' => 'Mileage updated successfully',
            'current_mileage' => $request->current_mileage
        ]);
    }
}