<?php

declare(strict_types = 1);

namespace QuantumTecnology\FalconDataHub\Resources\Fipe;

use QuantumTecnology\FalconDataHub\Resources\AbstractResource;
use QuantumTecnology\FalconDataHub\Response\ApiResponse;

final class FipeResource extends AbstractResource
{
    public function brands(string $vehicleType): ApiResponse
    {
        return $this->get("private/v1/fipe/{$vehicleType}/brands");
    }

    public function models(string $vehicleType, string $brandCode): ApiResponse
    {
        return $this->get("private/v1/fipe/{$vehicleType}/brands/{$brandCode}/models");
    }

    public function years(string $vehicleType, string $brandCode, string $modelCode): ApiResponse
    {
        return $this->get("private/v1/fipe/{$vehicleType}/brands/{$brandCode}/models/{$modelCode}/years");
    }

    public function price(string $vehicleType, string $brandCode, string $modelCode, string $yearCode): ApiResponse
    {
        return $this->get("private/v1/fipe/{$vehicleType}/brands/{$brandCode}/models/{$modelCode}/years/{$yearCode}");
    }
}
