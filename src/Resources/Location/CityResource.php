<?php

declare(strict_types = 1);

namespace QuantumTecnology\FalconDataHub\Resources\Location;

use QuantumTecnology\FalconDataHub\Resources\AbstractResource;
use QuantumTecnology\FalconDataHub\Response\ApiResponse;

final class CityResource extends AbstractResource
{
    public function list(?int $stateId = null): ApiResponse
    {
        $query = [];

        if ($stateId !== null) {
            $query['state_id'] = $stateId;
        }

        return $this->get('private/v1/cities', $query);
    }

    public function show(int $id): ApiResponse
    {
        return $this->get("private/v1/cities/{$id}");
    }
}
