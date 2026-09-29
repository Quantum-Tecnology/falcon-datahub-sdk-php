<?php

declare(strict_types = 1);

namespace QuantumTecnology\FalconDataHub\Resources\ApiKeys;

use QuantumTecnology\FalconDataHub\Exceptions\FalconException;
use QuantumTecnology\FalconDataHub\Resources\AbstractResource;
use QuantumTecnology\FalconDataHub\Response\ApiResponse;

/**
 * Chave ANTIGA de API (token Sanctum `api-key`) — em transição.
 *
 * O DataHub não cria mais este tipo de chave: a de agora é a `fdx_`
 * (`integrationTokens()`). A antiga continua valendo em `/private` até a data
 * de corte, que vem em `sunset_at` na listagem.
 *
 * @deprecated desde 1.8.0 — use integrationTokens()
 */
final class ApiKeyResource extends AbstractResource
{
    /** As chaves antigas ainda ativas, com `sunset_at` (a data de corte). */
    public function list(): ApiResponse
    {
        return $this->get('panel/v1/api-keys');
    }

    /**
     * @deprecated o endpoint foi removido do DataHub — use integrationTokens()->create()
     *
     * @throws FalconException sempre
     */
    public function create(array $data = []): ApiResponse
    {
        // O DataHub responde 405 aqui, e o 405 voltaria como resposta comum —
        // quem chamasse seguiria achando que tem uma chave nova.
        throw new FalconException(
            'O DataHub não cria mais a chave antiga. Use integrationTokens()->create(), que gera uma chave fdx_.',
        );
    }

    public function destroy(int $id): ApiResponse
    {
        return $this->delete("panel/v1/api-keys/{$id}");
    }
}
