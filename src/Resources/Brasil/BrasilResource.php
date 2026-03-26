<?php

declare(strict_types = 1);

namespace QuantumTecnology\FalconDataHub\Resources\Brasil;

use QuantumTecnology\FalconDataHub\Resources\AbstractResource;
use QuantumTecnology\FalconDataHub\Response\ApiResponse;

final class BrasilResource extends AbstractResource
{
    public function ddd(string $ddd): ApiResponse
    {
        return $this->get("private/v1/brasil/ddd/{$ddd}");
    }

    public function banks(): ApiResponse
    {
        return $this->get('private/v1/brasil/banks');
    }

    public function bank(string $code): ApiResponse
    {
        return $this->get("private/v1/brasil/banks/{$code}");
    }

    public function holidays(int $year): ApiResponse
    {
        return $this->get("private/v1/brasil/holidays/{$year}");
    }
}
