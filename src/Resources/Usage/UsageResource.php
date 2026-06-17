<?php

declare(strict_types = 1);

namespace QuantumTecnology\FalconDataHub\Resources\Usage;

use QuantumTecnology\FalconDataHub\Resources\AbstractResource;
use QuantumTecnology\FalconDataHub\Response\ApiResponse;

final class UsageResource extends AbstractResource
{
    /**
     * Uso/limite da conta no período corrente.
     * Retorna { unlimited, used, limit, reset_in_seconds }.
     */
    public function show(): ApiResponse
    {
        return $this->get('private/v1/usage');
    }
}
