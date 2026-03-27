<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;

class EnsureSessionIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()) {
            return $next($request);
        }

        $timeoutMinutes = (int) config('session.inactivity_timeout', 30);
        $lastActivity = $request->session()->get('last_activity_at');

        if ($lastActivity && Carbon::createFromTimestamp((int) $lastActivity)->diffInMinutes(now()) >= $timeoutMinutes) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session expired due to inactivity.',
                ], 401);
            }

            return redirect()
                ->route('login')
                ->with('loggedOutStatus', 'Session expired due to inactivity.');
        }

        $request->session()->put('last_activity_at', now()->timestamp);

        return $next($request);
    }
}
