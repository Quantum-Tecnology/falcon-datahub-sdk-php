<?php

declare(strict_types = 1);

namespace QuantumTecnology\FalconDataHub\Resources\Fiscal;

use QuantumTecnology\FalconDataHub\Resources\AbstractResource;
use QuantumTecnology\FalconDataHub\Response\ApiResponse;

final class FiscalResource extends AbstractResource
{
    public function cfopList(?string $filter = null): ApiResponse
    {
        $query = [];

        if ($filter !== null) {
            $query['filter'] = $filter;
        }

        return $this->get('private/v1/fiscal/cfop', $query);
    }

    public function cfop(string $code): ApiResponse
    {
        return $this->get("private/v1/fiscal/cfop/{$code}");
    }

    public function cstList(string $type): ApiResponse
    {
        return $this->get("private/v1/fiscal/cst/{$type}");
    }

    public function cst(string $type, string $code): ApiResponse
    {
        return $this->get("private/v1/fiscal/cst/{$type}/{$code}");
    }
}
