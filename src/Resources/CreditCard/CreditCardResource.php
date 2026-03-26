<?php

declare(strict_types = 1);

namespace QuantumTecnology\FalconDataHub\Resources\CreditCard;

use QuantumTecnology\FalconDataHub\Resources\AbstractResource;
use QuantumTecnology\FalconDataHub\Response\ApiResponse;

final class CreditCardResource extends AbstractResource
{
    public function list(): ApiResponse
    {
        return $this->get('private/v1/credit-cards');
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): ApiResponse
    {
        return $this->post('private/v1/credit-cards', $data);
    }

    public function destroy(int $id): ApiResponse
    {
        return $this->delete("private/v1/credit-cards/{$id}");
    }
}
