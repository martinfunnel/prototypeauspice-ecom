<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use App\Models\Role;
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
        $allowedRoles = Role::pluck('key')->toArray();
        if (!$user->hasAnyRole($allowedRoles)) {
            ActivityLog::create([
                'user_id' => $user->id,
                'action' => 'unauthorized_access',
                'description' => "Tentative d'accès au panel admin par un utilisateur non-admin (rôles : " . implode(', ', $user->userRoles->pluck('role')->toArray()) . ")",
                'ip_address' => $request->ip(),
                'metadata' => ['url' => $request->fullUrl(), 'method' => $request->method()],
            ]);
            abort(403, 'Accès réservé aux administrateurs.');
        }

        return $next($request);
    }
}
