<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
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
        // Accès admin : super_admin, admin, vendeur, comptable
        if (!$user->hasAnyRole(['super_admin', 'admin', 'vendeur', 'comptable'])) {
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
