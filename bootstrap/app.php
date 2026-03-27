<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        channels: __DIR__ . '/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'account.active' => \App\Http\Middleware\EnsureAccountIsActive::class,
            'session.active' => \App\Http\Middleware\EnsureSessionIsActive::class,
            'token.active' => \App\Http\Middleware\EnsureTokenIsActive::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $shouldRenderJson = static fn($request): bool => $request->expectsJson() || $request->is('api/*');

        $exceptions->render(function (ValidationException $exception, $request) use ($shouldRenderJson) {
            if (!$shouldRenderJson($request)) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => collect($exception->errors())->flatten()->first(),
                'errors' => $exception->errors(),
            ], 422);
        });

        $exceptions->render(function (AuthenticationException $exception, $request) use ($shouldRenderJson) {
            if (!$shouldRenderJson($request)) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        });

        $exceptions->render(function (AuthorizationException $exception, $request) use ($shouldRenderJson) {
            if (!$shouldRenderJson($request)) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => $exception->getMessage() ?: 'This action is unauthorized.',
            ], 403);
        });

        $exceptions->render(function (ModelNotFoundException $exception, $request) use ($shouldRenderJson) {
            if (!$shouldRenderJson($request)) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => 'Resource not found.',
            ], 404);
        });

        $exceptions->render(function (HttpExceptionInterface $exception, $request) use ($shouldRenderJson) {
            if (!$shouldRenderJson($request)) {
                return null;
            }

            return response()->json([
                'success' => false,
                'message' => $exception->getMessage() ?: 'Request failed.',
            ], $exception->getStatusCode());
        });
    })->create();
