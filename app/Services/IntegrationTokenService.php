<?php

namespace App\Services;

use App\Models\IntegrationClient;
use Illuminate\Support\Str;

class IntegrationTokenService
{
    public function rotate(IntegrationClient $client): string
    {
        $plain = 'dfi_'.$client->app_code.'_'.Str::random(48);

        $client->update([
            'token_hash' => hash('sha256', $plain),
            'token_rotated_at' => now(),
            'token_revoked_at' => null,
        ]);

        return $plain;
    }

    public function revoke(IntegrationClient $client): void
    {
        $client->update([
            'token_hash' => null,
            'token_revoked_at' => now(),
        ]);
    }

    public function findByToken(?string $token): ?IntegrationClient
    {
        if (blank($token)) {
            return null;
        }

        $hash = hash('sha256', (string) $token);

        return IntegrationClient::query()
            ->where('token_hash', $hash)
            ->first();
    }
}
