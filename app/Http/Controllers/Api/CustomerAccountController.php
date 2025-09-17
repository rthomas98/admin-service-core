<?php

namespace App\Http\Controllers\Api;

use App\Enums\NotificationCategory;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class CustomerAccountController extends Controller
{
    /**
     * Get customer profile information
     */
    public function profile(Request $request): JsonResponse
    {
        $customer = Auth::guard('customer')->user();
        $customer->load('company');

        return response()->json([
            'profile' => [
                'id' => $customer->id,
                'first_name' => $customer->first_name,
                'last_name' => $customer->last_name,
                'name' => $customer->name,
                'full_name' => $customer->full_name,
                'organization' => $customer->organization,
                'customer_number' => $customer->customer_number,
                'customer_since' => $customer->customer_since?->format('F Y'),
                'emails' => $customer->emails ?? [],
                'primary_email' => $customer->getNotificationEmail(),
                'phone' => $customer->phone,
                'phone_ext' => $customer->phone_ext,
                'secondary_phone' => $customer->secondary_phone,
                'secondary_phone_ext' => $customer->secondary_phone_ext,
                'fax' => $customer->fax,
                'fax_ext' => $customer->fax_ext,
                'address' => $customer->address,
                'secondary_address' => $customer->secondary_address,
                'city' => $customer->city,
                'state' => $customer->state,
                'zip' => $customer->zip,
                'county' => $customer->county,
                'business_type' => $customer->business_type,
                'tax_exempt_reason' => $customer->tax_exempt_reason,
                'delivery_method' => $customer->delivery_method,
                'referral' => $customer->referral,
            ],
            'company' => [
                'name' => $customer->company->name,
                'address' => $customer->company->address,
                'city' => $customer->company->city,
                'state' => $customer->company->state,
                'zip' => $customer->company->zip,
                'phone' => $customer->company->phone,
                'email' => $customer->company->email,
            ],
            'notification_settings' => [
                'notifications_enabled' => $customer->notifications_enabled,
                'preferred_method' => $customer->preferred_notification_method,
                'sms_number' => $customer->sms_number,
                'sms_verified' => $customer->sms_verified,
                'preferences' => $customer->notification_preferences ?? [],
            ],
        ]);
    }

    /**
     * Update customer profile information
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $customer = Auth::guard('customer')->user();

        $validator = Validator::make($request->all(), [
            'first_name' => 'sometimes|required|string|max:255',
            'last_name' => 'sometimes|required|string|max:255',
            'name' => 'nullable|string|max:255',
            'organization' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'phone_ext' => 'nullable|string|max:10',
            'secondary_phone' => 'nullable|string|max:20',
            'secondary_phone_ext' => 'nullable|string|max:10',
            'fax' => 'nullable|string|max:20',
            'fax_ext' => 'nullable|string|max:10',
            'address' => 'nullable|string|max:500',
            'secondary_address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:2',
            'zip' => 'nullable|string|max:10',
            'county' => 'nullable|string|max:100',
            'business_type' => 'nullable|string|max:100',
            'delivery_method' => 'nullable|string|max:100',
            'referral' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $customer->update($request->only([
                'first_name', 'last_name', 'name', 'organization',
                'phone', 'phone_ext', 'secondary_phone', 'secondary_phone_ext',
                'fax', 'fax_ext', 'address', 'secondary_address',
                'city', 'state', 'zip', 'county', 'business_type',
                'delivery_method', 'referral',
            ]));

            return response()->json([
                'message' => 'Profile updated successfully',
                'profile' => [
                    'id' => $customer->id,
                    'full_name' => $customer->full_name,
                    'updated_at' => $customer->updated_at->format('M j, Y g:i A'),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error updating profile',
                'error' => 'An unexpected error occurred. Please try again.',
            ], 500);
        }
    }

    /**
     * Update customer password
     */
    public function updatePassword(Request $request): JsonResponse
    {
        $customer = Auth::guard('customer')->user();

        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'password' => ['required', 'string', Password::min(8)->mixedCase()->numbers()],
            'password_confirmation' => 'required|string|same:password',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Verify current password
        if (! Hash::check($request->current_password, $customer->portal_password)) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => [
                    'current_password' => ['The current password is incorrect.'],
                ],
            ], 422);
        }

        try {
            $customer->update([
                'portal_password' => Hash::make($request->password),
            ]);

            return response()->json([
                'message' => 'Password updated successfully',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error updating password',
                'error' => 'An unexpected error occurred. Please try again.',
            ], 500);
        }
    }

    /**
     * Update customer notification settings
     */
    public function updateNotifications(Request $request): JsonResponse
    {
        $customer = Auth::guard('customer')->user();

        $validator = Validator::make($request->all(), [
            'notifications_enabled' => 'boolean',
            'preferred_method' => 'nullable|string|in:email,sms,both',
            'sms_number' => 'nullable|string|max:20',
            'preferences' => 'nullable|array',
            'preferences.*.enabled' => 'boolean',
            'preferences.*.method' => 'string|in:email,sms,both',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $updateData = [];

            if ($request->has('notifications_enabled')) {
                $updateData['notifications_enabled'] = $request->notifications_enabled;
            }

            if ($request->has('preferred_method')) {
                $updateData['preferred_notification_method'] = $request->preferred_method;
            }

            if ($request->has('sms_number')) {
                $updateData['sms_number'] = $request->sms_number;
                // Reset SMS verification if number changed
                if ($customer->sms_number !== $request->sms_number) {
                    $updateData['sms_verified'] = false;
                    $updateData['sms_verified_at'] = null;
                }
            }

            if ($request->has('preferences')) {
                // Validate that all category values are valid
                $validCategories = array_column(NotificationCategory::cases(), 'value');
                $preferences = $request->preferences;

                foreach ($preferences as $category => $settings) {
                    if (! in_array($category, $validCategories)) {
                        return response()->json([
                            'message' => 'Invalid notification category',
                            'error' => "Category '{$category}' is not valid",
                        ], 422);
                    }
                }

                $updateData['notification_preferences'] = $preferences;
            }

            if (! empty($updateData)) {
                $customer->update($updateData);
            }

            return response()->json([
                'message' => 'Notification settings updated successfully',
                'notification_settings' => [
                    'notifications_enabled' => $customer->notifications_enabled,
                    'preferred_method' => $customer->preferred_notification_method,
                    'sms_number' => $customer->sms_number,
                    'sms_verified' => $customer->sms_verified,
                    'preferences' => $customer->notification_preferences ?? [],
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error updating notification settings',
                'error' => 'An unexpected error occurred. Please try again.',
            ], 500);
        }
    }
}
