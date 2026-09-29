<?php

declare(strict_types = 1);

namespace QuantumTecnology\FalconDataHub\Resources\IntegrationTokens;

use QuantumTecnology\FalconDataHub\Resources\AbstractResource;
use QuantumTecnology\FalconDataHub\Response\ApiResponse;

/**
 * Chaves de integração `fdx_` — o padrão da casa, no lugar da chave antiga.
 *
 * Várias chaves por conta, uma por sistema, cada uma com nome, permissões
 * (`data:read`, `ai:chat`, `xmls:write`, `usage:read`) e validade.
 *
 * ⚠️ Gerenciar chaves é ação do PAINEL: exige sessão de usuário (login por
 * e-mail/senha). Uma chave `fdx_` não cria nem revoga chaves — o painel a
 * recusa com 401.
 */
final class IntegrationTokenResource extends AbstractResource
{
    public function list(): ApiResponse
    {
        return $this->get('panel/v1/integration-tokens');
    }

    /** O catálogo de permissões: `value`, `label`, `description` e `default`. */
    public function abilities(): ApiResponse
    {
        return $this->get('panel/v1/integration-tokens/abilities');
    }

    /**
     * Cria a chave. A chave em claro (`data.key`) vem SÓ nesta resposta — o
     * servidor guarda apenas o hash. Guarde-a na hora.
     *
     * @param list<string> $abilities     ao menos uma, do catálogo (`abilities()`)
     *                                    — sem padrão implícito: a chave tem
     *                                    exatamente o que se pediu
     * @param int|null     $expiresInDays null = não expira
     * @param bool         $riskAccepted  o aceite do termo de responsabilidade (obrigatório)
     */
    public function create(
        string $name,
        array $abilities,
        ?int $expiresInDays = null,
        bool $riskAccepted = false,
    ): ApiResponse {
        return $this->post('panel/v1/integration-tokens', array_filter([
            'name'            => $name,
            'abilities'       => array_values($abilities),
            'expires_in_days' => $expiresInDays,
            'risk_accepted'   => $riskAccepted,
        ], static fn (mixed $value): bool => null !== $value));
    }

    /** Revoga (não apaga): a chave para de autenticar na hora. */
    public function revoke(string $id): ApiResponse
    {
        return $this->delete("panel/v1/integration-tokens/{$id}");
    }
}
