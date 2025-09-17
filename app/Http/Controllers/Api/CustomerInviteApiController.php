<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CustomerInviteResource;
use App\Mail\CustomerInvitationMail;
use App\Models\Customer;
use App\Models\CustomerInvite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class CustomerInviteApiController extends Controller
{
    /**
     * Display a listing of customer invitations
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = CustomerInvite::query()
            ->with(['customer', 'invitedBy', 'company']);

        // Apply filters
        if ($request->has('status')) {
            switch ($request->status) {
                case 'pending':
                    $query->pending();
                    break;
                case 'accepted':
                    $query->accepted();
                    break;
                case 'expired':
                    $query->expired();
                    break;
                case 'valid':
                    $query->valid();
                    break;
            }
        }

        if ($request->has('customer_id')) {
            $query->forCustomer($request->customer_id);
        }

        if ($request->has('email')) {
            $query->forEmail($request->email);
        }

        // Apply sorting
        $sortField = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        // Paginate results
        $perPage = $request->get('per_page', 15);
        $invites = $query->paginate($perPage);

        return CustomerInviteResource::collection($invites);
    }

    /**
     * Store a newly created invitation
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
            'customer_id' => 'nullable|exists:customers,id',
            'expires_at' => 'nullable|date|after:now',
            'send_email' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Check for existing active invitation
        $existingInvite = CustomerInvite::where('email', $request->email)
            ->active()
            ->first();

        if ($existingInvite) {
            return response()->json([
                'message' => 'An active invitation already exists for this email address',
                'invite' => new CustomerInviteResource($existingInvite),
            ], 409);
        }

        DB::beginTransaction();

        try {
            // Create the invitation
            $invite = CustomerInvite::create([
                'email' => $request->email,
                'customer_id' => $request->customer_id,
                'company_id' => Auth::user()->current_company_id,
                'invited_by' => Auth::id(),
                'expires_at' => $request->expires_at ?? now()->addDays(7),
                'token' => bin2hex(random_bytes(32)),
                'is_active' => true,
            ]);

            // Send invitation email if requested
            if ($request->get('send_email', true)) {
                $registrationUrl = route('customer.register.form', [
                    'token' => $invite->token,
                ]);

                Mail::to($invite->email)->send(
                    new CustomerInvitationMail($invite, $registrationUrl)
                );
            }

            DB::commit();

            return response()->json([
                'message' => 'Invitation created successfully',
                'data' => new CustomerInviteResource($invite),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to create invitation',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified invitation
     */
    public function show(CustomerInvite $invite): CustomerInviteResource
    {
        $invite->load(['customer', 'invitedBy', 'company']);

        return new CustomerInviteResource($invite);
    }

    /**
     * Resend an invitation email
     */
    public function resend(CustomerInvite $invite): JsonResponse
    {
        if ($invite->isAccepted()) {
            return response()->json([
                'message' => 'Cannot resend an accepted invitation',
            ], 400);
        }

        try {
            // Regenerate token and extend expiration
            $invite->regenerateToken();
            $invite->extendExpiration(7);

            // Generate registration URL
            $registrationUrl = route('customer.register.form', [
                'token' => $invite->token,
            ]);

            // Send the invitation email
            Mail::to($invite->email)->send(
                new CustomerInvitationMail($invite, $registrationUrl)
            );

            return response()->json([
                'message' => 'Invitation resent successfully',
                'data' => new CustomerInviteResource($invite),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to resend invitation',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cancel/delete an invitation
     */
    public function destroy(CustomerInvite $invite): JsonResponse
    {
        if ($invite->isAccepted()) {
            return response()->json([
                'message' => 'Cannot delete an accepted invitation',
            ], 400);
        }

        try {
            $invite->delete();

            return response()->json([
                'message' => 'Invitation cancelled successfully',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to cancel invitation',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Bulk create invitations
     */
    public function bulkCreate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|exists:customers,id',
            'emails' => 'required|array|min:1|max:50',
            'emails.*' => 'required|email',
            'expires_at' => 'nullable|date|after:now',
            'send_emails' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $result = CustomerInvite::createBulk(
                $request->customer_id,
                $request->emails,
                Auth::id()
            );

            // Send emails if requested
            if ($request->get('send_emails', true)) {
                foreach ($result['created'] as $invite) {
                    $registrationUrl = route('customer.register.form', [
                        'token' => $invite->token,
                    ]);

                    Mail::to($invite->email)->send(
                        new CustomerInvitationMail($invite, $registrationUrl)
                    );
                }
            }

            return response()->json([
                'message' => 'Bulk invitations processed',
                'created' => count($result['created']),
                'skipped' => count($result['skipped']),
                'details' => [
                    'created' => CustomerInviteResource::collection(collect($result['created'])),
                    'skipped' => $result['skipped'],
                ],
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create bulk invitations',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get invitation statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        $customerId = $request->get('customer_id');
        $stats = CustomerInvite::getStatistics($customerId);

        // Add time-based statistics
        $companyId = Auth::user()->current_company_id;

        $today = CustomerInvite::query()
            ->where('company_id', $companyId)
            ->whereDate('created_at', today())
            ->count();

        $thisWeek = CustomerInvite::query()
            ->where('company_id', $companyId)
            ->where('created_at', '>=', now()->startOfWeek())
            ->count();

        $thisMonth = CustomerInvite::query()
            ->where('company_id', $companyId)
            ->where('created_at', '>=', now()->startOfMonth())
            ->count();

        // Add expiring soon count
        $expiringSoon = CustomerInvite::query()
            ->where('company_id', $companyId)
            ->valid()
            ->where('expires_at', '<=', now()->addDays(2))
            ->count();

        return response()->json([
            'statistics' => array_merge($stats, [
                'sent_today' => $today,
                'sent_this_week' => $thisWeek,
                'sent_this_month' => $thisMonth,
                'expiring_soon' => $expiringSoon,
            ]),
        ]);
    }

    /**
     * Extend expiration for multiple invitations
     */
    public function extendExpiration(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'invite_ids' => 'required|array|min:1',
            'invite_ids.*' => 'required|exists:customer_invites,id',
            'days' => 'required|integer|min:1|max:30',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $count = 0;
        $failed = [];

        foreach ($request->invite_ids as $inviteId) {
            $invite = CustomerInvite::find($inviteId);

            if (! $invite || $invite->isAccepted()) {
                $failed[] = $inviteId;

                continue;
            }

            $invite->extendExpiration($request->days);
            $count++;
        }

        return response()->json([
            'message' => "Extended expiration for {$count} invitations",
            'extended' => $count,
            'failed' => $failed,
        ]);
    }

    /**
     * Cleanup expired invitations
     */
    public function cleanup(): JsonResponse
    {
        try {
            $count = CustomerInvite::cleanupExpired();

            return response()->json([
                'message' => "Cleaned up {$count} expired invitations",
                'count' => $count,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to cleanup expired invitations',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
