<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Get user profile details
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        $driver = Driver::where('user_id', $user->id)->first();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'created_at' => $user->created_at,
            ],
            'driver' => [
                'id' => $driver->id,
                'first_name' => $driver->first_name,
                'last_name' => $driver->last_name,
                'phone' => $driver->phone,
                'license_number' => $driver->license_number,
                'license_state' => $driver->license_state,
                'license_expiry' => $driver->license_expiry,
                'date_of_birth' => $driver->date_of_birth,
                'hire_date' => $driver->hire_date,
                'emergency_contact' => $driver->emergency_contact,
                'emergency_phone' => $driver->emergency_phone,
                'address' => $driver->address,
                'city' => $driver->city,
                'state' => $driver->state,
                'zip' => $driver->zip,
                'status' => $driver->status,
            ],
            'company' => [
                'id' => $driver->company_id,
                'name' => $driver->company->name,
                'address' => $driver->company->address,
                'city' => $driver->company->city,
                'state' => $driver->company->state,
            ],
            'stats' => [
                'total_miles_driven' => $driver->total_miles_driven ?? 0,
                'total_deliveries' => $driver->total_deliveries ?? 0,
                'safety_score' => $driver->safety_score ?? 100,
                'years_of_service' => now()->diffInYears($driver->hire_date),
            ]
        ]);
    }

    /**
     * Update user profile
     */
    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => 'nullable|string|max:20',
            'emergency_contact' => 'nullable|string|max:255',
            'emergency_phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:2',
            'zip' => 'nullable|string|max:10',
        ]);

        $driver = Driver::where('user_id', $request->user()->id)->first();

        if (!$driver) {
            return response()->json(['message' => 'Driver not found'], 404);
        }

        $driver->update([
            'phone' => $request->phone ?? $driver->phone,
            'emergency_contact' => $request->emergency_contact ?? $driver->emergency_contact,
            'emergency_phone' => $request->emergency_phone ?? $driver->emergency_phone,
            'address' => $request->address ?? $driver->address,
            'city' => $request->city ?? $driver->city,
            'state' => $request->state ?? $driver->state,
            'zip' => $request->zip ?? $driver->zip,
        ]);

        return response()->json([
            'message' => 'Profile updated successfully',
            'driver' => $driver
        ]);
    }

    /**
     * Change password
     */
    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = $request->user();

        // Verify current password
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Current password is incorrect'
            ], 422);
        }

        // Update password
        $user->update([
            'password' => Hash::make($request->new_password)
        ]);

        return response()->json([
            'message' => 'Password changed successfully'
        ]);
    }

    /**
     * Upload profile picture
     */
    public function uploadPhoto(Request $request): JsonResponse
    {
        $request->validate([
            'photo' => 'required|string', // Base64 image
        ]);

        $driver = Driver::where('user_id', $request->user()->id)->first();

        if (!$driver) {
            return response()->json(['message' => 'Driver not found'], 404);
        }

        // Process base64 image
        $photoData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $request->photo));
        $photoPath = 'drivers/photos/' . $driver->id . '.jpg';
        
        \Storage::disk('public')->put($photoPath, $photoData);
        
        $driver->update(['photo' => $photoPath]);

        return response()->json([
            'message' => 'Photo uploaded successfully',
            'photo_url' => \Storage::url($photoPath)
        ]);
    }
}