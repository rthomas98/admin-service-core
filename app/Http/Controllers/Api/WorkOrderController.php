<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WorkOrder;
use App\Models\Driver;
use App\Enums\WorkOrderStatus;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class WorkOrderController extends Controller
{
    /**
     * Get work orders for the authenticated driver or admin
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Check if user is an admin (has access to multiple companies)
        $isAdmin = $user->companies()->count() > 1;
        
        // Get company ID from request header or use default
        $companyId = $request->header('X-Company-Id');
        
        if ($isAdmin) {
            // Admin users can see all work orders for the selected company
            if (!$companyId) {
                // If no company ID provided, use the first company
                $company = $user->companies()->first();
                $companyId = $company ? $company->id : null;
            }
            
            if (!$companyId) {
                return response()->json([
                    'work_orders' => [],
                    'total' => 0,
                    'message' => 'No company selected'
                ]);
            }
            
            $query = WorkOrder::where('company_id', $companyId)
                ->with(['customer', 'serviceOrder', 'driver']);
        } else {
            // Regular driver - get their specific work orders
            $driver = Driver::where('user_id', $user->id)->first();

            if (!$driver) {
                // Return empty work orders if no driver found
                return response()->json([
                    'work_orders' => [],
                    'total' => 0,
                    'message' => 'No driver profile found'
                ]);
            }

            $query = WorkOrder::where('driver_id', $driver->id)
                ->where('company_id', $driver->company_id)
                ->with(['customer', 'serviceOrder']);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date
        if ($request->has('date')) {
            $query->whereDate('service_date', $request->date);
        } else {
            // Default to today and future
            $query->where('service_date', '>=', Carbon::today());
        }

        $workOrders = $query->orderBy('service_date')
            ->orderBy('time_on_site')
            ->get()
            ->map(function ($order) use ($isAdmin) {
                $data = [
                    'id' => $order->id,
                    'ticket_number' => $order->ticket_number,
                    'po_number' => $order->po_number,
                    'service_date' => $order->service_date->format('Y-m-d'),
                    'time_on_site' => $order->time_on_site?->format('H:i'),
                    'time_on_site_period' => $order->time_on_site_period?->value,
                    'status' => $order->status->value,
                    'action' => $order->action?->value,
                    'customer' => [
                        'id' => $order->customer_id,
                        'name' => $order->customer_name ?? $order->customer?->company_name,
                        'address' => $order->address,
                        'city' => $order->city,
                        'state' => $order->state,
                        'zip' => $order->zip,
                    ],
                    'container_size' => $order->container_size,
                    'waste_type' => $order->waste_type,
                    'service_description' => $order->service_description,
                    'completed_at' => $order->completed_at?->toISOString(),
                ];
                
                // Include driver info for admin users
                if ($isAdmin && $order->driver) {
                    $data['driver'] = [
                        'id' => $order->driver->id,
                        'name' => $order->driver->first_name . ' ' . $order->driver->last_name,
                        'phone' => $order->driver->phone,
                    ];
                }
                
                return $data;
            });

        return response()->json([
            'work_orders' => $workOrders
        ]);
    }

    /**
     * Get a single work order
     */
    public function show(Request $request, $id): JsonResponse
    {
        $user = $request->user();
        
        // Check if user is an admin (has access to multiple companies)
        $isAdmin = $user->companies()->count() > 1;
        
        if ($isAdmin) {
            // Admin users can see work orders from the selected company
            $companyId = $request->header('X-Company-Id');
            if (!$companyId) {
                // If no company ID provided, use the first company
                $company = $user->companies()->first();
                $companyId = $company ? $company->id : null;
            }
            
            $workOrder = WorkOrder::where('id', $id)
                ->where('company_id', $companyId)
                ->with(['customer', 'serviceOrder', 'driver'])
                ->first();
        } else {
            // Regular driver - get their specific work orders
            $driver = Driver::where('user_id', $user->id)->first();
            
            if (!$driver) {
                return response()->json(['message' => 'Driver not found'], 404);
            }

            $workOrder = WorkOrder::where('id', $id)
                ->where('driver_id', $driver->id)
                ->where('company_id', $driver->company_id)
                ->with(['customer', 'serviceOrder'])
                ->first();
        }

        if (!$workOrder) {
            return response()->json(['message' => 'Work order not found'], 404);
        }

        return response()->json([
            'work_order' => [
                'id' => $workOrder->id,
                'ticket_number' => $workOrder->ticket_number,
                'po_number' => $workOrder->po_number,
                'service_date' => $workOrder->service_date->format('Y-m-d'),
                'time_on_site' => $workOrder->time_on_site?->format('H:i'),
                'time_off_site' => $workOrder->time_off_site?->format('H:i'),
                'time_on_site_period' => $workOrder->time_on_site_period?->value,
                'time_off_site_period' => $workOrder->time_off_site_period?->value,
                'truck_number' => $workOrder->truck_number,
                'dispatch_number' => $workOrder->dispatch_number,
                'status' => $workOrder->status->value,
                'action' => $workOrder->action?->value,
                'customer' => [
                    'id' => $workOrder->customer_id,
                    'name' => $workOrder->customer_name ?? $workOrder->customer?->company_name,
                    'address' => $workOrder->address,
                    'city' => $workOrder->city,
                    'state' => $workOrder->state,
                    'zip' => $workOrder->zip,
                    'phone' => $workOrder->customer?->phone,
                    'email' => $workOrder->customer?->email,
                ],
                'container_size' => $workOrder->container_size,
                'waste_type' => $workOrder->waste_type,
                'service_description' => $workOrder->service_description,
                'container_delivered' => $workOrder->container_delivered,
                'container_picked_up' => $workOrder->container_picked_up,
                'disposal_id' => $workOrder->disposal_id,
                'disposal_ticket' => $workOrder->disposal_ticket,
                'cod_amount' => $workOrder->cod_amount,
                'cod_signature' => $workOrder->cod_signature,
                'comments' => $workOrder->comments,
                'customer_signature' => $workOrder->customer_signature,
                'customer_signature_date' => $workOrder->customer_signature_date?->toISOString(),
                'driver_signature' => $workOrder->driver_signature,
                'driver_signature_date' => $workOrder->driver_signature_date?->toISOString(),
                'completed_at' => $workOrder->completed_at?->toISOString(),
            ]
        ]);
    }

    /**
     * Update work order status
     */
    public function updateStatus(Request $request, $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:in_progress,completed,cancelled'
        ]);

        $user = $request->user();
        $isAdmin = $user->companies()->count() > 1;
        
        if ($isAdmin) {
            $companyId = $request->header('X-Company-Id') ?? $user->companies()->first()?->id;
            $workOrder = WorkOrder::where('id', $id)
                ->where('company_id', $companyId)
                ->first();
        } else {
            $driver = Driver::where('user_id', $user->id)->first();
            
            if (!$driver) {
                return response()->json(['message' => 'Driver not found'], 404);
            }

            $workOrder = WorkOrder::where('id', $id)
                ->where('driver_id', $driver->id)
                ->where('company_id', $driver->company_id)
                ->first();
        }

        if (!$workOrder) {
            return response()->json(['message' => 'Work order not found'], 404);
        }

        $workOrder->status = WorkOrderStatus::from($request->status);
        
        if ($request->status === 'completed') {
            $workOrder->completed_at = Carbon::now();
        }
        
        $workOrder->save();

        return response()->json([
            'message' => 'Status updated successfully',
            'work_order' => [
                'id' => $workOrder->id,
                'status' => $workOrder->status->value,
                'completed_at' => $workOrder->completed_at?->toISOString()
            ]
        ]);
    }

    /**
     * Add signature to work order
     */
    public function addSignature(Request $request, $id): JsonResponse
    {
        $request->validate([
            'type' => 'required|in:customer,driver',
            'signature' => 'required|string', // Base64 encoded image
            'signed_by' => 'nullable|string'
        ]);

        $user = $request->user();
        $isAdmin = $user->companies()->count() > 1;
        
        if ($isAdmin) {
            $companyId = $request->header('X-Company-Id') ?? $user->companies()->first()?->id;
            $workOrder = WorkOrder::where('id', $id)
                ->where('company_id', $companyId)
                ->first();
        } else {
            $driver = Driver::where('user_id', $user->id)->first();
            
            if (!$driver) {
                return response()->json(['message' => 'Driver not found'], 404);
            }

            $workOrder = WorkOrder::where('id', $id)
                ->where('driver_id', $driver->id)
                ->where('company_id', $driver->company_id)
                ->first();
        }

        if (!$workOrder) {
            return response()->json(['message' => 'Work order not found'], 404);
        }

        // Save signature as file
        $signatureData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $request->signature));
        $fileName = 'signatures/' . $workOrder->ticket_number . '_' . $request->type . '_' . time() . '.png';
        Storage::disk('public')->put($fileName, $signatureData);

        if ($request->type === 'customer') {
            $workOrder->customer_signature = $fileName;
            $workOrder->customer_signature_date = Carbon::now();
        } else {
            $workOrder->driver_signature = $fileName;
            $workOrder->driver_signature_date = Carbon::now();
        }

        $workOrder->save();

        return response()->json([
            'message' => 'Signature added successfully',
            'signature_url' => Storage::url($fileName)
        ]);
    }

    /**
     * Add photos to work order
     */
    public function addPhotos(Request $request, $id): JsonResponse
    {
        $request->validate([
            'photos' => 'required|array',
            'photos.*' => 'required|string', // Base64 encoded images
            'descriptions' => 'nullable|array',
            'descriptions.*' => 'nullable|string'
        ]);

        $user = $request->user();
        $isAdmin = $user->companies()->count() > 1;
        
        if ($isAdmin) {
            $companyId = $request->header('X-Company-Id') ?? $user->companies()->first()?->id;
            $workOrder = WorkOrder::where('id', $id)
                ->where('company_id', $companyId)
                ->first();
        } else {
            $driver = Driver::where('user_id', $user->id)->first();
            
            if (!$driver) {
                return response()->json(['message' => 'Driver not found'], 404);
            }

            $workOrder = WorkOrder::where('id', $id)
                ->where('driver_id', $driver->id)
                ->where('company_id', $driver->company_id)
                ->first();
        }

        if (!$workOrder) {
            return response()->json(['message' => 'Work order not found'], 404);
        }

        $savedPhotos = [];
        foreach ($request->photos as $index => $photo) {
            $photoData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $photo));
            $fileName = 'work-orders/' . $workOrder->ticket_number . '/photo_' . time() . '_' . $index . '.jpg';
            Storage::disk('public')->put($fileName, $photoData);
            
            $savedPhotos[] = [
                'url' => Storage::url($fileName),
                'description' => $request->descriptions[$index] ?? null,
                'uploaded_at' => Carbon::now()->toISOString()
            ];
        }

        // Store photos metadata in work order (you may want to create a separate table for this)
        $existingPhotos = json_decode($workOrder->photos ?? '[]', true);
        $workOrder->photos = json_encode(array_merge($existingPhotos, $savedPhotos));
        $workOrder->save();

        return response()->json([
            'message' => 'Photos uploaded successfully',
            'photos' => $savedPhotos
        ]);
    }

    /**
     * Complete work order
     */
    public function complete(Request $request, $id): JsonResponse
    {
        $request->validate([
            'time_off_site' => 'required|date_format:H:i',
            'time_off_site_period' => 'required|in:AM,PM',
            'service_description' => 'nullable|string',
            'comments' => 'nullable|string',
            'container_delivered' => 'nullable|string',
            'container_picked_up' => 'nullable|string',
            'disposal_ticket' => 'nullable|string',
            'cod_amount' => 'nullable|numeric'
        ]);

        $user = $request->user();
        $isAdmin = $user->companies()->count() > 1;
        
        if ($isAdmin) {
            $companyId = $request->header('X-Company-Id') ?? $user->companies()->first()?->id;
            $workOrder = WorkOrder::where('id', $id)
                ->where('company_id', $companyId)
                ->first();
        } else {
            $driver = Driver::where('user_id', $user->id)->first();
            
            if (!$driver) {
                return response()->json(['message' => 'Driver not found'], 404);
            }

            $workOrder = WorkOrder::where('id', $id)
                ->where('driver_id', $driver->id)
                ->where('company_id', $driver->company_id)
                ->first();
        }

        if (!$workOrder) {
            return response()->json(['message' => 'Work order not found'], 404);
        }

        $workOrder->fill($request->only([
            'time_off_site',
            'time_off_site_period',
            'service_description',
            'comments',
            'container_delivered',
            'container_picked_up',
            'disposal_ticket',
            'cod_amount'
        ]));

        $workOrder->status = WorkOrderStatus::COMPLETED;
        $workOrder->completed_at = Carbon::now();
        $workOrder->save();

        return response()->json([
            'message' => 'Work order completed successfully',
            'work_order' => [
                'id' => $workOrder->id,
                'status' => $workOrder->status->value,
                'completed_at' => $workOrder->completed_at->toISOString()
            ]
        ]);
    }
}