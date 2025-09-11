<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use Carbon\Carbon;

class ForgotPasswordController extends Controller
{
    /**
     * Send a password reset link to the given user.
     */
    public function sendResetLink(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        try {
            // Find the user
            $user = User::where('email', $request->email)->first();
            
            if (!$user) {
                return response()->json([
                    'message' => 'If an account exists with this email, you will receive a password reset link.'
                ], 200);
            }

            // Generate a token
            $token = Str::random(64);
            
            // Store the token in password_resets table
            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $request->email],
                [
                    'email' => $request->email,
                    'token' => Hash::make($token),
                    'created_at' => Carbon::now()
                ]
            );
            
            // In production, you would send an email here
            // For now, we'll return the token in development mode
            $resetUrl = config('app.url') . '/reset-password?token=' . $token . '&email=' . urlencode($request->email);
            
            // Send password reset email
            $status = Password::sendResetLink(
                $request->only('email')
            );

            if ($status === Password::RESET_LINK_SENT) {
                return response()->json([
                    'message' => 'Password reset link sent to your email.',
                    'status' => 'success'
                ], 200);
            }

            return response()->json([
                'message' => 'Unable to send reset link. Please try again later.',
                'status' => 'error'
            ], 500);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred. Please try again later.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Validate the reset token
     */
    public function validateToken(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required|string',
        ]);

        // Check if token exists and is valid
        $passwordReset = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$passwordReset) {
            return response()->json([
                'message' => 'Invalid or expired reset token.',
                'valid' => false
            ], 400);
        }

        // Check if token matches
        if (!Hash::check($request->token, $passwordReset->token)) {
            return response()->json([
                'message' => 'Invalid or expired reset token.',
                'valid' => false
            ], 400);
        }

        // Check if token is not expired (valid for 1 hour)
        $tokenCreated = Carbon::parse($passwordReset->created_at);
        if ($tokenCreated->addHours(1)->isPast()) {
            return response()->json([
                'message' => 'Reset token has expired. Please request a new one.',
                'valid' => false
            ], 400);
        }

        return response()->json([
            'message' => 'Token is valid.',
            'valid' => true
        ], 200);
    }

    /**
     * Reset the given user's password.
     */
    public function reset(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        try {
            // Verify token first
            $passwordReset = DB::table('password_reset_tokens')
                ->where('email', $request->email)
                ->first();

            if (!$passwordReset) {
                return response()->json([
                    'message' => 'Invalid or expired reset token.'
                ], 400);
            }

            // Check if token matches
            if (!Hash::check($request->token, $passwordReset->token)) {
                return response()->json([
                    'message' => 'Invalid or expired reset token.'
                ], 400);
            }

            // Check if token is not expired
            $tokenCreated = Carbon::parse($passwordReset->created_at);
            if ($tokenCreated->addHours(1)->isPast()) {
                return response()->json([
                    'message' => 'Reset token has expired. Please request a new one.'
                ], 400);
            }

            // Update the user's password
            $user = User::where('email', $request->email)->first();
            
            if (!$user) {
                return response()->json([
                    'message' => 'User not found.'
                ], 404);
            }

            $user->password = Hash::make($request->password);
            $user->save();

            // Delete the password reset token
            DB::table('password_reset_tokens')
                ->where('email', $request->email)
                ->delete();

            // Revoke all tokens for this user (they'll need to login again)
            $user->tokens()->delete();

            return response()->json([
                'message' => 'Password has been reset successfully. Please login with your new password.',
                'status' => 'success'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while resetting your password.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}