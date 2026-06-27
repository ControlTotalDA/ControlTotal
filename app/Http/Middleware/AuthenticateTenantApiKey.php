<?php

namespace App\Http\Middleware;

use App\Http\Responses\ApiResponse;
use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateTenantApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-API-Key') ?? $request->query('api_key');

        if (! $apiKey) {
            return ApiResponse::error('API key is required.', null, 401);
        }

        $tenant = Tenant::where('api_key', $apiKey)->where('active', true)->first();

        if (! $tenant) {
            return ApiResponse::error('Invalid API key.', null, 401);
        }

        $request->attributes->set('tenant', $tenant);

        return $next($request);
    }
}
