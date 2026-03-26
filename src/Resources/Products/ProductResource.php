<?php

declare(strict_types = 1);

namespace QuantumTecnology\FalconDataHub\Resources\Products;

use QuantumTecnology\FalconDataHub\Resources\AbstractResource;
use QuantumTecnology\FalconDataHub\Response\ApiResponse;

final class ProductResource extends AbstractResource
{
    public function findByEan(string $ean, ?string $region = null): ApiResponse
    {
        $query = [];

        if ($region !== null) {
            $query['region'] = $region;
        }

        return $this->get("private/v1/products/ean/{$ean}", $query);
    }

    public function prices(int $id, ?string $region = null): ApiResponse
    {
        $query = [];

        if ($region !== null) {
            $query['region'] = $region;
        }

        return $this->get("private/v1/products/{$id}/prices", $query);
    }

    public function search(string $query): ApiResponse
    {
        return $this->get('private/v1/products/search', ['q' => $query]);
    }
}
