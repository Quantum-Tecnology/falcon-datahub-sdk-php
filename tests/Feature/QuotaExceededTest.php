<?php

declare(strict_types = 1);

use QuantumTecnology\FalconDataHub\Auth\TokenManager;
use QuantumTecnology\FalconDataHub\Auth\TokenStore;
use QuantumTecnology\FalconDataHub\Exceptions\QuotaExceededException;
use QuantumTecnology\FalconDataHub\Exceptions\RateLimitException;
use QuantumTecnology\FalconDataHub\Resources\Ai\AiResource;
use QuantumTecnology\FalconDataHub\Resources\Lookup\VehicleResource;

/*
|--------------------------------------------------------------------------
| 402: cota do plano esgotada (1.10.0)
|--------------------------------------------------------------------------
| Até a 1.9, o 402 caía no `default` e voltava como resposta comum.
*/

/**
 * @template T
 *
 * @param class-string<T> $class
 *
 * @return T
 */
function quotaResource(string $class, array $responses): object
{
    $config = makeConfig();
    $http   = fakeClient($responses);

    return new $class($http, new TokenManager($http, $config, new TokenStore()), $config);
}

it('cota diária de placas novas esgotada lança QuotaExceededException', function (): void {
    $vehicles = quotaResource(VehicleResource::class, [
        jsonResponse([
            'message' => 'Limite diário de consultas de placas novas do seu plano atingido (1 por dia).',
            'code'    => 'vehicle_daily_quota_exceeded',
            'data'    => ['limit' => 1, 'resets_at' => '2026-11-04T00:00:00-03:00'],
        ], 402),
    ]);

    try {
        $vehicles->search('ABC1D23');
        $this->fail('Deveria ter lançado QuotaExceededException.');
    } catch (QuotaExceededException $e) {
        expect($e->getCode())->toBe(402)
            ->and($e->isVehicleDailyQuota())->toBeTrue()
            ->and($e->getReason())->toBe(QuotaExceededException::VEHICLE_DAILY_QUOTA)
            ->and($e->getResetsAt())->toBe('2026-11-04T00:00:00-03:00')
            ->and($e->getMessage())->toContain('placas novas');
    }
});

it('402 não é RateLimitException: quem só trata o 429 não confunde as duas', function (): void {
    $vehicles = quotaResource(VehicleResource::class, [
        jsonResponse(['message' => 'x', 'code' => 'vehicle_daily_quota_exceeded'], 402),
    ]);

    try {
        $vehicles->search('ABC1D23');
        $this->fail('Deveria ter lançado QuotaExceededException.');
    } catch (QuotaExceededException $e) {
        expect($e)->not->toBeInstanceOf(RateLimitException::class);
    }
});

it('cota de IA esgotada lê o corpo no formato OpenAI', function (): void {
    $ai = quotaResource(AiResource::class, [
        jsonResponse(['error' => ['type' => 'insufficient_quota', 'message' => 'Sua cota de tokens de IA acabou neste mês.']], 402),
    ]);

    try {
        $ai->models();
        $this->fail('Deveria ter lançado QuotaExceededException.');
    } catch (QuotaExceededException $e) {
        expect($e->getReason())->toBe(QuotaExceededException::AI_QUOTA)
            ->and($e->isVehicleDailyQuota())->toBeFalse()
            ->and($e->getMessage())->toBe('Sua cota de tokens de IA acabou neste mês.');
    }
});
