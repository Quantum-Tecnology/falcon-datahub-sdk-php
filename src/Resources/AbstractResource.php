<?php

declare(strict_types = 1);

namespace QuantumTecnology\FalconDataHub\Resources;

use QuantumTecnology\FalconDataHub\Auth\TokenManager;
use QuantumTecnology\FalconDataHub\Exceptions\AuthException;
use QuantumTecnology\FalconDataHub\Exceptions\NotFoundException;
use QuantumTecnology\FalconDataHub\Exceptions\RateLimitException;
use QuantumTecnology\FalconDataHub\Exceptions\ServerException;
use QuantumTecnology\FalconDataHub\Exceptions\ValidationException;
use QuantumTecnology\FalconDataHub\FalconConfig;
use QuantumTecnology\FalconDataHub\Http\HttpClientInterface;
use QuantumTecnology\FalconDataHub\Http\HttpResponse;
use QuantumTecnology\FalconDataHub\Response\ApiResponse;

abstract class AbstractResource
{
    protected HttpClientInterface $http;
    protected TokenManager $tokenManager;
    protected FalconConfig $config;

    public function __construct(
        HttpClientInterface $http,
        TokenManager $tokenManager,
        FalconConfig $config,
    ) {
        $this->http         = $http;
        $this->tokenManager = $tokenManager;
        $this->config       = $config;
    }

    /**
     * @param array<string, mixed> $query
     */
    protected function get(string $path, array $query = []): ApiResponse
    {
        return $this->requestWithAuth('GET', $path, $query);
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function post(string $path, array $data = []): ApiResponse
    {
        return $this->requestWithAuth('POST', $path, $data);
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function put(string $path, array $data = []): ApiResponse
    {
        return $this->requestWithAuth('PUT', $path, $data);
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function delete(string $path, array $data = []): ApiResponse
    {
        return $this->requestWithAuth('DELETE', $path, $data);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function requestWithAuth(string $method, string $path, array $payload = []): ApiResponse
    {
        $url     = $this->buildUrl($path);
        $headers = $this->authHeaders();

        $response = $this->doRequest($method, $url, $payload, $headers);

        // Auto-retry on 401 (token expired)
        if ($response->getStatusCode() === 401 && $this->config->hasCredentials()) {
            $this->tokenManager->invalidate();
            $headers  = $this->authHeaders();
            $response = $this->doRequest($method, $url, $payload, $headers);
        }

        return $this->handleResponse($response);
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, string> $headers
     */
    private function doRequest(string $method, string $url, array $payload, array $headers): HttpResponse
    {
        return match ($method) {
            'GET'    => $this->http->get($url, $payload, $headers),
            'POST'   => $this->http->post($url, $payload, $headers),
            'PUT'    => $this->http->put($url, $payload, $headers),
            'DELETE' => $this->http->delete($url, $payload, $headers),
            default  => $this->http->get($url, $payload, $headers),
        };
    }

    private function handleResponse(HttpResponse $response): ApiResponse
    {
        $apiResponse = ApiResponse::fromHttpResponse($response);
        $statusCode  = $response->getStatusCode();

        if ($statusCode >= 200 && $statusCode < 300) {
            return $apiResponse;
        }

        match (true) {
            $statusCode === 401 => throw new AuthException(
                $apiResponse->message,
                $statusCode,
                null,
                $apiResponse,
            ),
            $statusCode === 404 => throw new NotFoundException(
                $apiResponse->message,
                $statusCode,
                null,
                $apiResponse,
            ),
            $statusCode === 422 => throw new ValidationException(
                $apiResponse->message,
                $apiResponse->errors,
                $statusCode,
                null,
                $apiResponse,
            ),
            $statusCode === 429 => throw new RateLimitException(
                $apiResponse->message,
                $response->retryAfter(),
                $statusCode,
                null,
                $apiResponse,
            ),
            $statusCode >= 500 => throw new ServerException(
                $apiResponse->message,
                $statusCode,
                null,
                $apiResponse,
            ),
            default => $apiResponse,
        };

        return $apiResponse;
    }

    private function buildUrl(string $path): string
    {
        return $this->config->getBaseUrl() . '/' . ltrim($path, '/');
    }

    /**
     * @return array<string, string>
     */
    private function authHeaders(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->tokenManager->getToken(),
        ];
    }

    protected function sanitizeDigits(string $value): string
    {
        return preg_replace('/\D/', '', $value) ?? $value;
    }
}
