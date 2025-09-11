<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login for field app users (drivers)
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'required',
            'company_id' => 'required|exists:companies,id',
        ]);

        // Check if user exists
        $user = User::where('email', $request->email)->first();
        
        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Check if user is a driver for the specified company
        $driver = Driver::where('user_id', $user->id)
            ->where('company_id', $request->company_id)
            ->where('status', 'active')
            ->first();

        // If not a driver, check if user is an admin/manager for the company
        $company = null;
        if (!$driver) {
            // Check if user has admin/manager access to this company
            $companyAccess = $user->companies()
                ->where('companies.id', $request->company_id)
                ->first();
            
            if (!$companyAccess) {
                throw ValidationException::withMessages([
                    'email' => ['You are not authorized to access this company.'],
                ]);
            }
            
            $company = $companyAccess;
        } else {
            // Get company details from driver
            $company = $driver->company;
        }

        // Create token with driver abilities
        $token = $user->createToken($request->device_name, [
            'driver:read',
            'driver:write',
            'work-orders:read',
            'work-orders:write',
            'inspections:write',
            'fuel-logs:write',
            'assignments:read',
            'assignments:write'
        ])->plainTextToken;

        // Prepare response data
        $responseData = [
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'company' => [
                'id' => $company->id,
                'name' => $company->name,
                'type' => $company->type ?? 'transport',
            ],
            'permissions' => [
                'driver:read',
                'driver:write',
                'work-orders:read',
                'work-orders:write',
                'inspections:write',
                'fuel-logs:write',
                'assignments:read',
                'assignments:write'
            ]
        ];

        // Add driver data if user is a driver
        if ($driver) {
            $responseData['driver'] = [
                'id' => $driver->id,
                'first_name' => $driver->first_name,
                'last_name' => $driver->last_name,
                'license_number' => $driver->license_number,
                'license_class' => $driver->license_class,
                'license_expiry_date' => $driver->license_expiry_date,
                'status' => $driver->status,
            ];
        } else {
            // For admin users without driver records
            $responseData['driver'] = null;
            $responseData['isAdmin'] = true;
        }

        return response()->json($responseData);
    }

    /**
     * Logout
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }

    /**
     * Refresh token
     */
    public function refresh(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->currentAccessToken()->delete();

        $token = $user->createToken($request->device_name ?? 'mobile', [
            'driver:read',
            'driver:write',
            'work-orders:read',
            'work-orders:write',
            'inspections:write',
            'fuel-logs:write',
            'assignments:read',
            'assignments:write'
        ])->plainTextToken;

        return response()->json([
            'token' => $token
        ]);
    }

    /**
     * Get current user info
     */
    public function user(Request $request): JsonResponse
    {
        $user = $request->user();
        $driver = Driver::where('user_id', $user->id)->first();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'driver' => $driver ? [
                'id' => $driver->id,
                'first_name' => $driver->first_name,
                'last_name' => $driver->last_name,
                'license_number' => $driver->license_number,
                'license_class' => $driver->license_class,
                'license_expiry_date' => $driver->license_expiry_date,
                'status' => $driver->status,
            ] : null,
            'company' => $driver ? [
                'id' => $driver->company_id,
                'name' => $driver->company->name,
                'type' => $driver->company->type,
            ] : null,
        ]);
    }

    /**
     * Get companies where user is a driver or admin
     */
    public function companies(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $user = User::where('email', $request->email)->first();
        
        if (!$user) {
            return response()->json([
                'companies' => []
            ]);
        }

        // Check if user is a driver
        $driverCompanies = Driver::where('user_id', $user->id)
            ->where('status', 'active')
            ->with('company')
            ->get()
            ->map(function ($driver) {
                return [
                    'id' => $driver->company_id,
                    'name' => $driver->company->name,
                    'type' => $driver->company->type,
                ];
            });

        // Check if user is an admin/manager with company associations
        $adminCompanies = $user->companies()
            ->get()
            ->map(function ($company) {
                return [
                    'id' => $company->id,
                    'name' => $company->name,
                    'type' => $company->type,
                ];
            });

        // Convert both to collections and merge
        $companies = collect($driverCompanies)
            ->concat($adminCompanies)
            ->unique('id')
            ->values();

        return response()->json([
            'companies' => $companies
        ]);
    }
}