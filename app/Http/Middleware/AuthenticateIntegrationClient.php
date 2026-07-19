<?php

namespace App\Http\Middleware;

use App\Services\IntegrationTokenService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateIntegrationClient
{
    public function __construct(private readonly IntegrationTokenService $tokens) {}

    public function handle(Request $request, Closure $next, string $ability): Response
    {
        $token = str($request->bearerToken() ?? '')->trim()->toString();
        $client = $this->tokens->findByToken($token);

        if (! $client) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        if (! $client->is_active || $client->token_revoked_at || ($client->expires_at && $client->expires_at->isPast())) {
            return response()->json(['message' => 'Integration client is inactive.'], 403);
        }

        if (! $client->hasAbility($ability)) {
            return response()->json(['message' => 'Integration client ability is not allowed.'], 403);
        }

        $client->forceFill(['last_used_at' => now()])->save();
        $request->attributes->set('integration_client', $client);

        return $next($request);
    }
}
