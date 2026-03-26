<?php

declare(strict_types = 1);

namespace QuantumTecnology\FalconDataHub\Resources\Finance;

use QuantumTecnology\FalconDataHub\Resources\AbstractResource;
use QuantumTecnology\FalconDataHub\Response\ApiResponse;

final class BcbResource extends AbstractResource
{
    public function selic(): ApiResponse
    {
        return $this->get('private/v1/bcb/selic');
    }

    public function cdi(): ApiResponse
    {
        return $this->get('private/v1/bcb/cdi');
    }

    public function ipca(?int $year = null): ApiResponse
    {
        $path = 'private/v1/bcb/ipca';

        if ($year !== null) {
            $path .= "/{$year}";
        }

        return $this->get($path);
    }

    public function currency(string $from, string $to): ApiResponse
    {
        return $this->get("private/v1/bcb/currency/{$from}/{$to}");
    }
}
