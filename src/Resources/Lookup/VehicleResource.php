<?php

declare(strict_types = 1);

namespace QuantumTecnology\FalconDataHub\Resources\Lookup;

use QuantumTecnology\FalconDataHub\Resources\AbstractResource;
use QuantumTecnology\FalconDataHub\Response\ApiResponse;

final class VehicleResource extends AbstractResource
{
    /**
     * Consulta os dados de um veiculo pela placa.
     *
     * Aceita placa no formato Mercosul (ABC1D23) ou antigo (ABC1234),
     * com ou sem hifen/maiusculas - sera normalizada no servidor.
     */
    public function search(string $placa): ApiResponse
    {
        $placa = $this->normalizePlate($placa);

        return $this->get("private/v1/vehicles/{$placa}/search");
    }

    /**
     * URL absoluta da imagem PNG da placa (gerada pelo DataHub,
     * sem expor provedores externos). Util para embedar em <img src>.
     */
    public function imageUrl(string $placa): string
    {
        $placa = $this->normalizePlate($placa);

        return $this->config->getBaseUrl() . "/private/v1/vehicles/{$placa}/image";
    }

    private function normalizePlate(string $placa): string
    {
        return mb_strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $placa) ?? $placa);
    }
}
