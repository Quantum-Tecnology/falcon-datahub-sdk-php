<?php

declare(strict_types = 1);

namespace QuantumTecnology\FalconDataHub\Exceptions;

use QuantumTecnology\FalconDataHub\Response\ApiResponse;
use Throwable;

/**
 * A requisição foi recusada (HTTP 403).
 *
 * Com chave de integração (`fdx_`), quase sempre é permissão: a chave foi
 * criada sem a ability da rota — `ai:chat` para a IA, `xmls:write` para enviar
 * XML — e `getAbility()` diz qual faltou. A solução é no painel do DataHub
 * (criar uma chave com a permissão); tentar de novo não muda nada.
 */
class ForbiddenException extends FalconException
{
    private ?string $ability;

    public function __construct(
        string $message = 'Forbidden',
        ?string $ability = null,
        int $code = 403,
        ?Throwable $previous = null,
        ?ApiResponse $response = null,
    ) {
        parent::__construct($message, $code, $previous, $response);
        $this->ability = $ability;
    }

    /** A permissão que faltou (ex.: `ai:chat`), quando o motivo foi esse. */
    public function getAbility(): ?string
    {
        return $this->ability;
    }
}
