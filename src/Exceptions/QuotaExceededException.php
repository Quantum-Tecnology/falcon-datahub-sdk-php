<?php

declare(strict_types = 1);

namespace QuantumTecnology\FalconDataHub\Exceptions;

use QuantumTecnology\FalconDataHub\Response\ApiResponse;
use Throwable;

/**
 * Uma cota do plano acabou (HTTP 402).
 *
 * Diferente do `RateLimitException` (429, limite por hora): esperar alguns
 * segundos não resolve, e tentar a MESMA chamada de novo não adianta.
 * `getReason()` diz qual cota:
 *
 * - `vehicle_daily_quota_exceeded`: acabou a cota diária de placas NOVAS
 *   (as que ainda não estão na base). Placas já conhecidas continuam
 *   respondendo: siga para a próxima placa. Renova em `getResetsAt()`.
 * - `insufficient_quota`: acabou a cota de tokens de IA do mês (ou o plano
 *   não inclui IA).
 */
class QuotaExceededException extends FalconException
{
    public const VEHICLE_DAILY_QUOTA = 'vehicle_daily_quota_exceeded';
    public const AI_QUOTA            = 'insufficient_quota';

    public function __construct(
        string $message = 'Quota exceeded',
        private readonly ?string $reason = null,
        private readonly ?string $resetsAt = null,
        int $code = 402,
        ?Throwable $previous = null,
        ?ApiResponse $response = null,
    ) {
        parent::__construct($message, $code, $previous, $response);
    }

    /** Qual cota acabou (ver constantes), quando a API informa. */
    public function getReason(): ?string
    {
        return $this->reason;
    }

    /** Quando a cota renova (ISO 8601), quando a API informa. */
    public function getResetsAt(): ?string
    {
        return $this->resetsAt;
    }

    /** A cota diária de placas novas acabou: placa conhecida ainda responde. */
    public function isVehicleDailyQuota(): bool
    {
        return self::VEHICLE_DAILY_QUOTA === $this->reason;
    }
}
