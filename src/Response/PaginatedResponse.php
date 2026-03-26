<?php

declare(strict_types = 1);

namespace QuantumTecnology\FalconDataHub\Response;

use QuantumTecnology\FalconDataHub\Http\HttpResponse;

final class PaginatedResponse extends ApiResponse
{
    public readonly int $currentPage;
    public readonly int $lastPage;
    public readonly int $perPage;
    public readonly int $total;

    /**
     * @param array<string, mixed>|list<mixed> $data
     * @param array<string, mixed> $errors
     * @param array<string, mixed> $meta
     */
    public function __construct(
        bool $success,
        int $statusCode,
        string $message,
        array $data = [],
        array $errors = [],
        array $meta = [],
    ) {
        parent::__construct($success, $statusCode, $message, $data, $errors, $meta);

        $this->currentPage = (int) ($meta['current_page'] ?? 1);
        $this->lastPage    = (int) ($meta['last_page'] ?? 1);
        $this->perPage     = (int) ($meta['per_page'] ?? 15);
        $this->total       = (int) ($meta['total'] ?? count($data));
    }

    public function hasMorePages(): bool
    {
        return $this->currentPage < $this->lastPage;
    }

    public static function fromHttpResponse(HttpResponse $response): self
    {
        $base = ApiResponse::fromHttpResponse($response);

        return new self(
            success: $base->success,
            statusCode: $base->statusCode,
            message: $base->message,
            data: $base->data,
            errors: $base->errors,
            meta: $base->meta,
        );
    }
}
