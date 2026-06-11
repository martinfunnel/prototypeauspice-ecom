<?php

namespace App\Http\Middleware;

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
            abort(403, 'Vous n\'avez pas la permission d\'effectuer cette action.');
        }

        return $next($request);
    }
}
