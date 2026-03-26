<?php

declare(strict_types = 1);

namespace QuantumTecnology\FalconDataHub\Resources\Lookup;

use QuantumTecnology\FalconDataHub\Resources\AbstractResource;
use QuantumTecnology\FalconDataHub\Response\ApiResponse;

final class CnaeResource extends AbstractResource
{
    public function search(string $cnae): ApiResponse
    {
        $cnae = $this->sanitizeDigits($cnae);

        return $this->get("private/v1/cnae/{$cnae}/search");
    }
}
