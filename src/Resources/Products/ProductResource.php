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

    /**
     * Histórico de preços. `$id` é o id do produto como a API devolve — em
     * produção, hashid (string); `int` segue aceito para não quebrar quem já
     * chamava assim.
     *
     * Período: só `$observedFrom` = a partir de, só `$observedTo` = até, os
     * dois = entre. Data pura (`2026-03-21`) vale o dia inteiro nas duas
     * pontas; com hora (ISO 8601), a hora é respeitada.
     */
    public function prices(
        int | string $id,
        ?string $region = null,
        ?string $observedFrom = null,
        ?string $observedTo = null,
    ): ApiResponse {
        $query  = [];
        $filter = [];

        if ($region !== null) {
            $query['region'] = $region;
        }

        if ($observedFrom !== null) {
            $filter['observed_from'] = $observedFrom;
        }

        if ($observedTo !== null) {
            $filter['observed_to'] = $observedTo;
        }

        if ($filter !== []) {
            $query['filter'] = $filter;
        }

        return $this->get("private/v1/products/{$id}/prices", $query);
    }

    /**
     * Busca por nome, marca ou EAN. `$storeIds` restringe aos produtos com
     * preço coletado na(s) loja(s) — ids (hashid) de `/public/v1/stores`.
     *
     * @param string|array<int, int|string>|null $storeIds
     */
    public function search(string $query, string | array | null $storeIds = null): ApiResponse
    {
        $params = ['q' => $query];

        if (is_array($storeIds)) {
            $storeIds = implode(',', $storeIds);
        }

        if ($storeIds !== null && $storeIds !== '') {
            $params['filter'] = ['store_id' => $storeIds];
        }

        return $this->get('private/v1/products/search', $params);
    }
}
