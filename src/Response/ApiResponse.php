<?php

declare(strict_types = 1);

namespace QuantumTecnology\FalconDataHub\Response;

use QuantumTecnology\FalconDataHub\Http\HttpResponse;

class ApiResponse
{
    /** @var array<string, mixed>|list<mixed> */
    public readonly array $data;

    /** @var array<string, mixed> */
    public readonly array $errors;

    /** @var array<string, mixed> */
    public readonly array $meta;

    /**
     * @param array<string, mixed>|list<mixed> $data
     * @param array<string, mixed> $errors
     * @param array<string, mixed> $meta
     */
    public function __construct(
        public readonly bool $success,
        public readonly int $statusCode,
        public readonly string $message,
        array $data = [],
        array $errors = [],
        array $meta = [],
    ) {
        $this->data   = $data;
        $this->errors = $errors;
        $this->meta   = $meta;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'success'    => $this->success,
            'statusCode' => $this->statusCode,
            'message'    => $this->message,
            'data'       => $this->data,
            'errors'     => $this->errors,
            'meta'       => $this->meta,
        ];
    }

    public static function fromHttpResponse(HttpResponse $response): self
    {
        $body = $response->json();

        $success = $body['success'] ?? $response->isSuccessful();
        $message = $body['message'] ?? ($response->isSuccessful() ? 'OK' : 'Error');
        $data    = $body['data'] ?? $body;
        $errors  = $body['errors'] ?? [];

        if (!is_array($data)) {
            $data = [$data];
        }

        if (!is_array($errors)) {
            $errors = [$errors];
        }

        $meta = [];

        foreach (['current_page', 'last_page', 'per_page', 'total', 'from', 'to'] as $key) {
            if (isset($body[$key])) {
                $meta[$key] = $body[$key];
            }
        }

        if (isset($data['current_page'])) {
            foreach (['current_page', 'last_page', 'per_page', 'total', 'from', 'to'] as $key) {
                if (isset($data[$key])) {
                    $meta[$key] = $data[$key];
                }
            }

            if (isset($data['data'])) {
                $data = $data['data'];
            }
        }

        return new self(
            success: (bool) $success,
            statusCode: $response->getStatusCode(),
            message: (string) $message,
            data: $data,
            errors: $errors,
            meta: $meta,
        );
    }
}
