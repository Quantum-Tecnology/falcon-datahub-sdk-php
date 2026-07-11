<?php

declare(strict_types = 1);

namespace QuantumTecnology\FalconDataHub\Resources\MarketData;

use QuantumTecnology\FalconDataHub\Resources\AbstractResource;
use QuantumTecnology\FalconDataHub\Response\ApiResponse;

/**
 * Dados de mercado (candles OHLCV de cripto) do DataHub.
 *
 * O DataHub coleta os candles da Binance sob demanda (lazy) e os cacheia.
 * `candles` devolve JSON estruturado; `dump` devolve klines crus no formato
 * Binance/Freqtrade ([[ts_ms,o,h,l,c,v],...]) — pronto para gravar o arquivo
 * data/binance/{PAR}_{QUOTE}-{tf}.json que o motor do Trade Hub lê no backtest.
 *
 * O par pode vir como "BTC/USDT" ou "BTCUSDT"; normalizamos para a rota.
 */
final class MarketDataResource extends AbstractResource
{
    /** Candles estruturados de um par/timeframe/intervalo. */
    public function candles(
        string $pair,
        string $timeframe = '1h',
        ?string $from = null,
        ?string $to = null,
    ): ApiResponse {
        return $this->get(
            "private/v1/market-data/{$this->normalizePair($pair)}/candles",
            $this->query($timeframe, $from, $to),
        );
    }

    /** Klines crus [[ts,o,h,l,c,v],...] no formato Binance/Freqtrade. */
    public function dump(
        string $pair,
        string $timeframe = '1h',
        ?string $from = null,
        ?string $to = null,
    ): ApiResponse {
        return $this->get(
            "private/v1/market-data/{$this->normalizePair($pair)}/dump",
            $this->query($timeframe, $from, $to),
        );
    }

    /** Lista de pares cacheados + cobertura. */
    public function pairs(): ApiResponse
    {
        return $this->get('private/v1/market-data/pairs');
    }

    /** Remove separadores do par (BTC/USDT -> BTCUSDT) para casar a rota. */
    private function normalizePair(string $pair): string
    {
        return urlencode(strtoupper((string) preg_replace('/[^A-Za-z0-9]/', '', $pair)));
    }

    /**
     * @return array<string, string>
     */
    private function query(string $timeframe, ?string $from, ?string $to): array
    {
        return array_filter(
            ['timeframe' => $timeframe, 'from' => $from, 'to' => $to],
            static fn ($value): bool => null !== $value && '' !== $value,
        );
    }
}
