<?php

declare(strict_types = 1);

namespace QuantumTecnology\FalconDataHub\Resources\Plans;

use QuantumTecnology\FalconDataHub\Resources\AbstractResource;
use QuantumTecnology\FalconDataHub\Response\ApiResponse;

final class PlanResource extends AbstractResource
{
    public function list(): ApiResponse
    {
        return $this->get('panel/v1/plans');
    }

    public function show(int $id): ApiResponse
    {
        return $this->get("panel/v1/plans/{$id}");
    }
}
