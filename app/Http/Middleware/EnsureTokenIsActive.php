<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class EnsureTokenIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $plainTextToken = $request->bearerToken();

        if (!$plainTextToken) {
            $authorizationHeader = (string) $request->header('Authorization', '');

            if (str_starts_with(strtolower($authorizationHeader), 'bearer ')) {
                $plainTextToken = trim(substr($authorizationHeader, 7));
            }
        }

        if (!$plainTextToken) {
            return $next($request);
        }

        $token = PersonalAccessToken::findToken($plainTextToken);

        if (!$token) {
            return $next($request);
        }

        $timeoutMinutes = (int) config('session.inactivity_timeout', 30);
        $lastActivity = $token->last_used_at ?? $token->created_at;

        if ($lastActivity && $lastActivity->diffInMinutes(now()) >= $timeoutMinutes) {
            $token->delete();

            return response()->json([
                'success' => false,
                'message' => 'Token expired due to inactivity.',
            ], 401);
        }

        return $next($request);
    }
}
