<?php

declare(strict_types = 1);

namespace QuantumTecnology\FalconDataHub\Resources\Lookup;

use QuantumTecnology\FalconDataHub\Resources\AbstractResource;
use QuantumTecnology\FalconDataHub\Response\ApiResponse;

final class CepResource extends AbstractResource
{
    public function search(string $cep): ApiResponse
    {
        $cep = $this->sanitizeDigits($cep);

        return $this->get("private/v1/cep/{$cep}/search");
    }
}
