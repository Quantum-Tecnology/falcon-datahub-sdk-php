<?php

declare(strict_types = 1);

namespace QuantumTecnology\FalconDataHub\Resources\Xml;

use QuantumTecnology\FalconDataHub\Resources\AbstractResource;
use QuantumTecnology\FalconDataHub\Response\ApiResponse;

final class XmlResource extends AbstractResource
{
    public function search(string $cnpj): ApiResponse
    {
        $cnpj = $this->sanitizeDigits($cnpj);

        return $this->get("private/v1/xmls/{$cnpj}/search");
    }

    public function list(): ApiResponse
    {
        return $this->get('private/v1/xmls');
    }

    /**
     * @param array<string, mixed> $data
     */
    public function upload(array $data): ApiResponse
    {
        return $this->post('private/v1/xmls', $data);
    }

    public function show(int $id): ApiResponse
    {
        return $this->get("private/v1/xmls/{$id}");
    }

    public function destroy(int $id): ApiResponse
    {
        return $this->delete("private/v1/xmls/{$id}");
    }
}
