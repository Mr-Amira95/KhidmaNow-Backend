<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CustomerMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || $request->user()->user_type !== 'customer') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Forbidden. Client access required.',
            ], 403);
        }

        return $next($request);
    }
}
