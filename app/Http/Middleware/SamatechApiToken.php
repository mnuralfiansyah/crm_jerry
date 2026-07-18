<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SamatechApiToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $expectedToken = (string) config('samatech.api_token', '');

        if ($expectedToken === '') {
            return response()->json([
                'success' => false,
                'message' => 'Token API belum dikonfigurasi',
            ], 500);
        }

        $requestToken = $request->bearerToken()
            ?: $request->header('X-API-Token')
            ?: $request->query('token', '');

        if (! is_string($requestToken) || ! hash_equals($expectedToken, $requestToken)) {
            return response()->json([
                'success' => false,
                'message' => 'Token API tidak valid',
            ], 401);
        }

        return $next($request);
    }
}
