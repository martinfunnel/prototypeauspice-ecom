<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (!$user || !$user->isSuperAdmin()) {
            if ($user) {
                ActivityLog::create([
                    'user_id' => $user->id,
                    'action' => 'unauthorized_access',
                    'description' => "Tentative d'accès super admin non autorisée sur " . $request->fullUrl(),
                    'ip_address' => $request->ip(),
                    'metadata' => ['url' => $request->fullUrl(), 'method' => $request->method()],
                ]);
            }
            abort(403, 'Accès réservé au super administrateur.');
        }

        return $next($request);
    }
}
