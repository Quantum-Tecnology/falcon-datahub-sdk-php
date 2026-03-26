<?php

declare(strict_types = 1);

namespace QuantumTecnology\FalconDataHub\Resources\Auth;

use QuantumTecnology\FalconDataHub\Auth\TokenManager;
use QuantumTecnology\FalconDataHub\FalconConfig;
use QuantumTecnology\FalconDataHub\Http\HttpClientInterface;
use QuantumTecnology\FalconDataHub\Response\ApiResponse;

final class AuthResource
{
    private HttpClientInterface $http;
    private FalconConfig $config;

    public function __construct(
        HttpClientInterface $http,
        TokenManager $tokenManager,
        FalconConfig $config,
    ) {
        $this->http   = $http;
        $this->config = $config;
    }

    /**
     * @param array<string, mixed> $data {name, email, password, password_confirmation, ...}
     */
    public function register(array $data): ApiResponse
    {
        $response = $this->http->post(
            $this->config->getBaseUrl() . '/auth/v1',
            $data,
        );

        return ApiResponse::fromHttpResponse($response);
    }

    public function login(string $email, string $password): ApiResponse
    {
        $response = $this->http->post(
            $this->config->getBaseUrl() . '/auth/v1/login',
            ['email' => $email, 'password' => $password],
        );

        return ApiResponse::fromHttpResponse($response);
    }

    public function forgotPassword(string $email): ApiResponse
    {
        $response = $this->http->post(
            $this->config->getBaseUrl() . '/auth/v1/forgot-password',
            ['email' => $email],
        );

        return ApiResponse::fromHttpResponse($response);
    }
}
