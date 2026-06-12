<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HasPermissionMiddleware
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        if (!$user->canDo($permission)) {
            ActivityLog::create([
                'user_id' => $user->id,
                'action' => 'unauthorized_access',
                'description' => "Tentative d'accès non autorisé : {$permission} sur " . $request->fullUrl(),
                'ip_address' => $request->ip(),
                'metadata' => ['permission' => $permission, 'url' => $request->fullUrl(), 'method' => $request->method()],
            ]);
            abort(403, 'Vous n\'avez pas la permission d\'effectuer cette action.');
        }

        return $next($request);
    }
}
