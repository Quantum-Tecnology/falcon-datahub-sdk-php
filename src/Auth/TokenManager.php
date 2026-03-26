<?php

declare(strict_types = 1);

namespace QuantumTecnology\FalconDataHub\Auth;

use QuantumTecnology\FalconDataHub\Exceptions\AuthException;
use QuantumTecnology\FalconDataHub\FalconConfig;
use QuantumTecnology\FalconDataHub\Http\HttpClientInterface;

final class TokenManager
{
    private const CACHE_KEY = 'falcon_datahub_token';

    private TokenStoreInterface $store;
    private HttpClientInterface $http;
    private FalconConfig $config;

    public function __construct(
        HttpClientInterface $http,
        FalconConfig $config,
        ?TokenStoreInterface $store = null,
    ) {
        $this->http   = $http;
        $this->config = $config;
        $this->store  = $store ?? new TokenStore();
    }

    public function getToken(): string
    {
        // 1. Direct token from config
        if ($this->config->hasToken()) {
            return (string) $this->config->getToken();
        }

        // 2. Cached token
        $cached = $this->store->get(self::CACHE_KEY);

        if ($cached !== null) {
            return $cached;
        }

        // 3. Login with credentials
        return $this->authenticate();
    }

    public function invalidate(): void
    {
        $this->store->delete(self::CACHE_KEY);
    }

    private function authenticate(): string
    {
        if (!$this->config->hasCredentials()) {
            throw new AuthException(
                'No token or credentials configured. Call FalconConfig with token or email/password.',
                401,
            );
        }

        $url = $this->config->getBaseUrl() . '/auth/v1/login';

        $response = $this->http->post($url, [
            'email'    => $this->config->getEmail(),
            'password' => $this->config->getPassword(),
        ]);

        if (!$response->isSuccessful()) {
            $body = $response->json();
            throw new AuthException(
                $body['message'] ?? 'Authentication failed',
                $response->getStatusCode(),
            );
        }

        $body  = $response->json();
        $data  = $body['data'] ?? $body;
        $token = $data['access_token'] ?? $data['token'] ?? null;

        if ($token === null) {
            throw new AuthException('No access_token returned from login endpoint');
        }

        // Cache token for 50 minutes (Sanctum default is 60 min)
        $expiresIn = (int) ($data['expires_in'] ?? 3000);
        $ttl       = max(60, $expiresIn - 600);

        $this->store->set(self::CACHE_KEY, $token, $ttl);

        return $token;
    }
}
