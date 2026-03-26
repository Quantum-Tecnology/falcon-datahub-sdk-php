<?php

declare(strict_types = 1);

namespace QuantumTecnology\FalconDataHub\Resources\Delivery;

use QuantumTecnology\FalconDataHub\Resources\AbstractResource;
use QuantumTecnology\FalconDataHub\Response\ApiResponse;

final class DeliveryResource extends AbstractResource
{
    /**
     * @param array<string, mixed> $params
     */
    public function bestRoute(array $params = []): ApiResponse
    {
        return $this->get('private/v1/delivery/calculate-best-route', $params);
    }

    /**
     * @param array<string, mixed> $params
     */
    public function calculateDistance(array $params = []): ApiResponse
    {
        return $this->get('private/v1/delivery/calculate-distance', $params);
    }
}
