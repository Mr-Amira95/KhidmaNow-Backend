<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    private const METHOD_ACTIONS = [
        'index'   => 'view',
        'show'    => 'view',
        'store'   => 'create',
        'update'  => 'edit',
        'destroy' => 'delete',
    ];

    public function handle(Request $request, Closure $next, string $spec): Response
    {
        $user = $request->user();

        if (!$user || $user->user_type !== 'admin') {
            return $this->forbidden();
        }

        if ($user->is_super_admin) {
            return $next($request);
        }

        $key = $this->resolveKey($spec, $request);

        if (!$key || !$user->hasPermission($key)) {
            return $this->forbidden();
        }

        if (!str_ends_with($key, '.view')) {
            $group = strstr($key, '.', true);
            if (!$user->hasPermission("{$group}.view")) {
                return $this->forbidden();
            }
        }

        return $next($request);
    }

    private function resolveKey(string $spec, Request $request): ?string
    {
        if (str_contains($spec, '.')) {
            return $spec;
        }

        $method = $request->route()?->getActionMethod();
        $action = self::METHOD_ACTIONS[$method] ?? null;

        return $action ? "{$spec}.{$action}" : null;
    }

    private function forbidden(): Response
    {
        return response()->json([
            'status'  => 'error',
            'message' => 'Forbidden. You do not have permission to perform this action.',
        ], 403);
    }
}
