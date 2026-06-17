<?php

declare(strict_types = 1);

namespace QuantumTecnology\FalconDataHub\Resources\Subscriptions;

use QuantumTecnology\FalconDataHub\Resources\AbstractResource;
use QuantumTecnology\FalconDataHub\Response\ApiResponse;

final class SubscriptionResource extends AbstractResource
{
    public function list(): ApiResponse
    {
        return $this->get('panel/v1/subscriptions');
    }

    public function active(): ApiResponse
    {
        return $this->get('panel/v1/subscriptions/active');
    }

    /**
     * @param array<string, mixed> $data {plan_id, credit_card_id}
     */
    public function create(array $data): ApiResponse
    {
        return $this->post('panel/v1/subscriptions', $data);
    }

    /**
     * @param array<string, mixed> $data {plan_id, credit_card_id}
     */
    public function changePlan(array $data): ApiResponse
    {
        return $this->put('panel/v1/subscriptions/change-plan', $data);
    }

    public function cancel(): ApiResponse
    {
        return $this->post('panel/v1/subscriptions/cancel');
    }
}
