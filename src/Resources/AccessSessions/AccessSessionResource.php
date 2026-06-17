<?php

declare(strict_types = 1);

namespace QuantumTecnology\FalconDataHub\Resources\AccessSessions;

use QuantumTecnology\FalconDataHub\Resources\AbstractResource;
use QuantumTecnology\FalconDataHub\Response\ApiResponse;

final class AccessSessionResource extends AbstractResource
{
    public function recent(): ApiResponse
    {
        return $this->get('panel/v1/access-sessions');
    }

    public function blocked(): ApiResponse
    {
        return $this->get('panel/v1/access-sessions/blocked');
    }

    /**
     * @param array<string, mixed> $data {ip?, user_agent?, reason?}
     */
    public function block(array $data): ApiResponse
    {
        return $this->post('panel/v1/access-sessions/block', $data);
    }

    public function unblock(int $id): ApiResponse
    {
        return $this->delete("panel/v1/access-sessions/block/{$id}");
    }
}
