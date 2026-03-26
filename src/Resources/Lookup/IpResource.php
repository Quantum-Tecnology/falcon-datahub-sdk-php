<?php

declare(strict_types = 1);

namespace QuantumTecnology\FalconDataHub\Resources\Lookup;

use QuantumTecnology\FalconDataHub\Resources\AbstractResource;
use QuantumTecnology\FalconDataHub\Response\ApiResponse;

final class IpResource extends AbstractResource
{
    public function search(string $ip, ?int $databaseId = null): ApiResponse
    {
        $path = "private/v1/ip/{$ip}/search";

        if ($databaseId !== null) {
            $path .= "/database-id/{$databaseId}";
        }

        return $this->get($path);
    }
}
