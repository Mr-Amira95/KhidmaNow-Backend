<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProviderMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || $request->user()->user_type !== 'provider') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Forbidden. Provider access required.',
            ], 403);
        }

        return $next($request);
    }
}
