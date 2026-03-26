<?php

declare(strict_types = 1);

namespace QuantumTecnology\FalconDataHub\Resources\Lookup;

use QuantumTecnology\FalconDataHub\Resources\AbstractResource;
use QuantumTecnology\FalconDataHub\Response\ApiResponse;

final class CnpjResource extends AbstractResource
{
    public function search(string $cnpj): ApiResponse
    {
        $cnpj = $this->sanitizeDigits($cnpj);

        return $this->get("private/v1/cnpj/{$cnpj}/search");
    }
}
