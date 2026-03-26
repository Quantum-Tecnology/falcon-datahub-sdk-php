<?php

declare(strict_types = 1);

namespace QuantumTecnology\FalconDataHub\Resources\Lookup;

use QuantumTecnology\FalconDataHub\Resources\AbstractResource;
use QuantumTecnology\FalconDataHub\Response\ApiResponse;

final class NcmResource extends AbstractResource
{
    public function search(string $ncm): ApiResponse
    {
        $ncm = $this->sanitizeDigits($ncm);

        return $this->get("private/v1/ncm/{$ncm}/search");
    }
}
