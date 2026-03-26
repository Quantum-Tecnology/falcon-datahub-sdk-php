<?php

declare(strict_types = 1);

namespace QuantumTecnology\FalconDataHub\Resources\Validation;

use QuantumTecnology\FalconDataHub\Resources\AbstractResource;
use QuantumTecnology\FalconDataHub\Response\ApiResponse;

final class ValidateResource extends AbstractResource
{
    public function cpf(string $cpf): ApiResponse
    {
        $cpf = $this->sanitizeDigits($cpf);

        return $this->get("private/v1/validate/cpf/{$cpf}");
    }

    public function cnpj(string $cnpj): ApiResponse
    {
        $cnpj = $this->sanitizeDigits($cnpj);

        return $this->get("private/v1/validate/cnpj/{$cnpj}");
    }

    public function pix(string $key): ApiResponse
    {
        $key = urlencode($key);

        return $this->get("private/v1/validate/pix/{$key}");
    }

    public function generateCpf(): ApiResponse
    {
        return $this->get('private/v1/validate/generate/cpf');
    }

    public function generateCnpj(): ApiResponse
    {
        return $this->get('private/v1/validate/generate/cnpj');
    }
}
