<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || $user->user_type !== 'admin' || !$user->is_super_admin) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Forbidden. Super admin access required.',
            ], 403);
        }

        return $next($request);
    }
}
