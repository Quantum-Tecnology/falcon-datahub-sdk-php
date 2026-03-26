<?php

declare(strict_types = 1);

namespace QuantumTecnology\FalconDataHub\Resources\Lookup;

use QuantumTecnology\FalconDataHub\Resources\AbstractResource;
use QuantumTecnology\FalconDataHub\Response\ApiResponse;

final class ActionResource extends AbstractResource
{
    public function search(string $action): ApiResponse
    {
        $action = urlencode($action);

        return $this->get("private/v1/action/{$action}/search");
    }
}
