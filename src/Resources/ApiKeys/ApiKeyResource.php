<?php

declare(strict_types = 1);

namespace QuantumTecnology\FalconDataHub\Resources\ApiKeys;

use QuantumTecnology\FalconDataHub\Resources\AbstractResource;
use QuantumTecnology\FalconDataHub\Response\ApiResponse;

final class ApiKeyResource extends AbstractResource
{
    public function list(): ApiResponse
    {
        return $this->get('private/v1/api-keys');
    }

    /**
     * @param array<string, mixed> $data {expires_in?: int}
     */
    public function create(array $data = []): ApiResponse
    {
        return $this->post('private/v1/api-keys', $data);
    }

    public function destroy(int $id): ApiResponse
    {
        return $this->delete("private/v1/api-keys/{$id}");
    }
}
