<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'can' => \App\Http\Middleware\HasPermissionMiddleware::class,
            'super_admin' => \App\Http\Middleware\SuperAdminMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );

        $exceptions->reportable(function (Throwable $e) {
            $user = auth()->user();
            if ($user && $e instanceof \Illuminate\Validation\ValidationException) {
                try {
                    \App\Models\ActivityLog::create([
                        'user_id' => $user->id,
                        'action' => 'validation_error',
                        'description' => 'Erreur de validation : ' . $e->getMessage(),
                        'ip_address' => request()->ip(),
                        'metadata' => ['url' => request()->fullUrl(), 'errors' => $e->errors()],
                    ]);
                } catch (\Throwable) {
                    // Ignore logging failures
                }
            } elseif ($user && !($e instanceof \Illuminate\Auth\Access\AuthorizationException) && !($e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface)) {
                // Log unexpected errors (not 403s which are already logged by middleware)
                try {
                    \App\Models\ActivityLog::create([
                        'user_id' => $user->id,
                        'action' => 'error',
                        'description' => 'Erreur : ' . get_class($e) . ' — ' . $e->getMessage(),
                        'ip_address' => request()->ip(),
                        'metadata' => ['url' => request()->fullUrl(), 'file' => $e->getFile(), 'line' => $e->getLine()],
                    ]);
                } catch (\Throwable) {
                    // Ignore logging failures
                }
            }
        });
    })->create();
