<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();
        // Un admin doit avoir au moins un rôle et au moins une permission
        $hasRole = $user->roles()->exists();
        $hasPermission = $user->roles()->whereHas('permissions')->exists();

        if (!$hasRole || !$hasPermission) {
            abort(403, 'Accès réservé aux administrateurs.');
        }

        return $next($request);
    }
}
