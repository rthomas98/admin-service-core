<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class CustomerInviteRateLimit
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $key = 'accept-invite', int $maxAttempts = 5): Response
    {
        $rateLimitKey = $key.':'.$request->ip();

        // Check if too many attempts
        if (RateLimiter::tooManyAttempts($rateLimitKey, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);

            return response()->json([
                'error' => 'Too many attempts. Please try again later.',
                'retry_after' => $seconds,
            ], 429);
        }

        // Increment the attempts
        RateLimiter::hit($rateLimitKey, 60); // 1 minute decay

        $response = $next($request);

        // Add rate limit headers
        $response->headers->set('X-RateLimit-Limit', $maxAttempts);
        $response->headers->set('X-RateLimit-Remaining', RateLimiter::remaining($rateLimitKey, $maxAttempts));

        return $response;
    }
}
