<?php

declare(strict_types = 1);

namespace QuantumTecnology\FalconDataHub;

use QuantumTecnology\FalconDataHub\Auth\CacheTokenStore;
use QuantumTecnology\FalconDataHub\Auth\TokenManager;
use QuantumTecnology\FalconDataHub\Auth\TokenStore;
use QuantumTecnology\FalconDataHub\Http\CurlHttpClient;
use QuantumTecnology\FalconDataHub\Http\HttpClientInterface;
use QuantumTecnology\FalconDataHub\Resources\AccessSessions\AccessSessionResource;
use QuantumTecnology\FalconDataHub\Resources\ApiKeys\ApiKeyResource;
use QuantumTecnology\FalconDataHub\Resources\Auth\AuthResource;
use QuantumTecnology\FalconDataHub\Resources\Brasil\BrasilResource;
use QuantumTecnology\FalconDataHub\Resources\CreditCard\CreditCardResource;
use QuantumTecnology\FalconDataHub\Resources\Delivery\DeliveryResource;
use QuantumTecnology\FalconDataHub\Resources\Finance\BcbResource;
use QuantumTecnology\FalconDataHub\Resources\Fipe\FipeResource;
use QuantumTecnology\FalconDataHub\Resources\Fiscal\FiscalResource;
use QuantumTecnology\FalconDataHub\Resources\Location\CityResource;
use QuantumTecnology\FalconDataHub\Resources\Location\StateResource;
use QuantumTecnology\FalconDataHub\Resources\Lookup\ActionResource;
use QuantumTecnology\FalconDataHub\Resources\Lookup\CepResource;
use QuantumTecnology\FalconDataHub\Resources\Lookup\CnaeResource;
use QuantumTecnology\FalconDataHub\Resources\Lookup\CnpjResource;
use QuantumTecnology\FalconDataHub\Resources\Lookup\IpResource;
use QuantumTecnology\FalconDataHub\Resources\Lookup\NcmResource;
use QuantumTecnology\FalconDataHub\Resources\Lookup\VehicleResource;
use QuantumTecnology\FalconDataHub\Resources\Plans\PlanResource;
use QuantumTecnology\FalconDataHub\Resources\Products\ProductResource;
use QuantumTecnology\FalconDataHub\Resources\Subscriptions\SubscriptionResource;
use QuantumTecnology\FalconDataHub\Resources\Usage\UsageResource;
use QuantumTecnology\FalconDataHub\Resources\Validation\ValidateResource;
use QuantumTecnology\FalconDataHub\Resources\Xml\XmlResource;

final class FalconClient
{
    private HttpClientInterface $http;
    private TokenManager $tokenManager;
    private FalconConfig $config;

    public function __construct(FalconConfig $config)
    {
        $this->config = $config;
        $this->http   = new CurlHttpClient($config);

        $tokenStore = $config->getCache() !== null
            ? new CacheTokenStore($config->getCache())
            : new TokenStore();

        $this->tokenManager = new TokenManager($this->http, $config, $tokenStore);
    }

    public function auth(): AuthResource
    {
        return new AuthResource($this->http, $this->tokenManager, $this->config);
    }

    public function cep(): CepResource
    {
        return new CepResource($this->http, $this->tokenManager, $this->config);
    }

    public function cnpj(): CnpjResource
    {
        return new CnpjResource($this->http, $this->tokenManager, $this->config);
    }

    public function cnae(): CnaeResource
    {
        return new CnaeResource($this->http, $this->tokenManager, $this->config);
    }

    public function ncm(): NcmResource
    {
        return new NcmResource($this->http, $this->tokenManager, $this->config);
    }

    public function vehicle(): VehicleResource
    {
        return new VehicleResource($this->http, $this->tokenManager, $this->config);
    }

    public function action(): ActionResource
    {
        return new ActionResource($this->http, $this->tokenManager, $this->config);
    }

    public function ip(): IpResource
    {
        return new IpResource($this->http, $this->tokenManager, $this->config);
    }

    public function cities(): CityResource
    {
        return new CityResource($this->http, $this->tokenManager, $this->config);
    }

    public function states(): StateResource
    {
        return new StateResource($this->http, $this->tokenManager, $this->config);
    }

    public function validate(): ValidateResource
    {
        return new ValidateResource($this->http, $this->tokenManager, $this->config);
    }

    public function brasil(): BrasilResource
    {
        return new BrasilResource($this->http, $this->tokenManager, $this->config);
    }

    public function bcb(): BcbResource
    {
        return new BcbResource($this->http, $this->tokenManager, $this->config);
    }

    public function fipe(): FipeResource
    {
        return new FipeResource($this->http, $this->tokenManager, $this->config);
    }

    public function fiscal(): FiscalResource
    {
        return new FiscalResource($this->http, $this->tokenManager, $this->config);
    }

    public function products(): ProductResource
    {
        return new ProductResource($this->http, $this->tokenManager, $this->config);
    }

    public function delivery(): DeliveryResource
    {
        return new DeliveryResource($this->http, $this->tokenManager, $this->config);
    }

    public function xml(): XmlResource
    {
        return new XmlResource($this->http, $this->tokenManager, $this->config);
    }

    public function creditCards(): CreditCardResource
    {
        return new CreditCardResource($this->http, $this->tokenManager, $this->config);
    }

    public function plans(): PlanResource
    {
        return new PlanResource($this->http, $this->tokenManager, $this->config);
    }

    public function subscriptions(): SubscriptionResource
    {
        return new SubscriptionResource($this->http, $this->tokenManager, $this->config);
    }

    public function usage(): UsageResource
    {
        return new UsageResource($this->http, $this->tokenManager, $this->config);
    }

    public function apiKeys(): ApiKeyResource
    {
        return new ApiKeyResource($this->http, $this->tokenManager, $this->config);
    }

    public function accessSessions(): AccessSessionResource
    {
        return new AccessSessionResource($this->http, $this->tokenManager, $this->config);
    }
}
