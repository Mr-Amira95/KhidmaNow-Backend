<?php

namespace App\Services;

use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class SocialAuthService
{
    private const GOOGLE_JWKS_URL = 'https://www.googleapis.com/oauth2/v3/certs';
    private const APPLE_JWKS_URL = 'https://appleid.apple.com/auth/keys';

    /**
     * Verify a Google id_token and return its claims (sub, email, name, ...).
     */
    public function verifyGoogleToken(string $idToken): array
    {
        $claims = $this->decode($idToken, self::GOOGLE_JWKS_URL, 'google_jwks');

        $allowedIssuers = ['accounts.google.com', 'https://accounts.google.com'];
        if (!in_array($claims['iss'] ?? null, $allowedIssuers, true)) {
            throw new RuntimeException('Invalid token issuer.');
        }

        $clientIds = config('services.google.client_ids', []);
        if (!empty($clientIds) && !in_array($claims['aud'] ?? null, $clientIds, true)) {
            throw new RuntimeException('Invalid token audience.');
        }

        return $claims;
    }

    /**
     * Verify an Apple id_token and return its claims (sub, email, ...).
     */
    public function verifyAppleToken(string $idToken): array
    {
        $claims = $this->decode($idToken, self::APPLE_JWKS_URL, 'apple_jwks');

        if (($claims['iss'] ?? null) !== 'https://appleid.apple.com') {
            throw new RuntimeException('Invalid token issuer.');
        }

        $clientId = config('services.apple.client_id');
        if ($clientId && ($claims['aud'] ?? null) !== $clientId) {
            throw new RuntimeException('Invalid token audience.');
        }

        return $claims;
    }

    private function decode(string $idToken, string $jwksUrl, string $cacheKey): array
    {
        $jwks = Cache::remember($cacheKey, now()->addHours(12), function () use ($jwksUrl) {
            return Http::get($jwksUrl)->throw()->json();
        });

        try {
            $keys = JWK::parseKeySet($jwks);
            $decoded = JWT::decode($idToken, $keys);
        } catch (\Throwable $e) {
            throw new RuntimeException('Invalid or expired token: ' . $e->getMessage());
        }

        return (array) $decoded;
    }
}
