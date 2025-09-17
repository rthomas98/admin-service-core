<?php

namespace App\Http\Controllers\Api;

use App\Enums\ServiceRequestStatus;
use App\Http\Controllers\Controller;
use App\Mail\ServiceRequestCreated;
use App\Models\ServiceRequest;
use App\Models\ServiceRequestActivity;
use App\Models\ServiceRequestAttachment;
use App\Services\ServiceRequestFileUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class CustomerServiceRequestController extends Controller
{
    public function __construct(
        private ServiceRequestFileUploadService $fileUploadService
    ) {}

    /**
     * Display a listing of customer service requests
     */
    public function index(Request $request): JsonResponse
    {
        $customer = Auth::guard('customer')->user();

        $query = ServiceRequest::where('customer_id', $customer->id)
            ->with(['assignedTo:id,name'])
            ->withCount(['attachments', 'publicActivities as activity_count']);

        // Apply filters
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->has('priority') && $request->priority !== 'all') {
            $query->where('priority', $request->priority);
        }

        if ($request->has('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        if ($request->has('search') && ! empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('customer_notes', 'like', "%{$search}%");
            });
        }

        if ($request->has('date_from') && ! empty($request->date_from)) {
            $query->whereDate('requested_date', '>=', $request->date_from);
        }

        if ($request->has('date_to') && ! empty($request->date_to)) {
            $query->whereDate('requested_date', '<=', $request->date_to);
        }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        if (in_array($sortBy, ['created_at', 'requested_date', 'scheduled_date', 'priority', 'status'])) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->latest();
        }

        $perPage = min($request->get('per_page', 15), 50);
        $serviceRequests = $query->paginate($perPage);

        return response()->json([
            'service_requests' => $serviceRequests->map(function ($request) {
                return [
                    'id' => $request->id,
                    'title' => $request->title,
                    'description' => $request->description,
                    'status' => $request->status->value,
                    'status_label' => $request->status->getLabel(),
                    'priority' => $request->priority,
                    'priority_label' => $request->priority_label,
                    'priority_color' => $request->priority_color,
                    'category' => $request->category,
                    'requested_date' => $request->requested_date?->format('Y-m-d'),
                    'formatted_requested_date' => $request->requested_date?->format('M j, Y'),
                    'scheduled_date' => $request->scheduled_date?->format('Y-m-d'),
                    'formatted_scheduled_date' => $request->scheduled_date?->format('M j, Y g:i A'),
                    'completed_date' => $request->completed_date?->format('Y-m-d'),
                    'formatted_completed_date' => $request->completed_date?->format('M j, Y'),
                    'estimated_cost' => $request->estimated_cost ? number_format($request->estimated_cost, 2) : null,
                    'actual_cost' => $request->actual_cost ? number_format($request->actual_cost, 2) : null,
                    'assigned_to' => $request->assignedTo ? [
                        'id' => $request->assignedTo->id,
                        'name' => $request->assignedTo->name,
                    ] : null,
                    'is_overdue' => $request->isOverdue(),
                    'days_until_scheduled' => $request->days_until_scheduled,
                    'attachments_count' => $request->attachments_count ?? 0,
                    'activity_count' => $request->activity_count ?? 0,
                    'has_attachments' => ($request->attachments_count ?? 0) > 0,
                    'created_at' => $request->created_at->format('M j, Y'),
                    'can_cancel' => in_array($request->status, [ServiceRequestStatus::PENDING, ServiceRequestStatus::ON_HOLD]),
                ];
            }),
            'pagination' => [
                'current_page' => $serviceRequests->currentPage(),
                'last_page' => $serviceRequests->lastPage(),
                'per_page' => $serviceRequests->perPage(),
                'total' => $serviceRequests->total(),
                'from' => $serviceRequests->firstItem(),
                'to' => $serviceRequests->lastItem(),
            ],
            'filters' => [
                'status' => $request->get('status', 'all'),
                'priority' => $request->get('priority', 'all'),
                'category' => $request->get('category', 'all'),
                'search' => $request->get('search', ''),
                'date_from' => $request->get('date_from', ''),
                'date_to' => $request->get('date_to', ''),
                'sort_by' => $sortBy,
                'sort_order' => $sortOrder,
            ],
        ]);
    }

    /**
     * Store a newly created service request
     */
    public function store(Request $request): JsonResponse
    {
        $customer = Auth::guard('customer')->user();

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|in:low,medium,high,urgent',
            'category' => 'nullable|string|max:100',
            'requested_date' => 'nullable|date|after_or_equal:today',
            'details' => 'nullable|array',
            'customer_notes' => 'nullable|string',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'file|max:10240|mimes:jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,txt',
            'attachment_descriptions' => 'nullable|array',
            'attachment_descriptions.*' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $serviceRequest = ServiceRequest::create([
                'customer_id' => $customer->id,
                'company_id' => $customer->company_id,
                'title' => $request->title,
                'description' => $request->description,
                'priority' => $request->priority,
                'category' => $request->category,
                'requested_date' => $request->requested_date ? now()->parse($request->requested_date) : now(),
                'status' => ServiceRequestStatus::PENDING,
                'details' => $request->details,
                'customer_notes' => $request->customer_notes,
            ]);

            // Handle file uploads if present
            $attachments = [];
            if ($request->hasFile('attachments')) {
                $files = $request->file('attachments');
                $descriptions = $request->get('attachment_descriptions', []);

                foreach ($files as $index => $file) {
                    $description = $descriptions[$index] ?? null;
                    $attachment = $this->fileUploadService->uploadFile(
                        $serviceRequest,
                        $file,
                        $customer,
                        $description
                    );
                    $attachments[] = [
                        'id' => $attachment->id,
                        'filename' => $attachment->original_filename,
                        'size' => $attachment->getFileSizeFormatted(),
                        'type' => $attachment->mime_type,
                    ];
                }
            }

            // Send email notification
            try {
                Mail::send(new ServiceRequestCreated($serviceRequest));
            } catch (\Exception $e) {
                // Log email error but don't fail the request creation
                \Log::error('Failed to send service request created email: '.$e->getMessage());
            }

            return response()->json([
                'message' => 'Service request created successfully',
                'service_request' => [
                    'id' => $serviceRequest->id,
                    'title' => $serviceRequest->title,
                    'status' => $serviceRequest->status->value,
                    'priority' => $serviceRequest->priority,
                    'attachments_count' => count($attachments),
                    'attachments' => $attachments,
                    'created_at' => $serviceRequest->created_at->format('M j, Y'),
                ],
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error creating service request',
                'error' => 'An unexpected error occurred. Please try again.',
            ], 500);
        }
    }

    /**
     * Display the specified service request
     */
    public function show(ServiceRequest $serviceRequest): JsonResponse
    {
        $customer = Auth::guard('customer')->user();

        // Ensure the service request belongs to the authenticated customer
        if ($serviceRequest->customer_id !== $customer->id) {
            abort(404);
        }

        $serviceRequest->load([
            'assignedTo:id,name,email',
            'attachments:id,service_request_id,original_filename,file_path,file_size,mime_type,description,created_at',
            'publicActivities.user:id,name',
        ]);

        return response()->json([
            'service_request' => [
                'id' => $serviceRequest->id,
                'title' => $serviceRequest->title,
                'description' => $serviceRequest->description,
                'status' => $serviceRequest->status->value,
                'status_label' => $serviceRequest->status->getLabel(),
                'priority' => $serviceRequest->priority,
                'priority_label' => $serviceRequest->priority_label,
                'priority_color' => $serviceRequest->priority_color,
                'category' => $serviceRequest->category,
                'details' => $serviceRequest->details ?? [],
                'requested_date' => $serviceRequest->requested_date?->format('Y-m-d'),
                'formatted_requested_date' => $serviceRequest->requested_date?->format('F j, Y'),
                'scheduled_date' => $serviceRequest->scheduled_date?->format('Y-m-d H:i'),
                'formatted_scheduled_date' => $serviceRequest->scheduled_date?->format('F j, Y \a\t g:i A'),
                'completed_date' => $serviceRequest->completed_date?->format('Y-m-d'),
                'formatted_completed_date' => $serviceRequest->completed_date?->format('F j, Y'),
                'estimated_cost' => $serviceRequest->estimated_cost ? number_format($serviceRequest->estimated_cost, 2) : null,
                'actual_cost' => $serviceRequest->actual_cost ? number_format($serviceRequest->actual_cost, 2) : null,
                'customer_notes' => $serviceRequest->customer_notes,
                'internal_notes' => null, // Don't expose internal notes to customers
                'assigned_to' => $serviceRequest->assignedTo ? [
                    'id' => $serviceRequest->assignedTo->id,
                    'name' => $serviceRequest->assignedTo->name,
                    'email' => $serviceRequest->assignedTo->email,
                ] : null,
                'is_overdue' => $serviceRequest->isOverdue(),
                'days_until_scheduled' => $serviceRequest->days_until_scheduled,
                'created_at' => $serviceRequest->created_at->format('M j, Y g:i A'),
                'updated_at' => $serviceRequest->updated_at->format('M j, Y g:i A'),
                'can_cancel' => in_array($serviceRequest->status, [ServiceRequestStatus::PENDING, ServiceRequestStatus::ON_HOLD]),
                'attachments' => $serviceRequest->attachments->map(function ($attachment) {
                    return [
                        'id' => $attachment->id,
                        'filename' => $attachment->original_filename,
                        'size' => $attachment->getFileSizeFormatted(),
                        'size_bytes' => $attachment->file_size,
                        'type' => $attachment->mime_type,
                        'is_image' => $attachment->isImage(),
                        'is_pdf' => $attachment->isPdf(),
                        'is_document' => $attachment->isDocument(),
                        'description' => $attachment->description,
                        'uploaded_at' => $attachment->created_at->format('M j, Y g:i A'),
                        'preview_url' => $attachment->isImage() ? $attachment->getFileUrl() : null,
                        'download_url' => route('api.service-requests.attachments.download', $attachment),
                    ];
                }),
                'attachments_count' => $serviceRequest->attachments->count(),
                'timeline' => $this->getEnhancedRequestTimeline($serviceRequest),
            ],
        ]);
    }

    /**
     * Update the specified service request
     */
    public function update(Request $request, ServiceRequest $serviceRequest): JsonResponse
    {
        $customer = Auth::guard('customer')->user();

        // Ensure the service request belongs to the authenticated customer
        if ($serviceRequest->customer_id !== $customer->id) {
            abort(404);
        }

        // Only allow updates if request is pending or on hold
        if (! in_array($serviceRequest->status, [ServiceRequestStatus::PENDING, ServiceRequestStatus::ON_HOLD])) {
            return response()->json([
                'message' => 'Service request cannot be updated in its current status',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'priority' => 'sometimes|required|in:low,medium,high,urgent',
            'category' => 'nullable|string|max:100',
            'requested_date' => 'nullable|date|after_or_equal:today',
            'details' => 'nullable|array',
            'customer_notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $serviceRequest->update($request->only([
                'title', 'description', 'priority', 'category',
                'requested_date', 'details', 'customer_notes',
            ]));

            return response()->json([
                'message' => 'Service request updated successfully',
                'service_request' => [
                    'id' => $serviceRequest->id,
                    'title' => $serviceRequest->title,
                    'status' => $serviceRequest->status->value,
                    'priority' => $serviceRequest->priority,
                    'updated_at' => $serviceRequest->updated_at->format('M j, Y g:i A'),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error updating service request',
                'error' => 'An unexpected error occurred. Please try again.',
            ], 500);
        }
    }

    /**
     * Cancel the specified service request
     */
    public function cancel(ServiceRequest $serviceRequest): JsonResponse
    {
        $customer = Auth::guard('customer')->user();

        // Ensure the service request belongs to the authenticated customer
        if ($serviceRequest->customer_id !== $customer->id) {
            abort(404);
        }

        // Only allow cancellation if request is pending or on hold
        if (! in_array($serviceRequest->status, [ServiceRequestStatus::PENDING, ServiceRequestStatus::ON_HOLD])) {
            return response()->json([
                'message' => 'Service request cannot be cancelled in its current status',
            ], 403);
        }

        try {
            $serviceRequest->markCancelled();

            return response()->json([
                'message' => 'Service request cancelled successfully',
                'service_request' => [
                    'id' => $serviceRequest->id,
                    'title' => $serviceRequest->title,
                    'status' => $serviceRequest->status->value,
                    'updated_at' => $serviceRequest->updated_at->format('M j, Y g:i A'),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error cancelling service request',
                'error' => 'An unexpected error occurred. Please try again.',
            ], 500);
        }
    }

    /**
     * Get enhanced timeline for a service request using actual activity data
     */
    private function getEnhancedRequestTimeline(ServiceRequest $serviceRequest): array
    {
        return $serviceRequest->publicActivities->map(function ($activity) {
            return [
                'id' => $activity->id,
                'type' => $activity->activity_type,
                'title' => $activity->title,
                'description' => $activity->description,
                'user' => [
                    'id' => $activity->user->id,
                    'name' => $activity->user->name,
                ],
                'performed_at' => $activity->performed_at->format('Y-m-d H:i:s'),
                'formatted_date' => $activity->performed_at->format('M j, Y g:i A'),
                'time_ago' => $activity->performed_at->diffForHumans(),
                'icon' => $this->getActivityIcon($activity->activity_type),
                'color' => $this->getActivityColor($activity->activity_type),
                'old_values' => $activity->old_values,
                'new_values' => $activity->new_values,
            ];
        })->sortByDesc('performed_at')->values()->toArray();
    }

    /**
     * Get icon for activity type
     */
    private function getActivityIcon(string $activityType): string
    {
        return match ($activityType) {
            ServiceRequestActivity::TYPE_CREATED => 'plus-circle',
            ServiceRequestActivity::TYPE_STATUS_CHANGED => 'arrow-path',
            ServiceRequestActivity::TYPE_ASSIGNED => 'user-plus',
            ServiceRequestActivity::TYPE_UNASSIGNED => 'user-minus',
            ServiceRequestActivity::TYPE_COMMENT => 'chat-bubble-left',
            ServiceRequestActivity::TYPE_ATTACHMENT_ADDED => 'paper-clip',
            ServiceRequestActivity::TYPE_ATTACHMENT_REMOVED => 'x-circle',
            ServiceRequestActivity::TYPE_SCHEDULED => 'calendar',
            ServiceRequestActivity::TYPE_COST_UPDATED => 'currency-dollar',
            ServiceRequestActivity::TYPE_PRIORITY_CHANGED => 'exclamation-triangle',
            ServiceRequestActivity::TYPE_CATEGORY_CHANGED => 'tag',
            ServiceRequestActivity::TYPE_UPDATED => 'pencil',
            default => 'information-circle',
        };
    }

    /**
     * Get color for activity type
     */
    private function getActivityColor(string $activityType): string
    {
        return match ($activityType) {
            ServiceRequestActivity::TYPE_CREATED => 'green',
            ServiceRequestActivity::TYPE_STATUS_CHANGED => 'blue',
            ServiceRequestActivity::TYPE_ASSIGNED => 'blue',
            ServiceRequestActivity::TYPE_UNASSIGNED => 'gray',
            ServiceRequestActivity::TYPE_COMMENT => 'blue',
            ServiceRequestActivity::TYPE_ATTACHMENT_ADDED => 'green',
            ServiceRequestActivity::TYPE_ATTACHMENT_REMOVED => 'red',
            ServiceRequestActivity::TYPE_SCHEDULED => 'yellow',
            ServiceRequestActivity::TYPE_COST_UPDATED => 'yellow',
            ServiceRequestActivity::TYPE_PRIORITY_CHANGED => 'orange',
            ServiceRequestActivity::TYPE_CATEGORY_CHANGED => 'blue',
            ServiceRequestActivity::TYPE_UPDATED => 'gray',
            default => 'gray',
        };
    }

    /**
     * Add attachment to service request
     */
    public function addAttachment(Request $request, ServiceRequest $serviceRequest): JsonResponse
    {
        $customer = Auth::guard('customer')->user();

        // Ensure the service request belongs to the authenticated customer
        if ($serviceRequest->customer_id !== $customer->id) {
            abort(404);
        }

        // Only allow file uploads if request is not completed or cancelled
        if (in_array($serviceRequest->status, [ServiceRequestStatus::COMPLETED, ServiceRequestStatus::CANCELLED])) {
            return response()->json([
                'message' => 'Cannot add files to a completed or cancelled service request',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'file' => 'required|file|max:10240|mimes:jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,txt',
            'description' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $attachment = $this->fileUploadService->uploadFile(
                $serviceRequest,
                $request->file('file'),
                $customer,
                $request->get('description')
            );

            return response()->json([
                'message' => 'File uploaded successfully',
                'attachment' => [
                    'id' => $attachment->id,
                    'filename' => $attachment->original_filename,
                    'size' => $attachment->getFileSizeFormatted(),
                    'type' => $attachment->mime_type,
                    'description' => $attachment->description,
                    'uploaded_at' => $attachment->created_at->format('M j, Y g:i A'),
                ],
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error uploading file',
                'error' => 'An unexpected error occurred. Please try again.',
            ], 500);
        }
    }

    /**
     * Download attachment
     */
    public function downloadAttachment(ServiceRequestAttachment $attachment): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $customer = Auth::guard('customer')->user();

        // Ensure the attachment belongs to a service request owned by the customer
        if ($attachment->serviceRequest->customer_id !== $customer->id) {
            abort(404);
        }

        return $this->fileUploadService->downloadFile($attachment);
    }

    /**
     * Add comment to service request
     */
    public function addComment(Request $request, ServiceRequest $serviceRequest): JsonResponse
    {
        $customer = Auth::guard('customer')->user();

        // Ensure the service request belongs to the authenticated customer
        if ($serviceRequest->customer_id !== $customer->id) {
            abort(404);
        }

        $validator = Validator::make($request->all(), [
            'comment' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $serviceRequest->addComment($request->comment, $customer, false);

            return response()->json([
                'message' => 'Comment added successfully',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error adding comment',
                'error' => 'An unexpected error occurred. Please try again.',
            ], 500);
        }
    }
}
