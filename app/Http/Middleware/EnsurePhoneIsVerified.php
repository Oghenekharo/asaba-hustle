<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePhoneIsVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || $user->hasRole('admin') || $user->phone_verified_at) {
            return $next($request);
        }

        if ($request->routeIs('web.verify.phone.*', 'web.logout', 'web.app.verification.send')) {
            return $next($request);
        }

        $redirect = route('web.verify.phone.page', ['phone' => $user->phone]);
        $message = 'Verify your phone number to continue.';

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'redirect' => $redirect,
            ], 403);
        }

        return redirect()->to($redirect)->with('loggedOutStatus', $message);
    }
}
