<?php

declare(strict_types = 1);

namespace QuantumTecnology\FalconDataHub\Resources\Location;

use QuantumTecnology\FalconDataHub\Resources\AbstractResource;
use QuantumTecnology\FalconDataHub\Response\ApiResponse;

final class StateResource extends AbstractResource
{
    public function list(): ApiResponse
    {
        return $this->get('private/v1/states');
    }

    public function show(int $id): ApiResponse
    {
        return $this->get("private/v1/states/{$id}");
    }
}
